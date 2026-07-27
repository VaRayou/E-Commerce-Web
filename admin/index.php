<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$totalProducts = 0;
$totalCategories = 0;
$totalOrders = 0;
$totalRevenue = 0;

$r = $conn->query("SELECT COUNT(*) AS cnt FROM producttbl");
if ($r) $totalProducts = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM categorytbl");
if ($r) $totalCategories = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
if ($r) $totalOrders = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COALESCE(SUM(total), 0) AS revenue FROM orders WHERE payment_status = 'paid'");
if ($r) $totalRevenue = $r->fetch_assoc()['revenue'];
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h1>
        <p class="page-subtitle">Welcome back, Administrator</p>
    </div>
    <ol class="breadcrumb-custom">
        <li><a href="index.php">Home</a></li>
        <li class="active">Dashboard</li>
    </ol>
</div>

<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon blue"><i class="bi bi-box-seam-fill"></i></div>
            <div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?php echo number_format($totalProducts); ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon green"><i class="bi bi-tag-fill"></i></div>
            <div>
                <div class="stat-label">Categories</div>
                <div class="stat-value"><?php echo number_format($totalCategories); ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon yellow"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-icon purple"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card-pro">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-semibold" style="color:var(--text-primary);">Revenue Overview</h6>
                <span class="badge-pro badge-pro-primary">Live</span>
            </div>
            <canvas id="revenueChart" height="140"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-pro">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="mb-0 fw-semibold" style="color:var(--text-primary);">Quick Stats</h6>
                <span class="badge-pro badge-pro-success">This Month</span>
            </div>
            <div class="p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded" style="background:rgba(67,97,238,0.08);">
                    <div><i class="bi bi-box-seam-fill text-primary me-2"></i><span style="color:var(--text-secondary);">Products</span></div>
                    <strong style="color:var(--text-primary);"><?php echo number_format($totalProducts); ?></strong>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded" style="background:rgba(16,185,129,0.08);">
                    <div><i class="bi bi-tag-fill text-success me-2"></i><span style="color:var(--text-secondary);">Categories</span></div>
                    <strong style="color:var(--text-primary);"><?php echo number_format($totalCategories); ?></strong>
                </div>
                <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded" style="background:rgba(245,158,11,0.08);">
                    <div><i class="bi bi-receipt text-warning me-2"></i><span style="color:var(--text-secondary);">Orders</span></div>
                    <strong style="color:var(--text-primary);"><?php echo number_format($totalOrders); ?></strong>
                </div>
                <div class="d-flex align-items-center justify-content-between p-3 rounded" style="background:rgba(139,92,246,0.08);">
                    <div><i class="bi bi-cash-coin text-purple me-2"></i><span style="color:var(--text-secondary);">Revenue</span></div>
                    <strong style="color:var(--text-primary);">$<?php echo number_format($totalRevenue, 2); ?></strong>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (document.getElementById('revenueChart')) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue',
                data: [0, 0, 0, 0, 0, 0, 0],
                backgroundColor: '#4361ee',
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } }
            }
        }
    });
}
</script>

<?php
include 'includes/footer.php';
?>