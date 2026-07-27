<?php
include '../includes/db.php';
requireAdmin();

if (isset($_GET['approve'])) {
    $rid = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE reviews SET status = 1 WHERE id = ?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $stmt->close();
    header('Location: reviews.php');
    exit();
}

if (isset($_GET['delete'])) {
    $rid = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param("i", $rid);
    $stmt->execute();
    $stmt->close();
    header('Location: reviews.php');
    exit();
}

$reviews = $conn->query("
    SELECT r.*, p.productname, CONCAT(u.first_name, ' ', u.last_name) AS customer_name
    FROM reviews r
    LEFT JOIN producttbl p ON r.productid = p.productid
    LEFT JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
");

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-star-fill me-2 text-primary"></i>Reviews</h1>
        <p class="page-subtitle">Manage customer reviews</p>
    </div>
</div>

<div class="card-pro">
    <div class="table-responsive">
        <table class="table-pro">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reviews && $reviews->num_rows > 0): ?>
                    <?php $i = 1; while ($rev = $reviews->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $i++; ?></td>
                        <td><?php echo sanitize($rev['productname'] ?? 'Deleted'); ?></td>
                        <td><?php echo sanitize($rev['customer_name'] ?? 'Unknown'); ?></td>
                        <td>
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="bi bi-star<?php echo $s <= $rev['rating'] ? '-fill' : ''; ?>" style="color:#f59e0b;font-size:0.85rem;"></i>
                            <?php endfor; ?>
                        </td>
                        <td style="max-width:250px;"><?php echo sanitize($rev['comment']); ?></td>
                        <td>
                            <?php if ($rev['status']): ?>
                                <span class="badge-pro badge-pro-success">Approved</span>
                            <?php else: ?>
                                <span class="badge-pro badge-pro-warning">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($rev['created_at'])); ?></td>
                        <td>
                            <?php if (!$rev['status']): ?>
                                <a href="reviews.php?approve=<?php echo $rev['id']; ?>" class="btn-pro btn-pro-sm btn-pro-success" title="Approve"><i class="bi bi-check-lg"></i></a>
                            <?php endif; ?>
                            <a href="reviews.php?delete=<?php echo $rev['id']; ?>" class="btn-pro btn-pro-sm btn-pro-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this review?');"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-4" style="color:var(--text-secondary);">No reviews found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
include 'includes/footer.php';
?>