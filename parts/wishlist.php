<section class="wrap section wishlist-page">
  <div class="page-title"><span class="eyebrow">YOUR FAVORITES</span><h1>Saved jerseys<span class="dot">.</span></h1><p>Keep your favorite kits in one place.</p></div>
  <?php
  $ids = array_values(array_filter(array_map('intval', $_SESSION['wishlist'] ?? [])));
  $products = [];
  if ($ids) {
      $marks = implode(',', array_fill(0, count($ids), '?'));
      $query = db()->prepare("SELECT p.*, cl.name club, c.name category FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id WHERE p.status=1 AND p.id IN ($marks) ORDER BY p.id DESC");
      $query->execute($ids);
      $products = $query->fetchAll();
  }
  ?>
  <?php if(!$products): ?><div class="empty">Nothing saved yet. <a href="<?=path('shop')?>">Browse jerseys →</a></div><?php else: ?><div class="grid"><?php foreach($products as $product) include __DIR__.'/card.php'; ?></div><?php endif; ?>
</section>
