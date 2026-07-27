<?php
session_start();

$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') ?: '';
$database = getenv('DB_NAME') ?: 'db_sv13.23';
$port = (int) (getenv('DB_PORT') ?: 3306);

$conn = new mysqli();
if (getenv('DB_HOST')) {
    $conn->ssl_set(null, null, '/etc/ssl/certs/ca-certificates.crt', null, null);
    $conn->real_connect($host, $username, $password, $database, $port, null, MYSQLI_CLIENT_SSL);
} else {
    $conn->real_connect($host, $username, $password, $database, $port);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$httpHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$projectDir = str_replace('\\', '/', dirname(__DIR__));
$relativePath = str_replace($docRoot, '', $projectDir);
$baseUrl = $scheme . '://' . $httpHost . $relativePath;

define('SITE_URL', $baseUrl);
define('ADMIN_URL', $baseUrl . '/admin');
define('UPLOADS_DIR', __DIR__ . '/../uploads/products');
define('IMAGE_DIR', __DIR__ . '/../image');

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isCustomer() {
    return isLoggedIn() && !isAdmin();
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function getUser() {
    global $conn;
    if (!isLoggedIn()) return null;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

function getProductImage($product) {
    if (!empty($product['photo1'])) {
        $photo = $product['photo1'];
        if (strpos($photo, 'uploads/products/') === 0) {
            $photo = str_replace('uploads/products/', '', $photo);
        }
        
        $path = IMAGE_DIR . '/' . $photo;
        if (file_exists($path)) {
            return SITE_URL . '/image/' . $photo;
        }
        $path = UPLOADS_DIR . '/' . $photo;
        if (file_exists($path)) {
            return SITE_URL . '/uploads/products/' . $photo;
        }
        
        $rawPath = __DIR__ . '/../' . $product['photo1'];
        if (file_exists($rawPath)) {
            return SITE_URL . '/' . $product['photo1'];
        }
    }
    return SITE_URL . '/assets/images/placeholder.svg';
}

function getDiscountPrice($price, $discount) {
    if ($discount > 0) {
        return $price - ($price * $discount / 100);
    }
    return $price;
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function getCartCount() {
    global $conn;
    if (isLoggedIn()) {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    } else {
        $sid = session_id();
        $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity), 0) FROM cart WHERE session_id = ?");
        $stmt->bind_param("s", $sid);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }
}

function getWishlistCount() {
    global $conn;
    if (isLoggedIn()) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_row()[0];
    }
    return 0;
}

function isInWishlist($productId) {
    global $conn;
    if (!isLoggedIn()) return false;
    $stmt = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND productid = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $productId);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getProductRating($productId) {
    global $conn;
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE productid = ? AND status = 1");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getSetting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function csrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . csrfToken() . '">';
}

function verifyCsrf() {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $projectRoot = '/New UI';
    return $protocol . '://' . $host . $projectRoot;
}
