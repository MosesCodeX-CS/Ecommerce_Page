<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$user_id = $_SESSION['user_id'];
// This assumes you have an 'orders' table with user_id, product_id, quantity, total, date
$result = mysqli_query($conn, "SELECT o.*, p.name, p.image FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = $user_id ORDER BY o.date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3>Order History</h3>
    <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="alert alert-info">No orders found.</div>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Quantity</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><img src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" width="50"></td>
                        <td><?php echo (int)$row['quantity']; ?></td>
                        <td>KSh <?php echo number_format($row['total'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary">Back to Home</a>
</div>
</body>
</html>
