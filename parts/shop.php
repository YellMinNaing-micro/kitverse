<?php
$shopView = (string)($_GET['view'] ?? 'all');
if (!in_array($shopView,['all','new','sale'],true)) $shopView = 'all';
$headings = [
    'all'=>['THE COLLECTION / ALL KITS','Find your colors','Matchday starts here. Explore your next favorite kit.'],
    'new'=>['JUST LANDED / LATEST FIVE','New arrivals','The five newest jerseys in the store.'],
    'sale'=>['LIMITED OFFERS / SALE','Sale jerseys','Real discounts on selected kits.'],
];
[$eyebrow,$heading,$intro] = $headings[$shopView];
$sql = 'SELECT p.*,cl.name club,c.name category FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN categories c ON c.id=p.category_id WHERE p.status=1';
$args = [];
if ($shopView === 'sale') $sql .= ' AND p.discount_percent>0';
if ($shopView === 'new') $sql .= ' AND p.id IN ('.implode(',',new_product_ids() ?: [0]).')';
if (trim((string)($_GET['q']??'')) !== '') {
    $sql .= ' AND (p.name LIKE ? OR cl.name LIKE ?)';
    $search = '%'.trim((string)$_GET['q']).'%';
    array_push($args,$search,$search);
}
foreach (['league'=>'cl.league_id','club'=>'p.club_id','category'=>'p.category_id'] as $key=>$column) {
    if ((int)($_GET[$key]??0)>0) { $sql .= " AND $column=?"; $args[] = (int)$_GET[$key]; }
}
$sql .= $shopView === 'new' ? ' ORDER BY p.id DESC LIMIT 5' : ' ORDER BY p.is_featured DESC,p.id DESC';
$statement = db()->prepare($sql);
$statement->execute($args);
$products = $statement->fetchAll();
?>
<section class="wrap section"><div class="page-title"><span class="eyebrow"><?=e($eyebrow)?></span><h1><?=e($heading)?><span class="dot">.</span></h1><p><?=e($intro)?></p></div><?php include __DIR__.'/shop-filters.php'; ?><p class="count"><?=count($products)?> KITS FOUND</p><div class="grid"><?php foreach($products as $product) include __DIR__.'/card.php'; ?></div><?php if(!$products): ?><div class="empty"><?=$shopView==='sale'?'No sale jerseys right now.':'No kits found.'?> <a href="<?=path('shop')?>">Browse all jerseys →</a></div><?php endif; ?></section>
