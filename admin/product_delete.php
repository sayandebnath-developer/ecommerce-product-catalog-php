<?php
// admin/product_delete.php - delete a product after confirmation (FR-10)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/product_validation.php';

$error   = '';
$product = null;

// 1. Read the product ID: from the address (confirmation page) or from the form (delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
} else {
    $id = $_GET['id'] ?? '';
}

if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    $error = 'Invalid product.';
} else {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, 'SELECT product_id, product_name, image FROM products WHERE product_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$product) {
        $error = 'Product not found.';
    }
}

// 2. Delete only when the confirmation form was submitted
if ($product && $_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Delete the database row first, then the image file
        $stmt = mysqli_prepare($conn, 'DELETE FROM products WHERE product_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        delete_product_image($product['image']);

        header('Location: products.php?deleted=1');
        exit;
    } catch (mysqli_sql_exception $e) {
        $error = 'The product could not be deleted. Please try again.';
    }
}

$page_title = 'Delete Product';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Delete Product</h1>

<?php if ($error !== '') { ?>

    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <a class="btn" href="products.php">Back to products</a>

<?php } else { ?>

    <div class="message error">
        Are you sure you want to delete the product
        <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>?
        Its image will also be removed. This action cannot be undone.
    </div>

    <form method="post" action="product_delete.php">
        <input type="hidden" name="id" value="<?php echo (int)$product['product_id']; ?>">
        <button type="submit" class="btn btn-danger">Yes, delete</button>
        <a class="btn btn-secondary" href="products.php">Cancel</a>
    </form>

<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>