<?php
// admin/category_edit.php - edit a category (FR-09, FR-11)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$error    = '';
$category = null;

// 1. Read and check the category ID from the address
$id = $_GET['id'] ?? '';
if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    $error = 'Invalid category.';
} else {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, 'SELECT category_id, category_name, description FROM categories WHERE category_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $category = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$category) {
        $error = 'Category not found.';
    }
}

// Values shown in the form (the saved values, or what the admin typed)
$name        = $category ? $category['category_name'] : '';
$description = $category ? ($category['description'] ?? '') : '';
$form_error  = '';

// 2. Form submitted: validate and update
if ($category && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $form_error = 'Category name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $form_error = 'Category name must be between 2 and 100 characters.';
    } elseif (mb_strlen($description) > 255) {
        $form_error = 'Description must not be longer than 255 characters.';
    } else {
        // The name must be unique among the OTHER categories
        $stmt = mysqli_prepare($conn, 'SELECT category_id FROM categories WHERE category_name = ? AND category_id <> ?');
        mysqli_stmt_bind_param($stmt, 'si', $name, $id);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($exists) {
            $form_error = 'Another category with this name already exists.';
        } else {
            $stmt = mysqli_prepare($conn, 'UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?');
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $description, $id);
            mysqli_stmt_execute($stmt);

            header('Location: categories.php?updated=1');
            exit;
        }
    }
}

$page_title = 'Edit Category';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Edit Category</h1>

<?php if ($error !== '') { ?>

    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <a class="btn" href="categories.php">Back to categories</a>

<?php } else { ?>

    <?php if ($form_error !== '') { ?>
        <div class="message error"><?php echo htmlspecialchars($form_error); ?></div>
    <?php } ?>

    <form class="form-box" method="post" action="category_edit.php?id=<?php echo (int)$id; ?>">
        <label for="category_name">Category name *</label>
        <input type="text" id="category_name" name="category_name" maxlength="100"
               value="<?php echo htmlspecialchars($name); ?>">

        <label for="description">Description (optional)</label>
        <textarea id="description" name="description" rows="3" maxlength="255"><?php echo htmlspecialchars($description); ?></textarea>

        <button type="submit" class="btn">Save changes</button>
        <a class="btn btn-secondary" href="categories.php">Cancel</a>
    </form>

<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>