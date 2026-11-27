<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->connect();

// Get all spare parts
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
    <title>Manage Spare Parts - Admin</title>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="admin-dashboard">

    <div class="admin-welcome">
        <h1>Manage Spare Parts</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>.</p>
    </div>

    <div class="admin-actions">
        <a href="dashboard.php">Dashboard</a>
        <a href="add_part.php">Add New Part</a>
        <a href="../index.php">View Website</a>
        <a href="../logout.php">Logout</a>
    </div>

    <h2>All Spare Parts</h2>

    <?php if (isset($_GET['added'])): ?>
        <p class="success-message">Spare part added successfully!</p>
    <?php endif; ?>

    <div class="table-container">

        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Price (RWF)</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($parts) > 0): ?>

                    <?php foreach ($parts as $part): ?>

                        <tr>
                            <td><?php echo htmlspecialchars($part['part_id']); ?></td>

                            <td><?php echo htmlspecialchars($part['name']); ?></td>

                            <td><?php echo htmlspecialchars($part['category']); ?></td>

                            <td><?php echo htmlspecialchars($part['brand']); ?></td>

                            <td>
                                <?php echo number_format((float)$part['price']); ?>
                            </td>

                            <td><?php echo htmlspecialchars($part['stock']); ?></td>

                            <td>
                                <a href="edit_part.php?id=<?php echo (int)$part['part_id']; ?>">
                                    Edit
                                </a>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="7">No spare parts found.</td>
                    </tr>

                <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

</body>
</html>