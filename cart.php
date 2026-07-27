<?php
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/includes/db.php';

if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['coupon_code']);
    unset($_SESSION['coupon_discount']);
    header('Location: cart.php');
    exit();
}

if (isset($_GET['remove'])) {
    $cartId = intval($_GET['remove']);
    if (isLoggedIn()) {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
    } else {
        $sid = session_id();
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
        $stmt->bind_param("is", $cartId, $sid);
    }
    $stmt->execute();
    header('Location: cart.php');
    exit();
}

if (isset($_GET['update_qty'])) {
    $cartId = intval($_GET['update_qty']);
    $qty = max(1, intval($_GET['qty'] ?? 1));
    if (isLoggedIn()) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $qty, $cartId, $_SESSION['user_id']);
    } else {
        $sid = session_id();
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?");
        $stmt->bind_param("iis", $qty, $cartId, $sid);
    }
    $stmt->execute();
    header('Location: cart.php');
    exit();
}

$cartItems = [];
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT c.*, p.productname, p.price, p.discount, p.photo1, p.stock FROM cart c JOIN producttbl p ON c.productid = p.productid WHERE c.user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $sid = session_id();
    $stmt = $conn->prepare("SELECT c.*, p.productname, p.price, p.discount, p.photo1, p.stock FROM cart c JOIN producttbl p ON c.productid = p.productid WHERE c.session_id = ?");
    $stmt->bind_param("s", $sid);
    $stmt->execute();
    $cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = getDiscountPrice($item['price'], $item['discount']);
    $subtotal += $itemPrice * $item['quantity'];
}
$shippingCost = ($subtotal >= floatval(getSetting('free_shipping_min', '100'))) ? 0 : floatval(getSetting('shipping_cost', '5.00'));
$discount = $_SESSION['coupon_discount'] ?? 0;
$total = $subtotal + $shippingCost - $discount;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $code = trim($_POST['coupon_code'] ?? '');
    $coupon = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (max_uses IS NULL OR used_count < max_uses) AND (start_date IS NULL OR start_date <= CURDATE()) AND (end_date IS NULL OR end_date >= CURDATE())");
    $coupon->bind_param("s", $code);
    $coupon->execute();
    $couponResult = $coupon->get_result()->fetch_assoc();

    if ($couponResult && $subtotal >= $couponResult['min_purchase']) {
        $_SESSION['coupon_code'] = $code;
        if ($couponResult['type'] === 'percentage') {
            $_SESSION['coupon_discount'] = $subtotal * $couponResult['value'] / 100;
        } else {
            $_SESSION['coupon_discount'] = $couponResult['value'];
        }
        flash('success', 'Coupon applied successfully!');
    } else {
        flash('error', 'Invalid coupon or minimum purchase not met.');
    }
    header('Location: cart.php');
    exit();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <ul class="breadcrumbs-list">
            <li><a href="<?= SITE_URL ?>/">Home</a></li>
            <li class="separator"><i class="bi bi-chevron-right"></i></li>
            <li class="active">Shopping Cart</li>
        </ul>
    </div>
</div>

<div class="cart-page">
    <div class="container">
        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <div class="icon"><i class="bi bi-bag-x"></i></div>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <div class="cart-main">
                    <div class="cart-table">
                        <div class="cart-table-header">
                            <span>Product</span>
                            <span>Price</span>
                            <span>Quantity</span>
                            <span>Total</span>
                            <span></span>
                        </div>
                        <?php foreach ($cartItems as $item): ?>
                            <?php $itemPrice = getDiscountPrice($item['price'], $item['discount']); ?>
                            <div class="cart-item">
                                <div class="cart-item-product">
                                    <div class="cart-item-image">
                                        <a href="<?= SITE_URL ?>/product.php?id=<?= $item['productid'] ?>">
                                            <img src="<?= getProductImage($item) ?>" alt="<?= sanitize($item['productname']) ?>">
                                        </a>
                                    </div>
                                    <div>
                                        <a href="<?= SITE_URL ?>/product.php?id=<?= $item['productid'] ?>" class="cart-item-name"><?= sanitize($item['productname']) ?></a>
                                    </div>
                                </div>
                                <div class="cart-item-price">
                                    <?php if ($item['discount'] > 0): ?>
                                        <span style="text-decoration:line-through;color:var(--text-muted);font-size:var(--font-size-xs);margin-right:4px;"><?= formatPrice($item['price']) ?></span>
                                    <?php endif; ?>
                                    <?= formatPrice($itemPrice) ?>
                                </div>
                                <div class="cart-item-quantity">
                                    <div class="quantity-selector">
                                        <button type="button" class="qty-btn-minus" data-cart-id="<?= $item['id'] ?>" data-max="<?= $item['stock'] ?>">-</button>
                                        <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" data-cart-id="<?= $item['id'] ?>">
                                        <button type="button" class="qty-btn-plus" data-cart-id="<?= $item['id'] ?>" data-max="<?= $item['stock'] ?>">+</button>
                                    </div>
                                </div>
                                <div class="cart-item-subtotal"><?= formatPrice($itemPrice * $item['quantity']) ?></div>
                                <a href="<?= SITE_URL ?>/cart.php?remove=<?= $item['id'] ?>" class="cart-item-remove" title="Remove"><i class="bi bi-x-lg"></i></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="cart-continue">
                        <a href="<?= SITE_URL ?>/shop.php"><i class="bi bi-arrow-left"></i> Continue Shopping</a>
                    </div>
                </div>

                <div class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="cart-summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="cart-summary-row">
                        <span class="label">Shipping</span>
                        <?php if ($shippingCost == 0): ?>
                            <span class="value free">Free</span>
                        <?php else: ?>
                            <span class="value"><?= formatPrice($shippingCost) ?></span>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="">
                        <div class="cart-coupon">
                            <input type="text" name="coupon_code" placeholder="Coupon code" value="<?= sanitize($_SESSION['coupon_code'] ?? '') ?>" required>
                            <button type="submit" name="apply_coupon" class="btn btn-outline">Apply</button>
                        </div>
                    </form>

                    <?php if (isset($_SESSION['coupon_code']) && isset($_SESSION['coupon_discount']) && $_SESSION['coupon_discount'] > 0): ?>
                        <div class="cart-summary-row" style="color:var(--success);">
                            <span class="label"><i class="bi bi-tag-fill"></i> <?= sanitize($_SESSION['coupon_code']) ?></span>
                            <span class="value">-<?= formatPrice($_SESSION['coupon_discount']) ?></span>
                        </div>
                        <div style="margin-bottom:12px;font-size:var(--font-size-xs);">
                            <a href="<?= SITE_URL ?>/cart.php?remove_coupon=1" style="color:var(--danger);">Remove coupon</a>
                        </div>
                    <?php endif; ?>

                    <div class="cart-summary-total">
                        <span class="label">Total</span>
                        <span class="value"><?= formatPrice($total) ?></span>
                    </div>

                    <a href="<?= SITE_URL ?>/checkout.php" class="btn btn-primary btn-checkout">Proceed to Checkout</a>
                    <div style="text-align:center;margin-top:12px;font-size:var(--font-size-xs);color:var(--text-muted);">
                        <i class="bi bi-lock-fill"></i> Secure checkout
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.qty-btn-plus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.cartId;
            var max = parseInt(this.dataset.max) || 999;
            var input = document.querySelector('.qty-input[data-cart-id="' + id + '"]');
            var val = parseInt(input.value) || 1;
            if (val < max) {
                window.location.href = '<?= SITE_URL ?>/cart.php?update_qty=' + id + '&qty=' + (val + 1);
            }
        });
    });
    document.querySelectorAll('.qty-btn-minus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.cartId;
            var input = document.querySelector('.qty-input[data-cart-id="' + id + '"]');
            var val = parseInt(input.value) || 1;
            if (val > 1) {
                window.location.href = '<?= SITE_URL ?>/cart.php?update_qty=' + id + '&qty=' + (val - 1);
            }
        });
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>