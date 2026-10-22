<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$sid = store_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = $_POST['items'] ?? [];
    $method = (string)($_POST['payment_method'] ?? 'cash');
    $cash = $_POST['cash_received'] ?? '';
    if (!is_array($items) || !in_array($method, ['cash', 'card', 'ewallet'], true)) {
        flash('error', 'Invalid checkout details.'); redirect('pos.php');
    }
    $clean = [];
    foreach ($items as $pid => $qty) {
        $pid = (int)$pid;
        $qty = filter_var($qty, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($pid > 0 && $qty !== false) $clean[$pid] = $qty;
    }
    if (!$clean) { flash('error', 'Add at least one product to the cart.'); redirect('pos.php'); }

    try {
        $pdo->beginTransaction();
        $ids = array_keys($clean);
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id,name,price,cost,stock_qty FROM products WHERE store_id=? AND is_active=1 AND id IN ($marks) FOR UPDATE");
        $stmt->execute(array_merge([$sid], $ids));
        $found = $stmt->fetchAll();
        if (count($found) !== count($clean)) throw new RuntimeException('One or more products are no longer available.');

        $total = 0.0;
        foreach ($found as $product) {
            $qty = $clean[$product['id']];
            if ((int)$product['stock_qty'] < $qty) throw new RuntimeException('Not enough stock for ' . $product['name'] . '.');
            $total += (float)$product['price'] * $qty;
        }
        $cashReceived = null; $change = 0.0;
        if ($method === 'cash') {
            if (!money_input($cash) || (float)$cash + 0.00001 < $total) throw new RuntimeException('Cash received must be at least the sale total.');
            $cashReceived = round((float)$cash, 2); $change = round($cashReceived - $total, 2);
        }
        $sale = $pdo->prepare('INSERT INTO sales (store_id,user_id,total,payment_method,cash_received,change_due) VALUES (?,?,?,?,?,?)');
        $sale->execute([$sid, $user['id'], $total, $method, $cashReceived, $change]);
        $saleId = (int)$pdo->lastInsertId();
        $insert = $pdo->prepare('INSERT INTO sale_items (store_id,sale_id,product_id,product_name,quantity,unit_price,unit_cost,line_total) VALUES (?,?,?,?,?,?,?,?)');
        $update = $pdo->prepare('UPDATE products SET stock_qty=stock_qty-? WHERE id=? AND store_id=?');
        foreach ($found as $product) {
            $qty = $clean[$product['id']];
            $insert->execute([$sid, $saleId, $product['id'], $product['name'], $qty, $product['price'], $product['cost'], round((float)$product['price'] * $qty, 2)]);
            $update->execute([$qty, $product['id'], $sid]);
        }
        $pdo->commit();
        $_SESSION['last_sale'] = ['id' => $saleId, 'total' => $total, 'change' => $change, 'method' => $method];
        redirect('pos.php?done=' . $saleId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Checkout could not be completed. Please try again.');
        redirect('pos.php');
    }
}

$search = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT id,name,sku,price,stock_qty,category,image FROM products WHERE store_id=? AND is_active=1 AND stock_qty>0';
$params = [$sid];
if ($search !== '') { $sql .= ' AND (name LIKE ? OR sku LIKE ?)'; $term = '%' . $search . '%'; $params[] = $term; $params[] = $term; }
$sql .= ' ORDER BY name LIMIT 100';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $products = $stmt->fetchAll();
require __DIR__ . '/includes/layout.php';
page_header('POS checkout', 'pos');
$completed = isset($_GET['done'], $_SESSION['last_sale']) && (int)$_GET['done'] === (int)$_SESSION['last_sale']['id'];
if ($completed) { $done = $_SESSION['last_sale']; unset($_SESSION['last_sale']); ?>
<div class="page-head"><div><div class="eyebrow">Checkout</div><h1>Point of sale</h1><p class="subtitle">Search or tap products to add them. Inventory stock updates when the sale completes.</p></div><a href="inventory.php" class="btn btn-light">Manage inventory</a></div>
<div class="alert success">Sale #<?= (int)$done['id'] ?> completed for <?= money($done['total']) ?><?php if ($done['method'] === 'cash'): ?> · Change: <?= money($done['change']) ?><?php endif; ?>. <button class="btn btn-light" onclick="window.print()">Print receipt</button></div>
<?php } else { ?>
<div class="page-head"><div><div class="eyebrow">Checkout</div><h1>Point of sale</h1><p class="subtitle">Search or tap products to add them. Inventory stock updates when the sale completes.</p></div><a href="inventory.php" class="btn btn-light">Manage inventory</a></div>
<?php } ?>
<div class="pos-layout">
  <section class="panel">
    <form class="search-row" method="get"><div class="search"><input type="search" name="q" value="<?= e($search) ?>" placeholder="Find by product or SKU"></div><button class="btn btn-light">Search</button></form>
    <div class="product-grid" id="productGrid">
      <?php foreach ($products as $product): ?><button type="button" class="product-card" onclick='addToCart(<?= json_encode($product, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'><img src="<?= $product['image'] ? e($product['image']) : 'assets/no-image.svg' ?>" alt=""><strong><?= e($product['name']) ?></strong><span><?= money($product['price']) ?></span><small><?= (int)$product['stock_qty'] ?> in stock<?= $product['sku'] ? ' · ' . e($product['sku']) : '' ?></small></button><?php endforeach; ?>
      <?php if (!$products): ?><div class="empty" style="grid-column:1/-1">No available products. Add stock in Inventory.</div><?php endif; ?>
    </div>
  </section>
  <section class="panel order-panel">
    <div class="panel-head"><h2>Current sale</h2><button type="button" class="btn btn-light btn-sm" onclick="clearCart()">Clear</button></div>
    <div id="cart" style="padding:10px 14px 0"><div class="cart-empty-state"><div>🛒</div><strong>Cart is empty</strong><span>Tap a product to begin</span></div></div>
    <form id="checkoutForm" method="post" style="padding:0 14px 16px"><?= csrf_field() ?><div id="hiddenItems"></div>
      <div class="total-line grand-total"><span>Total due</span><strong id="grandTotal">₱0.00</strong></div>
      <div class="field"><label for="payment">Payment method</label><select id="payment" name="payment_method" onchange="toggleCash()"><option value="cash">Cash</option><option value="card">Card</option><option value="ewallet">E-wallet</option></select></div>
      <div class="field" id="cashField"><label for="cash">Cash received (₱)</label><input id="cash" name="cash_received" type="number" min="0" step="0.01" placeholder="Enter amount tendered" oninput="showChange()"></div>
      <div class="total-line" id="changeRow" style="display:none"><span>Change</span><strong id="changeDue">₱0.00</strong></div>
      <button class="btn btn-primary btn-block" id="checkoutBtn" type="submit" disabled>Complete sale</button>
    </form>
  </section>
</div>
<script>
const cart = {};
const currency = '₱';
const storageKey = 'tindahub-cart-<?= $sid ?>';
const saleCompleted = <?= $completed ? 'true' : 'false' ?>;
function fmt(n) { return currency + Number(n).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
function persistCart() { try { localStorage.setItem(storageKey, JSON.stringify(cart)); } catch (_) {} }
function addToCart(product) {
  if (!cart[product.id]) cart[product.id] = {product, qty: 0};
  if (cart[product.id].qty < Number(product.stock_qty)) cart[product.id].qty++;
  renderCart();
}
function changeQty(id, delta) {
  cart[id].qty += delta;
  if (cart[id].qty <= 0) delete cart[id];
  else if (cart[id].qty > Number(cart[id].product.stock_qty)) cart[id].qty = Number(cart[id].product.stock_qty);
  renderCart();
}
function clearCart() { for (const id of Object.keys(cart)) delete cart[id]; renderCart(); }
function removePosItem(id) { delete cart[id]; renderCart(); }
function renderCart() {
  const box = document.getElementById('cart'), hidden = document.getElementById('hiddenItems');
  hidden.innerHTML = '';
  const ids = Object.keys(cart);
  if (!ids.length) {
    box.innerHTML = `<div class="cart-empty-state"><div>🛒</div><strong>Cart is empty</strong><span>Tap a product to begin</span></div>`;
  } else {
    box.innerHTML = ids.map(id => {
      const row = cart[id], p = row.product;
      return `<div class="cart-item-light">
        <div class="cic-top">
          <div>
            <div class="cic-name" title="${escapeText(p.name)}">${escapeText(p.name)}</div>
            <div class="cic-unit">${fmt(p.price)} each</div>
          </div>
          <button type="button" class="cic-remove" onclick="removePosItem(${id})" title="Remove item">&times;</button>
        </div>
        <div class="cic-bottom">
          <div class="cic-stepper">
            <button type="button" class="cic-step-btn" onclick="changeQty(${Number(id)},-1)">−</button>
            <span class="cic-step-qty">${row.qty}</span>
            <button type="button" class="cic-step-btn" onclick="changeQty(${Number(id)},1)">+</button>
          </div>
          <div class="cic-subtotal">${fmt(Number(p.price) * row.qty)}</div>
        </div>
      </div>`;
    }).join('');
    for (const id of ids) { const input = document.createElement('input'); input.type = 'hidden'; input.name = 'items[' + id + ']'; input.value = cart[id].qty; hidden.appendChild(input); }
  }
  const total = ids.reduce((sum, id) => sum + Number(cart[id].product.price) * cart[id].qty, 0);
  document.getElementById('grandTotal').textContent = fmt(total);
  document.getElementById('checkoutBtn').disabled = !ids.length;
  persistCart(); showChange();
}
function escapeText(value) { const node = document.createElement('span'); node.textContent = value; return node.innerHTML; }
function toggleCash() {
  const cash = document.getElementById('payment').value === 'cash';
  document.getElementById('cashField').style.display = cash ? '' : 'none';
  document.getElementById('changeRow').style.display = cash ? 'flex' : 'none';
  showChange();
}
function showChange() {
  const total = Object.keys(cart).reduce((sum, id) => sum + Number(cart[id].product.price) * cart[id].qty, 0);
  const received = Number(document.getElementById('cash').value || 0);
  document.getElementById('changeDue').textContent = fmt(Math.max(0, received - total));
}
document.getElementById('checkoutForm').addEventListener('submit', event => {
  if (!Object.keys(cart).length) { event.preventDefault(); return; }
  const total = Object.keys(cart).reduce((sum, id) => sum + Number(cart[id].product.price) * cart[id].qty, 0);
  if (document.getElementById('payment').value === 'cash' && Number(document.getElementById('cash').value) < total) { event.preventDefault(); alert('Cash received must cover the total.'); }
});
if (saleCompleted) { try { localStorage.removeItem(storageKey); } catch (_) {} }
else {
  try { const saved = JSON.parse(localStorage.getItem(storageKey) || '{}'); for (const [id, row] of Object.entries(saved)) { if (row && row.product && Number.isInteger(row.qty) && row.qty > 0) cart[id] = row; } } catch (_) {}
}
renderCart(); toggleCash();
</script>
<?php page_footer(); ?>
