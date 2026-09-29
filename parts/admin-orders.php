<?php
$search = trim((string)($_GET['q'] ?? ''));
$pageNumber = max(1, (int)($_GET['p'] ?? 1));
$pageSize = 20;
$where = $search === '' ? '' : ' WHERE order_no LIKE ? OR customer_name LIKE ? OR phone LIKE ?';
$params = $search === '' ? [] : array_fill(0,3,'%'.$search.'%');
$count = db()->prepare('SELECT COUNT(*) FROM orders'.$where);
$count->execute($params);
$total = (int)$count->fetchColumn();
$pageCount = max(1,(int)ceil($total/$pageSize));
$pageNumber = min($pageNumber,$pageCount);
$offset = ($pageNumber-1)*$pageSize;
$statement = db()->prepare('SELECT * FROM orders'.$where.' ORDER BY id DESC LIMIT '.$pageSize.' OFFSET '.$offset);
$statement->execute($params);
$orders = $statement->fetchAll();
?>
<div class="admin-list-panel">
  <div class="admin-list-toolbar"><div><h2>All orders</h2><p>Review orders and update fulfillment status.</p></div><form class="admin-list-search" method="get"><input type="hidden" name="page" value="admin"><input type="hidden" name="tab" value="orders"><svg><use href="#a-search"/></svg><input type="search" name="q" value="<?=e($search)?>" placeholder="Search orders or customers" aria-label="Search orders or customers"></form></div>
  <div class="admin-table-scroll"><table class="admin-data-table"><thead><tr><th>Order no</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Created on</th><th>Update status</th></tr></thead><tbody><?php foreach($orders as $order): ?><tr><td><strong><?=e($order['order_no'])?></strong></td><td><strong><?=e($order['customer_name'])?></strong><small><?=e($order['phone'])?></small></td><td><?=price($order['total'])?></td><td><?=e(strtoupper($order['payment_method']))?><small><?=e(ucfirst($order['payment_status']))?></small></td><td><span class="admin-status <?=in_array($order['order_status'],['cancelled'],true)?'is-hidden':(in_array($order['order_status'],['delivered'],true)?'is-active':'is-featured')?>"><?=e(ucfirst($order['order_status']))?></span></td><td><?=e($order['created_at'])?></td><td><form class="admin-inline-update" method="post"><input type="hidden" name="csrf" value="<?=token()?>"><input type="hidden" name="action" value="admin_order"><input type="hidden" name="id" value="<?=$order['id']?>"><select name="status" aria-label="Status for <?=e($order['order_no'])?>"><?php foreach(['pending','confirmed','processing','packed','shipped','delivered','cancelled'] as $status): ?><option value="<?=$status?>" <?=$order['order_status']===$status?'selected':''?>><?=ucfirst($status)?></option><?php endforeach; ?></select><button type="submit" class="admin-table-link">Update</button></form></td></tr><?php endforeach; ?><?php if(!$orders): ?><tr><td colspan="7" class="admin-table-empty">No orders found.</td></tr><?php endif; ?></tbody></table></div>
  <div class="admin-list-footer"><span>Showing <?=$total?$offset+1:0?> to <?=min($offset+$pageSize,$total)?> of <?=$total?> orders</span><div class="admin-page-links"><?php if($pageNumber>1): ?><a href="<?=path('admin',['tab'=>'orders','q'=>$search,'p'=>$pageNumber-1])?>">‹ Previous</a><?php endif; ?><span><?=$pageNumber?> / <?=$pageCount?></span><?php if($pageNumber<$pageCount): ?><a href="<?=path('admin',['tab'=>'orders','q'=>$search,'p'=>$pageNumber+1])?>">Next ›</a><?php endif; ?></div></div>
</div>
