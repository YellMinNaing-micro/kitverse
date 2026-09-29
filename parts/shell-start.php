<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>KitVerse | Wear the Match</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="assets/mockup.css">
  <link rel="stylesheet" href="assets/shop-filters.css">
  <script src="assets/filters.js" defer></script>
</head>
<body>
<svg class="svg-defs" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
  <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="7.2"/><path d="m16.2 16.2 5 5"/></symbol>
  <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="7.5" r="4"/><path d="M4.2 21v-2.1c0-3.3 3.1-5.3 7.8-5.3s7.8 2 7.8 5.3V21z"/></symbol>
  <symbol id="i-heart" viewBox="0 0 24 24"><path d="M20.8 8.4c0 4.9-8.8 11.3-8.8 11.3S3.2 13.3 3.2 8.4a4.9 4.9 0 0 1 8.8-2.9 4.9 4.9 0 0 1 8.8 2.9z"/></symbol>
  <symbol id="i-cart" viewBox="0 0 24 24"><path d="M2 3h2.6l2.5 12.4h12.5l2-9.2H5.3"/><circle cx="9" cy="20" r="1"/><circle cx="19" cy="20" r="1"/></symbol>
  <symbol id="i-truck" viewBox="0 0 24 24"><path d="M2 6h12v12H2zM14 10h4l4 4v4h-8z"/><circle cx="6.5" cy="19" r="1.6"/><circle cx="18.5" cy="19" r="1.6"/></symbol>
  <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 2 21 6v6c0 6-4 8.7-9 10-5-1.3-9-4-9-10V6z"/><path d="m8 12 3 3 5-6"/></symbol>
  <symbol id="i-shirt" viewBox="0 0 24 24"><path d="m7 3 5 2 5-2 5 4-3 5-2-1.4V22H7V10.6L5 12 2 7z"/></symbol>
  <symbol id="i-box" viewBox="0 0 24 24"><path d="m12 2 9 4.5v11L12 22l-9-4.5v-11zM3 6.5 12 11l9-4.5M12 11v11"/></symbol>
  <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c-3 3-3 15 0 18M12 3c3 3 3 15 0 18"/></symbol>
</svg>
<div class="site-frame">
  <header class="site-header">
    <div class="header-inner">
      <details class="mobile-menu"><summary aria-label="Open menu"><span></span><span></span><span></span></summary><nav><a href="<?=path()?>">Home</a><a href="<?=path('shop')?>">Jerseys</a><a href="<?=path('shop')?>">Clubs</a><a href="<?=path('shop',['sort'=>'new'])?>">New Arrivals</a><a href="<?=path('shop')?>">Sale</a><?php if(current_user()): ?><a href="<?=path(is_admin()?'admin':'orders')?>"><?=is_admin()?'Dashboard':'Orders'?></a><?php else: ?><a href="<?=path('login')?>">Sign in</a><?php endif; ?></nav></details>
      <a class="brand" href="<?=path()?>" aria-label="KitVerse home"><svg class="brand-icon" viewBox="0 0 64 64" aria-hidden="true"><path fill="#f8fafc" d="m17 6 15 5L47 6l14 14-9 12-7-5v31H19V27l-7 5L3 20z"/><path fill="#0f172a" d="M25 10h14l-7 9z"/><path fill="#a3e635" d="M36 22 22 43h10l-5 14 18-24H34z"/></svg><span>Kit<strong>Verse</strong></span></a>
      <nav class="desktop-nav"><a class="<?=$page==='home'?'active':''?>" href="<?=path()?>">Home</a><a class="<?=$page==='shop'?'active':''?>" href="<?=path('shop')?>">Jerseys</a><a href="<?=path('shop')?>">Clubs</a><a href="<?=path('shop',['sort'=>'new'])?>">New Arrivals</a><a class="sale-link" href="<?=path('shop')?>">Sale</a></nav>
      <div class="header-actions"><a class="icon-link search-icon" href="<?=path('shop')?>" aria-label="Search jerseys"><svg><use href="#i-search"/></svg></a><a class="icon-link account-icon" href="<?=path(current_user()?(is_admin()?'admin':'orders'):'login')?>" aria-label="<?=current_user()?'Your account':'Sign in'?>"><svg><use href="#i-user"/></svg></a><a class="icon-link wishlist-icon" href="<?=path('wishlist')?>" aria-label="Wishlist"><svg><use href="#i-heart"/></svg></a><a class="icon-link cart-icon" href="<?=path('cart')?>" aria-label="Cart, <?=count(cart())?> items"><svg><use href="#i-cart"/></svg><?php if(count(cart())): ?><span class="cart-count"><?=count(cart())?></span><?php endif; ?></a></div>
    </div>
  </header>
  <?php if($message): ?><div class="wrap alert success"><?=e($message)?></div><?php endif; ?>
  <?php if($error): ?><div class="wrap alert error"><?=e($error)?></div><?php endif; ?>
  <main>
