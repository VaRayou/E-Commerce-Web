<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$customers = $conn->query("SELECT * FROM users WHERE role = 'customer' ORDER BY id DESC");
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-people-fill me-2 text-primary"></i>Customers</h1>
        <p class="page-subtitle">Manage registered customers</p>
    </div>
</div>

<div class="card-pro">
    <div class="table-responsive">
        <table class="table-pro">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Joined</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($customers && $customers->num_rows > 0): ?>
                    <?php $i = 1; while ($cust = $customers->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo sanitize($cust['first_name'] . ' ' . $cust['last_name']); ?></td>
                        <td><?php echo sanitize($cust['email']); ?></td>
                        <td><?php echo sanitize($cust['phone'] ?? '-'); ?></td>
                        <td><?php echo date('M d, Y', strtotime($cust['created_at'])); ?></td>
                        <td>
                            <?php if ($cust['is_active']): ?>
                                <span class="badge-pro badge-pro-success">Active</span>
                            <?php else: ?>
                                <span class="badge-pro badge-pro-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4" style="color:var(--text-secondary);">No customers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>