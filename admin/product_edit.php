<?php
// admin/product_edit.php - edit an existing product (FR-10, FR-11)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/product_validation.php';

$error     = '';
$errors    = [];
$product   = null;
$image_ext = null;

// 1. Read the product ID from the address (it stays in the address when the form is posted)
$id = $_GET['id'] ?? '';

if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    $error = 'Invalid product.';
} else {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, 'SELECT * FROM products WHERE product_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$product) {
        $error = 'Product not found.';
    }
}

// 2. Categories for the drop-down list
$categories = [];
$result = mysqli_query($conn, 'SELECT category_id, category_name FROM categories ORDER BY category_name');
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// 3. Pre-fill the form with the saved values
if ($product) {
    $data = [
        'name'        => $product['product_name'],
        'category_id' => $product['category_id'],
        'description' => $product['description'],
        'price'       => $product['price'],
        'stock'       => $product['stock_quantity'],
        'featured'    => (int)$product['is_featured'],
    ];
}

// 4. Form submitted: validate, then save
if ($product && $_SERVER['REQUEST_METHOD'] === 'POST') {

    // If the whole request was bigger than post_max_size, PHP empties $_POST and $_FILES
    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'The upload is too large. Please choose an image smaller than 2 MB.';
    } else {
        list($data, $errors) = validate_product($conn, $_POST);

        // Image check (a new image is optional)
        list($image_ext, $image_error) = check_image_upload($_FILES['image'] ?? null);
        if ($image_error !== '') {
            $errors[] = $image_error;
        }
    }

    // Everything is valid: save the new image file first (if one was chosen)
    $new_image = null;
    if (!$errors && $image_ext !== null) {
        $new_image = save_product_image($_FILES['image']['tmp_name'], $image_ext);
        if ($new_image === null) {
            $errors[] = 'The image could not be saved on the server.';
        }
    }

    // Then update the product
    if (!$errors) {
        $category_id = (int)$data['category_id'];
        $price       = number_format((float)$data['price'], 2, '.', '');
        $stock       = (int)$data['stock'];
        $image_file  = ($new_image !== null) ? $new_image : $product['image'];   // keep or replace

        try {
            $stmt = mysqli_prepare($conn,
                'UPDATE products
                 SET category_id = ?, product_name = ?, description = ?, price = ?,
                     image = ?, stock_quantity = ?, is_featured = ?
                 WHERE product_id = ?');
            mysqli_stmt_bind_param($stmt, 'issssiii',
                $category_id, $data['name'], $data['description'], $price,
                $image_file, $stock, $data['featured'], $id);
            mysqli_stmt_execute($stmt);

            // The product is saved: the old image file is no longer needed
            if ($new_image !== null) {
                delete_product_image($product['image']);
            }

            header('Location: products.php?updated=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            if ($new_image !== null) {
                delete_product_image($new_image);   // do not leave an unused file behind
            }
            $errors[] = 'The product could not be updated. Please try again.';
        }
    }
}

$page_title = 'Edit Product';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Edit Product</h1>

<?php if ($error !== '') { ?>

    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <a class="btn" href="products.php">Back to products</a>

<?php } else { ?>

    <?php if ($errors) { ?>
        <div class="message error">
            Please correct the following:
            <ul>
                <?php foreach ($errors as $e) { ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <style>
        .current-image img { width: 120px; height: 120px; object-fit: cover; border-radius: 4px; border: 1px solid #dde3ee; }
    </style>

    <form class="form-box" method="post" action="product_edit.php?id=<?php echo (int)$product['product_id']; ?>" enctype="multipart/form-data">
        <label for="product_name">Product name *</label>
        <input type="text" id="product_name" name="product_name" maxlength="150"
               value="<?php echo htmlspecialchars($data['name']); ?>">

        <label for="category_id">Category *</label>
        <select id="category_id" name="category_id">
            <option value="">-- Select a category --</option>
            <?php foreach ($categories as $c) { ?>
                <option value="<?php echo (int)$c['category_id']; ?>"<?php echo ((string)$c['category_id'] === (string)$data['category_id']) ? ' selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['category_name']); ?>
                </option>
            <?php } ?>
        </select>

        <label for="description">Description *</label>
        <textarea id="description" name="description" rows="4" maxlength="1000"><?php echo htmlspecialchars($data['description']); ?></textarea>

        <label for="price">Price (&#8377;) *</label>
        <input type="number" id="price" name="price" step="0.01" min="0.01"
               value="<?php echo htmlspecialchars($data['price']); ?>">

        <label for="stock_quantity">Stock quantity *</label>
        <input type="number" id="stock_quantity" name="stock_quantity" step="1" min="0"
               value="<?php echo htmlspecialchars($data['stock']); ?>">

        <label>Current image</label>
        <div class="current-image"><?php echo product_image($product['image'], $product['product_name']); ?></div>

        <label for="image">Replace image (optional)</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <small class="subtitle">JPG, PNG or WEBP, maximum 2 MB. Leave empty to keep the current image.</small>

        <label class="check-label">
            <input type="checkbox" name="is_featured" value="1"<?php echo $data['featured'] ? ' checked' : ''; ?>>
            Show as a featured product on the home page
        </label>

        <button type="submit" class="btn">Save changes</button>
        <a class="btn btn-secondary" href="products.php">Cancel</a>
    </form>

<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>