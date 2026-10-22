<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$textLength = static fn(string $value): int =>
    function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);

$storeId = filter_var(
    $_GET['store'] ?? $_POST['store_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($storeId === false || $storeId === null) {
    http_response_code(404);
    exit('Store not found.');
}

$stmt = $pdo->prepare('SELECT id, name FROM stores WHERE id = ?');
$stmt->execute([$storeId]);
$store = $stmt->fetch();

if (!$store) {
    http_response_code(404);
    exit('Store not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['customer_name'] ?? ''));
    $phone = trim((string)($_POST['customer_phone'] ?? ''));
    $fulfillment = (string)($_POST['fulfillment'] ?? 'pickup');
    $address = trim((string)($_POST['customer_address'] ?? ''));
    $notes = trim((string)($_POST['notes'] ?? ''));
    $items = $_POST['items'] ?? [];

    if (
        $name === ''
        || $textLength($name) > 120
        || $phone === ''
        || $textLength($phone) > 40
        || !in_array($fulfillment, ['pickup', 'delivery'], true)
        || ($fulfillment === 'delivery' && $address === '')
        || $textLength($address) > 255
        || $textLength($notes) > 2000
        || !is_array($items)
    ) {
        flash('error', 'Please enter your name, phone number, and required order details.');
        redirect('customer.php?store=' . $storeId);
    }

    $quantities = [];

    foreach ($items as $id => $quantity) {
        $id = (int)$id;
        $quantity = filter_var(
            $quantity,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1, 'max_range' => 1000]]
        );

        if ($id > 0 && $quantity !== false) {
            $quantities[$id] = $quantity;
        }
    }

    if (!$quantities || count($quantities) > 50) {
        flash('error', 'Please add between 1 and 50 different products to your order.');
        redirect('customer.php?store=' . $storeId);
    }

    try {
        $pdo->beginTransaction();

        $ids = array_keys($quantities);
        $marks = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare(
            "SELECT id, name, price, stock_qty
             FROM products
             WHERE store_id = ?
               AND is_active = 1
               AND id IN ($marks)
             FOR UPDATE"
        );
        $stmt->execute(array_merge([$storeId], $ids));
        $products = $stmt->fetchAll();

        if (count($products) !== count($quantities)) {
            throw new RuntimeException(
                'One or more products are no longer available. Please refresh and try again.'
            );
        }

        $total = 0.0;

        foreach ($products as $product) {
            $quantity = $quantities[$product['id']];

            if ((int)$product['stock_qty'] < $quantity) {
                throw new RuntimeException(
                    'There is not enough stock for ' . $product['name'] . '. Please update the quantity.'
                );
            }

            $total += (float)$product['price'] * $quantity;
        }

        $stmt = $pdo->prepare(
            "INSERT INTO orders
                (store_id, customer_name, customer_contact, fulfillment,
                 customer_address, note, total, payment_method, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'cash', 'pending')"
        );
        $stmt->execute([
            $storeId,
            $name,
            $phone,
            $fulfillment,
            $fulfillment === 'delivery' ? $address : null,
            $notes ?: null,
            round($total, 2)
        ]);

        $orderId = (int)$pdo->lastInsertId();

        $insert = $pdo->prepare(
            'INSERT INTO order_items
                (store_id, order_id, product_id, product_name,
                 quantity, unit_price, line_total)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($products as $product) {
            $quantity = $quantities[$product['id']];

            $insert->execute([
                $storeId,
                $orderId,
                $product['id'],
                $product['name'],
                $quantity,
                $product['price'],
                round((float)$product['price'] * $quantity, 2)
            ]);
        }

        $pdo->commit();

        $_SESSION['public_order'] = [
            'id' => $orderId,
            'store_id' => $storeId
        ];

        redirect('customer.php?store=' . $storeId . '&ordered=' . $orderId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash(
            'error',
            $e instanceof RuntimeException
                ? $e->getMessage()
                : 'We could not send your order. Please try again.'
        );

        redirect('customer.php?store=' . $storeId);
    }
}

$search = trim((string)($_GET['q'] ?? ''));

$sql = 'SELECT id, name, sku, category, price, stock_qty, image
        FROM products
        WHERE store_id = ?
          AND is_active = 1
          AND stock_qty > 0';

$params = [$storeId];

if ($search !== '') {
    $sql .= ' AND (name LIKE ? OR sku LIKE ? OR category LIKE ?)';
    $term = '%' . $search . '%';
    array_push($params, $term, $term, $term);
}

$sql .= ' ORDER BY name LIMIT 100';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$ordered = isset($_GET['ordered'], $_SESSION['public_order'])
    && (int)$_GET['ordered'] === (int)$_SESSION['public_order']['id']
    && (int)$_SESSION['public_order']['store_id'] === (int)$storeId;

if ($ordered) {
    $orderId = (int)$_SESSION['public_order']['id'];
    unset($_SESSION['public_order']);
}

$flash = take_flash();
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($store['name']) ?> · Order online</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="pub-topbar">
  <div class="pub-brand">
    <span class="brand-mark">T</span>
    <span><?= e($store['name']) ?><small>ORDER ONLINE</small></span>
  </div>
  <span style="color:rgba(255,255,255,.6);font-size:12px">No account needed</span>
  <a class="btn btn-light" href="stores.php">Browse stores</a>
</header>

<main class="pub-page-wrap">
    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($ordered): ?>
        <section class="panel" style="max-width:720px;margin:24px auto;text-align:center;padding:40px 24px;border:none;box-shadow:var(--shadow-md)">
            <div class="stat-icon" style="margin:0 auto 14px;font-size:26px;color:var(--green-dark)">✓</div>
            <div class="eyebrow">Order received</div>
            <h1>Thanks for your order</h1>
            <p class="subtitle">
                Your order number is <strong>#<?= $orderId ?></strong>.
                The store will contact you to confirm availability and pickup or delivery details.
            </p>
            <p class="subtitle" style="margin-top:12px">This order is not paid yet. Please wait for the store to confirm it.</p>
            <a class="btn btn-primary" style="margin-top:18px" href="customer.php?store=<?= (int)$storeId ?>">Continue shopping</a>
        </section>
    <?php endif; ?>

    <?php if (!$ordered): ?>
        <div class="page-head" style="display:flex;align-items:center;justify-content:space-between;gap:12px">
          <div>
            <div class="eyebrow">Welcome to <?= e($store['name']) ?></div>
            <h1>Shop products</h1>
            <p class="subtitle">Add products to your order and send it to the store—no sign-in required.</p>
          </div>
        </div>

        <form class="search-row premium-search" method="get">
          <input type="hidden" name="store" value="<?= (int)$storeId ?>">
          <div class="search search-premium">
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search products">
          </div>
          <button class="btn btn-light">Search</button>
          <?php if ($search): ?><a class="btn btn-light" href="customer.php?store=<?= (int)$storeId ?>">Clear</a><?php endif; ?>
        </form>

        <div class="pos-layout premium-customer-grid">
            <section class="panel product-panel premium-panel-surface">
                <div class="product-grid" id="productGrid">
                    <?php foreach ($products as $product): ?>
                        <button type="button" class="product-card premium-product-card" onclick='addProduct(<?= json_encode(
                            $product,
                            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                        ) ?>)'>
                            <img src="<?= $product['image'] ? e($product['image']) : 'assets/no-image.svg' ?>" alt="">
                            <strong><?= e($product['name']) ?></strong>
                            <span><?= money($product['price']) ?></span>
                            <small><?= e($product['category'] ?: 'Available') ?> · <?= (int)$product['stock_qty'] ?> in stock</small>
                        </button>
                    <?php endforeach; ?>

                    <?php if (!$products): ?>
                        <div class="empty" style="grid-column:1/-1;padding:30px">No products are available right now.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="panel order-panel premium-checkout-panel">
                <div class="panel-head"><h2>Your order</h2><button class="btn btn-light btn-sm" type="button" onclick="clearCart()">Clear</button></div>

                <div id="cart" style="padding:10px 16px 0"><div class="cart-empty-state"><div>🛒</div><strong>Your cart is empty</strong><span>Add a product to begin</span></div></div>

                <form method="post" id="orderForm" style="padding:0 16px 16px">
                    <?= csrf_field() ?>
                    <input type="hidden" name="store_id" value="<?= (int)$storeId ?>">
                    <div id="cartInputs"></div>

                    <div class="total-line grand-total"><span>Estimated total</span><strong id="total">₱0.00</strong></div>

                    <div class="field"><label for="customer_name">Your name</label><input id="customer_name" name="customer_name" required maxlength="120" autocomplete="name"></div>

                    <div class="field"><label for="customer_phone">Phone number</label><input id="customer_phone" name="customer_phone" required maxlength="40" autocomplete="tel" placeholder="So the store can contact you"></div>

                    <div class="field"><label for="fulfillment">Pickup or delivery?</label><select id="fulfillment" name="fulfillment" onchange="toggleAddress()"><option value="pickup">Pickup at store</option><option value="delivery">Delivery</option></select></div>

                    <div class="field" id="addressField" style="display:none"><label for="customer_address">Delivery address</label><input id="customer_address" name="customer_address" maxlength="255" autocomplete="street-address"></div>

                    <div class="field"><label for="notes">Notes (optional)</label><input id="notes" name="notes" maxlength="2000" placeholder="Preferences or order instructions"></div>

                    <button id="submitOrder" class="btn btn-primary btn-block" disabled>Send order to store</button>

                    <p class="order-note">No payment is collected online. The store will confirm your order with you.</p>
                </form>
            </section>
        </div>
    <?php endif; ?>
</main>
<footer class="footer">Online ordering for <?= e($store['name']) ?> · Powered by TindaHub</footer>

<?php if ($ordered): ?>
<script>
try { localStorage.removeItem('tindahub-public-cart-<?= (int)$storeId ?>'); } catch (e) {}
</script>
<?php endif; ?>

<?php if (!$ordered): ?>
<script>
const cart = {};
const key = 'tindahub-public-cart-<?= (int)$storeId ?>';

function formatMoney(amount) {
    return '₱' + Number(amount).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
function addProduct(product) {
    const productId = Number(product.id);
    const stock = Number(product.stock_qty) || 0;

    if (!Number.isInteger(productId) || productId <= 0) {
        return;
    }

    if (!cart[productId]) {
        cart[productId] = { product, qty: 0 };
    }

    if (cart[productId].qty < stock) {
        cart[productId].qty += 1;
    }

    render();
}
function change(id, delta) {
    const productId = Number(id);
    if (!cart[productId]) {
        return;
    }

    const nextQty = Number(cart[productId].qty) + Number(delta);
    const stock = Number(cart[productId].product.stock_qty) || 0;

    if (nextQty <= 0) {
        delete cart[productId];
    } else {
        cart[productId].qty = Math.min(nextQty, stock);
    }

    render();
}
function removeProduct(id) {
    delete cart[Number(id)];
    render();
}
function clearCart() {
    Object.keys(cart).forEach((id) => delete cart[id]);
    render();
}
function safeText(value) {
    const element = document.createElement('span');
    element.textContent = value;
    return element.innerHTML;
}
function render() {
    const ids = Object.keys(cart).sort((a, b) => Number(a) - Number(b));
    const box = document.getElementById('cart');
    const inputs = document.getElementById('cartInputs');

    inputs.innerHTML = '';

    if (!ids.length) {
        box.innerHTML = '<div class="cart-empty-state"><div>🛒</div><strong>Your cart is empty</strong><span>Add a product to begin</span></div>';
    } else {
        box.innerHTML = ids.map((id) => {
            const row = cart[id];
            const product = row && row.product ? row.product : null;

            if (!product) return '';

            return `<div class="cart-item-light">
                <div class="cic-top">
                    <div>
                        <div class="cic-name">${safeText(product.name)}</div>
                        <div class="cic-unit">${formatMoney(product.price)} each</div>
                    </div>
                    <button type="button" class="cic-remove" onclick="removeProduct(${Number(id)})" title="Remove item">&times;</button>
                </div>
                <div class="cic-bottom">
                    <div class="cic-stepper">
                        <button type="button" class="cic-step-btn" onclick="change(${Number(id)},-1)">−</button>
                        <span class="cic-step-qty">${row.qty}</span>
                        <button type="button" class="cic-step-btn" onclick="change(${Number(id)},1)">+</button>
                    </div>
                    <div class="cic-subtotal">${formatMoney(Number(product.price) * row.qty)}</div>
                </div>
            </div>`;
        }).join('');

        for (const id of ids) {
            const qty = Number(cart[id].qty);
            if (!Number.isFinite(qty) || qty <= 0) {
                continue;
            }

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${id}]`;
            input.value = String(qty);
            inputs.appendChild(input);
        }
    }

    const total = ids.reduce((sum, id) => {
        const row = cart[id];
        if (!row || !row.product) return sum;
        return sum + Number(row.product.price) * Number(row.qty || 0);
    }, 0);

    document.getElementById('total').textContent = formatMoney(total);
    document.getElementById('submitOrder').disabled = ids.length === 0;

    try {
        localStorage.setItem(key, JSON.stringify(cart));
    } catch (e) {}
}

function toggleAddress() {
    const delivery = document.getElementById('fulfillment').value === 'delivery';
    document.getElementById('addressField').style.display = delivery ? '' : 'none';
    document.getElementById('customer_address').required = delivery;
}

try {
    const saved = JSON.parse(localStorage.getItem(key) || '{}');

    for (const [id, row] of Object.entries(saved)) {
        const productId = Number(id);
        const qty = Number(row && row.qty ? row.qty : 0);
        const product = row && row.product ? row.product : null;

        if (Number.isInteger(productId) && productId > 0 && product && Number.isFinite(qty) && qty > 0) {
            cart[productId] = { product, qty: Math.min(qty, Number(product.stock_qty) || qty) };
        }
    }
} catch (e) {}

render();
toggleAddress();

document.getElementById('orderForm').addEventListener('submit', event => {
    if (!Object.keys(cart).length) {
        event.preventDefault();
        alert('Please add a product first.');
    }
});
</script>
<?php endif; ?>
</body>
</html>
