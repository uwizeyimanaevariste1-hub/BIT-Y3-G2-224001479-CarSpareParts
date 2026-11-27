<?php
session_start();

// Customers must log in before adding products
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

// Accept POST requests only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: parts.php");
    exit;
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Invalid form submission. Please try again.");
}

// Validate product ID
$part_id = filter_input(INPUT_POST, 'part_id', FILTER_VALIDATE_INT);

if (!$part_id || $part_id < 1) {
    header("Location: parts.php");
    exit;
}

// Database connection
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

// Find the selected spare part
$stmt = $conn->prepare(
    "SELECT part_id, name, stock FROM parts WHERE part_id = :id"
);

$stmt->execute([':id' => $part_id]);

$part = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$part || (int)$part['stock'] < 1) {
    header("Location: parts.php?unavailable=1");
    exit;
}

// Create cart if it does not exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check existing quantity
$current_quantity = $_SESSION['cart'][$part_id] ?? 0;

// Do not allow quantity to exceed available stock
if ($current_quantity >= (int)$part['stock']) {
    header("Location: cart.php?stock_limit=1");
    exit;
}

// Add one item
$_SESSION['cart'][$part_id] = $current_quantity + 1;

header("Location: cart.php?added=1");
exit;