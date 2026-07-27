<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit();
}

$search = "%$query%";
$stmt = $conn->prepare("SELECT productid, productname, price, discount, photo1, c.catename FROM producttbl p LEFT JOIN categorytbl c ON p.cateid = c.cateid WHERE p.status = 1 AND (p.productname LIKE ? OR p.brand LIKE ? OR c.catename LIKE ?) ORDER BY p.sales_count DESC LIMIT 8");
$stmt->bind_param("sss", $search, $search, $search);
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$response = [];
foreach ($results as $row) {
    $imageUrl = !empty($row['photo1']) ? SITE_URL . '/image/' . $row['photo1'] : SITE_URL . '/assets/images/placeholder.png';
    $finalPrice = $row['price'];
    if ($row['discount'] > 0) {
        $finalPrice = $row['price'] - ($row['price'] * $row['discount'] / 100);
    }
    $response[] = [
        'id' => $row['productid'],
        'name' => $row['productname'],
        'price' => '$' . number_format($finalPrice, 2),
        'image' => $imageUrl,
        'category' => $row['catename'] ?? ''
    ];
}

echo json_encode(['results' => $response]);
