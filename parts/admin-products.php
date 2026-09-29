<?php
$edit = max(0, (int)($_GET['edit'] ?? 0));
$product = null;
if ($edit) {
    $statement = db()->prepare('SELECT * FROM products WHERE id=?');
    $statement->execute([$edit]);
    $product = $statement->fetch();
}
$showForm = $product || (($_GET['mode'] ?? '') === 'create');
$search = trim((string)($_GET['q'] ?? ''));
$pageNumber = max(1, (int)($_GET['p'] ?? 1));
$pageSize = 15;
$where = $search === '' ? '' : ' WHERE p.name LIKE ? OR cl.name LIKE ? OR c.name LIKE ?';
$params = $search === '' ? [] : array_fill(0, 3, '%' . $search . '%');
$count = db()->prepare('SELECT COUNT(*) FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id' . $where);
$count->execute($params);
$total = (int)$count->fetchColumn();
$pageCount = max(1, (int)ceil($total / $pageSize));
$pageNumber = min($pageNumber, $pageCount);
$offset = ($pageNumber - 1) * $pageSize;
$statement = db()->prepare('SELECT p.*, cl.name club_name, c.name category_name FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id' . $where . ' ORDER BY p.id DESC LIMIT ' . $pageSize . ' OFFSET ' . $offset);
$statement->execute($params);
$products = $statement->fetchAll();
?>
<div class="admin-list-panel">
  <div class="admin-list-toolbar">
    <div class="admin-list-actions"><a class="admin-create" href="<?=path('admin', ['tab'=>'products','mode'=>'create'])?>">＋ Create product</a><button class="admin-secondary" type="button" data-edit-selected disabled>Edit selected</button></div>
    <form class="admin-list-search" method="get"><input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="products"><svg><use href="#a-search"/></svg><input type="search" name="q" value="<?=e($search)?>" placeholder="Search products or clubs" aria-label="Search products or clubs"></form>
  </div>
  <?php if ($showForm): ?>
  <div class="admin-editor" id="product-editor">
    <div class="admin-editor-heading"><div><h2><?=$product ? 'Edit product' : 'Create product'?></h2><p><?=$product ? 'Update this jersey and its inventory.' : 'Add a jersey to the store.'?></p></div><a href="<?=path('admin',['tab'=>'products'])?>" aria-label="Close product form">✕</a></div>
    <form method="post" class="form" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="admin_product"><input type="hidden" name="id" value="<?=$product['id']??0?>">
      <label>NAME<input name="name" value="<?=e($product['name']??'')?>" required></label>
      <div class="two"><label>CLUB<select name="club_id" required><option value="">Choose club</option><?php foreach($clubs as $row): ?><option value="<?=$row['id']?>" <?=($product['club_id']??0)==$row['id']?'selected':''?>><?=e($row['name'])?></option><?php endforeach; ?></select></label><label>TYPE<select name="category_id" required><option value="">Choose type</option><?php foreach($categories as $row): ?><option value="<?=$row['id']?>" <?=($product['category_id']??0)==$row['id']?'selected':''?>><?=e($row['name'])?></option><?php endforeach; ?></select></label></div>
      <div class="two"><label>SEASON<input name="season" value="<?=e($product['season']??'') ?>"></label><label>PRICE (MMK)<input type="number" min="1" step="0.01" name="base_price" value="<?=e($product['base_price']??'')?>" required></label></div>
      <label>DISCOUNT (%)<input type="number" min="0" max="90" step="1" name="discount_percent" value="<?=e($product['discount_percent']??0)?>" required><small>Set 0 for regular price. Sale prices update automatically in the shop and cart.</small></label>
      <div class="product-image-preview" data-image-preview><?php if($product && $product['image'] && is_file(__DIR__.'/../'.$product['image'])): ?><img src="<?=e($product['image'])?>" alt="Current product image"><?php else: ?><span>No product image yet</span><?php endif; ?></div>
      <label>PRODUCT IMAGE<input type="file" name="product_image" accept="image/jpeg,image/png,image/webp" data-product-image></label><small class="image-upload-hint">JPG, PNG or WebP · up to 8 MB. Choose a new image to replace the current one.</small>
      <label>DESCRIPTION<textarea name="description"><?=e($product['description']??'')?></textarea></label>
      <div class="two"><label>FEATURED<select name="is_featured"><option value="0" <?=empty($product['is_featured'])?'selected':''?>>Standard</option><option value="1" <?=!empty($product['is_featured'])?'selected':''?>>Featured</option></select></label><label>STATUS<select name="status"><option value="1" <?=($product['status']??1)?'selected':''?>>Active</option><option value="0" <?=isset($product['status']) && !$product['status']?'selected':''?>>Hidden</option></select></label></div>
      <div class="admin-editor-buttons"><button class="btn lime">SAVE PRODUCT</button><a class="admin-secondary" href="<?=path('admin',['tab'=>'products'])?>">Cancel</a></div>
    </form>
    <?php if($product): ?>
    <div class="admin-variant-section"><h3>Sizes and stock</h3><div class="admin-table-scroll"><table class="admin-data-table"><thead><tr><th>Size</th><th>SKU</th><th>Stock</th><th>Price override</th></tr></thead><tbody><?php $sizes=db()->prepare('SELECT * FROM product_variants WHERE product_id=? ORDER BY FIELD(size,"XS","S","M","L","XL","XXL")');$sizes->execute([$product['id']]);foreach($sizes as $variant): ?><tr><td><?=e($variant['size'])?></td><td><?=e($variant['sku'])?></td><td><?=e($variant['stock'])?></td><td><?=$variant['price']===null?'—':price($variant['price'])?></td></tr><?php endforeach; ?></tbody></table></div>
      <form method="post" class="form admin-variant-form"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="admin_variant"><input type="hidden" name="product_id" value="<?=$product['id']?>"><div class="two"><label>SIZE<select name="size"><?php foreach(['XS','S','M','L','XL','XXL'] as $size): ?><option><?=$size?></option><?php endforeach; ?></select></label><label>STOCK<input type="number" name="stock" min="0" required></label></div><div class="two"><label>SKU<input name="sku" placeholder="Auto generated"></label><label>PRICE OVERRIDE<input type="number" name="price" min="0" step="0.01"></label></div><button class="btn dark">SAVE SIZE / STOCK</button></form>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <div class="admin-table-scroll"><table class="admin-data-table"><thead><tr><th class="admin-select-col">#</th><th>Product</th><th>Club</th><th>Type</th><th>Price</th><th>Discount</th><th>Featured</th><th>Status</th><th>Created on</th><th>Action</th></tr></thead><tbody>
  <?php foreach($products as $row): ?><tr data-product-row><td><input type="radio" name="selected_product" value="<?=$row['id']?>" aria-label="Select <?=e($row['name'])?>"></td><td><div class="admin-product-cell"><?php if($row['image']): ?><img src="<?=e($row['image'])?>" alt="" loading="lazy"><?php else: ?><span class="admin-no-image">KV</span><?php endif; ?><span><strong><?=e($row['name'])?></strong><small>#<?=e($row['id'])?> · <?=e($row['season'])?></small></span></div></td><td><?=e($row['club_name'])?></td><td><?=e($row['category_name'])?></td><td><strong><?=price(discounted_price($row['base_price'],$row['discount_percent']))?></strong></td><td><span class="admin-status <?=$row['discount_percent']?'is-hidden':'is-muted'?>"><?=$row['discount_percent']?'-'.e($row['discount_percent']).'%':'—'?></span></td><td><span class="admin-status <?=$row['is_featured']?'is-featured':'is-muted'?>"><?=$row['is_featured']?'Featured':'Standard'?></span></td><td><span class="admin-status <?=$row['status']?'is-active':'is-hidden'?>"><?=$row['status']?'Active':'Hidden'?></span></td><td><?=e($row['created_at'])?></td><td><a class="admin-table-link" href="<?=path('admin',['tab'=>'products','edit'=>$row['id']])?>">Edit</a></td></tr><?php endforeach; ?>
  <?php if(!$products): ?><tr><td colspan="10" class="admin-table-empty">No products found.</td></tr><?php endif; ?>
  </tbody></table></div>
  <div class="admin-list-footer"><span>Showing <?=$total ? $offset+1 : 0?> to <?=min($offset+$pageSize,$total)?> of <?=$total?> products</span><div class="admin-page-links"><?php if($pageNumber>1): ?><a href="<?=path('admin',['tab'=>'products','q'=>$search,'p'=>$pageNumber-1])?>">‹ Previous</a><?php endif; ?><span><?=$pageNumber?> / <?=$pageCount?></span><?php if($pageNumber<$pageCount): ?><a href="<?=path('admin',['tab'=>'products','q'=>$search,'p'=>$pageNumber+1])?>">Next ›</a><?php endif; ?></div></div>
</div>
