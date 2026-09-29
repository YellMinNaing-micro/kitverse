  </main>
  <footer class="site-footer">
    <div class="footer-main">
      <div class="footer-brand"><a class="brand" href="<?=path()?>"><img class="brand-lockup" src="assets/kitverse-logo.png" alt=""></a><small>© <?=date('Y')?> KitVerse. All rights reserved.</small></div>
      <div class="footer-links"><b>Shop</b><a href="<?=path('shop')?>">Jerseys</a><a href="<?=path('clubs')?>">Clubs</a><a href="<?=path('shop',['view'=>'new'])?>">New Arrivals</a><a href="<?=path('shop',['view'=>'sale'])?>">Sale</a></div>
      <div class="footer-links"><b>Support</b><a href="<?=path('cart')?>">Your Cart</a><a href="<?=path(current_user()?'orders':'login')?>">Orders</a><a href="<?=path('shop')?>">Size Guide</a></div>
      <div class="footer-links"><b>About</b><span>Our Story</span><span>Sustainability</span><span>Contact</span></div>
      <div class="footer-social"><b>Follow Us</b><span>◎</span><span>▶</span><span>𝕏</span><small>Wear the Match. Live the Verse.</small></div>
    </div>
  </footer>
</div>
</body>
</html>
