<?php
include '../includes/db.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: orders.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $orderStatus = trim($_POST['order_status'] ?? '');
    $paymentStatus = trim($_POST['payment_status'] ?? '');
    $stmt = $conn->prepare("UPDATE orders SET order_status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("ssi", $orderStatus, $paymentStatus, $id);
    $stmt->execute();
    $stmt->close();
    header('Location: order-detail.php?id=' . $id);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: orders.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-receipt me-2 text-primary"></i>Order #<?php echo sanitize($order['order_number']); ?></h1>
        <p class="page-subtitle">Placed on <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></p>
    </div>
    <a href="orders.php" class="btn-pro btn-pro-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Orders</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card-pro">
            <h6 class="mb-3 fw-semibold" style="color:var(--text-primary);">Order Items</h6>
            <div class="table-responsive">
                <table class="table-pro">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Color</th>
                            <th>Size</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo sanitize($item['product_name']); ?></td>
                            <td><?php echo sanitize($item['color'] ?? '-'); ?></td>
                            <td><?php echo sanitize($item['size'] ?? '-'); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><strong>$<?php echo number_format($item['total'], 2); ?></strong></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end mt-3 gap-4">
                <div><span style="color:var(--text-secondary);">Subtotal:</span> <strong>$<?php echo number_format($order['subtotal'], 2); ?></strong></div>
                <div><span style="color:var(--text-secondary);">Shipping:</span> <strong>$<?php echo number_format($order['shipping_cost'], 2); ?></strong></div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div><span style="color:var(--text-secondary);">Discount:</span> <strong class="text-danger">-$<?php echo number_format($order['discount_amount'], 2); ?></strong></div>
                <?php endif; ?>
                <div><span style="color:var(--text-secondary);">Total:</span> <strong style="color:var(--text-primary);font-size:1.1rem;">$<?php echo number_format($order['total'], 2); ?></strong></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-pro mb-3">
            <h6 class="mb-3 fw-semibold" style="color:var(--text-primary);">Update Status</h6>
            <form method="POST" action="">
                <div class="form-group-pro">
                    <label>Order Status</label>
                    <select name="order_status" class="form-control-pro">
                        <?php foreach (['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['order_status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group-pro">
                    <label>Payment Status</label>
                    <select name="payment_status" class="form-control-pro">
                        <?php foreach (['pending', 'paid', 'failed', 'refunded'] as $s): ?>
                            <option value="<?php echo $s; ?>" <?php echo $order['payment_status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-pro btn-pro-primary"><i class="bi bi-check-lg me-1"></i>Update</button>
            </form>
        </div>
        <div class="card-pro">
            <h6 class="mb-3 fw-semibold" style="color:var(--text-primary);">Customer Info</h6>
            <p class="mb-1"><i class="bi bi-person me-2" style="color:var(--text-secondary);"></i><?php echo sanitize($order['first_name'] . ' ' . $order['last_name']); ?></p>
            <p class="mb-1"><i class="bi bi-envelope me-2" style="color:var(--text-secondary);"></i><?php echo sanitize($order['email']); ?></p>
            <p class="mb-1"><i class="bi bi-phone me-2" style="color:var(--text-secondary);"></i><?php echo sanitize($order['phone']); ?></p>
            <p class="mb-0"><i class="bi bi-geo-alt me-2" style="color:var(--text-secondary);"></i><?php echo sanitize($order['address'] . ', ' . $order['city'] . ', ' . $order['state'] . ' ' . $order['zip_code'] . ', ' . $order['country']); ?></p>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>