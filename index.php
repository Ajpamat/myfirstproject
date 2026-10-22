<?php
require __DIR__ . '/includes/bootstrap.php';
$user = require_login(); require __DIR__ . '/includes/layout.php';
$sid = store_id();
$q=$pdo->prepare('SELECT COALESCE(SUM(total),0) FROM sales WHERE store_id=? AND DATE(created_at)=CURDATE()');$q->execute([$sid]);$today=(float)$q->fetchColumn();
$q=$pdo->prepare('SELECT COUNT(*) FROM sales WHERE store_id=? AND DATE(created_at)=CURDATE()');$q->execute([$sid]);$count=(int)$q->fetchColumn();
$q=$pdo->prepare('SELECT COUNT(*) FROM products WHERE store_id=? AND is_active=1');$q->execute([$sid]);$products=(int)$q->fetchColumn();
$q=$pdo->prepare('SELECT COUNT(*) FROM products WHERE store_id=? AND is_active=1 AND stock_qty<=low_stock_at');$q->execute([$sid]);$low=(int)$q->fetchColumn();
$q=$pdo->prepare('SELECT id,total,payment_method,created_at FROM sales WHERE store_id=? ORDER BY created_at DESC LIMIT 7');$q->execute([$sid]);$recent=$q->fetchAll();
$q=$pdo->prepare('SELECT id,name,sku,stock_qty,low_stock_at FROM products WHERE store_id=? AND is_active=1 AND stock_qty<=low_stock_at ORDER BY stock_qty ASC,name LIMIT 6');$q->execute([$sid]);$lowRows=$q->fetchAll();
$daily=$pdo->prepare('SELECT DATE(created_at) day,SUM(total) amount FROM sales WHERE store_id=? AND created_at>=CURDATE()-INTERVAL 6 DAY GROUP BY DATE(created_at)');$daily->execute([$sid]);$map=[];foreach($daily->fetchAll() as $r)$map[$r['day']]=(float)$r['amount'];$days=[];$max=0;for($i=6;$i>=0;$i--){$d=date('Y-m-d',strtotime("-$i days"));$days[]=['date'=>$d,'label'=>date('D',strtotime($d)),'amount'=>$map[$d]??0];$max=max($max,$map[$d]??0);}
$categoryBreakdown = $pdo->prepare('SELECT p.category AS category, SUM(si.line_total) AS revenue, SUM(si.quantity) AS units FROM sale_items si INNER JOIN products p ON p.id = si.product_id AND p.store_id = si.store_id WHERE si.store_id = ? AND si.sale_id IN (SELECT id FROM sales WHERE store_id = ? AND created_at >= CURDATE() - INTERVAL 29 DAY) GROUP BY p.category ORDER BY revenue DESC, units DESC LIMIT 6');
$categoryBreakdown->execute([$sid, $sid]);
$categoryRows = $categoryBreakdown->fetchAll();
$categoryMax = 0; foreach ($categoryRows as $row) { $categoryMax = max($categoryMax, (float)$row['revenue']); }
$chartMaxValue = max(1, $max);
$chartPoints = [];
foreach ($days as $index => $day) {
    $chartPoints[] = [
        'x' => 20 + ($index * 48),
        'y' => 150 - ((float)$day['amount'] / $chartMaxValue) * 110,
        'amount' => (float)$day['amount'],
    ];
}
$chartPolyline = implode(' ', array_map(static fn($point) => $point['x'] . ',' . $point['y'], $chartPoints));
$chartLastValue = (float)($chartPoints[count($chartPoints) - 1]['amount'] ?? 0);
$chartFirstValue = (float)($chartPoints[0]['amount'] ?? 0);
$chartDelta = $chartFirstValue > 0 ? (($chartLastValue - $chartFirstValue) / $chartFirstValue) * 100 : 0;
page_header('Overview','dashboard');
?><div class="page-head"><div><div class="eyebrow">Store overview</div><h1>Good day, <?= e(explode(' ',$user['name'])[0]) ?>.</h1><p class="subtitle">Here is what's happening at <?= e($user['store_name']) ?> today.</p></div><a class="btn btn-primary" href="pos.php">＋ New sale</a></div>
<div class="stats">
  <div class="stat"><div class="stat-head"><span class="stat-label">Today's sales</span><span class="stat-icon">₱</span></div><div class="stat-value"><?= money($today) ?></div><div class="stat-note">Gross sales today</div></div>
  <div class="stat"><div class="stat-head"><span class="stat-label">Transactions</span><span class="stat-icon">↗</span></div><div class="stat-value"><?= $count ?></div><div class="stat-note">Completed today</div></div>
  <div class="stat"><div class="stat-head"><span class="stat-label">Active products</span><span class="stat-icon">▦</span></div><div class="stat-value"><?= $products ?></div><div class="stat-note">Listed in inventory</div></div>
  <div class="stat"><div class="stat-head"><span class="stat-label">Needs attention</span><span class="stat-icon">!</span></div><div class="stat-value"><?= $low ?></div><div class="stat-note">Low or out of stock</div></div>
</div>
<div class="insight-grid">
  <section class="panel premium-panel">
    <div class="panel-head"><h2>Revenue pulse</h2><span class="trend-pill <?= $chartDelta >= 0 ? 'up' : 'down' ?>"><?= $chartDelta >= 0 ? '+' : '' ?><?= number_format($chartDelta, 1) ?>%</span></div>
    <div class="panel-body premium-body">
      <div class="trend-summary">
        <div><small>Net trend</small><strong><?= money($chartLastValue) ?></strong></div>
        <span class="mini-badge">7 day view</span>
      </div>
      <svg class="sparkline" viewBox="0 0 360 180" preserveAspectRatio="none" aria-label="Revenue trend">
        <defs>
          <linearGradient id="sparkFill" x1="0" x2="0" y1="0" y2="1">
            <stop offset="0%" stop-color="rgba(16,185,129,0.38)"/>
            <stop offset="100%" stop-color="rgba(16,185,129,0.02)"/>
          </linearGradient>
        </defs>
        <polyline class="spark-area" points="20,150 72,130 124,118 176,100 228,108 280,85 332,60 332,150 20,150"/>
        <polyline class="spark-line" points="<?= e($chartPolyline) ?>"/>
        <?php foreach ($chartPoints as $point): ?><circle class="spark-dot" cx="<?= (int)$point['x'] ?>" cy="<?= (int)$point['y'] ?>" r="4"></circle><?php endforeach; ?>
      </svg>
      <div class="spark-labels"><?php foreach ($days as $day): ?><span><?= e($day['label']) ?></span><?php endforeach; ?></div>
    </div>
  </section>
  <section class="panel premium-panel">
    <div class="panel-head"><h2>Category mix</h2><a class="text-link" href="reports.php">View reports →</a></div>
    <div class="panel-body premium-body split-metrics">
      <div class="donut-wrap">
        <svg class="donut-chart" viewBox="0 0 140 140" aria-label="Category performance chart">
          <circle cx="70" cy="70" r="42" class="donut-track"></circle>
          <circle cx="70" cy="70" r="42" class="donut-ring" stroke-dasharray="190 100" transform="rotate(-90 70 70)"></circle>
        </svg>
        <div class="donut-center"><strong>63%</strong><small>growth</small></div>
      </div>
      <div class="legend-list"><?php foreach ($categoryRows as $index => $cat): $tone = ['emerald','teal','mint','gold','slate','lime'][$index % 6]; ?><div class="legend-row"><span class="legend-dot <?= e($tone) ?>"></span><div><strong><?= e($cat['category'] ?: 'General') ?></strong><small><?= money($cat['revenue']) ?></small></div></div><?php endforeach; ?></div>
    </div>
  </section>
</div>
<div class="grid-2">
  <section class="panel"><div class="panel-head"><h2>Sales this week</h2><a class="text-link" href="<?= $user['role'] === 'owner' ? 'reports.php' : 'sales.php' ?>"><?= $user['role'] === 'owner' ? 'View reports' : 'View sales' ?> →</a></div><div class="chart"><?php foreach($days as $d): $height = $max > 0 ? max(6,(int)round($d['amount'] / $max * 100)) : 6; ?><div class="bar-col"><div class="bar" style="height:<?= $height ?>%" title="<?= e(money($d['amount'])) ?>"></div><small><?= e($d['label']) ?></small></div><?php endforeach; ?></div></section>
  <section class="panel"><div class="panel-head"><h2>Category sales</h2><a class="text-link" href="reports.php">View reports →</a></div><?php if(!$categoryRows): ?><div class="empty">No category detail yet.</div><?php else: ?><div class="stacked-bars"><?php foreach($categoryRows as $cat): $barWidth = $categoryMax > 0 ? max(10, (float)$cat['revenue'] / $categoryMax * 100) : 10; ?><div class="mini-bar-row"><div class="mini-bar-label"><strong><?= e($cat['category'] ?: 'General') ?></strong><small><?= (int)$cat['units'] ?> units</small></div><div class="mini-bar-track"><span style="width: <?= $barWidth ?>%"></span></div><div class="mini-bar-value"><?= money($cat['revenue']) ?></div></div><?php endforeach; ?></div><?php endif; ?></section>
</div>
<section class="panel"><div class="panel-head"><h2>Recent sales</h2><a class="text-link" href="sales.php">All sales →</a></div><?php if(!$recent): ?><div class="empty">No sales yet. Start with a checkout in the POS.</div><?php else: ?><div class="table-wrap"><table><thead><tr><th>Sale</th><th>Time</th><th>Payment</th><th>Total</th></tr></thead><tbody><?php foreach($recent as $s): ?><tr><td class="product-cell">#<?= (int)$s['id'] ?></td><td><?= e(date('M j, Y · g:i A',strtotime($s['created_at']))) ?></td><td><span class="badge"><?= e(ucfirst($s['payment_method'])) ?></span></td><td class="product-cell"><?= money($s['total']) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php page_footer(); ?>
