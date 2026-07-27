<?php
$id = $_GET['id'] ?? null;
require_once __DIR__ . '/includes/db.php';
requireAdmin();
include __DIR__ . '/shared/header.php';
include __DIR__ . '/shared/navbar.php';

$productname = '';
$price = '';
$discount = '';
$cateid = '';
$photo1 = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productname = $_POST['product_name'];
    $price = $_POST['product_price'];
    $discount = $_POST['discount'];
    $cateid = $_POST['cateid'];
    $photo1 = '';

    if (isset($_FILES['photo1']) && $_FILES['photo1']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['photo1']['type'], $allowedTypes)) {
            $ext = pathinfo($_FILES['photo1']['name'], PATHINFO_EXTENSION);
            $photo1 = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $uploadPath = __DIR__ . '/image/' . $photo1;
            move_uploaded_file($_FILES['photo1']['tmp_name'], $uploadPath);
        }
    }

    if ($id && $id != -2) {
        if ($photo1) {
            $stmt = $conn->prepare(
                "UPDATE producttbl SET productname = ?, price = ?, discount = ?, cateid = ?, photo1 = ? WHERE productid = ?"
            );
            $stmt->bind_param("ssdisi", $productname, $price, $discount, $cateid, $photo1, $id);
        } else {
            $stmt = $conn->prepare(
                "UPDATE producttbl SET productname = ?, price = ?, discount = ?, cateid = ? WHERE productid = ?"
            );
            $stmt->bind_param("ssdii", $productname, $price, $discount, $cateid, $id);
        }
    } else {
        if ($photo1) {
            $stmt = $conn->prepare(
                "INSERT INTO producttbl (productname, price, discount, cateid, photo1) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssdis", $productname, $price, $discount, $cateid, $photo1);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO producttbl (productname, price, discount, cateid) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssdi", $productname, $price, $discount, $cateid);
        }
    }

    if ($stmt->execute()) {
        header("location: product.php?success=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Get data if update
if ($id && $id != -2) {
    $stmt = $conn->prepare(
        "SELECT productname, price, discount, cateid, photo1 FROM producttbl WHERE productid = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $productname = $row['productname'];
        $price = $row['price'];
        $discount = $row['discount'];
        $cateid = $row['cateid'];
        $photo1 = $row['photo1'];
    }
}

$isNew = ($id == -2);
$pageTitle = $isNew ? 'Add Product' : 'Edit Product';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">
            <i class="bi bi-<?= $isNew ? 'plus-circle-fill' : 'pencil-square' ?> me-2" style="color:var(--accent)"></i>
            <?= $pageTitle ?>
        </h1>
        <p class="page-subtitle"><?= $isNew ? 'Create a new product listing' : 'Update product information' ?></p>
    </div>
    <ol class="breadcrumb-custom">
        <li><a href="index.php">Home</a></li>
        <li><a href="product.php">Products</a></li>
        <li class="active"><?= $pageTitle ?></li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-7 col-md-9">
        <div class="card-pro">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3" style="border-bottom:1px solid var(--border-color);">
                <div style="width:40px;height:40px;background:var(--accent-soft);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:18px;">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
                <div>
                    <div style="font-weight:600;color:var(--text-primary);"><?= $pageTitle ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Fill in the fields below</div>
                </div>
            </div>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-group-pro">
                    <label class="form-label-pro">Category</label>
                    <select class="form-select-pro" name="cateid" required style="background-image:url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e\");background-repeat:no-repeat;background-position:right 12px center;background-size:14px;padding-right:36px;cursor:pointer;">
                        <option value="" <?= empty($cateid) ? 'selected' : '' ?>>— Select Category —</option>
                        <?php
                        $catResult = $conn->query("SELECT cateid, catename FROM categorytbl");
                        while ($cat = $catResult->fetch_assoc()) {
                            $selected = ($cateid == $cat['cateid']) ? 'selected' : '';
                            echo '<option value="' . $cat['cateid'] . '" ' . $selected . '>' . htmlspecialchars($cat['catename']) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group-pro">
                    <label class="form-label-pro">Product Name</label>
                    <input
                        type="text"
                        class="form-control-pro"
                        value="<?= htmlspecialchars($productname) ?>"
                        name="product_name"
                        placeholder="e.g. iPhone 15 Pro"
                        required>
                </div>

                <div class="row g-3">
                    <div class="col-6">
                        <div class="form-group-pro">
                            <label class="form-label-pro">Price ($)</label>
                            <input
                                type="number"
                                class="form-control-pro"
                                value="<?= htmlspecialchars($price) ?>"
                                name="product_price"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                                required>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group-pro">
                            <label class="form-label-pro">Discount (%)</label>
                            <input
                                type="number"
                                class="form-control-pro"
                                value="<?= htmlspecialchars($discount) ?>"
                                name="discount"
                                placeholder="0"
                                min="0"
                                max="100"
                                required>
                        </div>
                    </div>
                </div>

                <div class="form-group-pro">
                    <label class="form-label-pro">Product Photo</label>
                    <div class="file-input-wrapper">
                        <div class="file-input-icon"><i class="bi bi-cloud-upload-fill"></i></div>
                        <div>
                            <div class="file-input-text" style="font-weight:500;color:var(--text-secondary);">Click to upload photo</div>
                            <div class="file-input-text" style="font-size:11px;margin-top:2px;">JPEG, PNG, GIF, WEBP supported</div>
                        </div>
                        <input type="file" name="photo1" accept="image/*">
                    </div>
                    <?php if (!empty($photo1) && $id && $id != -2): ?>
                    <div style="margin-top:12px;display:flex;align-items:center;gap:10px;">
                        <img src="image/<?= htmlspecialchars($photo1) ?>" style="width:64px;height:64px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">
                        <div style="font-size:12px;color:var(--text-muted);">Current photo<br><span style="color:var(--accent);"><?= htmlspecialchars($photo1) ?></span></div>
                    </div>
                    <?php endif; ?>
                </div>

                <div style="display:flex;gap:10px;margin-top:28px;">
                    <button type="submit" class="btn-pro btn-pro-primary">
                        <i class="bi bi-check-lg"></i>
                        <?= $isNew ? 'Create Product' : 'Save Changes' ?>
                    </button>
                    <a href="product.php" class="btn-pro btn-pro-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/shared/footer.php'; ?>