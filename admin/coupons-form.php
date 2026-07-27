<?php
include '../includes/db.php';
requireAdmin();

$id = isset($_GET['id']) ? intval($_GET['id']) : -2;
$isEdit = $id > 0;
$fields = [
    'code' => '', 'type' => 'percentage', 'value' => '', 'min_purchase' => '',
    'max_uses' => '', 'start_date' => '', 'end_date' => '', 'is_active' => 1
];

if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $coupon = $result->fetch_assoc();
        $fields['code'] = $coupon['code'];
        $fields['type'] = $coupon['type'];
        $fields['value'] = $coupon['value'];
        $fields['min_purchase'] = $coupon['min_purchase'];
        $fields['max_uses'] = $coupon['max_uses'];
        $fields['start_date'] = $coupon['start_date'];
        $fields['end_date'] = $coupon['end_date'];
        $fields['is_active'] = $coupon['is_active'];
    } else {
        header('Location: coupons.php');
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields['code'] = strtoupper(trim($_POST['code']));
    $fields['type'] = trim($_POST['type']);
    $fields['value'] = floatval($_POST['value']);
    $fields['min_purchase'] = floatval($_POST['min_purchase']);
    $fields['max_uses'] = intval($_POST['max_uses']);
    $fields['start_date'] = $_POST['start_date'];
    $fields['end_date'] = $_POST['end_date'];
    $fields['is_active'] = isset($_POST['is_active']) ? 1 : 0;

    if ($isEdit) {
        $stmt = $conn->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, min_purchase = ?, max_uses = ?, start_date = ?, end_date = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssddissii", $fields['code'], $fields['type'], $fields['value'], $fields['min_purchase'], $fields['max_uses'], $fields['start_date'], $fields['end_date'], $fields['is_active'], $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO coupons (code, type, value, min_purchase, max_uses, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddissi", $fields['code'], $fields['type'], $fields['value'], $fields['min_purchase'], $fields['max_uses'], $fields['start_date'], $fields['end_date'], $fields['is_active']);
    }

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: coupons.php');
        exit();
    }
    $stmt->close();
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 class="page-title"><i class="bi bi-percent me-2 text-primary"></i><?php echo $isEdit ? 'Edit Coupon' : 'Add Coupon'; ?></h1>
        <p class="page-subtitle"><?php echo $isEdit ? 'Update coupon information' : 'Create a new coupon'; ?></p>
    </div>
    <a href="coupons.php" class="btn-pro btn-pro-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="card-pro">
    <form method="POST" action="">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Coupon Code</label>
                    <input type="text" name="code" class="form-control-pro" value="<?php echo sanitize($fields['code']); ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Type</label>
                    <select name="type" class="form-control-pro" required>
                        <option value="percentage" <?php echo $fields['type'] === 'percentage' ? 'selected' : ''; ?>>Percentage</option>
                        <option value="fixed" <?php echo $fields['type'] === 'fixed' ? 'selected' : ''; ?>>Fixed Amount</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Value</label>
                    <input type="number" name="value" class="form-control-pro" step="0.01" value="<?php echo $fields['value']; ?>" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Minimum Purchase</label>
                    <input type="number" name="min_purchase" class="form-control-pro" step="0.01" value="<?php echo $fields['min_purchase']; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Max Uses</label>
                    <input type="number" name="max_uses" class="form-control-pro" value="<?php echo $fields['max_uses']; ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group-pro">
                    <label>Status</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?php echo $fields['is_active'] ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="isActive">Active</label>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>Start Date</label>
                    <input type="date" name="start_date" class="form-control-pro" value="<?php echo sanitize($fields['start_date']); ?>">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group-pro">
                    <label>End Date</label>
                    <input type="date" name="end_date" class="form-control-pro" value="<?php echo sanitize($fields['end_date']); ?>">
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn-pro btn-pro-primary"><i class="bi bi-check-lg me-1"></i><?php echo $isEdit ? 'Update Coupon' : 'Add Coupon'; ?></button>
        </div>
    </form>
</div>

<?php
include 'includes/footer.php';
?>