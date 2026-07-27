<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$coupons = $conn->query("SELECT * FROM coupons ORDER BY id DESC");
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-percent me-2 text-primary"></i>Coupons</h1>
        <p class="page-subtitle">Manage discount coupons</p>
    </div>
    <a href="coupons-form.php?id=-2" class="btn-pro btn-pro-primary"><i class="bi bi-plus-lg me-1"></i>Add Coupon</a>
</div>

<div class="card-pro">
    <div class="table-responsive">
        <table class="table-pro">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Min Purchase</th>
                    <th>Uses</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($coupons && $coupons->num_rows > 0): ?>
                    <?php $i = 1; while ($coupon = $coupons->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><strong><?php echo sanitize($coupon['code']); ?></strong></td>
                        <td><span class="badge-pro badge-pro-primary"><?php echo ucfirst(sanitize($coupon['type'])); ?></span></td>
                        <td><?php echo $coupon['type'] === 'percentage' ? $coupon['value'] . '%' : '$' . number_format($coupon['value'], 2); ?></td>
                        <td>$<?php echo number_format($coupon['min_purchase'], 2); ?></td>
                        <td><?php echo $coupon['used_count']; ?> / <?php echo $coupon['max_uses'] > 0 ? $coupon['max_uses'] : '∞'; ?></td>
                        <td>
                            <?php if ($coupon['is_active']): ?>
                                <span class="badge-pro badge-pro-success">Active</span>
                            <?php else: ?>
                                <span class="badge-pro badge-pro-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="coupons-form.php?id=<?php echo $coupon['id']; ?>" class="btn-pro btn-pro-sm btn-pro-info" title="Edit"><i class="bi bi-pencil-square"></i></a>
                            <a href="coupons-delete.php?id=<?php echo $coupon['id']; ?>" class="btn-pro btn-pro-sm btn-pro-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this coupon?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4" style="color:var(--text-secondary);">No coupons found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>