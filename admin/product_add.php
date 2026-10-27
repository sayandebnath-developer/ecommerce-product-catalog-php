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
    list($data, $errors) = validate_product($conn, $_POST);

    if (!$errors) {
        $category_id = (int)$data['category_id'];
        $price       = number_format((float)$data['price'], 2, '.', '');
        $stock       = (int)$data['stock'];

        $stmt = mysqli_prepare($conn,
            'INSERT INTO products (category_id, product_name, description, price, stock_quantity, is_featured)
             VALUES (?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($stmt, 'isssii',
            $category_id, $data['name'], $data['description'], $price, $stock, $data['featured']);
        mysqli_stmt_execute($stmt);

        header('Location: products.php?added=1');
        exit;
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

    <form class="form-box" method="post" action="product_add.php">
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

        <label class="check-label">
            <input type="checkbox" name="is_featured" value="1"<?php echo $data['featured'] ? ' checked' : ''; ?>>
            Show as a featured product on the home page
        </label>

        <button type="submit" class="btn">Add product</button>
        <a class="btn btn-secondary" href="products.php">Cancel</a>
    </form>

<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>