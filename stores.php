<?php
require __DIR__ . '/includes/bootstrap.php';

$search = trim((string)($_GET['q'] ?? ''));
$sql = 'SELECT id, name, store_type, created_at FROM stores WHERE 1=1';
$params = [];
if ($search !== '') {
    $sql .= ' AND name LIKE ?';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY name LIMIT 100';
$stmt = $pdo->prepare($sql); $stmt->execute($params); $stores = $stmt->fetchAll();
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Browse Stores · TindaHub</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="bg-brackets" aria-hidden="true">
  <span class="bb b1">&lt;</span>
  <span class="bb b2">&gt;</span>
  <span class="bb b3">&lt;/&gt;</span>
  <span class="bb b4">&lt;</span>
  <span class="bb b5">&gt;</span>
  <span class="bb b6">&lt;/&gt;</span>
  <span class="bb b7">&lt;</span>
  <span class="bb b8">&gt;</span>
  <span class="bb b9">&lt;/&gt;</span>
  <span class="bb b10">&lt;</span>
</div>
<header class="pub-topbar">
  <div class="pub-brand"><span class="brand-mark">T</span><span>TindaHub<small>STORE DIRECTORY</small></span></div>
  <a class="btn btn-light" href="admin-login.php">Owner / Admin sign in</a>
</header>
<main class="pub-container" style="position:relative;z-index:1">
  <div class="page-head">
    <div>
      <div class="eyebrow">Public directory</div>
      <h1>Browse stores</h1>
      <p class="subtitle">Find a store and place your order online.</p>
    </div>
  </div>

  <form class="search-row" method="get">
    <div class="search"><input type="search" name="q" value="<?= e($search) ?>" placeholder="Search store name…"></div>
    <button class="btn btn-light" type="submit">Search</button>
    <?php if ($search): ?><a class="btn btn-light" href="stores.php">Clear</a><?php endif; ?>
  </form>

  <?php if (!$stores): ?>
    <div class="empty panel" style="margin-top:20px">No stores found<?= $search ? ' for "'.e($search).'"' : '' ?>.</div>
  <?php else: ?>
  <div class="stores-grid">
    <?php foreach ($stores as $s): ?>
    <a class="store-card" href="customer.php?store=<?= (int)$s['id'] ?>">
      <div class="store-initial"><?= e(strtoupper(mb_substr($s['name'], 0, 1))) ?></div>
      <h2><?= e($s['name']) ?></h2>
      <?php if ($s['store_type']): ?><small><?= e(ucwords(str_replace('_', ' ', $s['store_type']))) ?></small><?php endif; ?>
      <small>Member since <?= e(date('M Y', strtotime($s['created_at']))) ?></small>
      <span class="badge" style="margin-top:4px;align-self:flex-start">View products →</span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</main>
<footer class="footer">TindaHub · Simple tools for local stores</footer>
</body>
</html>
