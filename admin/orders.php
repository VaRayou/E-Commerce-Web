<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$orders = $conn->query("
    SELECT o.*, CONCAT(o.first_name, ' ', o.last_name) AS customer_name, u.email AS user_email
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
");

function getStatusBadge($status) {
    $map = [
        'pending' => 'badge-pro-warning',
        'processing' => 'badge-pro-primary',
        'shipped' => 'badge-pro-info',
        'delivered' => 'badge-pro-success',
        'cancelled' => 'badge-pro-danger',
        'paid' => 'badge-pro-success',
        'unpaid' => 'badge-pro-danger',
        'refunded' => 'badge-pro-warning'
    ];
    return $map[strtolower($status)] ?? 'badge-pro-secondary';
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-receipt me-2 text-primary"></i>Orders</h1>
        <p class="page-subtitle">Manage customer orders</p>
    </div>
</div>

<div class="card-pro">
    <div class="table-responsive">
        <table class="table-pro">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Order Number</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php $i = 1; while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo sanitize($order['order_number']); ?></strong></td>
                        <td><?php echo sanitize($order['customer_name']); ?></td>
                        <td><strong>$<?php echo number_format($order['total'], 2); ?></strong></td>
                        <td><span class="badge-pro <?php echo getStatusBadge($order['payment_status']); ?>"><?php echo ucfirst(sanitize($order['payment_status'])); ?></span></td>
                        <td><span class="badge-pro <?php echo getStatusBadge($order['order_status']); ?>"><?php echo ucfirst(sanitize($order['order_status'])); ?></span></td>
                        <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn-pro btn-pro-sm btn-pro-info" title="View"><i class="bi bi-eye"></i></a>
                            <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn-pro btn-pro-sm btn-pro-primary" title="Edit Status"><i class="bi bi-pencil-square"></i></a>
                            <a href="order-delete.php?id=<?php echo $order['id']; ?>" class="btn-pro btn-pro-sm btn-pro-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this order?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4" style="color:var(--text-secondary);">No orders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>