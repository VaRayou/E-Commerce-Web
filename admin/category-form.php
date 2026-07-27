<?php
include '../includes/db.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : -2;
$isEdit = $id > 0;
$catName = '';
$catLevel = '';

if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM categorytbl WHERE cateid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $cat = $result->fetch_assoc();
        $catName = $cat['catename'];
        $catLevel = $cat['catelevel'];
    } else {
        header('Location: category.php');
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catName = trim($_POST['category_name']);
    $catLevel = trim($_POST['category_level']);

    if ($isEdit) {
        $stmt = $conn->prepare("UPDATE categorytbl SET catename = ?, catelevel = ? WHERE cateid = ?");
        $stmt->bind_param("ssi", $catName, $catLevel, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO categorytbl (catename, catelevel) VALUES (?, ?)");
        $stmt->bind_param("ss", $catName, $catLevel);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: category.php?success=1');
        exit();
    }
    $stmt->close();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-tag-fill me-2 text-primary"></i><?php echo $isEdit ? 'Edit Category' : 'Add Category'; ?></h1>
        <p class="page-subtitle"><?php echo $isEdit ? 'Update category information' : 'Create a new category'; ?></p>
    </div>
    <a href="category.php" class="btn-pro btn-pro-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card-pro">
    <form method="POST" action="">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Category Name</label>
                    <input type="text" name="category_name" class="form-control-pro" value="<?php echo sanitize($catName); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Category Level</label>
                    <input type="text" name="category_level" class="form-control-pro" value="<?php echo sanitize($catLevel); ?>" required>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn-pro btn-pro-primary"><i class="bi bi-check-lg me-1"></i><?php echo $isEdit ? 'Update Category' : 'Add Category'; ?></button>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>