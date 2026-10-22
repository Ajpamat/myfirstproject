<?php
require __DIR__ . '/includes/bootstrap.php'; require_owner(); $sid=store_id();
if($_SERVER['REQUEST_METHOD']==='POST'){
 $action=(string)($_POST['action']??'save');$id=(int)($_POST['id']??0);
 if($action==='toggle' && $id>0){$st=$pdo->prepare('UPDATE products SET is_active=1-is_active WHERE id=? AND store_id=?');$st->execute([$id,$sid]);flash('success','Product availability updated.');redirect('inventory.php');}
 $name=trim((string)($_POST['name']??''));$sku=trim((string)($_POST['sku']??''));$category=trim((string)($_POST['category']??''));$price=$_POST['price']??'';$cost=$_POST['cost']??'';$stock=$_POST['stock_qty']??'';$low=$_POST['low_stock_at']??'5';
 if($name===''||!money_input($price)||!money_input($cost)||filter_var($stock,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]])===false||filter_var($low,FILTER_VALIDATE_INT,['options'=>['min_range'=>0]])===false) {flash('error','Please enter a name, valid non-negative prices, and whole-number stock thresholds.');redirect('inventory.php');}
 $imageFile=null;
 if(!empty($_FILES['image']['tmp_name'])){
  $allowed=['image/jpeg','image/png','image/webp','image/gif'];
  $finfo=finfo_open(FILEINFO_MIME_TYPE);$mime=finfo_file($finfo,$_FILES['image']['tmp_name']);finfo_close($finfo);
  if(!in_array($mime,$allowed,true)||$_FILES['image']['size']>2*1024*1024){flash('error','Image must be JPG, PNG, WebP or GIF and under 2MB.');redirect('inventory.php');}
  $ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'][$mime];
  $imageFile='uploads/'.bin2hex(random_bytes(10)).'.'.$ext;
  move_uploaded_file($_FILES['image']['tmp_name'],__DIR__.'/'.$imageFile);
 }
 try{
  if($id>0){
   if($imageFile){
    $old=$pdo->prepare('SELECT image FROM products WHERE id=? AND store_id=?');$old->execute([$id,$sid]);$oldImg=$old->fetchColumn();
    if($oldImg && file_exists(__DIR__.'/'.$oldImg)) unlink(__DIR__.'/'.$oldImg);
    $st=$pdo->prepare('UPDATE products SET name=?,sku=?,category=?,price=?,cost=?,stock_qty=?,low_stock_at=?,image=? WHERE id=? AND store_id=?');$st->execute([$name,$sku?:null,$category?:null,$price,$cost,$stock,$low,$imageFile,$id,$sid]);
   }else{
    $st=$pdo->prepare('UPDATE products SET name=?,sku=?,category=?,price=?,cost=?,stock_qty=?,low_stock_at=? WHERE id=? AND store_id=?');$st->execute([$name,$sku?:null,$category?:null,$price,$cost,$stock,$low,$id,$sid]);
   }
  }else{
   $st=$pdo->prepare('INSERT INTO products (store_id,name,image,sku,category,price,cost,stock_qty,low_stock_at) VALUES (?,?,?,?,?,?,?,?,?)');$st->execute([$sid,$name,$imageFile,$sku?:null,$category?:null,$price,$cost,$stock,$low]);
  }
  flash('success',$id?'Product updated.':'Product added to inventory.');
 }catch(PDOException $e){flash('error',$e->getCode()==='23000'?'That SKU is already used by another product in this store.':'Could not save product.');}
 redirect('inventory.php');
}
$search=trim((string)($_GET['q']??''));$sortColumns=['name'=>'name','price'=>'price','cost'=>'cost','stock_qty'=>'stock_qty','low_stock_at'=>'low_stock_at','created_at'=>'created_at'];$sort=(string)($_GET['sort']??'name');$dir=strtoupper((string)($_GET['dir']??'asc'));if(!isset($sortColumns[$sort])||!in_array($dir,['ASC','DESC'],true)){$sort='name';$dir='ASC';}$sql='SELECT * FROM products WHERE store_id=?';$params=[$sid];if($search!==''){$sql.=' AND (name LIKE ? OR sku LIKE ? OR category LIKE ?)';$term='%'.$search.'%';array_push($params,$term,$term,$term);}$sql.=' ORDER BY is_active DESC, '. $sortColumns[$sort] .' '. $dir .', name ASC LIMIT 300';$st=$pdo->prepare($sql);$st->execute($params);$rows=$st->fetchAll();
require __DIR__.'/includes/layout.php';page_header('Inventory','inventory');
$sortUrl = function(string $field) use ($search, $dir): string { $nextDir = ($field === ($_GET['sort'] ?? 'name') && strtoupper((string)($_GET['dir'] ?? 'asc')) === 'ASC') ? 'DESC' : 'ASC'; return 'inventory.php?sort=' . urlencode($field) . '&dir=' . urlencode($nextDir) . ($search !== '' ? '&q=' . urlencode($search) : ''); };
?><div class="page-head"><div><div class="eyebrow">Products & stock</div><h1>Inventory</h1><p class="subtitle">Manage the items you sell. This section is separate from checkout.</p></div><button class="btn btn-primary" onclick="openProduct()">＋ Add product</button></div>
<section class="panel"><form class="search-row" method="get"><div class="search"><input name="q" type="search" placeholder="Search name, SKU or category" value="<?= e($search) ?>"></div><button class="btn btn-light">Search</button><?php if($search): ?><a class="btn btn-light" href="inventory.php">Clear</a><?php endif; ?><input type="hidden" name="sort" value="<?= e($sort) ?>"><input type="hidden" name="dir" value="<?= e($dir) ?>"></form>
<div class="table-wrap"><table><thead><tr><th></th><th><a class="sort-link" href="<?= $sortUrl('name') ?>">Product <?= (($sort === 'name') ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th>Category</th><th><a class="sort-link" href="<?= $sortUrl('price') ?>">Price <?= (($sort === 'price') ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th><a class="sort-link" href="<?= $sortUrl('cost') ?>">Cost <?= (($sort === 'cost') ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th><a class="sort-link" href="<?= $sortUrl('stock_qty') ?>">In stock <?= (($sort === 'stock_qty') ? ($dir === 'ASC' ? '↑' : '↓') : '') ?></a></th><th>Status</th><th></th></tr></thead><tbody><?php if(!$rows): ?><tr><td colspan="8" class="empty">No products found. Add your first product.</td></tr><?php endif; foreach($rows as $p): ?><tr><td><img src="<?= $p['image'] ? e($p['image']) : 'assets/no-image.svg' ?>" style="width:40px;height:40px;object-fit:cover;border-radius:7px;border:1px solid var(--line)"></td><td><div class="product-cell"><?= e($p['name']) ?></div><div class="small-muted"><?= e($p['sku'] ?: 'No SKU') ?></div></td><td><?= e($p['category'] ?: '—') ?></td><td><?= money($p['price']) ?></td><td><?= money($p['cost']) ?></td><td><strong><?= (int)$p['stock_qty'] ?></strong> <span class="small-muted">(alert at <?= (int)$p['low_stock_at'] ?>)</span></td><td><?php if(!(int)$p['is_active']): ?><span class="badge">Inactive</span><?php elseif((int)$p['stock_qty']===0): ?><span class="badge out">Out of stock</span><?php elseif((int)$p['stock_qty']<=(int)$p['low_stock_at']): ?><span class="badge low">Low stock</span><?php else: ?><span class="badge">In stock</span><?php endif; ?></td><td style="white-space:nowrap"><button type="button" class="btn btn-light" onclick='editProduct(<?= json_encode($p,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>)'>Edit</button> <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button class="btn btn-light" type="submit"><?= !(int)$p['is_active']?'Activate':'Deactivate' ?></button></form></td></tr><?php endforeach; ?></tbody></table></div>
</section>
<div class="modal-backdrop" id="productModal" onclick="if(event.target===this)closeProduct()"><section class="modal"><div class="modal-title"><div style="display:flex;align-items:center;gap:10px"><span style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#34d399,#059669);display:grid;place-items:center;font-size:15px;color:#fff;flex-shrink:0">⊞</span><h2 id="modalTitle">Add product</h2></div><button class="icon-button" type="button" onclick="closeProduct()">×</button></div><form method="post" id="productForm" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" id="productId" name="id" value="0">
<div class="pf-section"><div class="pf-section-label">Basic info</div><div class="field" style="margin-bottom:12px"><label>Product name</label><input name="name" id="pName" required maxlength="180" placeholder="e.g. Mineral Water 500ml"></div><div class="pf-row"><div class="field"><label>SKU / barcode</label><input name="sku" id="pSku" maxlength="80" placeholder="Optional"></div><div class="field"><label>Category</label><input name="category" id="pCategory" maxlength="100" placeholder="General"></div></div></div>
<div class="pf-section"><div class="pf-section-label">Pricing</div><div class="pf-row"><div class="field"><label>Selling price (₱)</label><input name="price" id="pPrice" type="number" min="0" step="0.01" required placeholder="0.00"></div><div class="field"><label>Unit cost (₱)</label><input name="cost" id="pCost" type="number" min="0" step="0.01" value="0" required placeholder="0.00"></div></div></div>
<div class="pf-section"><div class="pf-section-label">Stock</div><div class="pf-row"><div class="field"><label>Current stock</label><input name="stock_qty" id="pStock" type="number" min="0" step="1" value="0" required></div><div class="field"><label>Low stock alert at</label><input name="low_stock_at" id="pLow" type="number" min="0" step="1" value="5" required></div></div></div>
<div class="pf-section"><div class="pf-section-label">Image</div><div class="pf-upload" onclick="document.getElementById('pImage').click()"><img id="pImgPreview" src="assets/no-image.svg"><div class="pf-upload-text"><span id="pUploadLabel">Click to upload photo</span><small>JPG, PNG, WebP · max 2MB</small></div></div><input type="file" name="image" id="pImage" accept="image/jpeg,image/png,image/webp,image/gif" onchange="previewImg(this)" style="display:none"></div>
<div class="form-actions"><button class="btn btn-light" type="button" onclick="closeProduct()">Cancel</button><button class="btn btn-primary">Save product</button></div></form></section></div>
<script>
const modal = document.getElementById('productModal');
function openProduct() {
  document.getElementById('productForm').reset();
  document.getElementById('productId').value = 0;
  document.getElementById('pCost').value = 0;
  document.getElementById('pStock').value = 0;
  document.getElementById('pLow').value = 5;
  document.getElementById('pImgPreview').src = 'assets/no-image.svg';
  document.getElementById('modalTitle').textContent = 'Add product';
  modal.classList.add('open', 'show');
  setTimeout(() => document.getElementById('pName').focus(), 50);
}
function closeProduct() {
  modal.classList.remove('open', 'show');
}
function previewImg(input) {
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => document.getElementById('pImgPreview').src = e.target.result;
    r.readAsDataURL(input.files[0]);
  }
}
function editProduct(p) {
  document.getElementById('productId').value = p.id;
  document.getElementById('pName').value = p.name;
  document.getElementById('pSku').value = p.sku || '';
  document.getElementById('pCategory').value = p.category || '';
  document.getElementById('pPrice').value = p.price;
  document.getElementById('pCost').value = p.cost;
  document.getElementById('pStock').value = p.stock_qty;
  document.getElementById('pLow').value = p.low_stock_at;
  document.getElementById('pImgPreview').src = p.image ? p.image : 'assets/no-image.svg';
  document.getElementById('modalTitle').textContent = 'Edit product';
  modal.classList.add('open', 'show');
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape' && modal.classList.contains('open')) {
    closeProduct();
  }
});
</script>
<?php page_footer(); ?>
