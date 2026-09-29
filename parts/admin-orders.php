<?php
$search = trim((string)($_GET['q'] ?? ''));
$customerId = max(0,(int)($_GET['customer_id'] ?? 0));
$pageNumber = max(1, (int)($_GET['p'] ?? 1));
$pageSize = 20;
$filters = [];
$params = [];
if ($customerId) { $filters[] = 'user_id=?'; $params[] = $customerId; }
if ($search !== '') { $filters[] = '(order_no LIKE ? OR customer_name LIKE ? OR phone LIKE ?)'; array_push($params,...array_fill(0,3,'%'.$search.'%')); }
$where = $filters ? ' WHERE '.implode(' AND ',$filters) : '';
$count = db()->prepare('SELECT COUNT(*) FROM orders'.$where);
$count->execute($params);
$total = (int)$count->fetchColumn();
$pageCount = max(1,(int)ceil($total/$pageSize));
$pageNumber = min($pageNumber,$pageCount);
$offset = ($pageNumber-1)*$pageSize;
$statement = db()->prepare('SELECT * FROM orders'.$where.' ORDER BY id DESC LIMIT '.$pageSize.' OFFSET '.$offset);
$statement->execute($params);
$orders = $statement->fetchAll();
$itemsByOrder = [];
if ($orders) {
    $ids = array_column($orders,'id');
    $items = db()->prepare('SELECT * FROM order_items WHERE order_id IN ('.implode(',',array_fill(0,count($ids),'?')).') ORDER BY id');
    $items->execute($ids);
    foreach ($items as $item) $itemsByOrder[$item['order_id']][] = $item;
}
$customerName = '';
if ($customerId) { $customer = db()->prepare('SELECT name FROM users WHERE id=? AND role="customer"'); $customer->execute([$customerId]); $customerName = (string)$customer->fetchColumn(); }
?>
<div class="admin-list-panel">
  <div class="admin-list-toolbar"><div><h2><?=$customerId?'Orders for '.e($customerName?:'customer #'.$customerId):'All orders'?></h2><p>Review purchased kits and update fulfillment status. <?php if($customerId): ?><a href="<?=path('admin',['tab'=>'orders'])?>">Show all orders →</a><?php endif; ?></p></div><form class="admin-list-search" method="get"><input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="orders"><?php if($customerId): ?><input type="hidden" name="customer_id" value="<?=$customerId?>"><?php endif; ?><svg><use href="#a-search"/></svg><input type="search" name="q" value="<?=e($search)?>" placeholder="Search orders or customers" aria-label="Search orders or customers"></form></div>
  <div class="admin-table-scroll"><table class="admin-data-table"><thead><tr><th>Order no</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Created on</th><th>Update status</th></tr></thead><tbody><?php foreach($orders as $order): ?><tr><td><strong><?=e($order['order_no'])?></strong></td><td><?php if($order['user_id']): ?><a href="<?=path('admin',['tab'=>'orders','customer_id'=>$order['user_id']])?>"><strong><?=e($order['customer_name'])?></strong></a><?php else: ?><strong><?=e($order['customer_name'])?></strong><?php endif; ?><small><?=e($order['phone'])?></small></td><td><?=price($order['total'])?></td><td><?=e(strtoupper($order['payment_method']))?><small><?=e(ucfirst($order['payment_status']))?></small></td><td><span class="admin-status <?=in_array($order['order_status'],['cancelled'],true)?'is-hidden':(in_array($order['order_status'],['delivered'],true)?'is-active':'is-featured')?>"><?=e(ucfirst($order['order_status']))?></span></td><td><?=e($order['created_at'])?></td><td><form class="admin-inline-update" method="post"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="admin_order"><input type="hidden" name="id" value="<?=$order['id']?>"><input type="hidden" name="return_customer_id" value="<?=$customerId?>"><select name="status" aria-label="Status for <?=e($order['order_no'])?>"><?php foreach(['pending','confirmed','processing','packed','shipped','delivered','cancelled'] as $status): ?><option value="<?=$status?>" <?=$order['order_status']===$status?'selected':''?>><?=ucfirst($status)?></option><?php endforeach; ?></select><button type="submit" class="admin-table-link">Update</button></form></td></tr><?php include __DIR__.'/admin-order-items.php'; ?><?php endforeach; ?><?php if(!$orders): ?><tr><td colspan="7" class="admin-table-empty">No orders found.</td></tr><?php endif; ?></tbody></table></div>
  <div class="admin-list-footer"><span>Showing <?=$total?$offset+1:0?> to <?=min($offset+$pageSize,$total)?> of <?=$total?> orders</span><div class="admin-page-links"><?php if($pageNumber>1): ?><a href="<?=path('admin',['tab'=>'orders','customer_id'=>$customerId,'q'=>$search,'p'=>$pageNumber-1])?>">‹ Previous</a><?php endif; ?><span><?=$pageNumber?> / <?=$pageCount?></span><?php if($pageNumber<$pageCount): ?><a href="<?=path('admin',['tab'=>'orders','customer_id'=>$customerId,'q'=>$search,'p'=>$pageNumber+1])?>">Next ›</a><?php endif; ?></div></div>
</div>
