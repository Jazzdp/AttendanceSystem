<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: /attendance_system/public/index.php");
    exit;
}
?>
