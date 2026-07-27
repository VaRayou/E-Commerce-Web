<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid method']);
    exit();
}

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit();
}

$productId = intval($_POST['productid']);
$rating = intval($_POST['rating']);
$comment = trim($_POST['comment'] ?? '');

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Rating must be between 1 and 5']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO reviews (user_id, productid, rating, comment) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $_SESSION['user_id'], $productId, $rating, $comment);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
}
