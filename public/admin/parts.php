
<?php
session_start();

// Allow administrators only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Database connection
require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->connect();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Retrieve all spare parts
$sql = "SELECT * FROM parts ORDER BY part_id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$parts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Spare Parts - AutoParts Store</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

<div class="admin-dashboard">

    <!-- Page heading -->
    <div class="admin-welcome">

        <h1>Manage Spare Parts</h1>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </p>

    </div>

    <!-- Navigation -->
    <div class="admin-actions">

        <a href="dashboard.php">Dashboard</a>

        <a href="add_part.php">Add New Part</a>

        <a href="../index.php">View Website</a>

        <a href="../logout.php">Logout</a>

    </div>

    <h2>All Spare Parts</h2>

    <!-- Success messages -->
    <?php if (isset($_GET['added'])): ?>

        <p class="success-message">
            Spare part added successfully!
        </p>

    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>

        <p class="success-message">
            Spare part updated successfully!
        </p>

    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>

        <p class="success-message">
            Spare part deleted successfully!
        </p>

    <?php endif; ?>

    <!-- Error message -->
    <?php if (isset($_GET['delete_error'])): ?>

        <p class="error-message">
            This part cannot be deleted because it may be connected
            to an existing order.
        </p>

    <?php endif; ?>

    <!-- Spare parts table -->
    <div class="table-container">

        <table class="admin-table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Part Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price (RWF)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

                <?php if (!empty($parts)): ?>

                    <?php foreach ($parts as $part): ?>

                        <tr>

                            <td>
                                <?php echo (int)$part['part_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($part['name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($part['category']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($part['brand']); ?>
                            </td>

                            <td>
                                <?php echo number_format((float)$part['price']); ?>
                            </td>

                            <td>
                                <?php echo (int)$part['stock']; ?>
                            </td>

                            <td>

                                <!-- Edit button -->
                                <a href="edit_part.php?id=<?php echo (int)$part['part_id']; ?>">
                                    Edit
                                </a>

                                <!-- Delete form -->
                                <form
                                    action="delete_part.php"
                                    method="POST"
                                    style="display:inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this spare part?');"
                                >

                                    <input
                                        type="hidden"
                                        name="part_id"
                                        value="<?php echo (int)$part['part_id']; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                                    >

                                    <button type="submit">
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">
                            No spare parts found.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>
