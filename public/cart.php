<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

// Create CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$cart = $_SESSION['cart'] ?? [];

$cart_items = [];
$total = 0;

// Retrieve cart products from database
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

    <title>Shopping Cart - AutoParts Store</title>

    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="admin-dashboard">

    <h1>My Shopping Cart</h1>

    <div class="admin-actions">
        <a href="parts.php">Continue Shopping</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Logout</a>
    </div>

    <?php if (isset($_GET['added'])): ?>
        <p class="success-message">
            Product added to your cart successfully!
        </p>
    <?php endif; ?>

    <?php if (isset($_GET['stock_limit'])): ?>
        <p class="error-message">
            You cannot add more than the available stock.
        </p>
    <?php endif; ?>

    <h2>Selected Spare Parts</h2>

    <?php if (!empty($cart_items)): ?>

        <div class="table-container">

            <table class="admin-table">

                <thead>
                    <tr>
                        <th>Part Name</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($cart_items as $item): ?>

                        <tr>

                            <td>
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($item['brand']); ?>
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

                            <td>

                                <form action="remove_from_cart.php" method="POST">

                                    <input type="hidden" name="part_id"
                                        value="<?php echo (int)$item['part_id']; ?>">

                                    <input type="hidden" name="csrf_token"
                                        value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                                    <button type="submit">
                                        Remove
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <h2>
            Total:
            <?php echo number_format($total); ?> RWF
        </h2>

        <a href="checkout.php" class="add-cart-button">
    Proceed to Checkout
</a>

    <?php else: ?>

        <p>Your shopping cart is currently empty.</p>

        <a href="parts.php">Browse Spare Parts</a>

    <?php endif; ?>

</div>

</body>
</html>