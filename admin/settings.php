<?php
include '../includes/db.php';
requireAdmin();
include 'includes/header.php';
include 'includes/navbar.php';

$settings = [
    'site_name' => '', 'site_tagline' => '', 'site_email' => '',
    'site_phone' => '', 'site_address' => '', 'currency' => 'USD',
    'shipping_cost' => '', 'free_shipping_min' => ''
];

foreach ($settings as $key => $default) {
    $settings[$key] = getSetting($key, $default);
}

$success = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($settings as $key => $default) {
        $value = isset($_POST[$key]) ? trim($_POST[$key]) : $default;
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
        $stmt->close();
    }
    $success = 1;
    foreach ($settings as $key => $default) {
        $settings[$key] = getSetting($key, $default);
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title"><i class="bi bi-gear-fill me-2 text-primary"></i>Settings</h1>
        <p class="page-subtitle">Manage site settings</p>
    </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>Settings updated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card-pro">
    <form method="POST" action="">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Site Name</label>
                    <input type="text" name="site_name" class="form-control-pro" value="<?php echo sanitize($settings['site_name']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Site Tagline</label>
                    <input type="text" name="site_tagline" class="form-control-pro" value="<?php echo sanitize($settings['site_tagline']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Site Email</label>
                    <input type="email" name="site_email" class="form-control-pro" value="<?php echo sanitize($settings['site_email']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Site Phone</label>
                    <input type="text" name="site_phone" class="form-control-pro" value="<?php echo sanitize($settings['site_phone']); ?>">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group-pro">
                    <label>Site Address</label>
                    <textarea name="site_address" class="form-control-pro" rows="2"><?php echo sanitize($settings['site_address']); ?></textarea>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Currency</label>
                    <select name="currency" class="form-control-pro">
                        <option value="USD" <?php echo $settings['currency'] === 'USD' ? 'selected' : ''; ?>>USD ($)</option>
                        <option value="EUR" <?php echo $settings['currency'] === 'EUR' ? 'selected' : ''; ?>>EUR (€)</option>
                        <option value="KHR" <?php echo $settings['currency'] === 'KHR' ? 'selected' : ''; ?>>KHR (៛)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Shipping Cost</label>
                    <input type="number" name="shipping_cost" class="form-control-pro" step="0.01" value="<?php echo sanitize($settings['shipping_cost']); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Free Shipping Min</label>
                    <input type="number" name="free_shipping_min" class="form-control-pro" step="0.01" value="<?php echo sanitize($settings['free_shipping_min']); ?>">
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn-pro btn-pro-primary"><i class="bi bi-check-lg me-1"></i>Save Settings</button>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>