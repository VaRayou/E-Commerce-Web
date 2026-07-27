<?php
$pageTitle = 'Login';
require_once __DIR__ . '/includes/db.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['is_active']) {
                $error = 'Your account has been deactivated.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                $sid = session_id();
                $merge = $conn->prepare("UPDATE cart SET user_id = ?, session_id = NULL WHERE session_id = ?");
                $merge->bind_param("is", $user['id'], $sid);
                $merge->execute();

                $mergeW = $conn->prepare("UPDATE wishlist SET user_id = ?, session_id = NULL WHERE session_id = ?");
                $mergeW->bind_param("is", $user['id'], $sid);
                $mergeW->execute();

                if ($user['role'] === 'admin') {
                    header('Location: ' . ADMIN_URL . '/');
                } else {
                    $redirect = $_GET['redirect'] ?? '';
                    if (!empty($redirect) && strpos($redirect, SITE_URL) === 0) {
                        header('Location: ' . $redirect);
                    } else {
                        header('Location: ' . SITE_URL . '/');
                    }
                }
                exit();
            }
        } else {
            $error = 'Invalid username or password.';
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
                <h2>Welcome Back</h2>
                <p>Sign in to your account</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 py-2">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= sanitize($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-icon">
                        <i class="bi bi-person"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" value="" autocomplete="off" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon">
                        <i class="bi bi-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" class="forgot-link">Forgot Password?</a>
                </div>

                <button type="submit" class="auth-btn">Sign In</button>
            </form>

            <div class="auth-divider"><span>or</span></div>

            <p class="auth-switch">
                Don't have an account? <a href="<?= SITE_URL ?>/register.php">Create one now</a>
            </p>

            <p class="auth-admin-note">
                <i class="bi bi-shield-lock"></i> Admin? Use your admin credentials to access the dashboard.
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
