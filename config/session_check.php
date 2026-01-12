<?php
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['last_activity'])) {
    header("Location: login.php");
    exit;
}

$timeout = 1800;

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

$_SESSION['last_activity'] = time();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>