<?php
include '../includes/db.php';
requireAdmin();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT photo1 FROM producttbl WHERE productid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (!empty($row['photo1']) && file_exists('../image/' . $row['photo1'])) {
            unlink('../image/' . $row['photo1']);
        }
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM producttbl WHERE productid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header('Location: product.php');
exit();
?>