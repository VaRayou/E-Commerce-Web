<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$success = isset($_GET['success']) ? $_GET['success'] : 0;
$categories = $conn->query("SELECT * FROM categorytbl ORDER BY cateid DESC");
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-tag-fill me-2 text-primary"></i>Categories</h1>
        <p class="page-subtitle">Manage your product categories</p>
    </div>
    <a href="category-form.php?id=-2" class="btn-pro btn-pro-primary"><i class="bi bi-plus-lg me-1"></i>Add Category</a>
</div>

<?php if ($success == 1): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>Category saved successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card-pro">
    <div class="table-responsive">
        <table class="table-pro">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Category Name</th>
                    <th>Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php $i = 1; while ($cat = $categories->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo sanitize($cat['catename']); ?></td>
                        <td><?php echo sanitize($cat['catelevel']); ?></td>
                        <td>
                            <a href="category-form.php?id=<?php echo $cat['cateid']; ?>" class="btn-pro btn-pro-sm btn-pro-info" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="category-delete.php?id=<?php echo $cat['cateid']; ?>" class="btn-pro btn-pro-sm btn-pro-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4" style="color:var(--text-secondary);">No categories found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>