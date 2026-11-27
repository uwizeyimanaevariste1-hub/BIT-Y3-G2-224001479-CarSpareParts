<?php

session_start();

require_once __DIR__ . '/../config/database.php';

// Redirect users who are already logged in
if (isset($_SESSION['user_id'])) {

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    }

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
        header("Location: dashboard.php");
        exit;
    }
}

$database = new Database();
$conn = $database->connect();

$errors = [];
$email = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission. Please try again.";
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if ($password === '') {
        $errors[] = "Password is required.";
    }

    // Authenticate customer
    if (empty($errors)) {

        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {

            // Prevent session fixation
            session_regenerate_id(true);

            // Store user information in session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Generate a new CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Redirect according to role
            if ($user['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: dashboard.php");
            }

            exit;

        } else {

            $errors[] = "Incorrect email or password.";

        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | AutoParts</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <h2>Welcome Back!</h2>

        <p>Login to your AutoParts account.</p>

        <?php if (isset($_GET['registered'])): ?>

            <div class="success-message">
                Registration successful! You can now log in.
            </div>

        <?php endif; ?>

        <?php foreach ($errors as $error): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endforeach; ?>

        <form method="POST" action="login.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                value="<?= htmlspecialchars($email) ?>"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit" class="auth-btn">
                Login
            </button>

        </form>

        <p class="auth-link">
            Don't have an account?
            <a href="register.php">Register here</a>
        </p>

        <p class="auth-link">
            <a href="index.php">Back to Home</a>
        </p>

    </div>

</div>

</body>

</html>
