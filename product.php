<?php session_start();
include 'db.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Ecommerce_page</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="product.php">Products</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="cart.php">Cart</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Products Grid -->
<div class="container mt-5">
  <h2 class="text-center mb-4">Our Products</h2>
  <div class="row">
    <?php
      $result = mysqli_query($conn, "SELECT * FROM products") 
        or die("Query failed: " . mysqli_error($conn));

      while ($row = mysqli_fetch_assoc($result)) {
    ?>
      <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
          <img src="assets/images/<?php echo $row['image']; ?>" class="card-img-top" alt="<?php echo $row['name']; ?>">
          <div class="card-body text-center">
            <h5 class="card-title"><?php echo $row['name']; ?></h5>
            <p class="card-text"><?php echo $row['description']; ?></p>
            <p class="fw-bold text-primary">KSh <?php echo number_format($row['price'], 2); ?></p>
            <a href="cart.php?id=<?php echo $row['id']; ?>" class="btn btn-success">Add to Cart</a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</div>

</body>
</html>
