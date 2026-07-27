<?php
include '../includes/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
    $productid = intval($_POST['productid'] ?? 0);

    if ($productid <= 0) {
        header('Location: product.php');
        exit();
    }

    $stmt = $conn->prepare("SELECT photo1, photo2, photo3 FROM producttbl WHERE productid = ?");
    $stmt->bind_param("i", $productid);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();

    if (!$product) {
        header('Location: product.php');
        exit();
    }

    $targetSlot = null;
    if (empty($product['photo1']) || !file_exists('../image/' . $product['photo1'])) {
        $targetSlot = 'photo1';
    } elseif (empty($product['photo2']) || !file_exists('../image/' . $product['photo2'])) {
        $targetSlot = 'photo2';
    } elseif (empty($product['photo3']) || !file_exists('../image/' . $product['photo3'])) {
        $targetSlot = 'photo3';
    }

    if ($targetSlot) {
        $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = '../image/' . $filename;

        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $destination)) {
            $stmt = $conn->prepare("UPDATE producttbl SET $targetSlot = ? WHERE productid = ?");
            $stmt->bind_param("si", $filename, $productid);
            $stmt->execute();
            $stmt->close();
            header('Location: product.php?success=1');
            exit();
        }
    }
}

header('Location: product.php');
exit();
