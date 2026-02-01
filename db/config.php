<?php
$conn = mysqli_connect("localhost", "root", "", "std_dashboard",3307);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>