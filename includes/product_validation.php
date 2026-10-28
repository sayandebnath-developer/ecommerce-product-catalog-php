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

// ---------------------------------------------------------------------
// Image upload helpers (FR-10) - used by product_add.php and product_edit.php
// ---------------------------------------------------------------------
const IMAGE_MAX_BYTES = 2 * 1024 * 1024;   // 2 MB
const IMAGE_TYPES     = [                  // allowed MIME type => file extension
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];
const NO_IMAGE = 'no-image.png';           // same default as the database column

// Checks the uploaded image. The image is optional.
// Returns [file extension or null, error message or '']
function check_image_upload($file) {
    // No file chosen (or the field is missing): nothing to check
    if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])
        || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, ''];
    }

    // Upload errors reported by PHP (file bigger than the limit in php.ini, partial upload, ...)
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'The image is too large. The maximum size is 2 MB.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'The image could not be uploaded. Please try again.'];
    }

    // The file must really come from an HTTP upload
    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'Invalid image upload.'];
    }

    // Our own size limit
    if ($file['size'] > IMAGE_MAX_BYTES) {
        return [null, 'The image is too large. The maximum size is 2 MB.'];
    }

    // MIME type read from the file CONTENT (the type sent by the browser can be faked)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    if (!isset(IMAGE_TYPES[$mime])) {
        return [null, 'Only JPG, PNG or WEBP images are allowed.'];
    }

    // Second check: the file must be a readable image
    if (@getimagesize($file['tmp_name']) === false) {
        return [null, 'The selected file is not a valid image.'];
    }

    return [IMAGE_TYPES[$mime], ''];
}

// Moves the checked upload into assets/images/products with a unique name.
// Returns the new file name, or null if saving failed.
function save_product_image($tmp_name, $ext) {
    $dir = __DIR__ . '/../assets/images/products/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return null;
    }

    $name = 'product_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

    if (!move_uploaded_file($tmp_name, $dir . $name)) {
        return null;
    }
    return $name;
}

// Deletes a product image file (used when a save fails; also needed by delete and edit)
function delete_product_image($file) {
    $name = basename((string)$file);
    if ($name === '' || $name === NO_IMAGE) {
        return;   // never delete the default image
    }
    $path = __DIR__ . '/../assets/images/products/' . $name;
    if (is_file($path)) {
        unlink($path);
    }
}