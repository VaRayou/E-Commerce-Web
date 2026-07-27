<?php
$pageTitle = 'Order Details';
require_once __DIR__ . '/includes/db.php';

requireLogin();

$orderNumber = trim($_GET['order'] ?? '');
if (empty($orderNumber)) {
    header('Location: profile.php#orders-section');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM orders WHERE order_number = ? AND user_id = ?");
$stmt->bind_param("si", $orderNumber, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: profile.php#orders-section');
    exit();
}

$itemStmt = $conn->prepare("SELECT oi.*, p.photo1 FROM order_items oi LEFT JOIN producttbl p ON oi.productid = p.productid WHERE oi.order_id = ?");
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
            <li><a href="<?= SITE_URL ?>/profile.php#orders-section">My Orders</a></li>
            <li class="separator"><i class="bi bi-chevron-right"></i></li>
            <li class="active"><?= sanitize($order['order_number']) ?></li>
        </ul>
    </div>
</div>

<div style="padding: 40px 0 72px; background: var(--light);">
    <div class="container" style="max-width: 860px;">

        <!-- Header -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 1.4rem; font-weight: 700; color: var(--text-primary); margin: 0;">Order <?= sanitize($order['order_number']) ?></h1>
                <p style="color: var(--text-muted); font-size: var(--font-size-sm); margin: 4px 0 0;">Placed on <?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></p>
            </div>
            <a href="<?= SITE_URL ?>/profile.php#orders-section" class="btn btn-outline btn-sm" style="display:flex;align-items:center;gap:6px;">
                <i class="bi bi-arrow-left"></i> Back to Orders
            </a>
        </div>

        <!-- Status + Payment Row -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 24px;">
            <?php
            $statusColors = [
                'pending'    => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#d97706', 'icon' => 'bi-clock'],
                'processing' => ['bg' => 'rgba(59,130,246,0.1)', 'color' => '#2563eb', 'icon' => 'bi-gear'],
                'shipped'    => ['bg' => 'rgba(99,102,241,0.1)', 'color' => '#4f46e5', 'icon' => 'bi-truck'],
                'delivered'  => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#059669', 'icon' => 'bi-check-circle'],
                'cancelled'  => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#dc2626', 'icon' => 'bi-x-circle'],
            ];
            $payColors = [
                'pending'  => ['bg' => 'rgba(245,158,11,0.1)', 'color' => '#d97706'],
                'paid'     => ['bg' => 'rgba(16,185,129,0.1)', 'color' => '#059669'],
                'failed'   => ['bg' => 'rgba(239,68,68,0.1)', 'color' => '#dc2626'],
                'refunded' => ['bg' => 'rgba(99,102,241,0.1)', 'color' => '#4f46e5'],
            ];
            $os = strtolower($order['order_status'] ?? 'pending');
            $ps = strtolower($order['payment_status'] ?? 'pending');
            $sc = $statusColors[$os] ?? $statusColors['pending'];
            $pc = $payColors[$ps] ?? $payColors['pending'];
            ?>
            <div style="background: var(--white); border-radius: var(--radius-md); padding: 16px 20px; box-shadow: var(--shadow-sm);">
                <div style="font-size: var(--font-size-xs); color: var(--text-muted); margin-bottom: 6px;">Order Status</div>
                <span style="display:inline-flex;align-items:center;gap:5px;font-weight:600;font-size:var(--font-size-sm);color:<?= $sc['color'] ?>;">
                    <i class="bi <?= $sc['icon'] ?>"></i> <?= ucfirst(sanitize($order['order_status'])) ?>
                </span>
            </div>
            <div style="background: var(--white); border-radius: var(--radius-md); padding: 16px 20px; box-shadow: var(--shadow-sm);">
                <div style="font-size: var(--font-size-xs); color: var(--text-muted); margin-bottom: 6px;">Payment Status</div>
                <span style="display:inline-flex;align-items:center;gap:5px;font-weight:600;font-size:var(--font-size-sm);color:<?= $pc['color'] ?>;">
                    <i class="bi bi-credit-card"></i> <?= ucfirst(sanitize($order['payment_status'])) ?>
                </span>
            </div>
            <div style="background: var(--white); border-radius: var(--radius-md); padding: 16px 20px; box-shadow: var(--shadow-sm);">
                <div style="font-size: var(--font-size-xs); color: var(--text-muted); margin-bottom: 6px;">Payment Method</div>
                <span style="font-weight:600;font-size:var(--font-size-sm);color:var(--text-primary);">
                    <?= sanitize(ucwords(str_replace('_', ' ', $order['payment_method']))) ?>
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">

            <!-- Items -->
            <div style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--border); font-weight: 600; color: var(--text-primary);">
                    <i class="bi bi-box-seam" style="color: var(--primary);"></i> Order Items
                </div>
                <div>
                    <?php foreach ($items as $item): ?>
                    <div style="display: flex; align-items: center; gap: 14px; padding: 16px 24px; border-bottom: 1px solid var(--border);">
                        <div style="width: 52px; height: 52px; border-radius: var(--radius-md); overflow: hidden; flex-shrink: 0; background: var(--light); display:flex;align-items:center;justify-content:center;">
                            <?php if (!empty($item['photo1'])): ?>
                                <img src="<?= getProductImage($item) ?>" alt="<?= sanitize($item['product_name']) ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <i class="bi bi-box-seam" style="color:var(--text-muted);font-size:22px;"></i>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 500; color: var(--text-primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= sanitize($item['product_name']) ?></div>
                            <?php if (!empty($item['color'])): ?>
                                <div style="font-size: var(--font-size-xs); color: var(--text-muted);">Color: <?= sanitize($item['color']) ?></div>
                            <?php endif; ?>
                            <?php if (!empty($item['size'])): ?>
                                <div style="font-size: var(--font-size-xs); color: var(--text-muted);">Size: <?= sanitize($item['size']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: right; flex-shrink: 0;">
                            <div style="font-size: var(--font-size-xs); color: var(--text-muted);"><?= formatPrice($item['price']) ?> × <?= intval($item['quantity']) ?></div>
                            <div style="font-weight: 600; color: var(--text-primary);"><?= formatPrice($item['total']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <!-- Totals -->
                <div style="padding: 20px 24px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: var(--font-size-sm);">
                        <span style="color: var(--text-muted);">Subtotal</span>
                        <span><?= formatPrice($order['subtotal']) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: var(--font-size-sm);">
                        <span style="color: var(--text-muted);">Shipping</span>
                        <span><?= $order['shipping_cost'] > 0 ? formatPrice($order['shipping_cost']) : '<span style="color:var(--success)">Free</span>' ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: var(--font-size-sm); color: var(--success);">
                        <span>Discount</span>
                        <span>-<?= formatPrice($order['discount_amount']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="display: flex; justify-content: space-between; padding-top: 12px; border-top: 2px solid var(--border); font-weight: 700; font-size: 1.05rem;">
                        <span>Total</span>
                        <span style="color: var(--primary);"><?= formatPrice($order['total']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div>
                <!-- Shipping Address -->
                <div style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 20px 24px; margin-bottom: 16px;">
                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 14px;"><i class="bi bi-geo-alt-fill" style="color: var(--primary);"></i> Delivery Address</div>
                    <p style="color: var(--text-secondary); font-size: var(--font-size-sm); line-height: 1.7; margin: 0;">
                        <strong><?= sanitize($order['first_name'] . ' ' . $order['last_name']) ?></strong><br>
                        <?= sanitize($order['address']) ?><br>
                        <?= sanitize($order['city']) ?><?= !empty($order['state']) ? ', ' . sanitize($order['state']) : '' ?> <?= sanitize($order['zip_code']) ?><br>
                        <?= sanitize($order['country']) ?><br>
                        <i class="bi bi-telephone"></i> <?= sanitize($order['phone']) ?>
                    </p>
                </div>
                <!-- Notes -->
                <?php if (!empty($order['notes'])): ?>
                <div style="background: var(--white); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); padding: 20px 24px;">
                    <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 10px;"><i class="bi bi-sticky-fill" style="color:var(--primary);"></i> Order Notes</div>
                    <p style="color: var(--text-secondary); font-size: var(--font-size-sm); margin: 0;"><?= sanitize($order['notes']) ?></p>
                </div>
                <?php endif; ?>
            </div>

        </div>

        <style>
            @media (max-width: 768px) {
                div[style*="grid-template-columns: 1fr 340px"] {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
