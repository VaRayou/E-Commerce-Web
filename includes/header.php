<?php require_once __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= getSetting('site_tagline', 'Your Fashion Destination') ?>">
    <title><?= isset($pageTitle) ? sanitize($pageTitle) . ' | ' : '' ?><?= getSetting('site_name', 'WE YOUNG Shop') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= SITE_URL ?>/assets/css/frontend.css" rel="stylesheet">
</head>

<body>
    <?php if ($flash = flash('success')): ?>
        <div class="flash-message flash-success" id="flashMsg">
            <i class="bi bi-check-circle-fill"></i> <?= $flash ?>
            <button type="button" class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    <?php if ($flash = flash('error')): ?>
        <div class="flash-message flash-error" id="flashMsg">
            <i class="bi bi-exclamation-circle-fill"></i> <?= $flash ?>
            <button type="button" class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    <script>
        setTimeout(() => {
            const el = document.getElementById('flashMsg');
            if (el) el.remove();
        }, 4000);
    </script>