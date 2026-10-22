<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login();
$sid = store_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? '');
    $allowed = ['confirmed','paid','ready','done','cancelled'];
    if ($id > 0 && in_array($status, $allowed, true)) {
        $q = $pdo->prepare('UPDATE orders SET status=? WHERE id=? AND store_id=?');
        $q->execute([$status, $id, $sid]);
        flash('success', 'Order #' . $id . ' marked as ' . $status . '.');
    }
    redirect('orders.php');
}

$statusFilter = $_GET['status'] ?? 'pending';
$allowed = ['all','pending','confirmed','paid','ready','done','cancelled'];
if (!in_array($statusFilter, $allowed, true)) $statusFilter = 'pending';
$sort = (string)($_GET['sort'] ?? 'created_at');
$dir = strtoupper((string)($_GET['dir'] ?? 'DESC'));
$sortColumns = ['id' => 'o.id', 'customer_name' => 'o.customer_name', 'created_at' => 'o.created_at', 'total' => 'total', 'status' => 'o.status'];
if (!isset($sortColumns[$sort]) || !in_array($dir, ['ASC','DESC'], true)) { $sort = 'created_at'; $dir = 'DESC'; }
$sql = 'SELECT o.id,o.customer_name,o.customer_contact,o.status,o.note,o.created_at,o.payment_method,
        COALESCE(SUM(i.line_total),0) total, COUNT(i.id) item_count
        FROM orders o LEFT JOIN order_items i ON i.order_id=o.id
        WHERE o.store_id=?';
$params = [$sid];
if ($statusFilter !== 'all') { $sql .= ' AND o.status=?'; $params[] = $statusFilter; }
$sql .= ' GROUP BY o.id,o.customer_name,o.customer_contact,o.status,o.note,o.created_at,o.payment_method ORDER BY ' . $sortColumns[$sort] . ' ' . $dir . ', o.created_at DESC LIMIT 200';
$st = $pdo->prepare($sql); $st->execute($params); $orders = $st->fetchAll();

$detail = null;
if (isset($_GET['id'])) {
    $q = $pdo->prepare('SELECT o.*,COALESCE(SUM(i.line_total),0) total FROM orders o LEFT JOIN order_items i ON i.order_id=o.id WHERE o.store_id=? AND o.id=? GROUP BY o.id,o.store_id,o.customer_name,o.customer_contact,o.status,o.note,o.payment_method,o.created_at,o.updated_at');
    $q->execute([$sid, (int)$_GET['id']]); $detail = $q->fetch();
    if ($detail) {
        $q = $pdo->prepare('SELECT product_name,quantity,unit_price,line_total FROM order_items WHERE order_id=? ORDER BY id');
        $q->execute([$detail['id']]); $detail['items'] = $q->fetchAll();
    }
}

$counts = $pdo->prepare('SELECT status, COUNT(*) n FROM orders WHERE store_id=? GROUP BY status');
$counts->execute([$sid]); $countMap = [];
foreach ($counts->fetchAll() as $r) $countMap[$r['status']] = (int)$r['n'];

$sortLink = function(string $field, string $label) use ($statusFilter, $sort, $dir) { $nextDir = ($sort === $field && $dir === 'ASC') ? 'DESC' : 'ASC'; return 'orders.php?status=' . urlencode($statusFilter) . '&sort=' . urlencode($field) . '&dir=' . urlencode($nextDir); };

require __DIR__ . '/includes/layout.php';
page_header('Orders', 'orders');

$statusColors = ['pending'=>'low','confirmed'=>'','paid'=>'','ready'=>'low','done'=>'','cancelled'=>'out'];
$nextActions = [
    'pending'   => [['status'=>'confirmed','label'=>'Confirm'],['status'=>'cancelled','label'=>'Cancel']],
    'confirmed' => [['status'=>'paid','label'=>'Mark paid'],['status'=>'cancelled','label'=>'Cancel']],
    'paid'      => [['status'=>'ready','label'=>'Mark ready']],
    'ready'     => [['status'=>'done','label'=>'Mark done']],
    'done'      => [],
    'cancelled' => [],
];
?>
<div class="page-head">
  <div>
    <div class="eyebrow">Customer orders</div>
    <h1>Orders</h1>
    <p class="subtitle">Manage orders placed by customers online.</p>
  </div>
  <a class="btn btn-light" href="customer.php?store=<?= $sid ?>" target="_blank">View customer page ↗</a>
</div>

<div class="tab-pills">
  <?php foreach (['all','pending','confirmed','paid','ready','done','cancelled'] as $s): ?>
  <a class="tab-pill <?= $statusFilter===$s?'active':'' ?>" href="orders.php?status=<?= $s ?>">
    <?= ucfirst($s) ?><?php if(isset($countMap[$s])): ?> <span class="count">(<?= $countMap[$s] ?>)</span><?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<section class="panel orders-panel">
  <div class="table-wrap orders-table-wrap">
    <table>
      <thead><tr><th><a class="sort-link" href="<?= $sortLink('id', 'Order') ?>">Order <?= ($sort === 'id' ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th><a class="sort-link" href="<?= $sortLink('customer_name', 'Customer') ?>">Customer <?= ($sort === 'customer_name' ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th>Contact</th><th>Items</th><th>Payment</th><th><a class="sort-link" href="<?= $sortLink('total', 'Total') ?>">Total <?= ($sort === 'total' ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th><a class="sort-link" href="<?= $sortLink('status', 'Status') ?>">Status <?= ($sort === 'status' ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th><a class="sort-link" href="<?= $sortLink('created_at', 'Time') ?>">Time <?= ($sort === 'created_at' ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th></th></tr></thead>
      <tbody>
        <?php if (!$orders): ?>
          <tr><td colspan="9" class="empty">No orders found.</td></tr>
        <?php endif; foreach ($orders as $o): ?>
        <tr>
          <td class="product-cell">#<?= (int)$o['id'] ?></td>
          <td><?= e($o['customer_name']) ?></td>
          <td><?= e($o['customer_contact'] ?: '—') ?></td>
          <td><?= (int)$o['item_count'] ?> items</td>
          <td><span class="badge"><?= ucfirst($o['payment_method']) ?></span></td>
          <td class="product-cell"><?= money($o['total']) ?></td>
          <td><span class="badge <?= $statusColors[$o['status']] ?? '' ?>"><?= ucfirst($o['status']) ?></span></td>
          <td><?= e(date('M j · g:i A', strtotime($o['created_at']))) ?></td>
          <td style="white-space:nowrap">
            <a class="text-link" href="orders.php?status=<?= e($statusFilter) ?>&id=<?= (int)$o['id'] ?>">View</a>
            <?php foreach ($nextActions[$o['status']] ?? [] as $act): ?>
            <form method="post" style="display:inline;margin-left:6px">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
              <input type="hidden" name="status" value="<?= e($act['status']) ?>">
              <button class="btn btn-light" type="submit" style="font-size:11px;padding:6px 10px"><?= e($act['label']) ?></button>
            </form>
            <?php endforeach; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php if ($detail): ?>
<div class="modal-backdrop show"><section class="modal">
  <div class="modal-title">
    <h2>Order #<?= (int)$detail['id'] ?> · <?= e(ucfirst($detail['status'])) ?></h2>
    <a class="icon-button" href="orders.php?status=<?= e($statusFilter) ?>">×</a>
  </div>
  <p class="small-muted"><?= e(date('M j, Y · g:i A', strtotime($detail['created_at']))) ?></p>
  <p><strong>Customer:</strong> <?= e($detail['customer_name']) ?><?= $detail['customer_contact'] ? ' · ' . e($detail['customer_contact']) : '' ?></p>
  <p><strong>Payment:</strong> <span class="badge"><?= ucfirst($detail['payment_method']) ?></span></p>
  <?php if ($detail['note']): ?><p><strong>Note:</strong> <?= e($detail['note']) ?></p><?php endif; ?>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach ($detail['items'] as $it): ?>
        <tr><td><?= e($it['product_name']) ?></td><td><?= (int)$it['quantity'] ?></td><td><?= money($it['unit_price']) ?></td><td><?= money($it['line_total']) ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="total-line grand-total"><span>Total</span><strong><?= money($detail['total']) ?></strong></div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px">
    <?php foreach ($nextActions[$detail['status']] ?? [] as $act): ?>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)$detail['id'] ?>">
      <input type="hidden" name="status" value="<?= e($act['status']) ?>">
      <button class="btn btn-primary" type="submit"><?= e($act['label']) ?></button>
    </form>
    <?php endforeach; ?>
    <a class="btn btn-light" href="orders.php?status=<?= e($statusFilter) ?>">Close</a>
  </div>
</section></div>
<?php endif; ?>

<?php page_footer(); ?>
