<?php
session_start();
include 'db.php';

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shipping = $_POST['shipping'];
    $payment = $_POST['payment'];
    
    // Calculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $result = mysqli_query($conn, "SELECT price FROM products WHERE id=$id");
        $product = mysqli_fetch_assoc($result);
        $subtotal += $product['price'] * $qty;
    }
    
    $shipping_cost = ($shipping == 'express') ? 500 : 200;
    $total = $subtotal + $shipping_cost;
    
    // Save order rows (simple per-item record) for logged-in users
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    
    mysqli_begin_transaction($conn);
    $ok = true;
    
    foreach ($_SESSION['cart'] as $id => $qty) {
        $id = (int)$id;
        $qty = (int)$qty;
        $res = mysqli_query($conn, "SELECT price FROM products WHERE id=$id");
        if (!$res) { $ok = false; break; }
        $prod = mysqli_fetch_assoc($res);
        $lineTotal = $prod['price'] * $qty;
        
        // Create orders table if it doesn't exist
        $createSql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            total DECIMAL(10,2) NOT NULL,
            date DATETIME NOT NULL
        )";
        if (!mysqli_query($conn, $createSql)) { $ok = false; break; }
        
        $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, product_id, quantity, total, date) VALUES (?, ?, ?, ?, NOW())");
        if (!$stmt) { $ok = false; break; }
        mysqli_stmt_bind_param($stmt, 'iiid', $userId, $id, $qty, $lineTotal);
        if (!mysqli_stmt_execute($stmt)) { $ok = false; break; }
    }
    
    if ($ok) {
        mysqli_commit($conn);
        $order_id = 'ORD' . date('YmdHis') . rand(100, 999);
        // Clear cart after successful checkout
        $_SESSION['cart'] = [];
    } else {
        mysqli_rollback($conn);
        $error_message = 'Failed to save order. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Ecommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Ecommerce</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="product.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <?php if (isset($order_id)): ?>
            <!-- Success Message -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white text-center">
                            <h4>✅ Order Placed Successfully!</h4>
                        </div>
                        <div class="card-body text-center">
                            <h5>Thank you for your order!</h5>
                            <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
                            <p><strong>Payment Method:</strong> <?php echo ucfirst($payment); ?></p>
                            <p><strong>Shipping:</strong> <?php echo ucfirst($shipping); ?> (KSh <?php echo $shipping_cost; ?>)</p>
                            <p><strong>Total Amount:</strong> KSh <?php echo number_format($total, 2); ?></p>
                            
                            <div class="mt-4">
                                <a href="product.php" class="btn btn-primary">Continue Shopping</a>
                                <a href="index.php" class="btn btn-secondary">Back to Home</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Checkout Form -->
            <div class="row">
                <div class="col-md-8">
                    <h2>Checkout</h2>
                    
                    <!-- Order Summary -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $subtotal = 0;
                            foreach ($_SESSION['cart'] as $id => $qty) {
                                $result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
                                $product = mysqli_fetch_assoc($result);
                                $subtotal += $product['price'] * $qty;
                                
                                echo '<div class="d-flex justify-content-between">';
                                echo '<span>' . htmlspecialchars($product['name']) . ' x ' . $qty . '</span>';
                                echo '<span>KSh ' . number_format($product['price'] * $qty, 2) . '</span>';
                                echo '</div>';
                            }
                            ?>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Subtotal:</strong>
                                <strong>KSh <?php echo number_format($subtotal, 2); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Complete Your Order</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Shipping Option</label>
                                    <select name="shipping" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="standard">Standard (KSh 200)</option>
                                        <option value="express">Express (KSh 500)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Payment Method</label>
                                    <select name="payment" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="mpesa">M-Pesa</option>
                                        <option value="card">Credit/Debit Card</option>
                                        <option value="cod">Cash on Delivery</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-success w-100">Place Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
