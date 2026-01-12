<?php
session_start();
session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

setcookie(session_name(), '', time() - 3600, '/');
header("Location: login.php");
exit;
?>