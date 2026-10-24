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

// 3. Read the sort option (FR-05) - only values from this whitelist are allowed
$sort_options = [
    'name_asc'   => ['Name: A to Z',         'p.product_name ASC'],
    'name_desc'  => ['Name: Z to A',         'p.product_name DESC'],
    'price_asc'  => ['Price: Low to High',   'p.price ASC, p.product_name ASC'],
    'price_desc' => ['Price: High to Low',   'p.price DESC, p.product_name ASC']
];
$sort = 'name_asc';   // default
if (isset($_GET['sort']) && is_string($_GET['sort']) && isset($sort_options[$_GET['sort']])) {
    $sort = $_GET['sort'];
}

// 4. Get the products (category filter and search keyword can be used together)
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

        $where_sql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

    // Count ALL matching products (needed for the page numbers)
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM products p' . $where_sql);
    if ($params) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $total_rows = (int)mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];

    // Pagination (FR-06)
    $per_page    = 6;                                           // products on one page
    $total_pages = max(1, (int)ceil($total_rows / $per_page));
    $page = 1;
    if (isset($_GET['page']) && is_string($_GET['page']) && ctype_digit($_GET['page']) && (int)$_GET['page'] >= 1) {
        $page = (int)$_GET['page'];
    }
    if ($page > $total_pages) {
        $page = $total_pages;                                   // a page number that is too big
    }
    $offset = ($page - 1) * $per_page;

    // Get only the products of the current page
    $sql = 'SELECT p.*, c.category_name
            FROM products p JOIN categories c ON p.category_id = c.category_id'
         . $where_sql
         . ' ORDER BY ' . $sort_options[$sort][1]
         . ' LIMIT ? OFFSET ?';

    $page_types  = $types . 'ii';
    $page_params = array_merge($params, [$per_page, $offset]);

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, $page_types, ...$page_params);
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

        <?php $total = $total_rows; ?>

    <?php if ($q !== '') { ?>
        <p class="subtitle">Search results for &ldquo;<strong><?php echo htmlspecialchars($q); ?></strong>&rdquo;
            &ndash; <a href="<?php echo $base; ?>products.php<?php echo $category ? '?category=' . (int)$cid : ''; ?>">Clear search</a></p>
    <?php } ?>

    <?php if ($total > 0) { ?>
                <div class="toolbar">
            <span class="subtitle"><?php echo $total; ?> product(s) found &ndash; page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <form method="get" action="<?php echo $base; ?>products.php">
                <?php if ($category) { ?>
                    <input type="hidden" name="category" value="<?php echo (int)$cid; ?>">
                <?php } ?>
                <?php if ($q !== '') { ?>
                    <input type="hidden" name="q" value="<?php echo htmlspecialchars($q); ?>">
                <?php } ?>
                <label for="sort">Sort by</label>
                <select name="sort" id="sort" onchange="this.form.submit()">
                    <?php foreach ($sort_options as $key => $opt) { ?>
                        <option value="<?php echo $key; ?>"<?php echo ($sort === $key) ? ' selected' : ''; ?>><?php echo $opt[0]; ?></option>
                    <?php } ?>
                </select>
                <noscript><button type="submit" class="btn">Sort</button></noscript>
            </form>
        </div>
        <div class="product-grid">
                        <?php while ($p = mysqli_fetch_assoc($products)) { product_card($p); } ?>
        </div>

        <?php if ($total_pages > 1) {
            // Keep the category, search keyword and sort in every page link
            $link_params = [];
            if ($category)            { $link_params['category'] = $cid; }
            if ($q !== '')            { $link_params['q'] = $q; }
            if ($sort !== 'name_asc') { $link_params['sort'] = $sort; }
            $link_start = $base . 'products.php?' . http_build_query($link_params);
            if ($link_params) { $link_start .= '&'; }
        ?>
            <div class="pagination">
                <?php if ($page > 1) { ?>
                    <a href="<?php echo htmlspecialchars($link_start . 'page=' . ($page - 1)); ?>">&laquo; Previous</a>
                <?php } ?>

                <?php for ($i = 1; $i <= $total_pages; $i++) { ?>
                    <?php if ($i === $page) { ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php } else { ?>
                        <a href="<?php echo htmlspecialchars($link_start . 'page=' . $i); ?>"><?php echo $i; ?></a>
                    <?php } ?>
                <?php } ?>

                <?php if ($page < $total_pages) { ?>
                    <a href="<?php echo htmlspecialchars($link_start . 'page=' . ($page + 1)); ?>">Next &raquo;</a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="empty-message">
            <strong>No products found.</strong><br>
            <?php if ($q !== '') { ?>
                No product matches your search. Try a different keyword.
            <?php } else { ?>
                There are no products in this category yet.
            <?php } ?>
        </div>
        <?php } ?>

<?php } ?>

<?php require 'includes/footer.php'; ?>