<?php
$pageTitle = 'Register';
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/');
    exit();
}

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'username' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $old = compact('firstName', 'lastName', 'username', 'email', 'phone');

    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';
    if (empty($username)) $errors[] = 'Username is required.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param("ss", $username, $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            $errors[] = 'Username or email already registered.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
            $stmt->bind_param("ssssss", $firstName, $lastName, $username, $email, $hashedPassword, $phone);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $firstName . ' ' . $lastName;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'customer';

                $sid = session_id();
                $merge = $conn->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
                $merge->bind_param("is", $_SESSION['user_id'], $sid);
                $merge->execute();

                $mergeW = $conn->prepare("UPDATE wishlist SET user_id = ?, session_id = NULL WHERE session_id = ?");
                $mergeW->bind_param("is", $_SESSION['user_id'], $sid);
                $mergeW->execute();

                header('Location: ' . SITE_URL . '/');
                exit();
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="<?= SITE_URL ?>/" class="auth-brand">
                    <i class="bi bi-bag-fill"></i>
                    <span><?= getSetting('site_name', 'WE YOUNG Shop') ?></span>
                </a>
                <h2>Create Account</h2>
                <p>Join us and start shopping</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger py-2">
                    <i class="bi bi-exclamation-circle-fill me-1"></i>
                    <?php foreach ($errors as $err): ?>
                        <div><?= sanitize($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-row-2">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <div class="input-icon">
                            <i class="bi bi-person"></i>
                            <input type="text" id="first_name" name="first_name" placeholder="First name" value="<?= sanitize($old['firstName'] ?? $old['first_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <div class="input-icon">
                            <i class="bi bi-person"></i>
                            <input type="text" id="last_name" name="last_name" placeholder="Last name" value="<?= sanitize($old['lastName'] ?? $old['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" id="username" name="username" placeholder="Choose a username" value="<?= sanitize($old['username'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-icon">
                        <i class="bi bi-envelope"></i>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?= sanitize($old['email'] ?? '') ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number <span class="text-muted">(optional)</span></label>
                    <div class="input-icon">
                        <i class="bi bi-telephone"></i>
                        <input type="tel" id="phone" name="phone" placeholder="Phone number" value="<?= sanitize($old['phone'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-icon">
                        <i class="bi bi-lock-fill"></i>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="auth-btn">Create Account</button>
            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="auth-switch">
                Already have an account? <a href="<?= SITE_URL ?>/login.php">Sign In</a>
            </p>
        </div>
    </div>
</section>

<script>
    function togglePassword(btn) {
        const input = btn.previousElementSibling;
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>