<?php
$pageTitle = 'Order Successful';
require_once __DIR__ . '/includes/db.php';

$orderNumber = trim($_GET['order'] ?? '');

if (empty($orderNumber)) {
    header('Location: ' . SITE_URL . '/');
    exit();
}

// Fetch order details
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ?");
$stmt->bind_param("s", $orderNumber);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Validate: only owner or admin can see it
if (!$order || ($order['user_id'] != ($_SESSION['user_id'] ?? 0) && !isAdmin())) {
    header('Location: ' . SITE_URL . '/');
    exit();
}

// Fetch order items
$itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$itemStmt->bind_param("i", $order['id']);
$itemStmt->execute();
$items = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$itemStmt->close();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <ul class="breadcrumbs-list">
            <li><a href="<?= SITE_URL ?>/">Home</a></li>
            <li class="separator"><i class="bi bi-chevron-right"></i></li>
            <li class="active">Order Confirmation</li>
        </ul>
    </div>
</div>

<div style="padding: 60px 0; background: var(--light);">
    <div class="container" style="max-width: 780px;">

        <!-- Success Banner -->
        <div style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-md); overflow: hidden; margin-bottom: 24px;">
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 40px 32px; text-align: center; color: #fff;">
                <div style="width: 72px; height: 72px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 36px;">
                    <i class="bi bi-check-lg"></i>
                </div>
                <h1 style="font-size: 1.75rem; font-weight: 700; margin-bottom: 8px;">Order Placed Successfully!</h1>
                <p style="opacity: 0.9; margin: 0;">Thank you for your purchase. We'll send you a confirmation shortly.</p>
            </div>

            <div style="padding: 28px 32px;">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding-bottom: 20px; border-bottom: 1px solid var(--border);">
                    <div>
                        <div style="font-size: var(--font-size-xs); color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Order Number</div>
                        <div style="font-size: 1.1rem; font-weight: 700; color: var(--primary);"><?= sanitize($order['order_number']) ?></div>
                    </div>
                    <div>
                        <div style="font-size: var(--font-size-xs); color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Date</div>
                        <div style="font-weight: 500; color: var(--text-primary);"><?= date('F j, Y', strtotime($order['created_at'])) ?></div>
                    </div>
                    <div>
                        <div style="font-size: var(--font-size-xs); color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Payment</div>
                        <div style="font-weight: 500; color: var(--text-primary);"><?= sanitize(ucwords(str_replace('_', ' ', $order['payment_method']))) ?></div>
                    </div>
                    <div>
                        <div style="font-size: var(--font-size-xs); color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Status</div>
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 12px;border-radius:var(--radius-full);background:rgba(245,158,11,0.1);color:#d97706;font-size:var(--font-size-xs);font-weight:600;">
                            <i class="bi bi-clock"></i> Pending
                        </span>
                    </div>
                </div>

                <!-- Order Items -->
                <div style="padding-top: 20px;">
                    <h3 style="font-size: var(--font-size-md); font-weight: 600; color: var(--text-primary); margin-bottom: 16px;">Items Ordered</h3>
                    <?php foreach ($items as $item): ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--border);">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: var(--light); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 18px; flex-shrink: 0;">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <div style="font-weight: 500; color: var(--text-primary); font-size: var(--font-size-sm);"><?= sanitize($item['product_name']) ?></div>
                                <div style="font-size: var(--font-size-xs); color: var(--text-muted);">Qty: <?= intval($item['quantity']) ?></div>
                            </div>
                        </div>
                        <div style="font-weight: 600; color: var(--text-primary);"><?= formatPrice($item['total']) ?></div>
                    </div>
                    <?php endforeach; ?>

                    <!-- Order Totals -->
                    <div style="margin-top: 16px; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        <div style="display: flex; gap: 40px; font-size: var(--font-size-sm);">
                            <span style="color: var(--text-muted);">Subtotal</span>
                            <span><?= formatPrice($order['subtotal']) ?></span>
                        </div>
                        <div style="display: flex; gap: 40px; font-size: var(--font-size-sm);">
                            <span style="color: var(--text-muted);">Shipping</span>
                            <span><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : '<span style="color:var(--success)">Free</span>' ?></span>
                        </div>
                        <?php if ($order['discount_amount'] > 0): ?>
                        <div style="display: flex; gap: 40px; font-size: var(--font-size-sm); color: var(--success);">
                            <span>Discount</span>
                            <span>-<?= formatPrice($order['discount_amount']) ?></span>
                        </div>
                        <?php endif; ?>
                        <div style="display: flex; gap: 40px; font-weight: 700; font-size: 1.1rem; padding-top: 8px; border-top: 2px solid var(--border);">
                            <span>Total</span>
                            <span style="color: var(--primary);"><?= formatPrice($order['total']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Info -->
        <div style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 24px 32px; margin-bottom: 24px;">
            <h3 style="font-size: var(--font-size-md); font-weight: 600; color: var(--text-primary); margin-bottom: 16px;"><i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i> Delivery Address</h3>
            <p style="margin: 0; color: var(--text-secondary); line-height: 1.7;">
                <strong><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></strong><br>
                <?= sanitize($order['address']) ?><br>
                <?= sanitize($order['city']) ?><?= !empty($order['state']) ? ', ' . sanitize($order['state']) : '' ?> <?= sanitize($order['zip_code']) ?><br>
                <?= sanitize($order['country']) ?>
            </p>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= SITE_URL ?>/profile.php#orders-section" class="btn btn-outline" style="min-width: 160px; display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="bi bi-receipt"></i> View My Orders
            </a>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary" style="min-width: 160px; display:flex;align-items:center;justify-content:center;gap:8px;">
                <i class="bi bi-bag"></i> Continue Shopping
            </a>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
