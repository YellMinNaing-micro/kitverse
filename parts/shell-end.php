  </main>
  <footer class="site-footer">
    <div class="footer-main">
      <div class="footer-brand"><a class="brand" href="<?=path()?>"><svg class="brand-icon" viewBox="0 0 64 64" aria-hidden="true"><path fill="#f8fafc" d="m17 6 15 5L47 6l14 14-9 12-7-5v31H19V27l-7 5L3 20z"/><path fill="#0f172a" d="M25 10h14l-7 9z"/><path fill="#a3e635" d="M36 22 22 43h10l-5 14 18-24H34z"/></svg><span>Kit<strong>Verse</strong></span></a><small>© <?=date('Y')?> KitVerse. All rights reserved.</small></div>
      <div class="footer-links"><b>Shop</b><a href="<?=path('shop')?>">Jerseys</a><a href="<?=path('shop')?>">Clubs</a><a href="<?=path('shop',['sort'=>'new'])?>">New Arrivals</a></div>
      <div class="footer-links"><b>Support</b><a href="<?=path('cart')?>">Your Cart</a><a href="<?=path(current_user()?'orders':'login')?>">Orders</a><a href="<?=path('shop')?>">Size Guide</a></div>
      <div class="footer-links"><b>About</b><span>Our Story</span><span>Sustainability</span><span>Contact</span></div>
      <div class="footer-social"><b>Follow Us</b><span>◎</span><span>▶</span><span>𝕏</span><small>Wear the Match. Live the Verse.</small></div>
    </div>
  </footer>
</div>
</body>
</html>
