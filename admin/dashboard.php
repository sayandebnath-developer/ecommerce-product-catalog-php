<?php
// admin/dashboard.php - admin dashboard with summary counts
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

// Run a COUNT query and return the number
function get_count($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_row($result);
    return (int)$row[0];
}

$total_categories = get_count($conn, 'SELECT COUNT(*) FROM categories');
$total_products   = get_count($conn, 'SELECT COUNT(*) FROM products');
$featured         = get_count($conn, 'SELECT COUNT(*) FROM products WHERE is_featured = 1');
$out_of_stock     = get_count($conn, 'SELECT COUNT(*) FROM products WHERE stock_quantity <= 0');

$page_title = 'Admin Dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Admin Dashboard</h1>
<p class="subtitle">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>. Here is a summary of your catalog.</p>

<div class="stat-grid">
    <div class="stat-box"><strong><?php echo $total_categories; ?></strong>Categories</div>
    <div class="stat-box"><strong><?php echo $total_products; ?></strong>Products</div>
    <div class="stat-box"><strong><?php echo $featured; ?></strong>Featured products</div>
    <div class="stat-box"><strong><?php echo $out_of_stock; ?></strong>Out of stock</div>
</div>

<h2>Quick links</h2>
<a class="btn" href="categories.php">Manage categories</a>
<a class="btn" href="products.php">Manage products</a>

<?php require __DIR__ . '/../includes/footer.php'; ?>