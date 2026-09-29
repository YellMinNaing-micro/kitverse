<?php
$summary = db()->query("SELECT COUNT(*) orders_count, COALESCE(SUM(CASE WHEN order_status<>'cancelled' THEN total ELSE 0 END),0) sales FROM orders")->fetch();
$customerCount = (int)db()->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$lowStockCount = (int)db()->query('SELECT COUNT(*) FROM product_variants v JOIN products p ON p.id=v.product_id WHERE p.status=1 AND v.status=1 AND v.stock<=5')->fetchColumn();
$recentOrders = db()->query('SELECT id,order_no,customer_name,total,payment_method,order_status,created_at FROM orders ORDER BY id DESC LIMIT 5')->fetchAll();
$lowStock = db()->query('SELECT p.id,p.name,p.image,v.size,v.stock FROM product_variants v JOIN products p ON p.id=v.product_id WHERE p.status=1 AND v.status=1 AND v.stock<=5 ORDER BY v.stock ASC,p.name LIMIT 5')->fetchAll();
$recentCustomers = db()->query("SELECT name,email,created_at FROM users WHERE role='customer' ORDER BY id DESC LIMIT 5")->fetchAll();
$topProducts = db()->query("SELECT oi.product_name,SUM(oi.quantity) sold,SUM(oi.line_total) revenue,MAX(p.image) image FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN product_variants v ON v.id=oi.product_variant_id LEFT JOIN products p ON p.id=v.product_id WHERE o.order_status<>'cancelled' GROUP BY oi.product_name ORDER BY sold DESC LIMIT 5")->fetchAll();
$categorySales = db()->query("SELECT c.name,SUM(oi.line_total) revenue FROM order_items oi JOIN orders o ON o.id=oi.order_id JOIN product_variants v ON v.id=oi.product_variant_id JOIN products p ON p.id=v.product_id JOIN categories c ON c.id=p.category_id WHERE o.order_status<>'cancelled' GROUP BY c.id,c.name ORDER BY revenue DESC LIMIT 5")->fetchAll();
$statusRows = db()->query("SELECT order_status,COUNT(*) count FROM orders WHERE order_status<>'cancelled' GROUP BY order_status")->fetchAll();
$statusCounts = ['pending'=>0,'processing'=>0,'shipped'=>0,'delivered'=>0];
foreach ($statusRows as $row) {
    $group = in_array($row['order_status'], ['confirmed','processing','packed'], true) ? 'processing' : $row['order_status'];
    if (isset($statusCounts[$group])) $statusCounts[$group] += (int)$row['count'];
}
$activeOrders = array_sum($statusCounts);
$statusColors = ['pending'=>'#facc15','processing'=>'#3b82f6','shipped'=>'#2ec271','delivered'=>'#132236'];
$start = 0;
$segments = [];
foreach ($statusCounts as $key => $count) {
    $end = $start + ($activeOrders ? 100 * $count / $activeOrders : 0);
    $segments[] = $statusColors[$key].' '.$start.'% '.$end.'%';
    $start = $end;
}
$donut = $activeOrders ? 'conic-gradient('.implode(',',$segments).')' : '#e9eef2';
$salesRows = db()->query("SELECT DATE(created_at) day,SUM(total) sales FROM orders WHERE created_at>=CURDATE()-INTERVAL 29 DAY AND order_status<>'cancelled' GROUP BY DATE(created_at)")->fetchAll();
$salesByDate = array_column($salesRows, 'sales', 'day');
$chart = [];
for ($days = 29; $days >= 0; $days--) {
    $date = date('Y-m-d', strtotime('-'.$days.' days'));
    $chart[] = (float)($salesByDate[$date] ?? 0);
}
$chartMax = max(1, ...$chart);
$categoryMax = max(array_merge([1], array_map(static fn($row) => (float)$row['revenue'], $categorySales)));
?>
<div class="dash-metrics">
  <div class="dash-card dash-metric"><div class="dash-metric-icon"><svg><use href="#a-chart"/></svg></div><div><span>Order Revenue</span><strong><?=price($summary['sales'])?></strong><small>Excludes cancelled orders</small></div></div>
  <div class="dash-card dash-metric"><div class="dash-metric-icon"><svg><use href="#a-orders"/></svg></div><div><span>Total Orders</span><strong><?=e($summary['orders_count'])?></strong><small>All time</small></div></div>
  <div class="dash-card dash-metric"><div class="dash-metric-icon blue"><svg><use href="#a-users"/></svg></div><div><span>Customers</span><strong><?=$customerCount?></strong><small>Registered accounts</small></div></div>
  <div class="dash-card dash-metric"><div class="dash-metric-icon red"><svg><use href="#a-alert"/></svg></div><div><span>Low Stock Alerts</span><strong class="danger"><?=$lowStockCount?></strong><small>Sizes with 5 or fewer left</small></div></div>
</div>
<div class="dash-main-grid">
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-chart"/></svg><h2>Sales Overview</h2></div><span class="dash-period">Last 30 Days</span></div><strong class="dash-sales-value"><?=price(array_sum($chart))?></strong><span class="dash-sales-caption">from orders in this period</span><div class="dash-bars" role="img" aria-label="Daily order revenue over the last 30 days"><?php foreach($chart as $value): ?><span style="--bar-height:<?=round($value/$chartMax*100,1)?>%" title="<?=price($value)?>"></span><?php endforeach; ?></div><div class="dash-chart-labels"><span><?=date('M j',strtotime('-29 days'))?></span><span><?=date('M j',strtotime('-20 days'))?></span><span><?=date('M j',strtotime('-10 days'))?></span><span><?=date('M j')?></span></div></section>
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-box"/></svg><h2>Order Status</h2></div></div><div class="dash-status-body"><div class="dash-donut" style="--donut:<?=$donut?>"><span><?=$activeOrders?><small>Active orders</small></span></div><div class="dash-status-list"><?php foreach($statusCounts as $label=>$count): ?><div><span><i style="--status-color:<?=$statusColors[$label]?>"></i><?=e(ucfirst($label))?></span><b><?=$count?></b></div><?php endforeach; ?></div></div></section>
</div>
<div class="dash-bottom-grid">
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-orders"/></svg><h2>Recent Orders</h2></div><a href="<?=path('admin',['tab'=>'orders'])?>">View all →</a></div><?php if(!$recentOrders): ?><p class="dash-empty">Orders will appear here after checkout.</p><?php else: ?><div class="dash-table-scroll"><table class="dash-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead><tbody><?php foreach($recentOrders as $order): ?><tr><td><strong><?=e($order['order_no'])?></strong></td><td><?=e($order['customer_name'])?></td><td><?=price($order['total'])?></td><td><?=e(strtoupper($order['payment_method']))?></td><td><span class="badge"><?=e($order['order_status'])?></span></td><td><?=e(date('M j, Y',strtotime($order['created_at'])))?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-alert"/></svg><h2>Low Stock Alerts</h2></div><a href="<?=path('admin',['tab'=>'inventory'])?>">View all →</a></div><?php if(!$lowStock): ?><p class="dash-empty">No active sizes are low on stock.</p><?php else: ?><table class="dash-table"><thead><tr><th>Product</th><th>Size</th><th>Stock</th></tr></thead><tbody><?php foreach($lowStock as $row): ?><tr><td><a href="<?=path('admin',['tab'=>'products','edit'=>$row['id']])?>"><?=e($row['name'])?></a></td><td><?=e($row['size'])?></td><td class="dash-stock-count"><?=e($row['stock'])?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></section>
</div>
<div class="dash-footer-grid">
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-users"/></svg><h2>Recent Customers</h2></div><a href="<?=path('admin',['tab'=>'customers'])?>">View all →</a></div><?php if(!$recentCustomers): ?><p class="dash-empty">Customer accounts will appear here.</p><?php else: foreach($recentCustomers as $customer): ?><div class="dash-list-row"><span class="dash-rank"><?=e(mb_strtoupper(mb_substr($customer['name'],0,1)))?></span><span><strong><?=e($customer['name'])?></strong><small><?=e($customer['email'])?></small></span><span><?=e(date('M j, Y',strtotime($customer['created_at'])))?></span></div><?php endforeach; endif; ?></section>
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-shirt"/></svg><h2>Top Selling Jerseys</h2></div><a href="<?=path('admin',['tab'=>'products'])?>">Products →</a></div><?php if(!$topProducts): ?><p class="dash-empty">Top sellers will appear once orders arrive.</p><?php else: foreach($topProducts as $rank=>$row): ?><div class="dash-list-row"><span class="dash-rank"><?=$rank+1?></span><img class="dash-product-thumb" src="<?=e($row['image']&&is_file(__DIR__.'/../'.$row['image'])?$row['image']:'assets/jersey-home.png')?>" alt=""><span><strong><?=e($row['product_name'])?></strong><small><?=e($row['sold'])?> sold</small></span><span><?=price($row['revenue'])?></span></div><?php endforeach; endif; ?></section>
  <section class="dash-card dash-panel"><div class="dash-panel-title"><div><svg><use href="#a-chart"/></svg><h2>Sales by Category</h2></div></div><?php if(!$categorySales): ?><p class="dash-empty">Category sales will appear after orders.</p><?php else: foreach($categorySales as $row): ?><div class="dash-list-row"><span><strong><?=e($row['name'])?></strong><span class="dash-category-bar"><i style="width:<?=round((float)$row['revenue']/$categoryMax*100,1)?>%"></i></span></span><span><?=price($row['revenue'])?></span></div><?php endforeach; endif; ?></section>
</div>
