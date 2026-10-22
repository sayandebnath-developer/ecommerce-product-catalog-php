<?php
require_once 'config/db.php';
require 'includes/functions.php';

$product = null;
$error   = '';

// 1. Read and validate the product ID from the address (GET)
if (!isset($_GET['id']) || !is_string($_GET['id']) || !ctype_digit($_GET['id']) || (int)$_GET['id'] < 1) {
    $error = 'Invalid product.';
} else {
    $id = (int)$_GET['id'];

    // 2. Get the product with its category name (prepared statement)
    $stmt = mysqli_prepare($conn,
        'SELECT p.*, c.category_name
         FROM products p JOIN categories c ON p.category_id = c.category_id
         WHERE p.product_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$product) {
        $error = 'Product not found.';
    }
}

if ($error !== '') {
    http_response_code(404);
}

$page_title = ($error !== '') ? 'Product not found' : $product['product_name'];
require 'includes/header.php';
?>

<?php if ($error !== '') { ?>

    <h1>Product</h1>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <a class="btn" href="<?php echo $base; ?>products.php">Browse all products</a>

<?php } else { ?>

    <div class="breadcrumb">
        <a href="<?php echo $base; ?>index.php">Home</a> &rsaquo;
        <a href="<?php echo $base; ?>products.php?category=<?php echo (int)$product['category_id']; ?>">
            <?php echo htmlspecialchars($product['category_name']); ?></a> &rsaquo;
        <?php echo htmlspecialchars($product['product_name']); ?>
    </div>

    <div class="product-detail">
        <div class="detail-image">
            <?php echo product_image($product['image'], $product['product_name']); ?>
        </div>

        <div>
            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
            <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
            <div class="price">&#8377;<?php echo number_format($product['price'], 2); ?></div>
            <p><?php echo stock_badge($product['stock_quantity']); ?></p>
            <p><?php echo nl2br(htmlspecialchars((string)$product['description'])); ?></p>
            <p class="subtitle">Product ID: <?php echo (int)$product['product_id']; ?></p>
            <a class="btn btn-secondary"
               href="<?php echo $base; ?>products.php?category=<?php echo (int)$product['category_id']; ?>">
               &laquo; Back to <?php echo htmlspecialchars($product['category_name']); ?></a>
        </div>
    </div>

<?php } ?>

<?php require 'includes/footer.php'; ?>