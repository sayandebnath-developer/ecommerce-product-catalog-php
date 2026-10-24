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

// 2. Read the search keyword (FR-03)
$q = '';
if (isset($_GET['q']) && is_string($_GET['q'])) {
    $q = trim($_GET['q']);
    if (mb_strlen($q) > 100) {
        $q = mb_substr($q, 0, 100);   // keep the keyword to a sensible length
    }
}

// 3. Get the products (category filter and search keyword can be used together)
if ($error === '') {
    $where  = [];   // conditions
    $types  = '';   // parameter types for bind_param
    $params = [];   // parameter values

    if ($category) {
        $where[]  = 'p.category_id = ?';
        $types   .= 'i';
        $params[] = $cid;
    }
    if ($q !== '') {
        $where[]  = '(p.product_name LIKE ? OR p.description LIKE ?)';
        $like     = '%' . addcslashes($q, '%_\\') . '%';   // % and _ typed by the user are searched as normal characters
        $types   .= 'ss';
        $params[] = $like;
        $params[] = $like;
    }

    $sql = 'SELECT p.*, c.category_name
            FROM products p JOIN categories c ON p.category_id = c.category_id';
    if ($where) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }
    $sql .= ' ORDER BY p.product_name';

    $stmt = mysqli_prepare($conn, $sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $products = mysqli_stmt_get_result($stmt);
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