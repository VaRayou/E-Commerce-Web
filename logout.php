<?php
require_once __DIR__ . '/includes/db.php';
session_destroy();
header('Location: ' . SITE_URL . '/login.php');
exit();
