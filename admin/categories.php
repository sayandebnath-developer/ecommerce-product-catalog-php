<?php
// admin/categories.php - category list and add category (FR-09, FR-11)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

$error       = '';
$name        = '';
$description = '';

// Success messages sent back by this page and the edit/delete pages
$message = '';
if (isset($_GET['added'])) {
    $message = 'Category added successfully.';
} elseif (isset($_GET['updated'])) {
    $message = 'Category updated successfully.';
} elseif (isset($_GET['deleted'])) {
    $message = 'Category deleted successfully.';
}

// ---------- Add category (form submitted) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    // 1. Server-side validation (FR-11)
    if ($name === '') {
        $error = 'Category name is required.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $error = 'Category name must be between 2 and 100 characters.';
    } elseif (mb_strlen($description) > 255) {
        $error = 'Description must not be longer than 255 characters.';
    } else {
        // 2. The name must be unique
        $stmt = mysqli_prepare($conn, 'SELECT category_id FROM categories WHERE category_name = ?');
        mysqli_stmt_bind_param($stmt, 's', $name);
        mysqli_stmt_execute($stmt);
        $exists = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        if ($exists) {
            $error = 'A category with this name already exists.';
        } else {
            // 3. Insert (prepared statement)
            $stmt = mysqli_prepare($conn, 'INSERT INTO categories (category_name, description) VALUES (?, ?)');
            mysqli_stmt_bind_param($stmt, 'ss', $name, $description);
            mysqli_stmt_execute($stmt);

            // Redirect so that refreshing the page does not add the category again
            header('Location: categories.php?added=1');
            exit;
        }
    }
}

// ---------- Category list with the number of products in each ----------
$categories = mysqli_query($conn,
    'SELECT c.category_id, c.category_name, c.description, COUNT(p.product_id) AS total
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.category_id
     GROUP BY c.category_id, c.category_name, c.description
     ORDER BY c.category_name');

$page_title = 'Manage Categories';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/menu.php';
?>

<h1>Manage Categories</h1>

<?php if ($message !== '') { ?>
    <div class="message success"><?php echo htmlspecialchars($message); ?></div>
<?php } ?>

<h2>Add a new category</h2>
<?php if ($error !== '') { ?>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<form class="form-box" method="post" action="categories.php">
    <label for="category_name">Category name *</label>
    <input type="text" id="category_name" name="category_name" maxlength="100"
           value="<?php echo htmlspecialchars($name); ?>">

    <label for="description">Description (optional)</label>
    <textarea id="description" name="description" rows="3" maxlength="255"><?php echo htmlspecialchars($description); ?></textarea>

    <button type="submit" class="btn">Add category</button>
</form>

<h2>All categories</h2>
<?php if (mysqli_num_rows($categories) > 0) { ?>
    <div class="table-wrap">
        <table class="data-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
            <?php while ($c = mysqli_fetch_assoc($categories)) { ?>
                <tr>
                    <td><?php echo (int)$c['category_id']; ?></td>
                    <td><?php echo htmlspecialchars($c['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($c['description'] ?? ''); ?></td>
                    <td><?php echo (int)$c['total']; ?></td>
                    <td>
                        <a href="category_edit.php?id=<?php echo (int)$c['category_id']; ?>">Edit</a> |
                        <a href="category_delete.php?id=<?php echo (int)$c['category_id']; ?>">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
<?php } else { ?>
    <div class="empty-message">No categories yet. Add the first one using the form above.</div>
<?php } ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>