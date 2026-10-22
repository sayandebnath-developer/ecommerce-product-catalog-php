<?php
// includes/header.php - shared header (navigation bar, search box, category menu)

require_once __DIR__ . '/../config/db.php';

// Folder name of the project inside htdocs (change here only if you rename the folder)
$base = '/ecommerce-product-catalog-php/';

if (!isset($page_title)) {
    $page_title = 'Home';
}

// Categories for the menu
$cat_result = mysqli_query($conn, 'SELECT category_id, category_name FROM categories ORDER BY category_name');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> | Product Catalog</title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-top">
        <a class="logo" href="<?php echo $base; ?>index.php">Product Catalog</a>

        <form class="search-form" action="<?php echo $base; ?>products.php" method="get">
            <input type="text" name="q" placeholder="Search products..."
                   value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <nav class="container main-nav">
        <a href="<?php echo $base; ?>index.php">Home</a>
        <a href="<?php echo $base; ?>products.php">All Products</a>
        <?php while ($cat = mysqli_fetch_assoc($cat_result)) { ?>
            <a href="<?php echo $base; ?>products.php?category=<?php echo (int)$cat['category_id']; ?>">
                <?php echo htmlspecialchars($cat['category_name']); ?>
            </a>
        <?php } ?>
        <a href="<?php echo $base; ?>admin/login.php">Admin</a>
    </nav>
</header>

<main class="container">