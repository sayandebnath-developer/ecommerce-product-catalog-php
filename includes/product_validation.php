<?php
// includes/product_validation.php - server-side validation shared by add and edit product (FR-11)

// Checks the product form data. Returns [cleaned values, list of error messages].
function validate_product($conn, $input) {
    $errors = [];

    // Read a text field safely (an array or a missing field becomes an empty string)
    $text = function ($key) use ($input) {
        return (isset($input[$key]) && is_string($input[$key])) ? trim($input[$key]) : '';
    };

    $data = [
        'name'        => $text('product_name'),
        'category_id' => $text('category_id'),
        'description' => $text('description'),
        'price'       => $text('price'),
        'stock'       => $text('stock_quantity'),
        'featured'    => isset($input['is_featured']) ? 1 : 0,
    ];

    // Product name: required, 2 to 150 characters
    if ($data['name'] === '') {
        $errors[] = 'Product name is required.';
    } elseif (mb_strlen($data['name']) < 2 || mb_strlen($data['name']) > 150) {
        $errors[] = 'Product name must be between 2 and 150 characters.';
    }

    // Category: required and must exist
    if ($data['category_id'] === '') {
        $errors[] = 'Please select a category.';
    } elseif (!ctype_digit($data['category_id']) || (int)$data['category_id'] < 1) {
        $errors[] = 'Invalid category.';
    } else {
        $cid  = (int)$data['category_id'];
        $stmt = mysqli_prepare($conn, 'SELECT category_id FROM categories WHERE category_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $cid);
        mysqli_stmt_execute($stmt);
        if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) {
            $errors[] = 'The selected category does not exist.';
        }
    }

    // Description: required, up to 1000 characters
    if ($data['description'] === '') {
        $errors[] = 'Description is required.';
    } elseif (mb_strlen($data['description']) > 1000) {
        $errors[] = 'Description must not be longer than 1000 characters.';
    }

    // Price: a number above 0 with at most 2 decimal places
    if ($data['price'] === '') {
        $errors[] = 'Price is required.';
    } elseif (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $data['price'])) {
        $errors[] = 'Price must be a number such as 499 or 499.50.';
    } elseif ((float)$data['price'] <= 0) {
        $errors[] = 'Price must be greater than 0.';
    }

    // Stock: a whole number, 0 or more
    if ($data['stock'] === '') {
        $errors[] = 'Stock quantity is required.';
    } elseif (!preg_match('/^\d{1,6}$/', $data['stock'])) {
        $errors[] = 'Stock quantity must be a whole number (0 or more).';
    }

    return [$data, $errors];
}