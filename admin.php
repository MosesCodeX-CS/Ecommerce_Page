<?php
include 'db.php';
session_start();
// For demo, allow access without admin check

// Handle Admin Actions
// Create tables/columns if missing (lightweight migrations)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS categories (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL UNIQUE)");
mysqli_query($conn, "ALTER TABLE products ADD COLUMN IF NOT EXISTS category_id INT NULL");
mysqli_query($conn, "ALTER TABLE products ADD COLUMN IF NOT EXISTS quantity INT DEFAULT 0");
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, product_id INT NOT NULL, quantity INT NOT NULL, total DECIMAL(10,2) NOT NULL, date DATETIME NOT NULL)");
// Ensure users table/columns for admin management
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user','admin') DEFAULT 'user'
)");
mysqli_query($conn, "ALTER TABLE users ADD COLUMN IF NOT EXISTS role ENUM('user','admin') DEFAULT 'user'");

// Add / Update Product
if (isset($_POST['save_product'])) {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $image = trim($_POST['image']);
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

    if ($id > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE products SET name=?, description=?, price=?, image=?, category_id=?, quantity=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, 'ssdssii', $name, $description, $price, $image, $category_id, $quantity, $id);
        mysqli_stmt_execute($stmt);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO products (name, description, price, image, category_id, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssdssi', $name, $description, $price, $image, $category_id, $quantity);
        mysqli_stmt_execute($stmt);
    }
    header('Location: admin.php#products');
    exit();
}

// Delete Product
if (isset($_GET['delete_product'])) {
    $pid = (int)$_GET['delete_product'];
    mysqli_query($conn, "DELETE FROM products WHERE id=$pid");
    header('Location: admin.php#products');
    exit();
}

// Update Inventory quantity only
if (isset($_POST['update_inventory'])) {
    foreach ($_POST['qty'] as $pid => $qty) {
        $pid = (int)$pid; $qty = (int)$qty;
        mysqli_query($conn, "UPDATE products SET quantity=$qty WHERE id=$pid");
    }
    header('Location: admin.php#inventory');
    exit();
}

// Create / Update User
if (isset($_POST['save_user'])) {
    $uid = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = in_array($_POST['role'], ['user','admin']) ? $_POST['role'] : 'user';
    $password = trim($_POST['password']);

    if ($uid > 0) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, role=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssssi', $username, $email, $role, $hash, $uid);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'sssi', $username, $email, $role, $uid);
        }
        mysqli_stmt_execute($stmt);
    } else {
        // require password on create
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'ssss', $username, $email, $hash, $role);
        mysqli_stmt_execute($stmt);
    }
    header('Location: admin.php#customers');
    exit();
}

// Delete user
if (isset($_GET['delete_user'])) {
    $uid = (int)$_GET['delete_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$uid");
    header('Location: admin.php#customers');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Admin Dashboard</h2>
    <ul class="nav nav-tabs mb-4" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="products-tab" data-bs-toggle="tab" data-bs-target="#products" type="button" role="tab">Products</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab">Orders</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="customers-tab" data-bs-toggle="tab" data-bs-target="#customers" type="button" role="tab">Customers</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">Inventory</button>
        </li>
    </ul>
    <div class="tab-content" id="adminTabContent">
        <div class="tab-pane fade show active" id="products" role="tabpanel">
            <h4>Manage Products</h4>
            <div class="row">
                <div class="col-md-5">
                    <form method="POST" class="card card-body mb-4">
                        <h5>Add / Edit Product</h5>
                        <input type="hidden" name="id" value="<?php echo isset($_GET['edit_product']) ? (int)$_GET['edit_product'] : 0; ?>">
                        <?php
                        $edit = ['name'=>'','description'=>'','price'=>'','image'=>'','category_id'=>'','quantity'=>0];
                        if (isset($_GET['edit_product'])) {
                            $eid = (int)$_GET['edit_product'];
                            $res = mysqli_query($conn, "SELECT * FROM products WHERE id=$eid");
                            if ($res) { $edit = mysqli_fetch_assoc($res); }
                        }
                        ?>
                        <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control" value="<?php echo htmlspecialchars($edit['name']); ?>" required></div>
                        <div class="mb-2"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3" required><?php echo htmlspecialchars($edit['description']); ?></textarea></div>
                        <div class="mb-2"><label class="form-label">Price (KSh)</label><input type="number" step="0.01" name="price" class="form-control" value="<?php echo htmlspecialchars($edit['price']); ?>" required></div>
                        <div class="mb-2"><label class="form-label">Image filename</label><input name="image" class="form-control" value="<?php echo htmlspecialchars($edit['image']); ?>" placeholder="e.g., phone.jpg" required></div>
                        <div class="mb-2">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select...</option>
                                <?php
                                $cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
                                while ($c = mysqli_fetch_assoc($cats)) {
                                    $sel = ($edit['category_id'] == $c['id']) ? 'selected' : '';
                                    echo '<option value="'.$c['id'].'" '.$sel.'>'.htmlspecialchars($c['name']).'</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Initial Quantity</label><input type="number" name="quantity" class="form-control" value="<?php echo (int)$edit['quantity']; ?>" min="0"></div>
                        <button class="btn btn-primary" name="save_product" type="submit">Save Product</button>
                    </form>
                </div>
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Name</th><th>Price</th><th>Category</th><th>Qty</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php
                            $list = mysqli_query($conn, "SELECT p.*, c.name AS cat FROM products p LEFT JOIN categories c ON p.category_id=c.id ORDER BY p.id DESC");
                            while ($p = mysqli_fetch_assoc($list)) {
                                echo '<tr>';
                                echo '<td>'.htmlspecialchars($p['name']).'</td>';
                                echo '<td>KSh '.number_format($p['price'],2).'</td>';
                                echo '<td>'.htmlspecialchars($p['cat']).'</td>';
                                echo '<td>'.(int)$p['quantity'].'</td>';
                                echo '<td>';
                                echo '<a class="btn btn-sm btn-outline-primary me-2" href="admin.php?edit_product='.$p['id'].'#products">Edit</a>';
                                echo '<a class="btn btn-sm btn-outline-danger" href="admin.php?delete_product='.$p['id'].'" onclick="return confirm(\'Delete this product?\')">Delete</a>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="orders" role="tabpanel">
            <h4>Manage Orders</h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead><tr><th>Date</th><th>User ID</th><th>Product</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php
                        $orders = mysqli_query($conn, "SELECT o.*, p.name FROM orders o JOIN products p ON o.product_id=p.id ORDER BY o.date DESC");
                        while ($o = mysqli_fetch_assoc($orders)) {
                            echo '<tr>';
                            echo '<td>'.htmlspecialchars($o['date']).'</td>';
                            echo '<td>'.(int)$o['user_id'].'</td>';
                            echo '<td>'.htmlspecialchars($o['name']).'</td>';
                            echo '<td>'.(int)$o['quantity'].'</td>';
                            echo '<td>KSh '.number_format($o['total'],2).'</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="customers" role="tabpanel">
            <h4>Manage Customers</h4>
            <div class="row">
                <div class="col-md-5">
                    <form method="POST" class="card card-body mb-4">
                        <h5>Add / Edit User</h5>
                        <?php
                        $urow = ['username'=>'','email'=>'','role'=>'user'];
                        $edit_uid = isset($_GET['edit_user']) ? (int)$_GET['edit_user'] : 0;
                        if ($edit_uid) {
                            $r = mysqli_query($conn, "SELECT * FROM users WHERE id=$edit_uid");
                            if ($r) { $urow = mysqli_fetch_assoc($r); }
                        }
                        ?>
                        <input type="hidden" name="id" value="<?php echo $edit_uid; ?>">
                        <div class="mb-2"><label class="form-label">Username</label><input name="username" class="form-control" value="<?php echo htmlspecialchars($urow['username']); ?>" required></div>
                        <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($urow['email']); ?>" required></div>
                        <div class="mb-2">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select">
                                <option value="user" <?php echo ($urow['role']==='user')?'selected':''; ?>>User</option>
                                <option value="admin" <?php echo ($urow['role']==='admin')?'selected':''; ?>>Admin</option>
                            </select>
                        </div>
                        <div class="mb-3"><label class="form-label">Password <?php echo $edit_uid? '(leave blank to keep)': '' ?></label><input type="password" name="password" class="form-control" <?php echo $edit_uid? '': 'required'; ?>></div>
                        <button class="btn btn-primary" name="save_user" type="submit">Save User</button>
                    </form>
                </div>
                <div class="col-md-7">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
                            <tbody>
                                <?php
                                $users = mysqli_query($conn, "SELECT id, username, email, role FROM users ORDER BY id DESC");
                                while ($u = mysqli_fetch_assoc($users)) {
                                    echo '<tr>';
                                    echo '<td>'.(int)$u['id'].'</td>';
                                    echo '<td>'.htmlspecialchars($u['username']).'</td>';
                                    echo '<td>'.htmlspecialchars($u['email']).'</td>';
                                    echo '<td>'.htmlspecialchars($u['role']).'</td>';
                                    echo '<td>';
                                    echo '<a class="btn btn-sm btn-outline-primary me-2" href="admin.php?edit_user='.$u['id'].'#customers">Edit</a>';
                                    echo '<a class="btn btn-sm btn-outline-danger" href="admin.php?delete_user='.$u['id'].'" onclick="return confirm(\'Delete this user?\')">Delete</a>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="inventory" role="tabpanel">
            <h4>Update Inventory</h4>
            <form method="POST" class="card card-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead><tr><th>Product</th><th>Current Qty</th><th>New Qty</th></tr></thead>
                        <tbody>
                            <?php
                            $inv = mysqli_query($conn, "SELECT id, name, quantity FROM products ORDER BY name");
                            while ($i = mysqli_fetch_assoc($inv)) {
                                echo '<tr>';
                                echo '<td>'.htmlspecialchars($i['name']).'</td>';
                                echo '<td>'.(int)$i['quantity'].'</td>';
                                echo '<td style="max-width:120px"><input type="number" class="form-control" name="qty['.$i['id'].']" value="'.(int)$i['quantity'].'" min="0"></td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-primary" type="submit" name="update_inventory">Save Inventory</button>
            </form>
        </div>
    </div>
    <a href="index.php" class="btn btn-secondary mt-4">Back to Home</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
