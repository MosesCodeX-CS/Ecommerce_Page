<?php
$host = "localhost";
$user = "moses";   // default MySQL user
$pass = "Abc@1234xyz!";       // leave empty if no password
$dbname = "ecommerce_db";

// Create connection
$conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
