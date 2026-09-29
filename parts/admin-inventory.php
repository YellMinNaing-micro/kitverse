<?php
$search = trim((string)($_GET['q'] ?? ''));
$focus = max(0, (int)($_GET['focus'] ?? 0));
$rows = db()->query('SELECT cl.id club_id,cl.name club_name,p.id product_id,p.name product_name,p.season,p.image,p.status product_status,c.name kit_type,v.id variant_id,v.size,v.sku,v.stock,v.status variant_status FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id LEFT JOIN product_variants v ON v.product_id=p.id ORDER BY cl.name,p.season DESC,c.name,p.id,FIELD(v.size,"XS","S","M","L","XL","XXL")')->fetchAll();
$families = [];
foreach ($rows as $row) {
    $clubId = (int)$row['club_id'];
    $productId = (int)$row['product_id'];
    if ($search !== '' && stripos($row['club_name'].' '.$row['product_name'].' '.$row['kit_type'].' '.$row['season'], $search) === false) continue;
    if (!isset($families[$clubId])) $families[$clubId] = ['name'=>$row['club_name'], 'stock'=>0, 'low'=>0, 'products'=>[]];
    if (!isset($families[$clubId]['products'][$productId])) $families[$clubId]['products'][$productId] = ['id'=>$productId, 'name'=>$row['product_name'], 'type'=>$row['kit_type'], 'season'=>$row['season'], 'image'=>$row['image'], 'status'=>$row['product_status'], 'stock'=>0, 'variants'=>[]];
    if ($row['variant_id'] !== null) {
        $families[$clubId]['products'][$productId]['variants'][] = $row;
        if ($row['variant_status'] && $row['product_status']) {
            $families[$clubId]['products'][$productId]['stock'] += (int)$row['stock'];
            $families[$clubId]['stock'] += (int)$row['stock'];
            if ((int)$row['stock'] <= 5) $families[$clubId]['low']++;
        }
    }
}
?>
<div class="admin-list-panel inventory-panel">
  <div class="admin-list-toolbar"><div><h2>Inventory</h2><p>Open a jersey, then a kit to manage stock by size.</p></div><form class="admin-list-search" method="get"><input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="inventory"><svg><use href="#a-search"/></svg><input type="search" name="q" value="<?=e($search)?>" placeholder="Search jerseys or kits" aria-label="Search jerseys or kits"></form></div>
  <div class="inventory-tree-head"><span>Product / kit / size</span><span>Available stock</span></div>
  <?php foreach($families as $clubId=>$family): $familyFocused = isset($family['products'][$focus]); ?>
  <details class="inventory-family" <?=$familyFocused?'open':''?> id="club-<?=$clubId?>"><summary><span class="inventory-tree-toggle" aria-hidden="true"></span><span class="inventory-family-icon">KV</span><span class="inventory-tree-name"><strong><?=e($family['name'])?> Jersey</strong><small><?=count($family['products'])?> kit<?=count($family['products'])===1?'':'s'?> · <?=$family['low']?> low stock size<?= $family['low']===1?'':'s'?></small></span><span class="inventory-stock-total"><?=e($family['stock'])?> in stock</span></summary>
    <div class="inventory-family-children">
    <?php foreach($family['products'] as $product): ?><details class="inventory-kit" <?=$focus===$product['id']?'open':''?> id="kit-<?=$product['id']?>"><summary><span class="inventory-tree-toggle" aria-hidden="true"></span><?php if($product['image']): ?><img src="<?=e($product['image'])?>" alt="" loading="lazy"><?php else: ?><span class="inventory-kit-placeholder">KV</span><?php endif; ?><span class="inventory-tree-name"><strong><?=e($product['type'])?> Kit</strong><small><?=e($product['season']?:$product['name'])?> · <span class="<?=$product['status']?'':'inventory-hidden'?>"><?=$product['status']?'Active':'Hidden'?></span></small></span><span class="inventory-stock-total"><?=$product['stock']?> in stock</span></summary>
      <div class="inventory-kit-body"><div class="admin-table-scroll"><table class="admin-data-table"><thead><tr><th>Size</th><th>SKU</th><th>Stock</th><th>Status</th><th>Update stock</th></tr></thead><tbody><?php foreach($product['variants'] as $variant): ?><tr><td><strong><?=e($variant['size'])?></strong></td><td><?=e($variant['sku'])?></td><td class="<?=(int)$variant['stock']<=5?'dash-stock-count':''?>"><?=e($variant['stock'])?></td><td><span class="admin-status <?=$variant['variant_status']?'is-active':'is-hidden'?>"><?=$variant['variant_status']?'Active':'Hidden'?></span></td><td><form method="post" class="inventory-stock-form"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="admin_inventory_stock"><input type="hidden" name="variant_id" value="<?=$variant['variant_id']?>"><input type="number" name="stock" min="0" value="<?=e($variant['stock'])?>" aria-label="Stock for <?=e($product['name'].' '.$variant['size'])?>" required><button type="submit" class="admin-table-link">Save</button></form></td></tr><?php endforeach; ?><?php if(!$product['variants']): ?><tr><td colspan="5" class="admin-table-empty">No sizes added yet.</td></tr><?php endif; ?></tbody></table></div><a class="inventory-kit-edit" href="<?=path('admin',['tab'=>'products','edit'=>$product['id']])?>">Edit kit and add sizes →</a></div>
    </details><?php endforeach; ?>
    </div>
  </details><?php endforeach; ?>
  <?php if(!$families): ?><p class="admin-table-empty">No jerseys found.</p><?php endif; ?>
  <div class="admin-list-footer"><span><?=count($families)?> jersey group<?=count($families)===1?'':'s'?></span></div>
</div>
<?php if($focus): ?><script>document.addEventListener('DOMContentLoaded',()=>document.getElementById('kit-<?= $focus ?>')?.scrollIntoView({block:'center'}));</script><?php endif; ?>
