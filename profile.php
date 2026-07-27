<?php
$pageTitle = 'My Account';
require_once __DIR__ . '/includes/db.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user = getUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zipCode = trim($_POST['zip_code'] ?? '');
    $country = trim($_POST['country'] ?? '');

    $stmt = $conn->prepare("UPDATE users SET first_name=?, last_name=?, phone=?, address=?, city=?, state=?, zip_code=?, country=? WHERE id=?");
    $stmt->bind_param("ssssssssi", $firstName, $lastName, $phone, $address, $city, $state, $zipCode, $country, $_SESSION['user_id']);
    $stmt->execute();
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;

    flash('success', 'Profile updated successfully!');
    header('Location: profile.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!password_verify($currentPassword, $user['password'])) {
        flash('error', 'Current password is incorrect.');
        header('Location: profile.php');
        exit();
    }

    if (strlen($newPassword) < 6) {
        flash('error', 'New password must be at least 6 characters.');
        header('Location: profile.php');
        exit();
    }

    if ($newPassword !== $confirmPassword) {
        flash('error', 'New passwords do not match.');
        header('Location: profile.php');
        exit();
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $_SESSION['user_id']);
    $stmt->execute();

    flash('success', 'Password changed successfully!');
    header('Location: profile.php');
    exit();
}

$orderStmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$orderStmt->bind_param("i", $_SESSION['user_id']);
$orderStmt->execute();
$orderHistory = $orderStmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<div class="breadcrumbs">
    <div class="container">
        <ul class="breadcrumbs-list">
            <li><a href="<?= SITE_URL ?>/">Home</a></li>
            <li><span class="separator"><i class="bi bi-chevron-right"></i></span></li>
            <li class="active">My Account</li>
        </ul>
    </div>
</div>

<div class="profile-page" style="padding:40px 0 72px;">
    <div class="container">
        <div class="profile-layout" style="display:grid;grid-template-columns:260px 1fr;gap:32px;align-items:start;">

            <aside class="profile-sidebar" style="background:var(--white);border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);position:sticky;top:calc(var(--header-height) + 20px);overflow:hidden;">
                <div style="padding:28px 24px;text-align:center;border-bottom:1px solid var(--border);">
                    <div style="width:72px;height:72px;border-radius:var(--radius-full);background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;margin:0 auto 12px;">
                        <?= strtoupper(substr($user['first_name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div style="font-weight:700;color:var(--text-primary);font-size:var(--font-size-sm);margin-bottom:4px;"><?= sanitize(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?></div>
                    <div style="font-size:var(--font-size-xs);color:var(--text-muted);"><?= sanitize($user['email'] ?? '') ?></div>
                </div>
                <nav style="padding:12px 0;">
                    <a href="#profile-section" class="profile-nav-link active" style="display:flex;align-items:center;gap:10px;padding:12px 24px;font-size:var(--font-size-sm);font-weight:500;color:var(--text-secondary);transition:all var(--transition-fast);text-decoration:none;">
                        <i class="bi bi-person"></i> Profile
                    </a>
                    <a href="#orders-section" class="profile-nav-link" style="display:flex;align-items:center;gap:10px;padding:12px 24px;font-size:var(--font-size-sm);font-weight:500;color:var(--text-secondary);transition:all var(--transition-fast);text-decoration:none;">
                        <i class="bi bi-receipt"></i> Orders
                    </a>
                    <a href="<?= SITE_URL ?>/wishlist.php" class="profile-nav-link" style="display:flex;align-items:center;gap:10px;padding:12px 24px;font-size:var(--font-size-sm);font-weight:500;color:var(--text-secondary);transition:all var(--transition-fast);text-decoration:none;">
                        <i class="bi bi-heart"></i> Wishlist
                    </a>
                    <div style="height:1px;background:var(--border);margin:8px 24px;"></div>
                    <a href="<?= SITE_URL ?>/logout.php" class="profile-nav-link" style="display:flex;align-items:center;gap:10px;padding:12px 24px;font-size:var(--font-size-sm);font-weight:500;color:var(--danger);transition:all var(--transition-fast);text-decoration:none;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </nav>
            </aside>

            <div class="profile-content">

                <div id="profile-section" class="checkout-form-section" style="margin-bottom:24px;">
                    <h3><span class="step-num" style="width:28px;height:28px;border-radius:var(--radius-full);background:var(--primary);color:var(--white);display:inline-flex;align-items:center;justify-content:center;font-size:var(--font-size-xs);font-weight:700;margin-right:10px;">1</span> Edit Profile</h3>
                    <form method="POST" action="">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name</label>
                                <input type="text" id="first_name" name="first_name" value="<?= sanitize($user['first_name'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="last_name" name="last_name" value="<?= sanitize($user['last_name'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" value="<?= sanitize($user['email'] ?? '') ?>" readonly style="background:var(--light);cursor:not-allowed;color:var(--text-muted);">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" value="<?= sanitize($user['address'] ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" value="<?= sanitize($user['city'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="state">State</label>
                                <input type="text" id="state" name="state" value="<?= sanitize($user['state'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="zip_code">Zip Code</label>
                                <input type="text" id="zip_code" name="zip_code" value="<?= sanitize($user['zip_code'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" value="<?= sanitize($user['country'] ?? '') ?>">
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary" style="margin-top:8px;"><i class="bi bi-check-lg"></i> Save Changes</button>
                    </form>
                </div>

                <div id="password-section" class="checkout-form-section" style="margin-bottom:24px;">
                    <h3><span class="step-num" style="width:28px;height:28px;border-radius:var(--radius-full);background:var(--primary);color:var(--white);display:inline-flex;align-items:center;justify-content:center;font-size:var(--font-size-xs);font-weight:700;margin-right:10px;">2</span> Change Password</h3>
                    <form method="POST" action="" style="max-width:480px;">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>
                        <button type="submit" name="change_password" class="btn btn-outline-primary" style="margin-top:8px;"><i class="bi bi-key"></i> Change Password</button>
                    </form>
                </div>

                <div id="orders-section" class="checkout-form-section">
                    <h3><span class="step-num" style="width:28px;height:28px;border-radius:var(--radius-full);background:var(--primary);color:var(--white);display:inline-flex;align-items:center;justify-content:center;font-size:var(--font-size-xs);font-weight:700;margin-right:10px;">3</span> Recent Orders</h3>
                    <?php if (!empty($orderHistory)): ?>
                    <div style="overflow-x:auto;">
                        <table class="spec-table">
                            <thead>
                                <tr>
                                    <td style="font-weight:700;">Order #</td>
                                    <td style="font-weight:700;">Date</td>
                                    <td style="font-weight:700;">Total</td>
                                    <td style="font-weight:700;">Status</td>
                                    <td style="font-weight:700;">Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orderHistory as $order): ?>
                                <tr>
                                    <td style="font-weight:600;color:var(--text-primary);"><?= sanitize($order['order_number']) ?></td>
                                    <td><?= date('M d, Y', strtotime($order['created_at'])) ?></td>
                                    <td style="font-weight:600;"><?= formatPrice($order['total']) ?></td>
                                    <td>
                                        <?php
                                        $statusClass = 'badge-primary';
                                        $orderStatus = strtolower($order['order_status'] ?? 'pending');
                                        if ($orderStatus === 'delivered') $statusClass = 'badge-success';
                                        elseif ($orderStatus === 'cancelled') $statusClass = 'badge-sale';
                                        elseif ($orderStatus === 'shipped') $statusClass = 'badge-warning';
                                        ?>
                                        <span class="badge <?= $statusClass ?> badge-pill"><?= ucfirst(sanitize($order['order_status'] ?? 'Pending')) ?></span>
                                    </td>
                                    <td>
                                        <?php if (file_exists(__DIR__ . '/order-detail.php') || file_exists(__DIR__ . '/order-success.php')): ?>
                                        <a href="<?= SITE_URL ?>/order-detail.php?order=<?= sanitize($order['order_number']) ?>" class="btn btn-sm btn-outline" style="padding:6px 12px;font-size:var(--font-size-xs);">View</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div style="text-align:center;padding:40px 0;">
                        <i class="bi bi-receipt" style="font-size:48px;color:var(--border);display:block;margin-bottom:16px;"></i>
                        <p style="color:var(--text-muted);font-size:var(--font-size-sm);margin-bottom:16px;">You haven't placed any orders yet.</p>
                        <a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary btn-sm"><i class="bi bi-bag"></i> Start Shopping</a>
                    </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.profile-nav-link:hover { background:var(--light);color:var(--primary) !important; }
.profile-nav-link.active { background:var(--primary-light);color:var(--primary) !important;font-weight:600; }
@media (max-width: 992px) {
    .profile-layout { grid-template-columns: 1fr !important; }
    .profile-sidebar { position: static !important; }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>