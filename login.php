<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("db/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = ($_POST['username']);
    $password = ($_POST['password']);

    $_SESSION['username'] = $username;
// header("Location: dashboard.php");
// exit();


    // 🔥 CHECK CORRECT TABLE NAME HERE
    $sql = "SELECT * FROM users WHERE username = '$username' LIMIT 1";
    // $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $res = mysqli_query($conn, $sql);

    if ($res && mysqli_num_rows($res) === 1) {

        ($row = mysqli_fetch_assoc($res));
print_r($row );
echo $row['username'];
// echo $hashedPassword;
        // 🔥 IF PASSWORD IS STORED AS PLAIN TEXT
        // if (password_verify($row['password'])) {
        if (password_verify($password, $row['password'])) {

            // ✅ LOGIN SUCCESSFUL
            $_SESSION['user_id'] = $row['id'];
 //row['password']
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role']; // student / teacher

             header("Location: dashboard.php");
            exit();
        }
    }

    // ❌ LOGIN FAILED
    // $_SESSION['error'] = "Invalid username or password";
    // header("Location: dashboard.php");
    // exit();
}


