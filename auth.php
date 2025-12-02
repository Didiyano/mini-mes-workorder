<?php
// Start session kalau belum mula
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kalau tak login, redirect ke login page
if (!isset($_SESSION['operator_id'])) {
    header('Location: login.php');
    exit;
}
