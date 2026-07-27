<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

try {
switch ($action) {
    case 'add':
        $productId = intval($_POST['productid']);
        $quantity = max(1, intval($_POST['quantity'] ?? 1));
        $color = $_POST['color'] ?? null;
        $size = $_POST['size'] ?? null;
        
        if (isLoggedIn()) {
            $check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND productid = ?");
            $check->bind_param("ii", $_SESSION['user_id'], $productId);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            
            if ($existing) {
                $newQty = $existing['quantity'] + $quantity;
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $newQty, $existing['id']);
            } else {
                $stmt = $conn->prepare("INSERT INTO cart (user_id, productid, quantity, color, size) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiss", $_SESSION['user_id'], $productId, $quantity, $color, $size);
            }
        } else {
            $sid = session_id();
            $check = $conn->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND productid = ?");
            $check->bind_param("si", $sid, $productId);
            $check->execute();
            $existing = $check->get_result()->fetch_assoc();
            
            if ($existing) {
                $newQty = $existing['quantity'] + $quantity;
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->bind_param("ii", $newQty, $existing['id']);
            } else {
                $stmt = $conn->prepare("INSERT INTO cart (session_id, productid, quantity, color, size) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("siiss", $sid, $productId, $quantity, $color, $size);
            }
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => getCartCount()]);
        break;
    
    case 'update':
        $cartId = intval($_POST['cart_id']);
        $quantity = max(1, intval($_POST['quantity']));
        if (isLoggedIn()) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cartId, $_SESSION['user_id']);
        } else {
            $sid = session_id();
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND session_id = ?");
            $stmt->bind_param("iis", $quantity, $cartId, $sid);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        break;
    
    case 'remove':
        $cartId = intval($_POST['cart_id']);
        if (isLoggedIn()) {
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
        } else {
            $sid = session_id();
            $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND session_id = ?");
            $stmt->bind_param("is", $cartId, $sid);
        }
        $stmt->execute();
        echo json_encode(['success' => true, 'message' => 'Removed from cart', 'cart_count' => getCartCount()]);
        break;
    
    case 'count':
        echo json_encode(['success' => true, 'cart_count' => getCartCount()]);
        break;
    
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
