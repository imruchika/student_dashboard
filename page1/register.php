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

<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- styles SAME as yours (unchanged) -->

<style>
*{
    box-sizing:border-box;
    margin:0;
    padding:0;
    font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
}
body{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background:radial-gradient(circle at top, #DDD0C8, #807777 45%, #685b5b 85%);
}

/* Card */
.auth-box{
    width:100%;
    max-width:380px;
    background:#f9fafb;
    padding:28px 26px;
    border-radius:20px;
    box-shadow:0 24px 60px rgba(0,0,0,0.45);
}

/* Heading */
.auth-box h2{
    text-align:center;
    font-size:22px;
    font-weight:700;
    margin-bottom:16px;
    color:#413f3f;
}

 .form-group{
    position: relative;
    width: 100%;
    margin-bottom:14px;
}

.form-group input {
    width: 100%;
    padding-right: 40px; /* space ONLY for eye icon */
}

.eye-iconr {
    position: absolute;
    right: 14px;
    top: 55%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #666;
}
.form-group input,
.form-group select{
    width:100%;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
    outline:none;
}
.form-group input:focus,
.form-group select:focus{
    border-color:#4f46e5;
    box-shadow:0 0 0 1px rgba(79,70,229,0.25);
}

/* Button */
.btn{
    width:100%;
    padding:10px;
    border-radius:999px;
    border:none;
    font-size:14px;
    font-weight:600;
    color:#fff;
    background:linear-gradient(135deg, #6a6f75, #111827);
    cursor:pointer;
    box-shadow:0 12px 30px rgba(0,0,0,0.45);
}
.btn:hover{
    filter:brightness(1.05);
}

/* Error */
.error-msg{
    margin-bottom:10px;
    font-size:12px;
    color:#b91c1c;
    background:#fee2e2;
    border:1px solid #fecaca;
    padding:6px 8px;
    border-radius:8px;
}

/* Bottom text */
.bottom-text{
    margin-top:14px;
    font-size:12px;
    text-align:center;
    color:#6b7280;
}
.bottom-text a{
    color:#4f46e5;
    font-weight:600;
    text-decoration:none;
}

 </style>
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
