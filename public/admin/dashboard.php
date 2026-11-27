<?php

session_start();

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Check administrator role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../dashboard.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->connect();

// Count registered users
$stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$stmt->execute();

$total_users = $stmt->fetchColumn();

// Count spare parts
$stmt = $conn->prepare("SELECT COUNT(*) FROM parts");
$stmt->execute();

$total_parts = $stmt->fetchColumn();

// Count orders
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders");
$stmt->execute();

$total_orders = $stmt->fetchColumn();

// Calculate total stock
$stmt = $conn->prepare("SELECT COALESCE(SUM(stock), 0) FROM parts");
$stmt->execute();

$total_stock = $stmt->fetchColumn();

// Count pending orders
$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM orders WHERE status = 'pending'"
);

$stmt->execute();

$pending_orders = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | AutoParts</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<header>

    <nav class="navbar">

        <div class="logo">
            Auto<span>Parts</span>
        </div>

        <ul class="nav-links">

            <li><a href="dashboard.php">Dashboard</a></li>

            <li><a href="parts.php">Manage Parts</a></li>

            <li><a href="orders.php">Manage Orders</a></li>

            <li><a href="../index.php">View Store</a></li>

        </ul>

        <div class="nav-buttons">

            <a href="../logout.php" class="register-btn">
                Logout
            </a>

        </div>

    </nav>

</header>


<section class="admin-dashboard">

    <div class="admin-welcome">

        <h1>
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!
        </h1>

        <p>Administrator Control Panel</p>

    </div>


    <h2>Store Overview</h2>


    <div class="admin-cards">

        <div class="admin-card">

            <h3>Total Customers & Admins</h3>

            <h1><?= (int)$total_users ?></h1>

        </div>


        <div class="admin-card">

            <h3>Total Spare Parts</h3>

            <h1><?= (int)$total_parts ?></h1>

        </div>


        <div class="admin-card">

            <h3>Total Orders</h3>

            <h1><?= (int)$total_orders ?></h1>

        </div>


        <div class="admin-card">

            <h3>Total Stock</h3>

            <h1><?= (int)$total_stock ?></h1>

        </div>


        <div class="admin-card">

            <h3>Pending Orders</h3>

            <h1><?= (int)$pending_orders ?></h1>

        </div>

    </div>


    <div class="admin-actions">

        <h2>Management Activities</h2>

        <a href="parts.php" class="shop-btn">
            Manage Spare Parts
        </a>
        <a href="orders.php">Manage Orders</a>

        <a href="orders.php" class="shop-btn">
            Manage Orders
        </a>
        <a href="order_history.php">My Order History</a>

        <a href="../parts.php" class="shop-btn">
            View Products
        </a>

    </div>

</section>


<footer>

    <h3>AutoParts Store</h3>

    <p>Administrator Control Panel</p>

    <p>&copy; 2026 AutoParts. All Rights Reserved.</p>

</footer>

</body>

</html>