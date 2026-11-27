
<?php
session_start();

require_once __DIR__ . '/../config/database.php';

$database = new Database();
$conn = $database->connect();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Retrieve all spare parts initially
$sql = "SELECT * FROM parts ORDER BY part_id DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();

$parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$is_customer = isset($_SESSION['user_id']) &&
               $_SESSION['role'] === 'customer';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Spare Parts - AutoParts Store</title>

    <link rel="stylesheet" href="assets/css/style.css">

    <script src="assets/js/script.js" defer></script>
</head>

<body>

<header class="navbar">

    <div class="logo">
        AutoParts Store
    </div>

    <nav>
        <a href="index.php">Home</a>
        <a href="parts.php">Spare Parts</a>

        <?php if ($is_customer): ?>

            <a href="cart.php">My Cart</a>
            <a href="order_history.php">My Orders</a>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>

        <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>

            <a href="admin/dashboard.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>

        <?php else: ?>

            <a href="login.php">Login</a>
            <a href="register.php">Register</a>

        <?php endif; ?>
    </nav>

</header>

<main class="parts-page">

    <section class="parts-heading">

        <h1>Our Spare Parts</h1>

        <p>
            Find quality automotive spare parts at affordable prices.
        </p>

    </section>

    <?php if (isset($_GET['unavailable'])): ?>

        <p class="error-message">
            Sorry, this product is unavailable or out of stock.
        </p>

    <?php endif; ?>

    <!-- Live search input -->
    <section class="search-section">

        <input
            type="search"
            id="partSearch"
            placeholder="Search by part name, category or brand..."
            autocomplete="off"
        >

        <p id="searchMessage">
            Showing all spare parts.
        </p>

    </section>

    <!-- Product results -->
    <section class="product-grid" id="productResults">

        <?php if (!empty($parts)): ?>

            <?php foreach ($parts as $part): ?>

                <div class="product-card">

                    <h3>
                        <?php echo htmlspecialchars($part['name']); ?>
                    </h3>

                    <p>
                        <strong>Category:</strong>
                        <?php echo htmlspecialchars($part['category']); ?>
                    </p>

                    <p>
                        <strong>Brand:</strong>
                        <?php echo htmlspecialchars($part['brand']); ?>
                    </p>

                    <p class="product-price">
                        <?php echo number_format((float)$part['price']); ?> RWF
                    </p>

                    <p>
                        <strong>Stock:</strong>
                        <?php echo (int)$part['stock']; ?>
                    </p>

                    <?php if ((int)$part['stock'] > 0): ?>

                        <?php if ($is_customer): ?>

                            <form action="add_to_cart.php" method="POST">

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

                                <button type="submit" class="add-cart-button">
                                    Add to Cart
                                </button>

                            </form>

                        <?php elseif (isset($_SESSION['user_id'])): ?>

                            <p>Log in with a customer account to order.</p>

                        <?php else: ?>

                            <a href="login.php" class="add-cart-button">
                                Login to Order
                            </a>

                        <?php endif; ?>

                    <?php else: ?>

                        <button type="button" disabled>
                            Out of Stock
                        </button>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <p>No spare parts are currently available.</p>

        <?php endif; ?>

    </section>

</main>

<footer class="footer">

    <p>
        &copy; <?php echo date('Y'); ?> AutoParts Store.
        All rights reserved.
    </p>

</footer>

<!-- Data needed by JavaScript -->
<script>
    window.storeSearchConfig = {
        apiUrl: "api/search_parts.php",
        isCustomer: <?php echo $is_customer ? 'true' : 'false'; ?>,
        isLoggedIn: <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>,
        csrfToken: <?php echo json_encode($_SESSION['csrf_token']); ?>
    };
</script>

</body>
</html>
