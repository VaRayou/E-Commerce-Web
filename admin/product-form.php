<?php
include '../includes/db.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : -2;
$isEdit = $id > 0;
$fields = [
    'product_name' => '', 'product_price' => '', 'discount' => '', 'cateid' => '',
    'description' => '', 'stock' => '', 'brand' => '', 'sku' => '',
    'is_featured' => 0, 'is_new' => 0, 'status' => 1, 'photo1' => ''
];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM producttbl WHERE productid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $prod = $result->fetch_assoc();
        $fields['product_name'] = $prod['productname'];
        $fields['product_price'] = $prod['price'];
        $fields['discount'] = $prod['discount'];
        $fields['cateid'] = $prod['cateid'];
        $fields['description'] = $prod['description'];
        $fields['stock'] = $prod['stock'];
        $fields['brand'] = $prod['brand'];
        $fields['sku'] = $prod['sku'];
        $fields['is_featured'] = $prod['is_featured'];
        $fields['is_new'] = $prod['is_new'];
        $fields['status'] = $prod['status'];
        $fields['photo1'] = $prod['photo1'];
    } else {
        header('Location: product.php');
        exit();
    }
    $stmt->close();
}

$categories = $conn->query("SELECT * FROM categorytbl ORDER BY catename ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields['product_name'] = trim($_POST['product_name']);
    $fields['product_price'] = floatval($_POST['product_price']);
    $fields['discount'] = floatval($_POST['discount']);
    $fields['cateid'] = intval($_POST['cateid']);
    $fields['description'] = trim($_POST['description']);
    $fields['stock'] = intval($_POST['stock']);
    $fields['brand'] = trim($_POST['brand']);
    $fields['sku'] = trim($_POST['sku'] ?? '');
    $fields['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $fields['is_new'] = isset($_POST['is_new']) ? 1 : 0;
    $fields['status'] = isset($_POST['status']) ? 1 : 0;

    if (isset($_FILES['photo1']) && $_FILES['photo1']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo1']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destination = '../image/' . $filename;
        if (move_uploaded_file($_FILES['photo1']['tmp_name'], $destination)) {
            if ($isEdit && !empty($fields['photo1']) && file_exists('../image/' . $fields['photo1'])) {
                unlink('../image/' . $fields['photo1']);
            }
            $fields['photo1'] = $filename;
        }
    }

    if ($isEdit) {
        $stmt = $conn->prepare("UPDATE producttbl SET productname = ?, price = ?, discount = ?, cateid = ?, description = ?, stock = ?, brand = ?, sku = ?, is_featured = ?, is_new = ?, status = ?, photo1 = ? WHERE productid = ?");
        $stmt->bind_param("ssdisissiiisi", $fields['product_name'], $fields['product_price'], $fields['discount'], $fields['cateid'], $fields['description'], $fields['stock'], $fields['brand'], $fields['sku'], $fields['is_featured'], $fields['is_new'], $fields['status'], $fields['photo1'], $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO producttbl (productname, price, discount, cateid, description, stock, brand, sku, is_featured, is_new, status, photo1) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisissiiis", $fields['product_name'], $fields['product_price'], $fields['discount'], $fields['cateid'], $fields['description'], $fields['stock'], $fields['brand'], $fields['sku'], $fields['is_featured'], $fields['is_new'], $fields['status'], $fields['photo1']);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: product.php?success=1');
        exit();
    }
    $stmt->close();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-box-seam-fill me-2 text-primary"></i><?php echo $isEdit ? 'Edit Product' : 'Add Product'; ?></h1>
        <p class="page-subtitle"><?php echo $isEdit ? 'Update product information' : 'Add a new product to inventory'; ?></p>
    </div>
    <a href="product.php" class="btn-pro btn-pro-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card-pro">
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Product Name</label>
                    <input type="text" name="product_name" class="form-control-pro" value="<?php echo sanitize($fields['product_name']); ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Brand</label>
                    <input type="text" name="brand" class="form-control-pro" value="<?php echo sanitize($fields['brand']); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Price</label>
                    <input type="number" name="product_price" class="form-control-pro" step="0.01" value="<?php echo $fields['product_price']; ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Discount (%)</label>
                    <input type="number" name="discount" class="form-control-pro" step="0.01" value="<?php echo $fields['discount']; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Stock</label>
                    <input type="number" name="stock" class="form-control-pro" value="<?php echo $fields['stock']; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Category</label>
                    <select name="cateid" class="form-control-pro" required>
                        <option value="">Select Category</option>
                        <?php if ($categories): while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['cateid']; ?>" <?php echo $fields['cateid'] == $cat['cateid'] ? 'selected' : ''; ?>><?php echo sanitize($cat['catename']); ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>SKU</label>
                    <input type="text" name="sku" class="form-control-pro" value="<?php echo sanitize($fields['sku']); ?>">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group-pro">
                    <label>Description</label>
                    <textarea name="description" class="form-control-pro" rows="4"><?php echo sanitize($fields['description']); ?></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Product Image</label>
                    <input type="file" name="photo1" class="form-control-pro" accept="image/*">
                    <?php if ($isEdit && !empty($fields['photo1'])): ?>
                        <small style="color:var(--text-secondary);">Current: <?php echo sanitize($fields['photo1']); ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>&nbsp;</label>
                    <div class="d-flex gap-4 align-items-center" style="min-height:38px;">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" <?php echo $fields['is_featured'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isFeatured">Featured/On Trend</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_new" id="isNew" <?php echo $fields['is_new'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isNew">New</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" <?php echo $fields['status'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="status">Active</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn-pro btn-pro-primary"><i class="bi bi-check-lg me-1"></i><?php echo $isEdit ? 'Update Product' : 'Add Product'; ?></button>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>