<?php
session_start();
include 'db.php';

// Function to fetch categories
function getCategories($conn) {
    $categories = [];
    $query = "SELECT * FROM categories ORDER BY name";
    $result = mysqli_query($conn, $query);
    
    if (!$result) {
        error_log("Category query failed: " . mysqli_error($conn));
        return [];
    }
    
    while ($cat = mysqli_fetch_assoc($result)) {
        $categories[$cat['id']] = $cat['name'];
    }
    return $categories;
}

// Function to fetch products
function getProducts($conn, $search = '', $category_filter = '', $min_price = '', $max_price = '', $sort = '') {
    $products_by_cat = [];
    $uncategorized = [];
    
    // Build the query based on search and category filter
    $query = "SELECT p.*, c.name as category_name FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id";
    
    $conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $search_term = "%$search%";
        $params[] = $search_term;
        $params[] = $search_term;
    }
    
    if (!empty($category_filter)) {
        $conditions[] = "p.category_id = ?";
        $params[] = $category_filter;
    }
    if ($min_price !== '' && is_numeric($min_price)) {
        $conditions[] = "p.price >= ?";
        $params[] = $min_price;
    }
    if ($max_price !== '' && is_numeric($max_price)) {
        $conditions[] = "p.price <= ?";
        $params[] = $max_price;
    }
    
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }
    
    // Sorting options
    switch ($sort) {
        case 'price_asc':
            $query .= " ORDER BY c.name, p.price ASC";
            break;
        case 'price_desc':
            $query .= " ORDER BY c.name, p.price DESC";
            break;
        case 'name_desc':
            $query .= " ORDER BY c.name, p.name DESC";
            break;
        default:
            $query .= " ORDER BY c.name, p.name";
    }
    
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt && !empty($params)) {
        // Build types: numeric values should be bound as 'd'
        $types = '';
        foreach ($params as $prm) { $types .= is_numeric($prm) ? 'd' : 's'; }
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if ($stmt) {
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $query);
    }
    
    if (!$result) {
        error_log("Products query failed: " . mysqli_error($conn));
        return ['products_by_cat' => [], 'uncategorized' => [], 'search_term' => $search];
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        // Sanitize output
        $row['name'] = htmlspecialchars($row['name']);
        $row['description'] = htmlspecialchars($row['description']);
        $row['image'] = htmlspecialchars($row['image']);
        
        if (!empty($row['category_id'])) {
            $products_by_cat[$row['category_id']][] = $row;
        } else {
            $uncategorized[] = $row;
        }
    }
    
    return ['products_by_cat' => $products_by_cat, 'uncategorized' => $uncategorized, 'search_term' => $search];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Products - Ecommerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Show dropdown on hover for Products tab */
        .navbar-nav .dropdown:hover .dropdown-menu {
            display: block;
            margin-top: 0;
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .category-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Ecommerce</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" aria-current="page" href="#" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <?php if (!empty($_SESSION['user_id'])): ?>
                    <li class="nav-item"><a class="nav-link" href="order_history.php">Order History</a></li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            Cart <?php if (!empty($_SESSION['cart'])): ?>(<?php echo array_sum($_SESSION['cart']); ?>)<?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Auth Prompt Modal -->
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

    <!-- Products Grid -->
    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-5">Our Products</h2>
        
        <?php
        // Get filters
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category_filter = isset($_GET['category']) ? (int)$_GET['category'] : '';
        $min_price = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
        $max_price = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
        $sort = isset($_GET['sort']) ? $_GET['sort'] : '';
        ?>
        
        <!-- Search Bar -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <form method="GET" action="product.php" class="row g-2">
                    <div class="col-md-6">
                        <input class="form-control" type="search" name="search" 
                               placeholder="Search products by name or description..." 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               aria-label="Search">
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="number" step="0.01" min="0" name="min_price" placeholder="Min Price" value="<?php echo htmlspecialchars($min_price); ?>">
                    </div>
                    <div class="col-md-2">
                        <input class="form-control" type="number" step="0.01" min="0" name="max_price" placeholder="Max Price" value="<?php echo htmlspecialchars($max_price); ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-select">
                            <option value="" <?php echo $sort===''?'selected':''; ?>>Sort</option>
                            <option value="price_asc" <?php echo $sort==='price_asc'?'selected':''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo $sort==='price_desc'?'selected':''; ?>>Price: High to Low</option>
                            <option value="name_desc" <?php echo $sort==='name_desc'?'selected':''; ?>>Name Z-A</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button class="btn btn-outline-success" type="submit">Apply</button>
                        <?php if (!empty($search) || !empty($category_filter) || $min_price!=='' || $max_price!=='' || $sort!==''): ?>
                            <a href="product.php" class="btn btn-outline-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <?php
        
        $categories = getCategories($conn);
        $products_data = getProducts($conn, $search, $category_filter, $min_price, $max_price, $sort);
        $products_by_cat = $products_data['products_by_cat'];
        $uncategorized = $products_data['uncategorized'];
        $search_term = $products_data['search_term'];

        // Show search results header if searching
        if (!empty($search)) {
            echo '<div class="alert alert-info mb-4">';
            echo '<h4>Search Results for: "' . htmlspecialchars($search) . '"</h4>';
            echo '<a href="product.php" class="btn btn-outline-primary btn-sm">Clear Search</a>';
            echo '</div>';
        }

        // Show category filter if applied
        if (!empty($category_filter) && isset($categories[$category_filter])) {
            echo '<div class="alert alert-success mb-4">';
            echo '<h4>Category: ' . htmlspecialchars($categories[$category_filter]) . '</h4>';
            echo '<a href="product.php" class="btn btn-outline-success btn-sm">View All Products</a>';
            echo '</div>';
        }

        if (empty($products_by_cat) && empty($uncategorized)) {
            if (!empty($search)) {
                echo '<div class="alert alert-warning text-center">No products found matching "' . htmlspecialchars($search) . '". <a href="product.php">View all products</a></div>';
            } else {
                echo '<div class="alert alert-info text-center">No products available at the moment.</div>';
            }
        } else {
            // Display products by category
            foreach ($categories as $cat_id => $cat_name) {
                if (!empty($products_by_cat[$cat_id])) {
                    echo '<h3 class="category-title mt-5 mb-3">' . htmlspecialchars($cat_name) . '</h3>';
                    echo '<div class="row">';
                    foreach ($products_by_cat[$cat_id] as $product) {
        ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm">
                            <a href="product_details.php?id=<?php echo (int)$product['id']; ?>">
                                <img loading="lazy" src="assets/images/<?php echo $product['image']; ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo $product['name']; ?>">
                            </a>
                            <div class="card-body text-center">
                                <h5 class="card-title">
                                    <a href="product_details.php?id=<?php echo (int)$product['id']; ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo $product['name']; ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted"><?php echo $product['description']; ?></p>
                                <p class="fw-bold text-primary">KSh <?php echo number_format($product['price'], 2); ?></p>
                                <a href="cart.php?id=<?php echo (int)$product['id']; ?>" 
                                   class="btn btn-success w-100 add-to-cart-btn" data-product-id="<?php echo (int)$product['id']; ?>">Add to Cart</a>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/product_details.php?id=' . (int)$product['id']); ?>" target="_blank" class="btn btn-outline-primary btn-sm">Share</a>
                                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/product_details.php?id=' . (int)$product['id']); ?>&text=<?php echo urlencode($product['name']); ?>" target="_blank" class="btn btn-outline-info btn-sm">Tweet</a>
                                    <a href="https://wa.me/?text=<?php echo urlencode($product['name'] . ' ' . 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/product_details.php?id=' . (int)$product['id']); ?>" target="_blank" class="btn btn-outline-success btn-sm">WhatsApp</a>
                                </div>
                            </div>
                        </div>
                    </div>
        <?php
                    }
                    echo '</div>';
                }
            }

            // Display uncategorized products
            if (!empty($uncategorized)) {
                // Uncategorized products are no longer displayed
            }
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
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
    
    // Intercept Add to Cart for guests and show login prompt
    <?php if (empty($_SESSION['user_id'])): ?>
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
    <?php endif; ?>
    </script>
</body>
</html>