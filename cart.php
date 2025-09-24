
<?php
session_start();
include 'db.php';

// Add product to cart if "id" is passed
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Initialize cart if not set
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // If already in cart, increase quantity
    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]++;
    } else {
        $_SESSION['cart'][$id] = 1;
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Cart</title></head>
<body>
<h2>Your Cart</h2>

<?php
if (!empty($_SESSION['cart'])) {
    $total = 0;
    foreach ($_SESSION['cart'] as $id => $qty) {
        $result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
        $p = mysqli_fetch_assoc($result);

        $sub = $p['price'] * $qty;
        $total += $sub;

        echo $p['name'] . " - " . $qty . " x $" . $p['price'] . " = $" . $sub . "<br>";
    }
    echo "<strong>Total: $" . $total . "</strong>";
} else {
    echo "Cart is empty.";
}
?>

</body>
</html>
