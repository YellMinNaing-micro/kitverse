<?php
declare(strict_types=1);
require_once __DIR__ . '/db-config.php';
session_start();
function db(): PDO { static $pdo; $config=db_config(); return $pdo ??= new PDO('mysql:host='.$config['host'].';dbname='.$config['name'].';charset=utf8mb4', $config['user'], $config['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]); }
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function price($v): string { return number_format((float)$v).' MMK'; }
function discounted_price($amount, $percent): float { return round((float)$amount * (100 - (int)$percent) / 100); }
function new_product_ids(): array { static $ids=null; return $ids ??= array_map('intval',db()->query('SELECT id FROM products WHERE status=1 ORDER BY id DESC LIMIT 5')->fetchAll(PDO::FETCH_COLUMN)); }
function path(string $page='home', array $extra=[]): string { return '?'.http_build_query(['page'=>$page]+$extra); }
function redirect(string $page='home', array $extra=[]): never { header('Location: '.path($page,$extra)); exit; }
function token(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(24)); }
function current_user(): ?array { return $_SESSION['user']??null; }
function is_admin(): bool { $user=current_user();if(($user['role']??'')!=='admin')return false;static $allowed=null;if($allowed===null){$s=db()->prepare("SELECT 1 FROM users WHERE id=? AND role='admin' AND status=1");$s->execute([(int)$user['id']]);$allowed=(bool)$s->fetchColumn();}return $allowed; }
function owner(): array { return current_user()?['user_id',current_user()['id']]:['session_id',session_id()]; }
function cart(): array { [$key,$value]=owner(); $s=db()->prepare("SELECT ci.*,v.size,v.stock,COALESCE(v.price,p.base_price) list_price,ROUND(COALESCE(v.price,p.base_price)*(100-p.discount_percent)/100,0) price,p.discount_percent,p.name,p.image,p.id product_id,p.status product_status,v.status variant_status FROM cart_items ci JOIN product_variants v ON v.id=ci.product_variant_id JOIN products p ON p.id=v.product_id WHERE ci.$key=? ORDER BY ci.id DESC");$s->execute([$value]);return $s->fetchAll(); }
function totals(array $cart): array { $base=$custom=$saved=0;foreach($cart as $item){$base+=(float)$item['price']*$item['quantity'];$saved+=((float)($item['list_price']??$item['price'])-(float)$item['price'])*$item['quantity'];$custom+=($item['custom_name']!==null||$item['custom_number']!==null?5000:0)*$item['quantity'];}return [$base,$custom,$base+$custom,$saved]; }
function post(string $key): string { return trim((string)($_POST[$key]??'')); }
function flash(string $message): void { $_SESSION['flash']=$message; }
$page=(string)($_GET['page']??'home');$error='';
if($page==='admin' && !current_user() && $_SERVER['REQUEST_METHOD']==='GET') redirect('login');
try {
if($_SERVER['REQUEST_METHOD']==='POST') {
 if(!hash_equals(token(),(string)($_POST['csrf']??''))) throw new RuntimeException('Form expired. Try again.');
 $action=post('action');
 if($action==='register') { $name=post('name');$email=strtolower(post('email'));$password=(string)($_POST['password']??'');if(!$name||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($password)<8)throw new RuntimeException('Enter your name, valid email, and a password of at least 8 characters.');$s=db()->prepare('INSERT INTO users(name,email,password,phone,role) VALUES(?,?,?,?,"customer")');$s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),post('phone')]);$old=session_id();session_regenerate_id(true);$_SESSION['user']=['id'=>(int)db()->lastInsertId(),'name'=>$name,'role'=>'customer'];db()->prepare('UPDATE cart_items SET user_id=?,session_id=NULL WHERE session_id=? AND user_id IS NULL')->execute([current_user()['id'],$old]);redirect('shop'); }
 if($action==='login') { $s=db()->prepare('SELECT * FROM users WHERE email=? AND status=1');$s->execute([strtolower(post('email'))]);$u=$s->fetch();if(!$u||!password_verify((string)($_POST['password']??''),$u['password']))throw new RuntimeException('Invalid email or password.');$old=session_id();session_regenerate_id(true);$_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'role'=>$u['role']];db()->prepare('UPDATE cart_items SET user_id=?,session_id=NULL WHERE session_id=? AND user_id IS NULL')->execute([$u['id'],$old]);redirect($u['role']==='admin'?'admin':'shop'); }
 if($action==='logout') { $_SESSION=[];session_regenerate_id(true);redirect(); }
 if($action==='add') { $id=(int)($_POST['variant_id']??0);$qty=max(1,min(20,(int)($_POST['quantity']??1)));$s=db()->prepare('SELECT v.stock FROM product_variants v JOIN products p ON p.id=v.product_id WHERE v.id=? AND v.status=1 AND p.status=1');$s->execute([$id]);$v=$s->fetch();if(!$v||$v['stock']<$qty)throw new RuntimeException('This size is out of stock.');$name=post('custom_name');$number=post('custom_number');if(mb_strlen($name)>50||mb_strlen($number)>10)throw new RuntimeException('Customization is too long.');db()->prepare('INSERT INTO cart_items(user_id,session_id,product_variant_id,quantity,custom_name,custom_number) VALUES(?,?,?,?,?,?)')->execute([current_user()['id']??null,current_user()?null:session_id(),$id,$qty,$name?:null,$number?:null]);flash('Added to cart.');redirect('cart'); }
 if($action==='cart') { [$key,$value]=owner();$id=(int)($_POST['id']??0);$qty=(int)($_POST['quantity']??0);$s=db()->prepare("SELECT v.stock FROM cart_items ci JOIN product_variants v ON v.id=ci.product_variant_id WHERE ci.id=? AND ci.$key=?");$s->execute([$id,$value]);$stock=$s->fetchColumn();if($stock===false)throw new RuntimeException('Cart item not found.');if($qty<=0){db()->prepare("DELETE FROM cart_items WHERE id=? AND $key=?")->execute([$id,$value]);}else{if((int)$stock<1)throw new RuntimeException('This size is out of stock.');$qty=min($qty,(int)$stock,20);db()->prepare("UPDATE cart_items SET quantity=? WHERE id=? AND $key=?")->execute([$qty,$id,$value]);}if(post('ajax')==='1'){[$subtotal,$personalization,$total]=totals(cart());header('Content-Type: application/json; charset=utf-8');echo json_encode(['quantity'=>$qty<=0?0:$qty,'subtotal'=>$subtotal,'personalization'=>$personalization,'total'=>$total,'itemCount'=>count(cart())]);exit;}redirect('cart'); }
 if($action==='wishlist') { $id=(int)($_POST['id']??0); $s=db()->prepare('SELECT id FROM products WHERE id=? AND status=1'); $s->execute([$id]); if(!$s->fetch())throw new RuntimeException('Product not found.'); $list=$_SESSION['wishlist']??[]; if(in_array($id,$list,true))$list=array_values(array_diff($list,[$id])); else $list[]=$id; $_SESSION['wishlist']=$list; $returnTo=post('return_to'); redirect(in_array($returnTo,['home','shop','wishlist'],true)?$returnTo:'home'); }
 if($action==='checkout') { require __DIR__.'/parts/checkout-action.php'; }
 if(str_starts_with($action,'admin_')) { if(!is_admin())throw new RuntimeException('Access denied.');
  if($action==='admin_inventory_stock') { $variantId=(int)($_POST['variant_id']??0);$rawStock=post('stock');if($variantId<1||!ctype_digit($rawStock)||strlen($rawStock)>9)throw new RuntimeException('Enter a valid stock quantity.');$stock=(int)$rawStock;$s=db()->prepare('SELECT product_id FROM product_variants WHERE id=?');$s->execute([$variantId]);$productId=(int)$s->fetchColumn();if(!$productId)throw new RuntimeException('Size not found.');db()->prepare('UPDATE product_variants SET stock=? WHERE id=?')->execute([$stock,$variantId]);flash('Stock updated.');redirect('admin',['tab'=>'inventory','focus'=>$productId]); }
  if($action==='admin_order') { $status=post('status');if(!in_array($status,['pending','confirmed','processing','packed','shipped','delivered','cancelled'],true))throw new RuntimeException('Invalid status.');$id=(int)($_POST['id']??0);db()->beginTransaction();try{db()->prepare('UPDATE orders SET order_status=? WHERE id=?')->execute([$status,$id]);db()->prepare('INSERT INTO order_status_history(order_id,status) VALUES(?,?)')->execute([$id,$status]);db()->commit();}catch(Throwable $ex){db()->rollBack();throw $ex;}flash('Order updated.');$customerId=max(0,(int)($_POST['return_customer_id']??0));redirect('admin',$customerId?['tab'=>'orders','customer_id'=>$customerId]:['tab'=>'orders']); }
  if($action==='admin_product') {
   require_once __DIR__.'/parts/product-image-upload.php';
   $id=(int)($_POST['id']??0);$name=post('name');$club=(int)($_POST['club_id']??0);$category=(int)($_POST['category_id']??0);$price=(float)($_POST['base_price']??0);
   $discountRaw=post('discount_percent');$discount=ctype_digit($discountRaw)?(int)$discountRaw:-1;if(!$name||$club<1||$category<1||$price<=0||$discount<0||$discount>90)throw new RuntimeException('Complete product details and enter a discount from 0 to 90%.');
   $upload=$_FILES['product_image']??[];
   $extension=product_image_extension($upload);
   $oldImage=null;$newImage=null;
   db()->beginTransaction();
   try {
    if($id){
     $s=db()->prepare('SELECT image FROM products WHERE id=? FOR UPDATE');$s->execute([$id]);$row=$s->fetch();
     if(!$row)throw new RuntimeException('Product not found.');
     $oldImage=$row['image'];
     db()->prepare('UPDATE products SET club_id=?,category_id=?,name=?,season=?,description=?,base_price=?,discount_percent=?,is_featured=?,status=? WHERE id=?')->execute([$club,$category,$name,post('season'),post('description'),$price,$discount,post('is_featured')==='1'?1:0,post('status')==='1'?1:0,$id]);
    }else{
     db()->prepare('INSERT INTO products(club_id,category_id,name,season,description,base_price,discount_percent,is_featured,status) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$club,$category,$name,post('season'),post('description'),$price,$discount,post('is_featured')==='1'?1:0,post('status')==='1'?1:0]);
     $id=(int)db()->lastInsertId();
    }
    if($extension!==null){
     $newImage=save_product_image($upload,$id,$extension);
     db()->prepare('UPDATE products SET image=? WHERE id=?')->execute([$newImage,$id]);
    }
    db()->commit();
   }catch(Throwable $ex){
    db()->rollBack();
    if($newImage!==null)remove_replaced_product_image($newImage,$id);
    throw $ex;
   }
   if($newImage!==null)remove_replaced_product_image($oldImage,$id);
   flash('Product saved: '.$discount.'% discount, sale price '.price(discounted_price($price,$discount)).'.');redirect('admin',['tab'=>'products','edit'=>$id]);
  }
  if($action==='admin_variant') { $pid=(int)($_POST['product_id']??0);$size=post('size');$stock=(int)($_POST['stock']??-1);if($pid<1||!in_array($size,['XS','S','M','L','XL','XXL'],true)||$stock<0)throw new RuntimeException('Invalid variant.');$sku=post('sku')?:'KV-'.$pid.'-'.$size;$raw=post('price');$override=$raw===''?null:(float)$raw;db()->prepare('INSERT INTO product_variants(product_id,size,sku,price,stock,status) VALUES(?,?,?,?,?,1) ON DUPLICATE KEY UPDATE sku=VALUES(sku),price=VALUES(price),stock=VALUES(stock),status=1')->execute([$pid,$size,$sku,$override,$stock]);flash('Size and stock saved.');redirect('admin',['tab'=>'products','edit'=>$pid]); }
  if($action==='admin_catalog') { $type=post('type');$name=post('name');if(!$name)throw new RuntimeException('Name is required.');if($type==='clubs')db()->prepare('INSERT INTO clubs(league_id,name,status) VALUES(?,?,1)')->execute([(int)($_POST['league_id']??0),$name]);elseif($type==='leagues')db()->prepare('INSERT INTO leagues(name,country,status) VALUES(?,?,1)')->execute([$name,post('country')]);elseif($type==='categories')db()->prepare('INSERT INTO categories(name,description,status) VALUES(?,?,1)')->execute([$name,post('description')]);else throw new RuntimeException('Invalid catalog type.');flash('Catalog updated.');redirect('admin',['tab'=>'catalog']); }
 }
}
} catch(Throwable $ex) { $error=$ex instanceof PDOException?'Database error. Check the details and try again.':$ex->getMessage(); }
try {$categories=db()->query('SELECT * FROM categories WHERE status=1 ORDER BY name')->fetchAll();$leagues=db()->query('SELECT * FROM leagues WHERE status=1 ORDER BY name')->fetchAll();$clubs=db()->query('SELECT * FROM clubs WHERE status=1 ORDER BY name')->fetchAll();}catch(Throwable $ex){http_response_code(500);exit('Database unavailable. Start MySQL in XAMPP and import database/schema.sql.');}
$message=$_SESSION['flash']??'';unset($_SESSION['flash']);
if($page==='admin' && is_admin()) {
 include __DIR__.'/parts/admin-shell-start.php';
 include __DIR__.'/parts/admin.php';
 include __DIR__.'/parts/admin-shell-end.php';
 exit;
}
?><?php include __DIR__.'/parts/shell-start.php'; ?>
<?php if($page==='home'): include __DIR__.'/parts/home.php'; ?>
<?php elseif($page==='shop'): include __DIR__.'/parts/shop.php';
elseif($page==='clubs'): include __DIR__.'/parts/clubs.php';
elseif($page==='product'): $s=db()->prepare('SELECT p.*,cl.name club,l.name league,c.name category FROM products p JOIN clubs cl ON cl.id=p.club_id JOIN leagues l ON l.id=cl.league_id JOIN categories c ON c.id=p.category_id WHERE p.id=? AND p.status=1');$s->execute([(int)($_GET['id']??0)]);$product=$s->fetch();if(!$product):http_response_code(404);?><div class="wrap empty">Kit not found.</div><?php else:$s=db()->prepare('SELECT * FROM product_variants WHERE product_id=? AND status=1 ORDER BY FIELD(size,"XS","S","M","L","XL","XXL")');$s->execute([$product['id']]);$variants=$s->fetchAll();?><section class="wrap section"><a class="back" href="<?=path('shop')?>">← BACK TO SHOP</a><div class="detail"><div class="detail-image"><?php if($product['image']&&is_file(__DIR__.'/'.$product['image'])):?><img src="<?=e($product['image'])?>" alt="<?=e($product['name'])?>"><?php else:?><img src="assets/jersey-<?=str_contains(strtolower($product['category']),'away')?'away':(str_contains(strtolower($product['category']),'third')?'third':'home')?>.png" alt="<?=e($product['category'])?> jersey concept"><?php endif;?></div><div><span class="eyebrow"><?=e($product['league'])?> / <?=e($product['category'])?></span><h1><?=e($product['name'])?></h1><p><?=e($product['club'])?> · <?=e($product['season'])?></p><strong class="detail-price"><?=price(discounted_price($product['base_price'],$product['discount_percent']))?><?php if($product['discount_percent']): ?><del><?=price($product['base_price'])?></del><span class="detail-discount">-<?=e($product['discount_percent'])?>%</span><?php endif; ?></strong><p class="description"><?=nl2br(e($product['description']?:'Made for fans who wear their colors with pride.'))?></p><form method="post" class="form"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="add"><label>CHOOSE YOUR SIZE</label><div class="sizes"><?php foreach($variants as $variant):?><label><input type="radio" name="variant_id" value="<?=$variant['id']?>" <?=$variant['stock']<1?'disabled':''?> required><span><?=e($variant['size'])?></span></label><?php endforeach;?></div><small>Sold out sizes cannot be selected.</small><label>QUANTITY<input type="number" name="quantity" value="1" min="1" max="20" required></label><div class="two"><label>CUSTOM NAME <small>(OPTIONAL)</small><input name="custom_name" maxlength="50" placeholder="Name on back"></label><label>NUMBER <small>(OPTIONAL)</small><input name="custom_number" maxlength="10" placeholder="e.g. 10"></label></div><small>Personalization: +5,000 MMK per jersey</small><button class="btn lime full" <?=$variants?'':'disabled'?>>ADD TO CART ↗</button></form></div></div></section><?php endif;
elseif($page==='cart'): include __DIR__.'/parts/cart.php';
elseif($page==='login'||$page==='register'):?><section class="wrap auth"><div class="auth-card"><span class="eyebrow">JOIN THE TEAM</span><h1><?=$page==='login'?'Welcome back.':'Create account.'?></h1><p>Every kit has a story. Start yours here.</p><form method="post" class="form"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="<?=$page?>"><?php if($page==='register'):?><label>FULL NAME<input name="name" required maxlength="100"></label><label>PHONE<input name="phone" maxlength="30"></label><?php endif;?><label>EMAIL<input type="email" name="email" required></label><label>PASSWORD<input type="password" name="password" required <?=$page==='register'?'minlength="8"':''?>></label><button class="btn lime full"><?=$page==='login'?'SIGN IN':'CREATE ACCOUNT'?> ↗</button></form><p class="switch"><?=$page==='login'?'New to KitVerse?':'Already a member?'?> <a href="<?=path($page==='login'?'register':'login')?>"><?=$page==='login'?'Create an account':'Sign in'?></a></p></div></section>
<?php elseif($page==='checkout'): include __DIR__.'/parts/checkout.php';
elseif($page==='orders'): include __DIR__.'/parts/customer-orders.php';
elseif($page==='wishlist'): include __DIR__.'/parts/wishlist.php'; ?>
<?php elseif($page==='admin'):if(!is_admin()){http_response_code(403);?><div class="wrap empty">Access denied.</div><?php }else include __DIR__.'/parts/admin.php';
else:http_response_code(404);?><div class="wrap empty">Page not found.</div><?php endif;?><?php include __DIR__.'/parts/shell-end.php'; ?>
