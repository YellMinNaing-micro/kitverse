<?php
$kind = str_contains(strtolower($product['category']), 'away') ? 'away' : (str_contains(strtolower($product['category']), 'third') ? 'third' : 'home');
$visual = $cardVisual ?? $kind;
$image = $product['image'] && is_file(__DIR__.'/../'.$product['image']) ? $product['image'] : 'assets/jersey-'.$visual.'.png';
$onSale = (int)($product['discount_percent'] ?? 0) > 0;
$isNew = in_array((int)$product['id'], new_product_ids(), true);
$badge = ($shopView ?? '') === 'new' ? 'New' : ($onSale ? '-'.$product['discount_percent'].'%' : ($isNew ? 'New' : ($product['is_featured'] ? 'Featured' : '')));
$saved = in_array((int)$product['id'], $_SESSION['wishlist'] ?? [], true);
$variantQuery = db()->prepare('SELECT id,size,price FROM product_variants WHERE product_id=? AND status=1 AND stock>0 ORDER BY FIELD(size,"M","L","S","XL","XS","XXL") LIMIT 1');
$variantQuery->execute([$product['id']]);
$quickVariant = $variantQuery->fetch(PDO::FETCH_ASSOC);
$listPrice = (float)($quickVariant['price'] ?? $product['base_price']);
$salePrice = discounted_price($listPrice,$product['discount_percent'] ?? 0);
?>
<article class="product-card">
  <div class="product-image">
    <a class="product-picture-link" href="<?=path('product',['id'=>$product['id']])?>" aria-label="View <?=e($product['name'])?>"><img src="<?=e($image)?>" alt="<?=e($product['name'])?> concept jersey" loading="lazy"></a>
    <?php if($badge): ?><span class="featured <?=$onSale?'sale-badge':''?>"><?=e($badge)?></span><?php endif; ?>
    <form method="post" class="card-wishlist"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="wishlist"><input type="hidden" name="id" value="<?=$product['id']?>"><input type="hidden" name="return_to" value="<?=in_array($page,['home','shop','wishlist'],true)?$page:'home'?>"><button aria-label="<?=$saved?'Remove from':'Add to'?> wishlist" title="<?=$saved?'Remove from':'Add to'?> wishlist" class="<?=$saved?'saved':''?>"><svg><use href="#i-heart"/></svg></button></form>
  </div>
  <div class="product-copy"><a class="product-title" href="<?=path('product',['id'=>$product['id']])?>"><?=e($product['name'])?></a><small><?=e($product['season']?:'2026/27')?></small><div class="product-bottom"><span class="card-price <?=$onSale?'sale-price':''?>"><?=price($salePrice)?></span><?php if($onSale): ?><del><?=price($listPrice)?></del><?php endif; ?><?php if($quickVariant): ?><form method="post" class="quick-cart"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="add"><input type="hidden" name="variant_id" value="<?=$quickVariant['id']?>"><input type="hidden" name="quantity" value="1"><button aria-label="Add <?=e($product['name'])?> size <?=e($quickVariant['size'])?> to cart" title="Quick add size <?=e($quickVariant['size'])?>"><svg><use href="#i-cart"/></svg></button></form><?php else: ?><span class="sold-out">SOLD OUT</span><?php endif; ?></div></div>
</article>
