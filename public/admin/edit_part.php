<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->connect();

// Get the part ID from the URL
$part_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$part_id || $part_id < 1) {
    header("Location: parts.php");
    exit;
}

// Get the existing part
$stmt = $conn->prepare("SELECT * FROM parts WHERE part_id = :id");
$stmt->execute([':id' => $part_id]);

$part = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$part) {
    header("Location: parts.php");
    exit;
}

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

$name = $part['name'];
$category = $part['category'];
$brand = $part['brand'];
$price = $part['price'];
$stock = $part['stock'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission.";
    }

    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $brand = trim($_POST['brand'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    // Validate inputs
    if ($name === '') {
        $errors[] = "Part name is required.";
    }

    if ($category === '') {
        $errors[] = "Category is required.";
    }

    if ($brand === '') {
        $errors[] = "Brand is required.";
    }

    if ($price === '' || !is_numeric($price) || (float)$price < 0) {
        $errors[] = "Enter a valid price.";
    }

    if (
        $stock === '' ||
        filter_var($stock, FILTER_VALIDATE_INT) === false ||
        (int)$stock < 0
    ) {
        $errors[] = "Stock must be a whole number of zero or more.";
    }

    // Update the part
    if (empty($errors)) {

        $sql = "UPDATE parts
                SET name = :name,
                    category = :category,
                    brand = :brand,
                    price = :price,
                    stock = :stock
                WHERE part_id = :id";

        $stmt = $conn->prepare($sql);

        $stmt->execute([
            ':name' => $name,
            ':category' => $category,
            ':brand' => $brand,
            ':price' => $price,
            ':stock' => $stock,
            ':id' => $part_id
        ]);

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        header("Location: parts.php?updated=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Spare Part</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <h1>Edit Spare Part</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
            >

            <label>Part Name</label>
            <input type="text" name="name"
                value="<?php echo htmlspecialchars($name); ?>" required>

            <label>Category</label>
            <input type="text" name="category"
                value="<?php echo htmlspecialchars($category); ?>" required>

            <label>Brand</label>
            <input type="text" name="brand"
                value="<?php echo htmlspecialchars($brand); ?>" required>

            <label>Price (RWF)</label>
            <input type="number" name="price"
                value="<?php echo htmlspecialchars((string)$price); ?>"
                min="0" step="0.01" required>

            <label>Stock</label>
            <input type="number" name="stock"
                value="<?php echo htmlspecialchars((string)$stock); ?>"
                min="0" step="1" required>

            <button type="submit">Update Spare Part</button>

        </form>

        <p>
            <a href="parts.php">Back to Spare Parts</a>
        </p>

    </div>

</div>

</body>
</html>