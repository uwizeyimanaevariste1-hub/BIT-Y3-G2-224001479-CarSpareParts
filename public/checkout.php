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

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Display successful order message
if (isset($_GET['success'])) {
    $order_id = filter_input(INPUT_GET, 'success', FILTER_VALIDATE_INT);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Order Successful</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <div class="auth-container">
            <div class="auth-box">
                <h1>Order Placed Successfully!</h1>
                <p>Your order has been saved.</p>
                <p>Order Number: <strong>#<?php echo (int)$order_id; ?></strong></p>
                <a href="dashboard.php">Go to Dashboard</a>
                <br><br>
                <a href="parts.php">Continue Shopping</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$errors = [];

// Process checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Verify CSRF token
    if (
        !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $errors[] = "Invalid form submission. Please try again.";
    }

    if (empty($cart)) {
        $errors[] = "Your shopping cart is empty.";
    }

    if (empty($errors)) {

        try {
            // Start database transaction
            $conn->beginTransaction();

            $total = 0;
            $order_items = [];

            // Check every product and its stock
            foreach ($cart as $part_id => $quantity) {

                $part_id = (int)$part_id;
                $quantity = (int)$quantity;

                if ($part_id < 1 || $quantity < 1) {
                    throw new Exception("Invalid cart item.");
                }

                $stmt = $conn->prepare(
                    "SELECT * FROM parts WHERE part_id = :id FOR UPDATE"
                );

                $stmt->execute([':id' => $part_id]);

                $part = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$part) {
                    throw new Exception("A selected product no longer exists.");
                }

                if ((int)$part['stock'] < $quantity) {
                    throw new Exception(
                        "Not enough stock available for " . $part['name']
                    );
                }

                $unit_price = (float)$part['price'];
                $subtotal = $unit_price * $quantity;

                $total += $subtotal;

                $order_items[] = [
                    'part_id' => $part_id,
                    'quantity' => $quantity,
                    'unit_price' => $unit_price
                ];
            }

            // Create order
            $stmt = $conn->prepare(
                "INSERT INTO orders (customer_id, order_date, total, status)
                 VALUES (:customer_id, NOW(), :total, 'pending')"
            );

            $stmt->execute([
                ':customer_id' => $_SESSION['user_id'],
                ':total' => $total
            ]);

            $order_id = $conn->lastInsertId();

            // Save order items and update stock
            foreach ($order_items as $item) {

                $stmt = $conn->prepare(
                    "INSERT INTO order_items
                     (order_id, part_id, quantity, unit_price)
                     VALUES (:order_id, :part_id, :quantity, :unit_price)"
                );

                $stmt->execute([
                    ':order_id' => $order_id,
                    ':part_id' => $item['part_id'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['unit_price']
                ]);

                $stmt = $conn->prepare(
                    "UPDATE parts
                     SET stock = stock - :quantity
                     WHERE part_id = :part_id"
                );

                $stmt->execute([
                    ':quantity' => $item['quantity'],
                    ':part_id' => $item['part_id']
                ]);
            }

            // Commit all database changes
            $conn->commit();

            // Empty the shopping cart
            unset($_SESSION['cart']);

            // Generate a new CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            // Redirect to success page
            header("Location: checkout.php?success=" . $order_id);
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

            $errors[] = "Unable to place your order. Please try again.";
        }
    }
}

// Retrieve cart information for display
$cart_items = [];
$total = 0;

foreach ($cart as $part_id => $quantity) {

    $stmt = $conn->prepare(
        "SELECT * FROM parts WHERE part_id = :id"
    );

    $stmt->execute([':id' => $part_id]);

    $part = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($part) {

        $subtotal = (float)$part['price'] * (int)$quantity;

        $part['quantity'] = (int)$quantity;
        $part['subtotal'] = $subtotal;

        $cart_items[] = $part;

        $total += $subtotal;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Checkout - AutoParts Store</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="admin-dashboard">

    <h1>Checkout</h1>

    <div class="admin-actions">
        <a href="cart.php">Back to Cart</a>
        <a href="parts.php">Continue Shopping</a>
        <a href="dashboard.php">Dashboard</a>
    </div>

    <?php if (!empty($errors)): ?>

        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

    <h2>Order Summary</h2>

    <?php if (!empty($cart_items)): ?>

        <div class="table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($cart_items as $item): ?>

                        <tr>
                            <td>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>

                            <td>
                                <?php echo number_format((float)$item['price']); ?> RWF
                            </td>

                            <td>
                                <?php echo (int)$item['quantity']; ?>
                            </td>

                            <td>
                                <?php echo number_format($item['subtotal']); ?> RWF
                            </td>
                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <h2>
            Total Amount: <?php echo number_format($total); ?> RWF
        </h2>

        <form method="POST" action="checkout.php">

            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
            >

            <button type="submit">
                Confirm and Place Order
            </button>

        </form>

    <?php else: ?>

        <p>Your shopping cart is empty.</p>

        <a href="parts.php">Browse Spare Parts</a>

    <?php endif; ?>

</div>

</body>
</html>