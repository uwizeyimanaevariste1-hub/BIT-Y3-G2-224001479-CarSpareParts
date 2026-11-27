?variant="standard" title="Admin Orders Management PHP" id="73821"
<?php
session_start();

// Administrators only
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$conn = $database->connect();

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission.";
    }

    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);

    $status = $_POST['status'] ?? '';

    $allowed_statuses = [
        'pending',
        'processing',
        'completed',
        'cancelled'
    ];

    if (!$order_id || $order_id < 1) {
        $errors[] = "Invalid order ID.";
    }

    if (!in_array($status, $allowed_statuses, true)) {
        $errors[] = "Invalid order status.";
    }

    if (empty($errors)) {

        try {

            // Start database transaction
            $conn->beginTransaction();

            // Lock the selected order
            $stmt = $conn->prepare(
                "SELECT order_id, status
                 FROM orders
                 WHERE order_id = :order_id
                 FOR UPDATE"
            );

            $stmt->execute([
                ':order_id' => $order_id
            ]);

            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                throw new Exception("Order not found.");
            }

            $current_status = $order['status'];

            // Prevent changing an already cancelled order
            if ($current_status === 'cancelled') {
                throw new Exception("This order has already been cancelled.");
            }

            // Only pending or processing orders can be cancelled
            if (
                $status === 'cancelled' &&
                !in_array($current_status, ['pending', 'processing'], true)
            ) {
                throw new Exception(
                    "Only pending or processing orders can be cancelled."
                );
            }

            // Restore stock when cancelling an order
            if ($status === 'cancelled') {

                // Retrieve ordered products
                $stmt = $conn->prepare(
                    "SELECT part_id, quantity
                     FROM order_items
                     WHERE order_id = :order_id"
                );

                $stmt->execute([
                    ':order_id' => $order_id
                ]);

                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Return each ordered quantity to stock
                $restoreStock = $conn->prepare(
                    "UPDATE parts
                     SET stock = stock + :quantity
                     WHERE part_id = :part_id"
                );

                foreach ($items as $item) {

                    $restoreStock->execute([
                        ':quantity' => $item['quantity'],
                        ':part_id' => $item['part_id']
                    ]);
                }
            }

            // Update order status
            $stmt = $conn->prepare(
                "UPDATE orders
                 SET status = :status
                 WHERE order_id = :order_id"
            );

            $stmt->execute([
                ':status' => $status,
                ':order_id' => $order_id
            ]);

            // Save all changes
            $conn->commit();

            // Generate a new CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header("Location: orders.php?updated=1");
            exit;

        } catch (Exception $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $errors[] = $e->getMessage();

        } catch (PDOException $e) {

            if ($conn->inTransaction()) {
                $conn->rollBack();
            }

            $errors[] = "Unable to update the order. Please try again.";
        }
    }
}

// Retrieve all customer orders
$sql = "SELECT
            orders.order_id,
            orders.order_date,
            orders.total,
            orders.status,
            users.username,
            users.email
        FROM orders
        INNER JOIN users
        ON orders.customer_id = users.user_id
        ORDER BY orders.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Orders - Admin</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<div class="admin-dashboard">

    <div class="admin-welcome">

        <h1>Manage Customer Orders</h1>

        <p>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['username']); ?>!
        </p>

    </div>

    <div class="admin-actions">

        <a href="dashboard.php">Dashboard</a>

        <a href="parts.php">Manage Parts</a>

        <a href="../index.php">View Website</a>

        <a href="../logout.php">Logout</a>

    </div>

    <h2>All Customer Orders</h2>

    <?php if (isset($_GET['updated'])): ?>

        <p class="success-message">
            Order status updated successfully!
        </p>

    <?php endif; ?>

    <?php if (!empty($errors)): ?>

        <div class="error-message">

            <?php foreach ($errors as $error): ?>

                <p><?php echo htmlspecialchars($error); ?></p>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

    <div class="table-container">

        <table class="admin-table">

            <thead>

                <tr>

                    <th>Order ID</th>

                    <th>Customer</th>

                    <th>Email</th>

                    <th>Date</th>

                    <th>Total (RWF)</th>

                    <th>Status</th>

                    <th>Update Status</th>

                </tr>

            </thead>

            <tbody>

                <?php if (!empty($orders)): ?>

                    <?php foreach ($orders as $order): ?>

                        <tr>

                            <td>
                                #<?php echo (int)$order['order_id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['username']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['email']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($order['order_date']); ?>
                            </td>

                            <td>
                                <?php echo number_format((float)$order['total']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(ucfirst($order['status'])); ?>
                            </td>

                            <td>

                                <form method="POST" action="orders.php">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?php echo (int)$order['order_id']; ?>"
                                    >

                                    <select
                                        name="status"
                                        required
                                        <?php echo $order['status'] === 'cancelled' ? 'disabled' : ''; ?>
                                    >

                                        <option value="pending"
                                            <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>
                                            Pending
                                        </option>

                                        <option value="processing"
                                            <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>
                                            Processing
                                        </option>

                                        <option value="completed"
                                            <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>
                                            Completed
                                        </option>

                                        <option value="cancelled">
                                            Cancelled
                                        </option>

                                    </select>

                                    <?php if ($order['status'] !== 'cancelled'): ?>

                                        <button type="submit">
                                            Update
                                        </button>

                                    <?php else: ?>

                                        <span>Cancelled</span>

                                    <?php endif; ?>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="7">
                            No orders found.
                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>
</html>