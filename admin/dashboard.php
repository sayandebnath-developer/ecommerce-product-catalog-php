<?php
// admin/dashboard.php - TEMPORARY page (replaced on Day 4)
require_once __DIR__ . '/auth.php';

$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
?>

<h1>Admin Dashboard</h1>
<p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>.</p>

<div class="message success">You are logged in successfully.</div>

<p>Category and product management will be added here in the next steps.</p>

<a class="btn btn-danger" href="logout.php">Log out</a>

<?php require __DIR__ . '/../includes/footer.php'; ?>