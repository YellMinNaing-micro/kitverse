<section class="hero reference-hero">
  <div class="wrap hero-content">
    <span class="eyebrow">WEAR THE MATCH <span class="hero-rule"></span></span>
    <h1>FOOTBALL<br><em>LIVES HERE</em></h1>
    <p>AUTHENTIC JERSEYS. REAL FANS.<br>ONE VERSE.</p>
    <a class="btn lime hero-button" href="<?=path('shop')?>">Shop Now <span>→</span></a>
    <div class="hero-benefits"><div><svg><use href="#i-user"/></svg><span><b>14+</b><small>Unique kits</small></span></div><div><svg><use href="#i-shirt"/></svg><span><b>4</b><small>Kit styles</small></span></div><div><svg><use href="#i-globe"/></svg><span><b>Myanmar</b><small>Local delivery</small></span></div></div>
  </div>
  <div class="hero-scrawl">MORE<br>THAN<br>JERSEYS<br>A BIGGER<br>VERSE.<span></span></div>
  <div class="hero-dots" aria-hidden="true"><i class="active"></i><i></i><i></i><i></i></div>
</section>
<section class="wrap categories-section"><div class="category-grid">
  <?php foreach([['Home Kits','Clean looks. Bold stories.',path('shop',['category'=>1]),'home','jersey-home.png'],['Away Kits','Different places. Same passion.',path('shop',['category'=>2]),'away','jersey-away.png'],['Third Kits','Unique designs. Bigger expression.',path('shop',['category'=>3]),'third','jersey-third.png'],['New Season','Fresh kits. New journeys.',path('shop',['view'=>'new']),'new','jersey-apex.png']] as [$title,$tagline,$destination,$class,$image]): ?>
  <a class="category-tile <?=$class?>" href="<?=$destination?>"><img src="assets/<?=e($image)?>" alt="" loading="lazy"><span><?=e($title)?><small><?=e($tagline)?></small></span><b>→</b></a>
  <?php endforeach; ?>
</div></section>
<section class="wrap section featured-section"><div class="section-head"><span class="eyebrow">FEATURED JERSEYS</span><a href="<?=path('shop')?>">VIEW ALL →</a></div><div class="grid">
<?php
$products = db()->query('SELECT p.*,cl.name club,c.name category FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id WHERE p.status=1 ORDER BY p.is_featured DESC,p.id DESC LIMIT 4')->fetchAll();
foreach($products as $product) include __DIR__.'/card.php';
?>
</div></section>
<section class="wrap benefit-strip"><div><svg><use href="#i-truck"/></svg><span>Fast Delivery<small>Across Myanmar</small></span></div><div><svg><use href="#i-shield"/></svg><span>Secure Payment<small>Cash on delivery available</small></span></div><div><svg><use href="#i-shirt"/></svg><span>Custom Name & Number<small>Make it yours</small></span></div><div><svg><use href="#i-box"/></svg><span>Order Tracking<small>Follow your order</small></span></div></section>
<section class="wrap promo reference-promo"><div><span class="eyebrow">MAKE IT YOURS</span><h2>ADD YOUR<br><em>NAME & NUMBER</em></h2><p>PERSONALIZE YOUR JERSEY. MAKE IT UNIQUE.</p><a class="btn lime" href="<?=path('shop')?>">Customize Now →</a></div><div class="promo-steps"><span>✎ &nbsp; Choose Jersey</span><span>Ｔ &nbsp; Add Name & Number</span><span>◉ &nbsp; Preview & Order</span></div></section>
