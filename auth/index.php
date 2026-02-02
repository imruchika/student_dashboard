<?php
session_start();
include("../db/config.php");


$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    if ($username === "" || $password === "") {
        $error = "Please enter username and password.";
    } else {
       
$sql = "SELECT * FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 1) {
    $row = mysqli_fetch_assoc($result);

    // ✅ plain-text password check
    if(password_verify($password, $row["password"])) {

        $_SESSION["user_id"] = $row["id"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["role"] = $row["role"];

        if ($row["role"] === "teacher") {
            header("Location: ../dashboard.php");
        } else {
            header("Location: ../stud_dashboard.php");
        }
        exit();
    }
}

$error = "Invalid username or password.";

}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">

</head>
<body class="login-body">
<div class="shape shape-1"></div>
<div class="shape shape-2"></div>
<div class="shape shape-3"></div>
<div class="shape shape-4"></div>
<div class="shape shape-5"></div>

<div class="login-wrapper">
    <div class="login-illustration">
        <div class="tag">Student Performance</div>
        <h1>Welcome to your dashboard</h1>
        <p>Login to manage students, analyze performance and explore insights for every class.</p>
        
    </div>

    <div class="login-form">
        <h2>Login to account</h2>
        <p>Enter your credentials to access the student performance dashboard.</p>

        <?php if($error !== ""): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" >
    <div class="form-group">
        <label for="username">Username</label>
        <input class="form-control" type="text" id="username" name="username" placeholder="Enter username" required>
    </div>



            <div class="form-group">
                 <label for="password">Password</label>
                <input class="form-control" type="password" name="password" id="password" placeholder="Enter password">
                <span class="toggle-eye" onclick="togglePassword()">
                    <i class="fa-solid fa-eye-slash eye-icon" id="eyeIcon"></i>
                </span>
            </div>

        

            <div class="forgot-row">
                <span>Keep your credentials safe.</span>
                <a href="#">Need help?</a>
            </div>

            <button type="submit" class="btn-login">Login</button>
            <div class="bottom-text">
    Don’t have an account?
    <a href="register.php" style="color:#4f46e5; font-weight:600;">Register here</a>
</div>

        </form>

        <div class="bottom-text">
            Student Performance Dashboard · <?php echo date("Y"); ?>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const password = document.getElementById("password");
    const eyeIcon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    } else {
        password.type = "password";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    }
}
</script>

</body>
</html>
