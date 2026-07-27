<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$productid = intval($_GET['id'] ?? 0);
if ($productid <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit();
}

$stmt = $conn->prepare("SELECT productid, productname, price, discount, description, photo1, cateid FROM producttbl WHERE productid = ? AND status = 1");
$stmt->bind_param("i", $productid);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    http_response_code(404);
    echo json_encode(['error' => 'Product not found']);
    exit();
}

$finalPrice = $product['price'];
$oldPrice = null;
if ($product['discount'] > 0) {
    $oldPrice = $product['price'];
    $finalPrice = $product['price'] - ($product['price'] * $product['discount'] / 100);
}

$imageUrl = getProductImage($product);

echo json_encode([
    'id' => $product['productid'],
    'name' => $product['productname'],
    'price' => number_format($finalPrice, 2),
    'old_price' => $oldPrice ? number_format($oldPrice, 2) : null,
    'description' => $product['description'] ?? 'No description available.',
    'image' => $imageUrl
]);
