<?php
session_start();
include("db/config.php");

// Allow only POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: page1/register.php");
    exit();
}

// Get & sanitize inputs
$username  = trim($_POST['username'] ?? '');
$password  = trim($_POST['password'] ?? '');
$cpassword = trim($_POST['cpassword'] ?? '');
$role      = $_POST['role'] ?? 'teacher';

// ---------------- VALIDATION ----------------

// Empty fields check
if ($username === '' || $password === '' || $cpassword === '') {
    $_SESSION['error'] = "All fields are required.";
    header("Location: page1/register.php");
    exit();
}

// Password match check
if ($password !== $cpassword) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: page1/register.php");
    exit();
}

// Password length check (basic security)
if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters.";
    header("Location: page1/register.php");
    exit();
}

// ---------------- CHECK USERNAME ----------------
$check = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
mysqli_stmt_bind_param($check, "s", $username);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    $_SESSION['error'] = "Username already exists.";
    header("Location: page1/register.php");
    exit();
}
mysqli_stmt_close($check);

// ---------------- HASH PASSWORD ----------------
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ---------------- INSERT USER ----------------
$stmt = mysqli_prepare(
    $conn,
    "INSERT INTO users (username, password, role) VALUES (?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sss", $username, $hashedPassword, $role);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success'] = "Registration successful. Please login.";
    header("Location: page1/index.php"); // login page
    exit();
}

// ---------------- FALLBACK ERROR ----------------
$_SESSION['error'] = "Registration failed. Please try again.";
header("Location: page1/register.php");
exit();


