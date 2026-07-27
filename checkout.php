<?php
$pageTitle = 'Checkout';
require_once __DIR__ . '/includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php?redirect=' . urlencode(SITE_URL . '/checkout.php'));
    exit();
}

$cartItems = [];
$stmt = $conn->prepare("SELECT c.*, p.productname, p.price, p.discount, p.photo1, p.stock FROM cart c JOIN producttbl p ON c.productid = p.productid WHERE c.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$cartItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = getDiscountPrice($item['price'], $item['discount']);
    $subtotal += $itemPrice * $item['quantity'];
}
$shippingCost = ($subtotal >= floatval(getSetting('free_shipping_min', '100'))) ? 0 : floatval(getSetting('shipping_cost', '5.00'));
$discount = $_SESSION['coupon_discount'] ?? 0;
$total = $subtotal + $shippingCost - $discount;

$user = getUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');
    $country = trim($_POST['country'] ?? 'Cambodia');
    $paymentMethod = $_POST['payment_method'] ?? 'cod';
    $notes = trim($_POST['notes'] ?? '');

    $errors = [];
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (empty($phone)) $errors[] = 'Phone number is required.';
    if (empty($address)) $errors[] = 'Address is required.';
    if (empty($city)) $errors[] = 'City is required.';
    if (empty($zipCode)) $errors[] = 'Zip code is required.';

    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = '"' . $item['productname'] . '" is out of stock.';
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $err) {
            flash('error', $err);
        }
        header('Location: checkout.php');
        exit();
    }

    $orderNumber = generateOrderNumber();

    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, first_name, last_name, email, phone, address, city, state, zip_code, country, subtotal, shipping_cost, discount_amount, total, coupon_code, payment_method, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $couponCode = $_SESSION['coupon_code'] ?? '';
    $stmt->bind_param("issssssssssddddsss", $_SESSION['user_id'], $orderNumber, $firstName, $lastName, $email, $phone, $address, $city, $state, $zipCode, $country, $subtotal, $shippingCost, $discount, $total, $couponCode, $paymentMethod, $notes);
    $stmt->execute();
    $orderId = $conn->insert_id;

    $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, productid, product_name, price, quantity, total) VALUES (?, ?, ?, ?, ?, ?)");
    $stockStmt = $conn->prepare("UPDATE producttbl SET stock = stock - ?, sales_count = sales_count + ? WHERE productid = ?");

    foreach ($cartItems as $item) {
        $itemPrice = getDiscountPrice($item['price'], $item['discount']);
        $itemTotal = $itemPrice * $item['quantity'];
        $itemStmt->bind_param("iisdid", $orderId, $item['productid'], $item['productname'], $itemPrice, $item['quantity'], $itemTotal);
        $itemStmt->execute();

        $stockStmt->bind_param("iii", $item['quantity'], $item['quantity'], $item['productid']);
        $stockStmt->execute();
    }

    $clearStmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearStmt->bind_param("i", $_SESSION['user_id']);
    $clearStmt->execute();

    unset($_SESSION['coupon_code']);
    unset($_SESSION['coupon_discount']);

    header('Location: order-success.php?order=' . $orderNumber);
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
            <li class="active">Checkout</li>
        </ul>
    </div>
</div>

<div class="checkout-page">
    <div class="container">
        <div class="checkout-layout">
                <form method="POST" action="" id="checkoutForm">
            <div class="checkout-main">
                    <div class="checkout-form-section">
                        <h3><span class="step-num">1</span> Billing Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name *</label>
                                <input type="text" id="first_name" name="first_name" value="<?= sanitize($_POST['first_name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" value="<?= sanitize($_POST['last_name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?= sanitize($_POST['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone *</label>
                                <input type="tel" id="phone" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="address">Address *</label>
                                <input type="text" id="address" name="address" value="<?= sanitize($_POST['address'] ?? '') ?>" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" value="<?= sanitize($_POST['city'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" value="<?= sanitize($_POST['state'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="zip_code">Zip Code *</label>
                                <input type="text" id="zip_code" name="zip_code" value="<?= sanitize($_POST['zip_code'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" value="<?= sanitize($_POST['country'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="checkout-form-section">
                        <h3><span class="step-num">2</span> Payment Method</h3>
                        <div class="payment-methods">
                            <label class="payment-method active" onclick="selectPayment(this)">
                                <input type="radio" name="payment_method" value="cod" checked>
                                <div class="payment-method-icon"><i class="bi bi-cash-stack"></i></div>
                                <div class="payment-method-info">
                                    <div class="name">Cash on Delivery</div>
                                    <div class="desc">Pay when you receive your order</div>
                                </div>
                            </label>
                            <label class="payment-method" onclick="selectPayment(this)">
                                <input type="radio" name="payment_method" value="bank_transfer">
                                <div class="payment-method-icon"><i class="bi bi-bank"></i></div>
                                <div class="payment-method-info">
                                    <div class="name">Bank Transfer</div>
                                    <div class="desc">Transfer directly to our bank account</div>
                                </div>
                            </label>
                            <label class="payment-method" onclick="selectPayment(this)">
                                <input type="radio" name="payment_method" value="online">
                                <div class="payment-method-icon"><i class="bi bi-credit-card"></i></div>
                                <div class="payment-method-info">
                                    <div class="name">Online Payment</div>
                                    <div class="desc">Pay via credit/debit card</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="checkout-form-section">
                        <h3><span class="step-num">3</span> Order Notes <span style="font-weight:400;font-size:var(--font-size-sm);color:var(--text-muted);margin-left:4px;">(optional)</span></h3>
                        <div class="form-group">
                            <textarea id="notes" name="notes" placeholder="Any special instructions for your order..."><?= sanitize($_POST['notes'] ?? '') ?></textarea>
                        </div>
                    </div>
            </div>

            <div class="checkout-sidebar">
                <div class="checkout-order-summary">
                    <h3>Order Summary</h3>
                    <?php foreach ($cartItems as $item): ?>
                    <?php $itemPrice = getDiscountPrice($item['price'], $item['discount']); ?>
                    <div class="checkout-item">
                        <div class="checkout-item-image">
                            <img src="<?= getProductImage($item) ?>" alt="<?= sanitize($item['productname']) ?>">
                            <span class="qty-badge"><?= $item['quantity'] ?></span>
                        </div>
                        <div class="checkout-item-name"><?= sanitize($item['productname']) ?></div>
                        <div class="checkout-item-price"><?= formatPrice($itemPrice * $item['quantity']) ?></div>
                    </div>
                    <?php endforeach; ?>

                    <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
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
                        <?php if ($discount > 0): ?>
                        <div class="cart-summary-row" style="color:var(--success);">
                            <span class="label"><i class="bi bi-tag-fill"></i> Discount</span>
                            <span class="value">-<?= formatPrice($discount) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="cart-summary-total">
                        <span class="label">Total</span>
                        <span class="value"><?= formatPrice($total) ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-checkout">Place Order</button>
                </div>
            </div>
        </div>
                </form>
    </div>
</div>

<script>
function selectPayment(el) {
    document.querySelectorAll('.payment-method').forEach(function(m) { m.classList.remove('active'); });
    el.classList.add('active');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>