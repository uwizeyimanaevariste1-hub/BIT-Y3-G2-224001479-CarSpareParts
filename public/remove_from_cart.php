<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit;
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Invalid form submission.");
}

$part_id = filter_input(INPUT_POST, 'part_id', FILTER_VALIDATE_INT);

if ($part_id && isset($_SESSION['cart'][$part_id])) {
    unset($_SESSION['cart'][$part_id]);
}

header("Location: cart.php");
exit;