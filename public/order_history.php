<?php
session_start();

// Customers only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

// Retrieve orders belonging to the logged-in customer
$sql = "SELECT *
        FROM orders
        WHERE customer_id = :customer_id
        ORDER BY order_date DESC";

$stmt = $conn->prepare($sql);

$stmt->execute([
    ':customer_id' => $_SESSION['user_id']
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Orders - AutoParts Store</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="admin-dashboard">

    <h1>My Order History</h1>

    <p>
        Welcome,
        <?php echo htmlspecialchars($_SESSION['username']); ?>!
    </p>

    <div class="admin-actions">
        <a href="dashboard.php">Dashboard</a>
        <a href="parts.php">Shop Spare Parts</a>
        <a href="cart.php">My Cart</a>
        <a href="logout.php">Logout</a>
    </div>

    <h2>Previous Orders</h2>

    <?php if (!empty($orders)): ?>

        <div class="table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Products</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($orders as $order): ?>

                        <tr>

                            <td>
                                #<?php echo (int)$order['order_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['order_date']); ?>
                            </td>

                            <td>
                                <?php echo number_format((float)$order['total']); ?> RWF
                            </td>

                            <td>
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </td>

                            <td>

                                <?php
                                $item_sql = "SELECT
                                                order_items.quantity,
                                                order_items.unit_price,
                                                parts.name
                                             FROM order_items
                                             INNER JOIN parts
                                             ON order_items.part_id = parts.part_id
                                             WHERE order_items.order_id = :order_id";

                                $item_stmt = $conn->prepare($item_sql);

                                $item_stmt->execute([
                                    ':order_id' => $order['order_id']
                                ]);

                                $items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>

                                <?php foreach ($items as $item): ?>

                                    <p>
                                        <?php echo htmlspecialchars($item['name']); ?>
                                        (<?php echo (int)$item['quantity']; ?>)
                                    </p>

                                <?php endforeach; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php else: ?>

        <p>You have not placed any orders yet.</p>

        <a href="parts.php">Start Shopping</a>

    <?php endif; ?>

</div>

</body>
</html>