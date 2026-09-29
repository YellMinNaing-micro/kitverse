<?php
if (!current_user()) redirect('login');
$statement = db()->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC');
$statement->execute([(int)current_user()['id']]);
$orders = $statement->fetchAll();
$itemsByOrder = [];
if ($orders) {
    $ids = array_column($orders, 'id');
    $items = db()->prepare('SELECT * FROM order_items WHERE order_id IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY id');
    $items->execute($ids);
    foreach ($items as $item) $itemsByOrder[$item['order_id']][] = $item;
}
?>
<section class="wrap section customer-orders"><div class="page-title"><span class="eyebrow">YOUR MATCH HISTORY</span><h1>My orders<span class="dot">.</span></h1><p>See what you bought and track each order.</p></div>
<?php if (!$orders): ?><div class="empty">No orders yet. <a href="<?=path('shop')?>">Shop kits →</a></div><?php endif; ?>
<?php foreach ($orders as $order): ?><article class="customer-order-card"><div class="customer-order-head"><div><small>ORDER <?=e($order['order_no'])?> · <?=e(date('M j, Y',strtotime($order['created_at'])))?></small><h2><?=price($order['total'])?></h2></div><span class="badge"><?=e(ucfirst($order['order_status']))?></span></div><div class="customer-order-items"><?php foreach($itemsByOrder[$order['id']]??[] as $item): ?><div class="customer-order-item"><div><strong><?=e($item['product_name'])?></strong><small>Size <?=e($item['size'])?> · Qty <?=e($item['quantity'])?><?php if($item['custom_name'] || $item['custom_number']): ?> · <?=e(trim(($item['custom_name']??'').' '.($item['custom_number']??'')))?><?php endif; ?></small></div><b><?=price($item['line_total'])?></b></div><?php endforeach; ?></div><div class="customer-order-foot"><span>Payment: <?=e(strtoupper($order['payment_method']))?> · <?=e(ucfirst($order['payment_status']))?></span><?php if($order['discount_amount']>0): ?><span>Discount saved: <?=price($order['discount_amount'])?></span><?php endif; ?><span>Delivery: <?=e($order['address'])?></span></div></article><?php endforeach; ?>
</section>
