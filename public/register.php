<?php

session_start();

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

$errors = [];

$username = '';
$email = '';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission. Please try again.";
    }

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate username
    if (strlen($username) < 3) {
        $errors[] = "Username must contain at least 3 characters.";
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Validate password
    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    // Confirm password
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check duplicate email
    if (empty($errors)) {

        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE email = :email"
        );

        $check->execute(['email' => $email]);

        if ($check->fetch()) {
            $errors[] = "This email address is already registered.";
        }
    }

    // Register customer
    if (empty($errors)) {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users
                (username, email, password_hash, role)
                VALUES (:username, :email, :password_hash, 'customer')";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password_hash' => $password_hash
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: login.php?registered=1");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register | AutoParts</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <h2>Create Your Account</h2>

        <p>Join AutoParts and discover quality spare parts.</p>

        <?php foreach ($errors as $error): ?>

            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endforeach; ?>

        <form method="POST" action="register.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"
            >

            <label>Username</label>

            <input
                type="text"
                name="username"
                placeholder="Enter username"
                value="<?= htmlspecialchars($username) ?>"
                required
            >

            <label>Email Address</label>

            <input
                type="email"
                name="email"
                placeholder="Enter email"
                value="<?= htmlspecialchars($email) ?>"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Minimum 8 characters"
                required
            >

            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm your password"
                required
            >

            <button type="submit" class="auth-btn">
                Create Account
            </button>

        </form>

        <p class="auth-link">
            Already have an account?
            <a href="login.php">Login here</a>
        </p>

        <p class="auth-link">
            <a href="index.php">Back to Home</a>
        </p>

    </div>

</div>

</body>
</html>