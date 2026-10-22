<?php
require __DIR__ . '/includes/bootstrap.php';
require_owner();
$sid = store_id();

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to'] ?? date('Y-m-d');
$valid = fn($x) => is_string($x) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $x);
if (!$valid($from)) $from = date('Y-m-01');
if (!$valid($to))   $to   = date('Y-m-d');

// Summary stats
$st = $pdo->prepare('SELECT COUNT(*) transactions, COALESCE(SUM(total),0) revenue, COALESCE(AVG(total),0) average_sale FROM sales WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ?');
$st->execute([$sid, $from, $to]);
$summary = $st->fetch();

// Daily breakdown
$days = $pdo->prepare('SELECT DATE(created_at) day, COUNT(*) transactions, SUM(total) revenue FROM sales WHERE store_id=? AND DATE(created_at) BETWEEN ? AND ? GROUP BY DATE(created_at) ORDER BY day DESC');
$days->execute([$sid, $from, $to]);
$daily = $days->fetchAll();

// Top 10 Best Sellers
$best = $pdo->prepare('SELECT i.product_name, SUM(i.quantity) units, SUM(i.line_total) revenue FROM sale_items i JOIN sales s ON s.store_id=i.store_id AND s.id=i.sale_id WHERE i.store_id=? AND DATE(s.created_at) BETWEEN ? AND ? GROUP BY i.product_id, i.product_name ORDER BY units DESC LIMIT 10');
$best->execute([$sid, $from, $to]);
$products = $best->fetchAll();

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = 'tindahub-sales-' . $from . '-to-' . $to . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Transactions', 'Gross sales (PHP)']);
    foreach ($daily as $d) {
        fputcsv($out, [$d['day'], $d['transactions'], $d['revenue']]);
    }
    fclose($out);
    exit;
}

require __DIR__ . '/includes/layout.php';
page_header('Reports', 'reports');

$today = date('Y-m-d');
$first_this_month = date('Y-m-01');
$seven_days_ago = date('Y-m-d', strtotime('-6 days'));
$is_today = ($from === $today && $to === $today);
$is_7days = ($from === $seven_days_ago && $to === $today);
$is_this_month = ($from === $first_this_month && $to === $today);
?>
<div class="page-head">
  <div>
    <div class="eyebrow">Business insights</div>
    <h1>Sales reports</h1>
    <p class="subtitle">Detailed breakdown of gross revenue, completed transactions, and bestsellers.</p>
  </div>
  <a class="btn btn-light" href="reports.php?from=<?= e($from) ?>&amp;to=<?= e($to) ?>&amp;export=csv">
    ↓ Export CSV
  </a>
</div>

<div class="report-filter-bar">
  <form method="get" class="report-date-inputs">
    <div class="field" style="margin:0">
      <label>From date</label>
      <input type="date" name="from" value="<?= e($from) ?>" style="min-height:38px">
    </div>
    <div class="field" style="margin:0">
      <label>To date</label>
      <input type="date" name="to" value="<?= e($to) ?>" style="min-height:38px">
    </div>
    <button class="btn btn-light" type="submit" style="min-height:38px">Apply dates</button>
  </form>
</div>

<div class="quick-date-chips">
  <a class="date-chip <?= $is_today ? 'active' : '' ?>" href="reports.php?from=<?= $today ?>&amp;to=<?= $today ?>">Today</a>
  <a class="date-chip <?= $is_7days ? 'active' : '' ?>" href="reports.php?from=<?= $seven_days_ago ?>&amp;to=<?= $today ?>">Last 7 Days</a>
  <a class="date-chip <?= $is_this_month ? 'active' : '' ?>" href="reports.php?from=<?= $first_this_month ?>&amp;to=<?= $today ?>">This Month</a>
</div>

<div class="stats">
  <div class="stat">
    <div class="stat-top">Gross sales</div>
    <div class="stat-value"><?= money($summary['revenue']) ?></div>
    <div class="stat-note">Selected date range</div>
  </div>
  <div class="stat">
    <div class="stat-top">Transactions</div>
    <div class="stat-value"><?= number_format((int)$summary['transactions']) ?></div>
    <div class="stat-note">Completed sales</div>
  </div>
  <div class="stat">
    <div class="stat-top">Average sale</div>
    <div class="stat-value"><?= money($summary['average_sale']) ?></div>
    <div class="stat-note">Per transaction</div>
  </div>
  <div class="stat">
    <div class="stat-top">Products sold</div>
    <div class="stat-value"><?= number_format(array_sum(array_map(fn($p) => (int)$p['units'], $products))) ?></div>
    <div class="stat-note">Units across top 10 products</div>
  </div>
</div>

<div class="grid-2">
  <section class="panel">
    <div class="panel-head">
      <h2>Daily breakdown</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th class="text-right">Transactions</th>
            <th class="text-right">Sales</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$daily): ?>
            <tr><td colspan="3" class="empty">No sales in this date range.</td></tr>
          <?php endif; ?>
          <?php foreach ($daily as $d): ?>
            <tr>
              <td><?= e(date('D, M j, Y', strtotime($d['day']))) ?></td>
              <td class="text-right"><?= number_format((int)$d['transactions']) ?></td>
              <td class="product-cell text-right" style="font-weight:700"><?= money($d['revenue']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head">
      <h2>Top 10 Best sellers</h2>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th class="text-right">Units</th>
            <th class="text-right">Revenue</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$products): ?>
            <tr><td colspan="3" class="empty">No product sales recorded yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($products as $idx => $p): 
            $rank = $idx + 1;
            $rc = ($rank === 1) ? 'rank-1' : (($rank === 2) ? 'rank-2' : (($rank === 3) ? 'rank-3' : ''));
          ?>
            <tr>
              <td class="product-cell">
                <span class="rank-badge <?= $rc ?>"><?= $rank ?></span>
                <?= e($p['product_name']) ?>
              </td>
              <td class="text-right"><?= number_format((int)$p['units']) ?></td>
              <td class="text-right" style="font-weight:700"><?= money($p['revenue']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<?php page_footer(); ?>
