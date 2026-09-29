<section class="hero reference-hero">
  <div class="wrap hero-content">
    <span class="eyebrow">WEAR THE MATCH <span class="hero-rule"></span></span>
    <h1>FOOTBALL<br><em>LIVES HERE.</em></h1>
    <p>AUTHENTIC JERSEYS. REAL FANS.<br>ONE VERSE.</p>
    <a class="btn lime" href="<?=path('shop')?>">Shop Now <span>→</span></a>
    <div class="hero-benefits"><div><b>14+</b><small>Unique kits</small></div><div><b>4</b><small>Top leagues</small></div><div><b>Yours</b><small>Custom name & number</small></div></div>
  </div>
</section>
<section class="wrap categories-section">
  <div class="category-grid">
    <?php foreach([['Home Kits','Clean looks. Bold stories.',1,'home'],['Away Kits','Different places. Same passion.',2,'away'],['Third Kits','Unique designs. Bigger expression.',3,'third'],['Retro Kits','Classic colors. Forever iconic.',4,'retro']] as [$title,$tagline,$id,$class]): ?>
      <a class="category-tile <?=$class?>" href="<?=path('shop',['category'=>$id])?>"><span><?=e($title)?><small><?=e($tagline)?></small></span><b>→</b></a>
    <?php endforeach; ?>
  </div>
</section>
<section class="wrap section featured-section"><div class="section-head"><div><span class="eyebrow">FEATURED JERSEYS</span><h2>Made for matchday<span class="dot">.</span></h2></div><a href="<?=path('shop')?>">VIEW ALL →</a></div><div class="grid"><?php $products=db()->query('SELECT p.*,cl.name club,c.name category FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id WHERE p.status=1 ORDER BY p.is_featured DESC,p.id DESC LIMIT 8')->fetchAll();foreach($products as $product)include __DIR__.'/card.php';?></div></section>
<section class="wrap benefit-strip"><div><b>↗</b><span>Fast delivery<small>Across Myanmar</small></span></div><div><b>◇</b><span>Secure checkout<small>Shop with confidence</small></span></div><div><b>✦</b><span>Custom name & number<small>Make it yours</small></span></div><div><b>□</b><span>Easy support<small>We're here to help</small></span></div></section>
<section class="wrap promo reference-promo"><div><span class="eyebrow">MAKE IT YOURS</span><h2>ADD YOUR<br><em>NAME & NUMBER</em></h2><p>Personalize your jersey. Make it unique.</p><a class="btn lime" href="<?=path('shop')?>">Customize Now →</a></div><strong>10</strong></section>
