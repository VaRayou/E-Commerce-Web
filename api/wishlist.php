<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'toggle':
        if (!isLoggedIn()) {
            echo json_encode(['success' => false, 'message' => 'Login required', 'redirect' => SITE_URL . '/login.php']);
            exit();
        }
        $productId = intval($_POST['productid']);
        $check = $conn->prepare("SELECT id FROM wishlist WHERE user_id = ? AND productid = ?");
        $check->bind_param("ii", $_SESSION['user_id'], $productId);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND productid = ?");
            $stmt->bind_param("ii", $_SESSION['user_id'], $productId);
            $stmt->execute();
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist', 'wishlist_count' => getWishlistCount()]);
        } else {
            $stmt = $conn->prepare("INSERT INTO wishlist (user_id, productid) VALUES (?, ?)");
            $stmt->bind_param("ii", $_SESSION['user_id'], $productId);
            $stmt->execute();
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist', 'wishlist_count' => getWishlistCount()]);
        }
        break;
    
    case 'count':
        echo json_encode(['success' => true, 'wishlist_count' => getWishlistCount()]);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
