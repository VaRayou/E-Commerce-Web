<?php
$id = $_GET['id'] ?? null;
require_once __DIR__ . '/includes/db.php';
requireAdmin();
include __DIR__ . '/shared/header.php';
include __DIR__ . '/shared/navbar.php';

$catename = '';
$catelevel = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $catename = $_POST['category_name'];
    $catelevel = $_POST['category_level'];

    if ($id && $id != -2) {
        $stmt = $conn->prepare(
            "UPDATE categorytbl SET catename = ?, catelevel = ? WHERE cateid = ?"
        );
        $stmt->bind_param("ssi", $catename, $catelevel, $id);
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO categorytbl (catename, catelevel) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $catename, $catelevel);
    }

    if ($stmt->execute()) {
        header("location: category.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Get data if update
if ($id && $id != -2) {
    $stmt = $conn->prepare(
        "SELECT catename, catelevel FROM categorytbl WHERE cateid = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $catename = $row['catename'];
        $catelevel = $row['catelevel'];
    }
}

$isNew = ($id == -2);
$pageTitle = $isNew ? 'Add Category' : 'Edit Category';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-<?= $isNew ? 'plus-circle-fill' : 'pencil-square' ?> me-2" style="color:var(--accent)"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="page-subtitle"><?= $isNew ? 'Create a new product category' : 'Update category information' ?></p>
    </div>
    <ol class="breadcrumb-custom">
        <li><a href="<?= ADMIN_URL ?>/">Home</a></li>
        <li><a href="category.php">Categories</a></li>
        <li class="active"><?= $pageTitle ?></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-6 col-md-8">
        <div class="card-pro">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid var(--border-color);">
                <div style="width:40px;height:40px;background:var(--accent-soft);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:18px;">
                    <i class="bi bi-tag-fill"></i>
                </div>
                <div>
                    <div style="font-weight:600;color:var(--text-primary);"><?= $pageTitle ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Fill in the fields below</div>
                </div>
            </div>

            <form method="POST">
                <div class="form-group-pro">
                    <label class="form-label-pro">Category Name</label>
                    <input
                        type="text"
                        class="form-control-pro"
                        value="<?= htmlspecialchars($catename) ?>"
                        name="category_name"
                        placeholder="e.g. Electronics"
                        required>
                </div>

                <div class="form-group-pro">
                    <label class="form-label-pro">Category Level</label>
                    <input
                        type="number"
                        class="form-control-pro"
                        value="<?= htmlspecialchars($catelevel) ?>"
                        name="category_level"
                        placeholder="e.g. 1"
                        min="1"
                        required>
                </div>

                <div style="display:flex;gap:10px;margin-top:28px;">
                    <button type="submit" class="btn-pro btn-pro-primary">
                        <i class="bi bi-check-lg"></i>
                        <?= $isNew ? 'Create Category' : 'Save Changes' ?>
                    </button>
                    <a href="category.php" class="btn-pro btn-pro-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/shared/footer.php'; ?>