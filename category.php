<?php
    require_once __DIR__ . '/includes/db.php';
    requireAdmin();
    include __DIR__ . '/shared/header.php';
    include __DIR__ . '/shared/navbar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-tag-fill me-2" style="color:var(--accent)"></i>Category Management</h1>
        <p class="page-subtitle">Manage all product categories</p>
    </div>
    <ol class="breadcrumb-custom">
        <li><a href="<?= ADMIN_URL ?>/">Home</a></li>
        <li class="active">Categories</li>
    </ol>
</div>

<?php if (isset($_GET['success'])): ?>
<div class="alert-pro alert-pro-success">
    <i class="bi bi-check-circle-fill"></i>
    Category saved successfully!
</div>
<?php endif; ?>

<div class="card-pro">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="mb-0 fw-semibold" style="color:var(--text-primary);">All Categories</h6>
        <a href="category-form.php?id=-2" class="btn-pro btn-pro-primary">
            <i class="bi bi-plus-lg"></i> Add Category
        </a>
    </div>

    <div style="overflow-x:auto;">
        <table class="table-pro">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Category Name</th>
                    <th>Level</th>
                    <th style="width:140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $sql = "SELECT * FROM categorytbl";
                    $result = $conn->query($sql);
                    $i = 1;
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                    <td><span style="color:var(--text-muted);font-size:12px;"><?php echo $i++; ?></span></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;background:var(--accent-soft);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--accent);font-size:14px;">
                                <i class="bi bi-tag-fill"></i>
                            </div>
                            <span style="font-weight:500;"><?php echo htmlspecialchars($row["catename"]); ?></span>
                        </div>
                    </td>
                    <td><span class="badge-pro badge-pro-primary">Level <?php echo htmlspecialchars($row["catelevel"]); ?></span></td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            <a href="category-form.php?id=<?php echo $row["cateid"]; ?>" class="btn-pro btn-pro-primary btn-pro-sm">
                                <i class="bi bi-pencil-fill"></i> Edit
                            </a>
                            <a href="category-delete.php?id=<?php echo $row["cateid"]; ?>" class="btn-pro btn-pro-danger btn-pro-sm" onclick="return confirm('Delete this category?')">
                                <i class="bi bi-trash3-fill"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" style="text-align:center;padding:40px;color:var(--text-muted);"><i class="bi bi-inbox" style="font-size:32px;display:block;margin-bottom:8px;"></i>No categories found</td></tr>';
                    }
                    $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/shared/footer.php'; ?>