<?php
$tab = (string)($_GET['tab'] ?? 'overview');
if (!in_array($tab, ['overview', 'products', 'catalog', 'orders', 'customers', 'inventory', 'payments'], true)) $tab = 'overview';
?>
<section class="admin admin-workspace">
<?php if ($tab === 'overview'): include __DIR__ . '/admin-overview.php'; ?>
<?php elseif ($tab === 'products'): include __DIR__ . '/admin-products.php'; ?>
<?php elseif ($tab === 'catalog'): include __DIR__ . '/admin-catalog.php'; ?>
<?php elseif ($tab === 'orders'): include __DIR__ . '/admin-orders.php'; ?>
<?php else: include __DIR__ . '/admin-extra.php'; ?>
<?php endif; ?>
</section>
