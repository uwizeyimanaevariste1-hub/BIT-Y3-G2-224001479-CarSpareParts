<?php

session_start();

// Protect the dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Customers only
if ($_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

// Count available spare parts
$stmt = $conn->prepare("SELECT COUNT(*) FROM parts WHERE stock > 0");
$stmt->execute();

$available_parts = $stmt->fetchColumn();

// Count customer's orders
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM orders WHERE customer_id = :customer_id"
);

$stmt->execute([
    'customer_id' => $_SESSION['user_id']
]);

$total_orders = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Customer Dashboard | AutoParts</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<header>

    <nav class="navbar">

        <div class="logo">
            Auto<span>Parts</span>
        </div>

        <ul class="nav-links">

            <li><a href="dashboard.php">Dashboard</a></li>

            <li><a href="parts.php">Spare Parts</a></li>

            <li><a href="index.php">Home</a></li>

        </ul>

        <div class="nav-buttons">

            <a href="logout.php" class="register-btn">Logout</a>

        </div>

    </nav>

</header>


<section class="dashboard-container">

    <h1>
        Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
    </h1>

    <p>Welcome to your AutoParts customer dashboard.</p>


    <div class="dashboard-cards">

        <div class="dashboard-card">

            <h3>Available Spare Parts</h3>

            <h2><?= (int)$available_parts ?></h2>

            <a href="parts.php">Explore Parts</a>

        </div>


        <div class="dashboard-card">

            <h3>My Orders</h3>

            <h2><?= (int)$total_orders ?></h2>

            <p>Your total orders</p>

        </div>

    </div>


    <div class="dashboard-actions">

        <h2>Quick Actions</h2>

        <a href="parts.php" class="shop-btn">
            Browse Spare Parts
        </a>

    </div>

</section>


<footer>

    <h3>AutoParts Store</h3>

    <p>Your trusted destination for car spare parts.</p>

    <p>&copy; 2026 AutoParts. All Rights Reserved.</p>

</footer>

</body>

</html>