<?php
require_once 'config/db.php';
require 'includes/functions.php';

$category = null;   // the selected category (one row) or null
$products = null;   // the result of the product query
$error    = '';

// 1. Read and validate the category ID from the address (GET)
if (isset($_GET['category'])) {
    $cid = $_GET['category'];

    if (!is_string($cid) || !ctype_digit($cid) || (int)$cid < 1) {
        $error = 'Invalid category.';
    } else {
        $cid = (int)$cid;
        $stmt = mysqli_prepare($conn,
            'SELECT category_id, category_name, description FROM categories WHERE category_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $cid);
        mysqli_stmt_execute($stmt);
        $category = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if (!$category) {
            $error = 'Category not found.';
        }
    }
}

// 2. Get the products
if ($error === '') {
    if ($category) {
        $stmt = mysqli_prepare($conn,
            'SELECT p.*, c.category_name
             FROM products p JOIN categories c ON p.category_id = c.category_id
             WHERE p.category_id = ?
             ORDER BY p.product_name');
        mysqli_stmt_bind_param($stmt, 'i', $cid);
        mysqli_stmt_execute($stmt);
        $products = mysqli_stmt_get_result($stmt);
    } else {
        $products = mysqli_query($conn,
            'SELECT p.*, c.category_name
             FROM products p JOIN categories c ON p.category_id = c.category_id
             ORDER BY p.product_name');
    }
}

$heading    = $category ? $category['category_name'] : 'All Products';
$page_title = ($error !== '') ? 'Error' : $heading;
require 'includes/header.php';
?>

<?php if ($error !== '') { ?>

    <h1>Products</h1>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <a class="btn" href="<?php echo $base; ?>products.php">View all products</a>

<?php } else { ?>

    <div class="breadcrumb">
        <a href="<?php echo $base; ?>index.php">Home</a> &rsaquo; <?php echo htmlspecialchars($heading); ?>
    </div>
    <h1><?php echo htmlspecialchars($heading); ?></h1>
    <?php if ($category && $category['description'] !== '') { ?>
        <p class="subtitle"><?php echo htmlspecialchars($category['description']); ?></p>
    <?php } ?>

    <?php if (mysqli_num_rows($products) > 0) { ?>
        <p class="subtitle"><?php echo mysqli_num_rows($products); ?> product(s) found</p>
        <div class="product-grid">
            <?php while ($p = mysqli_fetch_assoc($products)) { product_card($p); } ?>
        </div>
    <?php } else { ?>
        <div class="empty-message"><strong>No products found.</strong><br>Try another category.</div>
    <?php } ?>

<?php } ?>

<?php require 'includes/footer.php'; ?>