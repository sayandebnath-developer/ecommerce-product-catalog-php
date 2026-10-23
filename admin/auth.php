<?php
// admin/auth.php - session guard for all admin pages (FR-12)
// Put this line at the very TOP of every admin page:
//     require_once __DIR__ . '/auth.php';

session_start();

// If the admin is not logged in, send the visitor to the login page
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}