<?php
$adminTab = (string)($_GET['tab'] ?? 'overview');
$adminTitles = ['overview' => 'Admin Dashboard', 'products' => 'Products', 'catalog' => 'Categories & Clubs', 'orders' => 'Orders', 'customers' => 'Customers', 'inventory' => 'Inventory', 'payments' => 'Payments'];
if(!isset($adminTitles[$adminTab]))$adminTab='overview';
$adminTitle = $adminTitles[$adminTab] ?? 'Admin Dashboard';
$pendingOrders = (int)db()->query("SELECT COUNT(*) FROM orders WHERE order_status='pending'")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?=e($adminTitle)?> | KitVerse</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/admin.css">
  <link rel="stylesheet" href="assets/admin-dashboard.css">
  <link rel="stylesheet" href="assets/admin-tables.css">
  <link rel="stylesheet" href="assets/account-orders.css">
  <script src="assets/admin-controls.js" defer></script>
</head>
<body class="admin-body">
<svg class="admin-icons" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <filter id="brand-on-dark" color-interpolation-filters="sRGB"><feColorMatrix type="matrix" values="-0.6216 0 0 0 1.0366 -0.1689 0 0 0 1.0099 -1.3649 0 0 0 1.0803 0 0 0 1 0"/></filter>
  <symbol id="a-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></symbol>
  <symbol id="a-shirt" viewBox="0 0 24 24"><path d="m7 3 5 2 5-2 5 4-3 5-2-1.4V22H7V10.6L5 12 2 7z"/></symbol>
  <symbol id="a-box" viewBox="0 0 24 24"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11zM3 6.5 12 11l9-4.5M12 11v11"/></symbol>
  <symbol id="a-users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2c0-3 2.5-5 6-5s6 2 6 5v2zM17 5a3 3 0 0 1 0 6m1 2c2 0 3 2 3 5v2h-4"/></symbol>
  <symbol id="a-orders" viewBox="0 0 24 24"><path d="M5 3h14v18H5zM8 8h8M8 12h8M8 16h5"/></symbol>
  <symbol id="a-card" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h5"/></symbol>
  <symbol id="a-alert" viewBox="0 0 24 24"><path d="m12 3 10 18H2zM12 9v5m0 3v.5"/></symbol>
  <symbol id="a-search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="7.2"/><path d="m16.2 16.2 5 5"/></symbol>
  <symbol id="a-logout" viewBox="0 0 24 24"><path d="M10 3H4v18h6M15 7l5 5-5 5M8 12h12"/></symbol>
</svg>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <a class="admin-brand" href="<?=path('admin')?>" aria-label="KitVerse admin dashboard"><img class="admin-brand-logo" src="assets/kitverse-logo.png" alt=""><small>ADMIN CONTROL</small></a>
    <div class="admin-sidebar-label">STORE MANAGEMENT</div>
    <nav class="admin-side-nav" aria-label="Admin navigation">
      <?php foreach([
        ['overview','Dashboard','a-grid'],['products','Products','a-shirt'],['catalog','Categories & Clubs','a-grid'],['orders','Orders','a-orders'],['customers','Customers','a-users'],['inventory','Inventory','a-box'],['payments','Payments','a-card']
      ] as [$key,$label,$icon]): ?>
      <a class="<?=$adminTab===$key?'active':''?>" href="<?=path('admin',$key==='overview'?[]:['tab'=>$key])?>" <?=$adminTab===$key?'aria-current="page"':''?>><svg><use href="#<?=$icon?>"/></svg><span><?=e($label)?></span><?php if($key==='orders'&&$pendingOrders): ?><b><?=e($pendingOrders)?></b><?php endif; ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="admin-sidebar-bottom"><span class="eyebrow">WEAR THE MATCH</span><strong>Football<br><em>lives here.</em></strong><a href="<?=path('home')?>">View storefront →</a></div>
  </aside>
  <div class="admin-main">
    <header class="admin-topbar">
      <div class="admin-topbar-heading"><span class="admin-role-tag">STORE ADMIN</span><h1><?=e($adminTitle)?></h1><p>Manage your store and track what matters.</p></div>
      <div class="admin-topbar-actions">
        <form class="admin-header-search" method="get"><input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="products"><svg><use href="#a-search"/></svg><input type="search" name="q" value="<?=e($_GET['q']??'')?>" placeholder="Search products..." aria-label="Search products"></form>
        <a class="admin-notification" href="<?=path('admin',['tab'=>'orders'])?>" aria-label="<?=e($pendingOrders)?> pending orders"><svg><use href="#a-orders"/></svg><?php if($pendingOrders): ?><b><?=e($pendingOrders)?></b><?php endif; ?></a>
        <div class="admin-profile"><span class="admin-avatar"><?=e(mb_strtoupper(mb_substr(current_user()['name']??'A',0,1)))?></span><span><strong><?=e(current_user()['name']??'Admin')?></strong><small>Store Admin</small></span></div>
        <form method="post" class="admin-logout"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="logout"><button type="submit" aria-label="Log out" title="Log out"><svg><use href="#a-logout"/></svg><span>Log out</span></button></form>
      </div>
    </header>
    <?php if($message): ?><div class="admin-flash success"><?=e($message)?></div><?php endif; ?>
    <?php if($error): ?><div class="admin-flash error"><?=e($error)?></div><?php endif; ?>
    <main class="admin-content">
