<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '" . mysqli_real_escape_string($conn, $email) . "'");
    if ($row = mysqli_fetch_assoc($result)) {
        // In a real app, send email with reset link. Here, just show a message.
        $msg = 'Password reset instructions sent to your email (demo).';
    } else {
        $msg = 'Email not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recover Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 400px;">
    <h3 class="mb-4">Password Recovery</h3>
    <?php if (isset($msg)) echo '<div class="alert alert-info">'.$msg.'</div>'; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
    </form>
    <p class="mt-3"><a href="login.php">Back to Login</a></p>
</div>
</body>
</html>
