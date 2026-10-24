<?php
// admin/category_delete.php - delete a category after confirmation (FR-09)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$error    = '';
$category = null;
$total    = 0;

// 1. Read the category ID: from the address (confirmation page) or from the form (delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
} else {
    $id = $_GET['id'] ?? '';
}

if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    $error = 'Invalid category.';
} else {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, 'SELECT category_id, category_name FROM categories WHERE category_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $category = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if (!$category) {
        $error = 'Category not found.';
    } else {
        // 2. How many products still use this category?
        $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) FROM products WHERE category_id = ?');
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $total = (int)mysqli_fetch_row(mysqli_stmt_get_result($stmt))[0];
    }
}

// 3. Delete only when the confirmation form was submitted AND no products use the category
if ($category && $total === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = mysqli_prepare($conn, 'DELETE FROM categories WHERE category_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);

    header('Location: categories.php?deleted=1');
    exit;
}

$page_title = 'Delete Category';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Delete Category</h1>

<?php if ($error !== '') { ?>

    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
    <a class="btn" href="categories.php">Back to categories</a>

<?php } elseif ($total > 0) { ?>

    <div class="message error">
        The category <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
        cannot be deleted because it still contains <?php echo $total; ?> product(s).
        Move or delete those products first.
    </div>
    <a class="btn" href="categories.php">Back to categories</a>

<?php } else { ?>

    <div class="message error">
        Are you sure you want to delete the category
        <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>?
        This action cannot be undone.
    </div>

    <form method="post" action="category_delete.php">
        <input type="hidden" name="id" value="<?php echo (int)$category['category_id']; ?>">
        <button type="submit" class="btn btn-danger">Yes, delete</button>
        <a class="btn btn-secondary" href="categories.php">Cancel</a>
    </form>

<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>