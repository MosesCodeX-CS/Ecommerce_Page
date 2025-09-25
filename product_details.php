<?php
include 'db.php';
if (!isset($_GET['id'])) {
    die('Product not specified.');
}
$id = (int)$_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id LIMIT 1");
if (!$result || mysqli_num_rows($result) === 0) {
    die('Product not found.');
}
$product = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Details</title>
    <!-- Open Graph for social sharing -->
    <meta property="og:title" content="<?php echo htmlspecialchars($product['name']); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($product['description']); ?>" />
    <meta property="og:image" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/assets/images/' . htmlspecialchars($product['image']); ?>" />
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" />
    <meta property="og:type" content="product" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Ecommerce_page</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Products</a>
          <ul class="dropdown-menu" aria-labelledby="productsDropdown">
            <?php
            $cat_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
            while ($cat = mysqli_fetch_assoc($cat_result)) {
              echo '<li><a class="dropdown-item" href="product.php?category=' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</a></li>';
            }
            ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
        <?php if (!empty($_SESSION['user_id'])): ?>
        <li class="nav-item"><a class="nav-link" href="order_history.php">Order History</a></li>
        <?php endif; ?>
        <li class="nav-item">
            <a class="nav-link" href="cart.php">
                Cart <?php if (!empty($_SESSION['cart'])): ?>(<?php echo array_sum($_SESSION['cart']); ?>)<?php endif; ?>
            </a>
        </li>
      </ul>
      <form class="d-flex ms-3" method="GET" action="product.php">
        <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>
    </div>
  </div>
</nav>
<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img loading="lazy" src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" class="img-fluid rounded shadow" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="col-md-6">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
            <p><?php echo htmlspecialchars($product['description']); ?></p>
            <p class="fw-bold text-primary">KSh <?php echo number_format($product['price'], 2); ?></p>
            <a href="cart.php?id=<?php echo (int)$product['id']; ?>" class="btn btn-success mb-3 add-to-cart-btn">Add to Cart</a>
            <div>
                <label class="fw-bold">Share:</label>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">Facebook</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($product['name']); ?>" target="_blank" class="btn btn-outline-info btn-sm">Twitter/X</a>
                <a href="https://wa.me/?text=<?php echo urlencode($product['name'] . ' ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-outline-success btn-sm">WhatsApp</a>
            </div>
        </div>
    </div>
</div>
<?php if (empty($_SESSION['user_id'])): ?>
<div class="modal fade" id="authPromptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login to Continue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Please login or sign up to purchase products and track your orders.</p>
      </div>
      <div class="modal-footer">
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-outline-secondary">Sign Up</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<?php if (empty($_SESSION['user_id'])): ?>
<script>
document.addEventListener('click', function(e) {
  var target = e.target.closest('.add-to-cart-btn');
  if (target) {
    e.preventDefault();
    try {
      var modal = new bootstrap.Modal(document.getElementById('authPromptModal'));
      modal.show();
    } catch (err) {
      window.location.href = 'login.php';
    }
  }
});
</script>
<?php endif; ?>
</body>
</html>
