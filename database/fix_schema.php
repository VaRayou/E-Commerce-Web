<?php
/**
 * Database Schema Fix Script
 * Visit this page ONCE in your browser to add missing columns to your database.
 * After fixing, DELETE this file for security.
 */

$host = "localhost";
$username = "root";
$password = "";
$database = "db_sv13.23";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$message = '';

// Get existing columns in producttbl
$result = $conn->query("DESCRIBE producttbl");
$existingColumns = [];
while ($row = $result->fetch_assoc()) {
    $existingColumns[] = $row['Field'];
}

// Columns that need to exist in producttbl
$requiredColumns = [
    'status'       => "ALTER TABLE producttbl ADD COLUMN `status` TINYINT NOT NULL DEFAULT 1 AFTER `sku`",
    'is_featured'  => "ALTER TABLE producttbl ADD COLUMN `is_featured` TINYINT NOT NULL DEFAULT 0 AFTER `status`",
    'is_new'       => "ALTER TABLE producttbl ADD COLUMN `is_new` TINYINT NOT NULL DEFAULT 0 AFTER `is_featured`",
    'sales_count'  => "ALTER TABLE producttbl ADD COLUMN `sales_count` INT NOT NULL DEFAULT 0 AFTER `is_new`",
    'created_at'   => "ALTER TABLE producttbl ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `sales_count`",
    'updated_at'   => "ALTER TABLE producttbl ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`",
];

$added = 0;
$skipped = 0;

foreach ($requiredColumns as $col => $sql) {
    if (in_array($col, $existingColumns)) {
        $skipped++;
    } else {
        if ($conn->query($sql)) {
            $added++;
            $message .= "<p style='color:green;'>Added column: <strong>$col</strong></p>";
        } else {
            $message .= "<p style='color:red;'>Error adding <strong>$col</strong>: " . $conn->error . "</p>";
        }
    }
}

// Check all other required tables exist
$tables = ['users', 'brands', 'product_attributes', 'cart', 'wishlist', 'orders', 'order_items', 'reviews', 'coupons', 'settings'];
foreach ($tables as $table) {
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if ($check->num_rows === 0) {
        $message .= "<p style='color:orange;'>Missing table: <strong>$table</strong> - Run database/setup.sql to create it.</p>";
    }
}

// Verify order_items has correct columns
$result = $conn->query("DESCRIBE order_items");
$orderItemCols = [];
while ($row = $result->fetch_assoc()) {
    $orderItemCols[] = $row['Field'];
}
if (!empty($orderItemCols) && !in_array('product_name', $orderItemCols) && in_array('productname', $orderItemCols)) {
    $conn->query("ALTER TABLE order_items CHANGE COLUMN `productname` `product_name` VARCHAR(255) NOT NULL");
    $message .= "<p style='color:blue;'>Fixed: Renamed order_items.productname to product_name</p>";
}
if (!empty($orderItemCols) && !in_array('total', $orderItemCols) && in_array('subtotal', $orderItemCols)) {
    $conn->query("ALTER TABLE order_items CHANGE COLUMN `subtotal` `total` DECIMAL(10,2) NOT NULL");
    $message .= "<p style='color:blue;'>Fixed: Renamed order_items.subtotal to total</p>";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head><title>Database Schema Fix</title></head>
<body style="font-family:sans-serif;max-width:700px;margin:40px auto;padding:20px;">
    <h1>Database Schema Fix</h1>
    <?php if ($message): ?>
        <?= $message ?>
    <?php else: ?>
        <p style="color:green;font-size:18px;">All columns already exist. Nothing to fix.</p>
    <?php endif; ?>
    <p><strong>Added:</strong> <?= $added ?> column(s)<br>
    <strong>Skipped (already exist):</strong> <?= $skipped ?> column(s)</p>
    <hr>
    <p style="color:red;font-weight:bold;">IMPORTANT: Delete this file (database/fix_schema.php) after running it!</p>
    <p><a href="../index.php">Go to Homepage</a></p>
</body>
</html>
