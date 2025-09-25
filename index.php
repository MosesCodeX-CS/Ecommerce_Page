<?php include 'db.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ecommerce_page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<style>
/* Show dropdown on hover for Products tab */
.navbar-nav .dropdown:hover .dropdown-menu {
  display: block;
  margin-top: 0;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">Ecommerce_page</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Products
          </a>
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

<!-- Auth Prompt Modal -->
<?php if (empty($_SESSION['user_id'])): ?>
<div class="modal fade" id="authPromptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Welcome to Ecommerce_page</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Please login or create an account to enjoy faster checkout and order history.</p>
      </div>
      <div class="modal-footer">
        <a href="login.php" class="btn btn-primary">Login</a>
        <a href="register.php" class="btn btn-outline-secondary">Sign Up</a>
      </div>
    </div>
  </div>
 </div>
<?php endif; ?>

<div class="container text-center mt-5">
    <h1 class="mb-3">Welcome to Ecommerce_page</h1>
    <p class="mb-4">Your one-stop shop for all your needs!</p>
    <a class="btn btn-primary btn-lg" href="product.php">Shop Now</a>
</div>

<div class="container mt-5">
    <h2 class="text-center mb-4">Featured Products</h2>
    <div class="row justify-content-center">
        <?php
        $featured = mysqli_query($conn, "SELECT * FROM products ORDER BY id DESC LIMIT 3");
        if ($featured && mysqli_num_rows($featured) > 0) {
            while ($row = mysqli_fetch_assoc($featured)) {
        ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <img loading="lazy" src="assets/images/<?php echo htmlspecialchars($row['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                <div class="card-body text-center">
                    <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                    <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="fw-bold text-primary">KSh <?php echo number_format($row['price'], 2); ?></p>
                    <a href="cart.php?id=<?php echo (int)$row['id']; ?>" class="btn btn-success">Add to Cart</a>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/product_details.php?id=' . (int)$row['id']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">Share</a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/product_details.php?id=' . (int)$row['id']); ?>&text=<?php echo urlencode($row['name']); ?>" target="_blank" class="btn btn-outline-info btn-sm">Tweet</a>
                        <a href="https://wa.me/?text=<?php echo urlencode($row['name'] . ' ' . 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/product_details.php?id=' . (int)$row['id']); ?>" target="_blank" class="btn btn-outline-success btn-sm">WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
        <?php }
        } else {
            echo "<div class='col-12 text-center'><em>No featured products available.</em></div>";
        }
        ?>
    </div>
</div>

<script>
// Ensure dropdown works on hover for Bootstrap 5
const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
dropdownElementList.map(function (dropdownToggleEl) {
  dropdownToggleEl.addEventListener('mouseover', function () {
    const dropdownMenu = this.nextElementSibling;
    if (dropdownMenu) dropdownMenu.classList.add('show');
  });
  dropdownToggleEl.parentElement.addEventListener('mouseleave', function () {
    const dropdownMenu = this.querySelector('.dropdown-menu');
    if (dropdownMenu) dropdownMenu.classList.remove('show');
  });
});

// Show auth modal once for guests on first visit
<?php if (empty($_SESSION['user_id'])): ?>
document.addEventListener('DOMContentLoaded', function() {
  try {
    if (!localStorage.getItem('authPromptShown')) {
      var modal = new bootstrap.Modal(document.getElementById('authPromptModal'));
      modal.show();
      localStorage.setItem('authPromptShown', '1');
    }
  } catch (e) {}
});
<?php endif; ?>
</script>
</body>
</html>


