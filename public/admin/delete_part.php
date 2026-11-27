<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: parts.php");
    exit;
}

// Verify CSRF token
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    die("Invalid form submission. Please go back and try again.");
}

$part_id = filter_input(INPUT_POST, 'part_id', FILTER_VALIDATE_INT);

if (!$part_id || $part_id < 1) {
    header("Location: parts.php");
    exit;
}

$database = new Database();
$conn = $database->connect();

try {

    $stmt = $conn->prepare("DELETE FROM parts WHERE part_id = :id");

    $stmt->execute([':id' => $part_id]);

    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    header("Location: parts.php?deleted=1");
    exit;

} catch (PDOException $e) {

    // A part may be referenced by an existing order
    header("Location: parts.php?delete_error=1");
    exit;
}