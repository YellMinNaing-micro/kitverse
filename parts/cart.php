<?php $items = cart(); [$base, $custom, $total] = totals($items); ?>
<section class="wrap section cart-page" data-cart-page>
  <div class="page-title"><span class="eyebrow">YOUR SELECTION</span><h1>Your cart<span class="dot">.</span></h1></div>
  <?php if (!$items): ?>
    <div class="empty">Your cart is empty. <a href="<?=path('shop')?>">Discover kits →</a></div>
  <?php else: ?>
  <div class="columns"><div class="cart-items">
  <?php foreach ($items as $item):
    $quantity = (int)$item['quantity'];
    $stock = min(20, (int)$item['stock']);
    $unitPrice = (float)$item['price'];
    $personalization = ($item['custom_name'] !== null || $item['custom_number'] !== null) ? 5000 : 0;
  ?>
    <article class="cart-item" data-cart-item data-unit-price="<?=$unitPrice?>" data-personalization="<?=$personalization?>" data-stock="<?=$stock?>">
      <div class="thumb"><?php if ($item['image'] && is_file(__DIR__.'/../'.$item['image'])): ?><img src="<?=e($item['image'])?>" alt=""><?php else: ?>KV<?php endif; ?></div>
      <div class="cart-item-info"><a class="item-name" href="<?=path('product',['id'=>$item['product_id']])?>"><?=e($item['name'])?></a><p>SIZE <?=e($item['size'])?><?php if ($item['custom_name'] || $item['custom_number']): ?> · <?=e(trim(($item['custom_name']??'').' '.($item['custom_number']??'')))?><?php endif; ?></p><small><?=price($unitPrice)?> each<?=$personalization?' · +'.price($personalization).' personalization':''?></small><strong class="cart-line-total" data-line-total><?=price(($unitPrice+$personalization)*$quantity)?></strong></div>
      <form method="post" class="quantity cart-quantity" data-cart-form><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="cart"><input type="hidden" name="id" value="<?=$item['id']?>"><div class="cart-stepper"><button type="submit" name="quantity" value="<?=max(1,$quantity-1)?>" data-cart-change="minus" aria-label="Decrease quantity of <?=e($item['name'])?>" <?=$quantity<=1?'disabled':''?>>−</button><output data-cart-quantity aria-label="Quantity"><?=$quantity?></output><button type="submit" name="quantity" value="<?=min($stock,$quantity+1)?>" data-cart-change="plus" aria-label="Increase quantity of <?=e($item['name'])?>" <?=$quantity >= $stock?'disabled':''?>>+</button></div><button type="submit" name="quantity" value="0" class="cart-remove" data-cart-change="remove" aria-label="Remove <?=e($item['name'])?> from cart" title="Remove"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v6m4-6v6"/></svg></button></form>
    </article>
  <?php endforeach; ?>
  </div><aside class="summary cart-summary"><h3>ORDER SUMMARY</h3><p><span>Subtotal</span><b data-cart-subtotal><?=price($base)?></b></p><p><span>Personalization</span><b data-cart-personalization><?=price($custom)?></b></p><p><span>Shipping</span><b>FREE</b></p><p class="total"><span>Total</span><b data-cart-total><?=price($total)?></b></p><a class="btn lime full" href="<?=path(current_user()?'checkout':'login')?>">CHECKOUT ↗</a><p class="cart-feedback" data-cart-feedback role="status" aria-live="polite"></p></aside></div>
  <?php endif; ?>
</section>
