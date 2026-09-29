<a class="product-card" href="<?=path('product',['id'=>$product['id']])?>">
  <div class="product-image">
    <?php if($product['image'] && is_file(__DIR__.'/../'.$product['image'])): ?>
      <img src="<?=e($product['image'])?>" alt="<?=e($product['name'])?>" loading="lazy">
    <?php else: ?>
      <div class="mini-shirt"><span>KV</span><b>10</b></div>
    <?php endif; ?>
    <?php if($product['is_featured']): ?><span class="featured">FEATURED</span><?php endif; ?>
  </div>
  <div class="product-meta"><span><?=e($product['club'])?></span><span><?=e($product['category'])?></span></div>
  <h3><?=e($product['name'])?></h3>
  <div class="product-bottom"><strong><?=price($product['base_price'])?></strong><span>↗</span></div>
</a>
