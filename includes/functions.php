<?php
// includes/functions.php - helper functions used by several pages

// Stock status label (FR-04)
function stock_badge($qty) {
    $qty = (int)$qty;
    if ($qty > 10) {
        return '<span class="badge in-stock">In stock</span>';
    }
    if ($qty > 0) {
        return '<span class="badge low-stock">Only ' . $qty . ' left</span>';
    }
    return '<span class="badge out-of-stock">Out of stock</span>';
}

// Product image, or the text "No image" if the file does not exist
function product_image($file, $alt) {
    global $base;
    $name = basename((string)$file);
    $path = __DIR__ . '/../assets/images/products/' . $name;

    if ($name !== '' && is_file($path)) {
        return '<img src="' . $base . 'assets/images/products/' . rawurlencode($name)
             . '" alt="' . htmlspecialchars($alt) . '">';
    }
    return 'No image';
}

// One product card (used on the home page and the product listing)
function product_card($p) {
    global $base;
    $id   = (int)$p['product_id'];
    $link = $base . 'product.php?id=' . $id;

    echo '<div class="product-card">';
    echo '<a href="' . $link . '"><div class="product-image">'
         . product_image($p['image'], $p['product_name']) . '</div></a>';
    echo '<div class="product-info">';
    echo '<div class="product-category">' . htmlspecialchars($p['category_name']) . '</div>';
    echo '<h3 class="product-name"><a href="' . $link . '">'
         . htmlspecialchars($p['product_name']) . '</a></h3>';
    echo '<div class="price">&#8377;' . number_format($p['price'], 2) . '</div>';
    echo stock_badge($p['stock_quantity']);
    echo '</div></div>';
}