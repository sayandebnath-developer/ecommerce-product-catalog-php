<?php
// admin/login.php - administrator login (FR-08, FR-11)
session_start();
require_once __DIR__ . '/../config/db.php';

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // 1. Validation: empty fields
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // 2. Find the user (prepared statement)
        $stmt = mysqli_prepare($conn,
            'SELECT admin_id, username, password_hash FROM admin_users WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

        // 3. Check the password against the stored hash
        if ($admin && password_verify($password, $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id']       = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

$page_title = 'Admin Login';
require __DIR__ . '/../includes/header.php';
?>

<h1>Admin Login</h1>
<p class="subtitle">Only the administrator can manage categories and products.</p>

<?php if ($error !== '') { ?>
    <div class="message error"><?php echo htmlspecialchars($error); ?></div>
<?php } ?>

<form class="form-box" method="post" action="login.php">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" maxlength="50"
           value="<?php echo htmlspecialchars($username); ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" maxlength="100" required>

    <button type="submit" class="btn">Log in</button>
</form>

<?php require __DIR__ . '/../includes/footer.php'; ?>