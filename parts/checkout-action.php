<?php
if (!current_user()) redirect('login');
$name = post('name');
$phone = post('phone');
$address = post('address');
$method = post('payment_method');
if (!$name || !$phone || !$address || !in_array($method,['cod','kbzpay','wavepay','ayapay'],true)) throw new RuntimeException('Complete delivery and payment details.');

db()->beginTransaction();
try {
    $items = cart();
    if (!$items) throw new RuntimeException('Cart is empty.');
    foreach ($items as $item) {
        $stockQuery = db()->prepare('SELECT stock FROM product_variants WHERE id=? AND status=1 FOR UPDATE');
        $stockQuery->execute([$item['product_variant_id']]);
        $stock = $stockQuery->fetch();
        if (!$stock || !$item['product_status'] || $stock['stock'] < $item['quantity']) throw new RuntimeException($item['name'].' is unavailable in that quantity.');
    }
    [$subtotal,$custom,$total,$saved] = totals($items);
    $number = 'KV-'.date('Ymd').'-'.strtoupper(bin2hex(random_bytes(4)));
    db()->prepare('INSERT INTO orders(user_id,order_no,customer_name,customer_email,phone,address,township,city,subtotal,customization_total,shipping_fee,discount_amount,total,payment_method,note) VALUES(?,?,?,?,?,?,?,?,?,?,0,?,?,?,?)')->execute([current_user()['id'],$number,$name,post('email')?:null,$phone,$address,post('township')?:null,post('city')?:null,$subtotal+$saved,$custom,$saved,$total,$method,post('note')?:null]);
    $orderId = (int)db()->lastInsertId();
    $insertItem = db()->prepare('INSERT INTO order_items(order_id,product_variant_id,product_name,size,quantity,unit_price,custom_name,custom_number,customization_price,line_total) VALUES(?,?,?,?,?,?,?,?,?,?)');
    $reduceStock = db()->prepare('UPDATE product_variants SET stock=stock-? WHERE id=?');
    foreach ($items as $item) {
        $extra = $item['custom_name'] !== null || $item['custom_number'] !== null ? 5000 : 0;
        $insertItem->execute([$orderId,$item['product_variant_id'],$item['name'],$item['size'],$item['quantity'],$item['price'],$item['custom_name'],$item['custom_number'],$extra,((float)$item['price']+$extra)*$item['quantity']]);
        $reduceStock->execute([$item['quantity'],$item['product_variant_id']]);
    }
    db()->prepare('INSERT INTO payments(order_id,payment_method,amount) VALUES(?,?,?)')->execute([$orderId,$method,$total]);
    db()->prepare('INSERT INTO order_status_history(order_id,status,note) VALUES(?,"pending","Order placed")')->execute([$orderId]);
    db()->prepare('DELETE FROM cart_items WHERE user_id=?')->execute([current_user()['id']]);
    db()->commit();
    flash('Order '.$number.' placed successfully.');
    redirect('orders');
} catch (Throwable $ex) {
    db()->rollBack();
    throw $ex;
}
