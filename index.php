<?php
$page_title = 'Home';
require 'includes/header.php';
require 'includes/functions.php';

// All categories with the number of products in each
$categories = mysqli_query($conn,
    'SELECT c.category_id, c.category_name, COUNT(p.product_id) AS total
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.category_id
     GROUP BY c.category_id, c.category_name
     ORDER BY c.category_name');

// Featured products with their category name
$featured = mysqli_query($conn,
    'SELECT p.*, c.category_name
     FROM products p
     JOIN categories c ON p.category_id = c.category_id
     WHERE p.is_featured = 1
     ORDER BY p.product_id DESC');
?>

<h1>Welcome to our Product Catalog</h1>
<p class="subtitle">Browse products by category, search for what you need, or pick from our featured products.</p>

<h2>Shop by Category</h2>
<div class="category-grid">
    <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
        <a class="category-card" href="<?php echo $base; ?>products.php?category=<?php echo (int)$c['category_id']; ?>">
            <?php echo htmlspecialchars($c['category_name']); ?>
            <br><small><?php echo (int)$c['total']; ?> products</small>
        </a>
    <?php } ?>
</div>

<h2>Featured Products</h2>
<?php if (mysqli_num_rows($featured) > 0) { ?>
    <div class="product-grid">
        <?php while ($p = mysqli_fetch_assoc($featured)) { product_card($p); } ?>
    </div>
<?php } else { ?>
    <div class="empty-message">No featured products yet.</div>
<?php } ?>

<?php require 'includes/footer.php'; ?>