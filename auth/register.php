<?php
session_start();

$success = $_SESSION['success'] ?? "";
$error   = $_SESSION['error'] ?? "";

unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Registration</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../style.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">



</head>

<body>

<div class="auth-box">
    <h2>User Registration</h2>

    <?php if($success): ?>
        <div style="
            margin-bottom:12px;
            font-size:13px;
            color:#065f46;
            background:#d1fae5;
            border:1px solid #6ee7b7;
            padding:8px 10px;
            border-radius:10px;
            text-align:center;
        ">
            <?= htmlspecialchars($success) ?><br>
            <a href="../index.php" style="font-weight:600;color:#047857;">
                Login now
            </a>
        </div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="../register_process.php">

    <div class="form-group">
    <input type="text" name="username" placeholder="Username" required>
    </div>
    <!-- ROLE (MANDATORY) -->
    <input type="hidden" name="role" value="teacher">

    <!-- PASSWORD -->
    <div class="form-group">
        <input type="password"
               id="password"
               name="password"
               placeholder="Password"
                required>

        <i class="fa-solid fa-eye-slash eye-iconr"
           id="eyeIcon"
           onclick="togglePassword()"></i>
    </div>
<div class="form-group">
    <!-- CONFIRM PASSWORD -->
    <input type="password"
           name="cpassword"
           placeholder="Confirm Password"
           required>

    </div>
    <button type="submit" class="btn">Register</button>
</form>


    <div class="bottom-text">
        Already have an account?
        <a href="index.php">Login here</a>
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
