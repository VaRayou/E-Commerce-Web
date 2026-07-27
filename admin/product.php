<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$success = isset($_GET['success']) ? $_GET['success'] : 0;
$products = $conn->query("
    SELECT p.*, c.catename 
    FROM producttbl p 
    LEFT JOIN categorytbl c ON p.cateid = c.cateid 
    ORDER BY p.productid DESC
");
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-box-seam-fill me-2 text-primary"></i>Products</h1>
        <p class="page-subtitle">Manage your product inventory</p>
    </div>
    <a href="product-form.php?id=-2" class="btn-pro btn-pro-primary"><i class="bi bi-plus-lg me-1"></i>Add Product</a>
</div>

<?php if ($success == 1): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>Product saved successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card-pro">
    <div class="table-responsive">
        <table class="table-pro">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Photo</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php $i = 1; while ($prod = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo sanitize($prod['productname']); ?></td>
                        <td><?php echo sanitize($prod['catename'] ?? 'N/A'); ?></td>
                        <td>$<?php echo number_format($prod['price'], 2); ?></td>
                        <td><?php echo $prod['discount'] > 0 ? $prod['discount'] . '%' : '-'; ?></td>
                        <td>
                            <?php
                            $photoSlots = ['photo1', 'photo2', 'photo3'];
                            $validPhotos = [];
                            foreach ($photoSlots as $slot) {
                                if (!empty($prod[$slot]) && file_exists('../image/' . $prod[$slot])) {
                                    $validPhotos[] = $prod[$slot];
                                }
                            }
                            $photoCount = count($validPhotos);
                            ?>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($photoCount > 0): ?>
                                    <?php foreach ($validPhotos as $photo): ?>
                                        <img src="../image/<?php echo sanitize($photo); ?>" alt="Product Image" width="40" height="40" style="border-radius:6px; object-fit:cover;">
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center bg-light border text-muted" style="width:40px; height:40px; border-radius:6px; font-size:10px; text-align:center; line-height:1;">
                                        <i class="bi bi-image fs-6"></i>
                                        <span style="font-size: 7px; margin-top: 2px;">No Image</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($photoCount < 3): ?>
                                    <div style="position:relative; display:inline-block;">
                                        <form id="uploadForm<?php echo $prod['productid']; ?>" action="upload-image.php" method="POST" enctype="multipart/form-data" style="display:none;">
                                            <input type="hidden" name="productid" value="<?php echo $prod['productid']; ?>">
                                            <input type="file" name="image_file" accept="image/*" onchange="this.form.submit();">
                                        </form>
                                        <a href="javascript:void(0);" class="text-primary text-decoration-none fs-5" title="Upload Image" onclick="document.getElementById('uploadForm<?php echo $prod['productid']; ?>').querySelector('input[type=file]').click();">
                                            <i class="bi bi-plus-circle-fill"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php if ($prod['status'] == 1): ?>
                                <span class="badge-pro badge-pro-success">Active</span>
                            <?php else: ?>
                                <span class="badge-pro badge-pro-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="product-form.php?id=<?php echo $prod['productid']; ?>" class="btn-pro btn-pro-sm btn-pro-info" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="product-delete.php?id=<?php echo $prod['productid']; ?>" class="btn-pro btn-pro-sm btn-pro-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4" style="color:var(--text-secondary);">No products found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>