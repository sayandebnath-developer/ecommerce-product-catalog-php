<?php
// admin/menu.php - shared admin menu (shown on every admin page)
// Use it after header.php:  require __DIR__ . '/menu.php';
require_once __DIR__ . '/auth.php';

$current = basename($_SERVER['PHP_SELF']);   // name of the page being viewed
?>
<nav class="admin-menu">
    <a href="dashboard.php" class="<?php echo ($current === 'dashboard.php') ? 'active' : ''; ?>">Dashboard</a>
    <a href="categories.php" class="<?php echo in_array($current, ['categories.php', 'category_edit.php', 'category_delete.php']) ? 'active' : ''; ?>">Categories</a>
    <a href="products.php" class="<?php echo in_array($current, ['products.php', 'product_add.php', 'product_edit.php', 'product_delete.php']) ? 'active' : ''; ?>">Products</a>
    <span class="admin-user">Logged in as <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
    <a href="logout.php" class="logout-link">Logout</a>
</nav>