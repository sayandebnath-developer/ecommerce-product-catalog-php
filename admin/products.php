<?php
// admin/products.php - admin product list (FR-10)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Success messages sent back by the add, edit and delete pages
$message = '';
if (isset($_GET['added'])) {
    $message = 'Product added successfully.';
} elseif (isset($_GET['updated'])) {
    $message = 'Product updated successfully.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Product deleted successfully.';
}

// All products with their category name
$products = mysqli_query($conn,
    'SELECT p.product_id, p.product_name, p.price, p.image, p.stock_quantity, p.is_featured, c.category_name
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     ORDER BY p.product_id DESC');

$page_title = 'Manage Products';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Manage Products</h1>

<?php if ($message !== '') { ?>
    <div class="message success"><?php echo htmlspecialchars($message); ?></div>
<?php } ?>

<p><a class="btn" href="product_add.php">+ Add new product</a></p>

<?php if (mysqli_num_rows($products) > 0) { ?>
    <p class="subtitle"><?php echo mysqli_num_rows($products); ?> product(s) in the catalog</p>
    <div class="table-wrap">
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Featured</th>
                <th>Actions</th>
            </tr>
            <?php while ($p = mysqli_fetch_assoc($products)) { ?>
                <tr>
                    <td><?php echo (int)$p['product_id']; ?></td>
                    <td class="thumb"><?php echo product_image($p['image'], $p['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name']); ?></td>
                    <td>&#8377;<?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo stock_badge($p['stock_quantity']); ?></td>
                    <td><?php echo $p['is_featured'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <a href="product_edit.php?id=<?php echo (int)$p['product_id']; ?>">Edit</a> |
                        <a href="product_delete.php?id=<?php echo (int)$p['product_id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } else { ?>
    <div class="empty-message">No products yet. Click "Add new product" to add the first one.</div>
<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>