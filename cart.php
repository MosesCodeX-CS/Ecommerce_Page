<?php
session_start();
include 'db.php';

// Add product to cart if "id" is passed
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

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
    
    // Redirect to prevent duplicate additions on refresh
    header('Location: cart.php');
    exit();
}

// Remove product from cart
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header('Location: cart.php');
    exit();
}

// Update quantity
if (isset($_POST['update_qty'])) {
    foreach ($_POST['qty'] as $id => $qty) {
        $id = (int)$id;
        $qty = (int)$qty;
        
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
    }
    header('Location: cart.php');
    exit();
}

// Clear entire cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
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
            <a class="nav-link active" href="cart.php">
                Cart <?php if (!empty($_SESSION['cart'])): ?>(<?php echo array_sum($_SESSION['cart']); ?>)<?php endif; ?>
            </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Cart Section -->
<div class="container mt-5">
  <h2 class="text-center mb-4">🛒 Your Cart</h2>

  <?php
  if (!empty($_SESSION['cart'])) {
      $total = 0;
      echo '<form method="POST" action="cart.php">';
      echo '<div class="table-responsive">';
      echo '<table class="table table-bordered text-center align-middle">';
      echo '<thead class="table-dark"><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th><th>Action</th></tr></thead><tbody>';

      foreach ($_SESSION['cart'] as $id => $qty) {
          $result = mysqli_query($conn, "SELECT * FROM products WHERE id=$id");
          $p = mysqli_fetch_assoc($result);
          $sub = $p['price'] * $qty;
          $total += $sub;

          echo '<tr>';
          echo '<td>' . htmlspecialchars($p['name']) . '</td>';
          echo '<td>';
          echo '<input type="number" name="qty[' . $id . ']" value="' . $qty . '" min="1" max="10" class="form-control text-center" style="width: 80px;">';
          echo '</td>';
          echo '<td>KSh ' . number_format($p['price'], 2) . '</td>';
          echo '<td>KSh ' . number_format($sub, 2) . '</td>';
          echo '<td>';
          echo '<a href="cart.php?remove=' . $id . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Remove this item?\')">Remove</a>';
          echo '</td>';
          echo '</tr>';
      }

      echo '</tbody>';
      echo '<tfoot>';
      echo '<tr class="table-secondary"><td colspan="3" class="text-end"><strong>Total:</strong></td><td><strong>KSh ' . number_format($total, 2) . '</strong><td></td></tr>';
      echo '</tfoot>';
      echo '</table>';
      echo '</div>';

      echo '<div class="row mb-3">';
      echo '<div class="col-md-6">';
      echo '<button type="submit" name="update_qty" class="btn btn-primary">Update Quantities</button>';
      echo '</div>';
      echo '<div class="col-md-6 text-end">';
      echo '<a href="cart.php?clear=1" class="btn btn-warning" onclick="return confirm(\'Clear entire cart?\')">Clear Cart</a>';
      echo '</div>';
      echo '</div>';
      echo '</form>';

      // Checkout section
      echo '<div class="card mt-4">';
      echo '<div class="card-header"><h5>Proceed to Checkout</h5></div>';
      echo '<div class="card-body">';
      echo '<form method="POST" action="checkout.php">';
      
      // Shipping
      echo '<div class="mb-3">';
      echo '<label class="form-label">Shipping Option</label>';
      echo '<select name="shipping" class="form-select" required>';
      echo '<option value="">Select...</option>';
      echo '<option value="standard">Standard (KSh 200)</option>';
      echo '<option value="express">Express (KSh 500)</option>';
      echo '</select>';
      echo '</div>';

      // Payment
      echo '<div class="mb-3">';
      echo '<label class="form-label">Payment Method</label>';
      echo '<select name="payment" class="form-select" required>';
      echo '<option value="">Select...</option>';
      echo '<option value="mpesa">M-Pesa</option>';
      echo '<option value="card">Credit/Debit Card</option>';
      echo '<option value="cod">Cash on Delivery</option>';
      echo '</select>';
      echo '</div>';

      echo '<button type="submit" class="btn btn-success w-100 proceed-checkout">Proceed to Checkout</button>';
      echo '</form>';
      echo '</div>';
      echo '</div>';
  } else {
      echo '<div class="alert alert-warning text-center">Your cart is empty.</div>';
      echo '<div class="text-center mt-3">';
      echo '<a href="product.php" class="btn btn-primary">Continue Shopping</a>';
      echo '</div>';
  }
  ?>
</div>

<?php if (empty($_SESSION['user_id'])): ?>
<div class="modal fade" id="authPromptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Login to Checkout</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"><p>Please login or sign up to complete your purchase.</p></div>
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
  var target = e.target.closest('.proceed-checkout');
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
