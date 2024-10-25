<?php
// index.php
session_start();

// First, check if we need to clear the session
if (isset($_GET['clear'])) {
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
    header('Location: login.php');
    exit();
}

// Simple redirect logic
if (!isset($_SESSION['user_id'])) {
    // Not logged in, go to login
    header('Location: login.php');
    exit();
}

// Logged in, check role and redirect
if ($_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
} else {
    header('Location: test2.php');
}
exit();
?>