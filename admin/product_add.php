<?php
// admin/product_add.php - add a new product (FR-10, FR-11)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/product_validation.php';

$errors = [];
$data   = ['name' => '', 'category_id' => '', 'description' => '', 'price' => '', 'stock' => '0', 'featured' => 0];

// Categories for the drop-down list
$categories = [];
$result = mysqli_query($conn, 'SELECT category_id, category_name FROM categories ORDER BY category_name');
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// Form submitted: validate, then save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // If the whole request was bigger than post_max_size, PHP empties $_POST and $_FILES
    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errors[] = 'The upload is too large. Please choose an image smaller than 2 MB.';
    } else {
        list($data, $errors) = validate_product($conn, $_POST);

        // Image check (the image is optional)
        list($image_ext, $image_error) = check_image_upload($_FILES['image'] ?? null);
        if ($image_error !== '') {
            $errors[] = $image_error;
        }
    }

    // Everything is valid: save the image file first
    $image_file = NO_IMAGE;
    if (!$errors && $image_ext !== null) {
        $saved = save_product_image($_FILES['image']['tmp_name'], $image_ext);
        if ($saved === null) {
            $errors[] = 'The image could not be saved on the server.';
        } else {
            $image_file = $saved;
        }
    }

    // Then save the product
    if (!$errors) {
        $category_id = (int)$data['category_id'];
        $price       = number_format((float)$data['price'], 2, '.', '');
        $stock       = (int)$data['stock'];

        try {
            $stmt = mysqli_prepare($conn,
                'INSERT INTO products (category_id, product_name, description, price, image, stock_quantity, is_featured)
                 VALUES (?, ?, ?, ?, ?, ?, ?)');
            mysqli_stmt_bind_param($stmt, 'issssii',
                $category_id, $data['name'], $data['description'], $price, $image_file, $stock, $data['featured']);
            mysqli_stmt_execute($stmt);

            header('Location: products.php?added=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            delete_product_image($image_file);   // do not leave an unused file behind
            $errors[] = 'The product could not be saved. Please try again.';
        }
    }
}

$page_title = 'Add Product';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Add New Product</h1>

<?php if (!$categories) { ?>

    <div class="message error">You need at least one category before you can add a product.</div>
    <a class="btn" href="categories.php">Add a category</a>

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

    <form class="form-box" method="post" action="product_add.php" enctype="multipart/form-data">
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

        <label for="image">Product image (optional)</label>
        <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
        <small class="subtitle">JPG, PNG or WEBP, maximum 2 MB. If the form shows an error, choose the image again (browsers do not keep file selections).</small>

        <label class="check-label">
            <input type="checkbox" name="is_featured" value="1"<?php echo $data['featured'] ? ' checked' : ''; ?>>
            Show as a featured product on the home page
        </label>

        <button type="submit" class="btn">Add product</button>
        <a class="btn btn-secondary" href="products.php">Cancel</a>
    </form>

<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>