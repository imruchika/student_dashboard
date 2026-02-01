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

    <!-- You can keep your global style.css if needed -->
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
            overflow:hidden;
            position:relative;
            color:  #413f3f;
        }

        /* light shapes in background similar to template */
        .shape{
            position:absolute;
            border-radius:50%;
            background:rgba(255,255,255,0.05);
            filter:blur(1px);
        }
        .shape.shape-1{width:260px;height:260px;top:-60px;left:-60px;}
        .shape.shape-2{width:180px;height:180px;bottom:-40px;left:10%;}
        .shape.shape-3{width:220px;height:220px;top:-70px;right:-40px;}
        .shape.shape-4{width:140px;height:140px;bottom:-50px;right:15%;}
        .shape.shape-5{
            width:420px;height:420px;
            border-radius:30px;
            background:radial-gradient(circle at top,rgba(255,255,255,0.06),transparent 60%);
            bottom:-220px;left:50%;
            transform:translateX(-50%);
        }

        .login-wrapper{
            position:relative;
            width:100%;
            max-width:900px;
            display:grid;
            grid-template-columns: minmax(0,1.1fr) minmax(0,0.9fr);
            gap:0;
            border-radius:24px;
            background:rgba(15,23,42,0.96);
            box-shadow:0 24px 70px rgba(0,0,0,0.65);
            overflow:hidden;
        }

        .login-illustration{
            background:linear-gradient(160deg, #413f3f,  #746f6721);
            padding:32px 28px;
            color: #e5e7eb;
            position:relative;
        }
        .login-illustration h1{
            font-size:26px;
            font-weight:800;
            margin-bottom:10px;
        }
        .login-illustration p{
            font-size:14px;
            color: #cbd5f5;
            max-width:260px;
        }
        .login-illustration .tag{
            display:inline-block;
            font-size:10px;
            text-transform:uppercase;
            letter-spacing:0.12em;
            background:rgba(15,23,42,0.35);
            padding:5px 10px;
            border-radius:999px;
            margin-bottom:10px;
        }
        .illustration-board{
            position:absolute;
            right:-40px;
            bottom:-30px;
            width:280px;
            height:220px;
            background:#0f172a;
            border-radius:24px;
            border:1px solid rgba(148,163,184,0.3);
            box-shadow:0 18px 40px rgba(15,23,42,0.8);
            overflow:hidden;
        }
        .illustration-board-inner{
            position:absolute;
            inset:14px;
            border-radius:18px;
            background:#065f46;
            background-image:url('../image/WhatsApp-Image-2026-01-17-at-8.25.43-PM.jpg');
            background-size:cover;
            background-position:center;
            opacity:0.9;
        }

        .login-form{
            padding:32px 30px;
            background:#f9fafb;
        }
        .login-form h2{
            font-size:22px;
            font-weight:700;
            margin-bottom:6px;
            color: #413f3f;
        }
        .login-form p{
            font-size:13px;
            color: #6b7280;
            margin-bottom:20px;
        }

        .form-group label{
            display:block;
            font-size:13px;
            margin-bottom:6px;
            color: #4b5563;
        }
        .form-control{
            width:100%;
            padding:9px 11px;
            border-radius:10px;
            border:1px solid #d1d5db;
            font-size:14px;
            outline:none;
            transition:border 0.2s,box-shadow 0.2s;
            /* background: #864c4c; */
             /* background: linear-gradient(to right, #DDD0C8, #bbb4b4); */
        }
        .form-control:focus{
            border-color:#4f46e5;
            box-shadow:0 0 0 1px rgba(46, 46, 49, 0.25);
        }

        .forgot-row{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:16px;
            font-size:12px;
            color:#6b7280;
        }
        .forgot-row a{
            color:#4f46e5;
            text-decoration:none;
            font-weight:500;
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

.eye-icon {
    position: absolute;
    right: 14px;
    top: 70%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #666;
}


        .btn-login{
            width:100%;
            padding:9px 11px;
            border-radius:999px;
            border:none;
            font-size:14px;
            font-weight:600;
            color: #f9fafb;
            background: linear-gradient(135deg, #6a6f75, #111827);
            cursor:pointer;
            box-shadow:0 12px 30px rgba(61, 61, 68, 0.55);
            transition:transform 0.1s,box-shadow 0.1s,filter 0.1s;
        }
        .btn-login:hover{
            filter:brightness(1.05);
            transform:translateY(-1px);
        }
        .btn-login:active{
            transform:translateY(0);
            box-shadow:0 8px 22px rgba(44, 44, 61, 0.55);
        }

        .error-msg{
            margin-bottom:10px;
            font-size:12px;
            color:#b91c1c;
            background:#fee2e2;
            border:1px solid #fecaca;
            padding:6px 8px;
            border-radius:8px;
        }

        .bottom-text{
            margin-top:14px;
            font-size:12px;
            color:#9ca3af;
            text-align:center;
        }

        @media(max-width:900px){
            .login-wrapper{
                max-width:520px;
                grid-template-columns:1fr;
            }
            .login-illustration{
                display:none;
            }
        }
        @media(max-width:600px){
            body{
                padding:14px;
            }
            .login-form{
                padding:24px 20px;
            }
        }
    </style>
</head>
<body>
<div class="shape shape-1"></div>
<div class="shape shape-2"></div>
<div class="shape shape-3"></div>
<div class="shape shape-4"></div>
<div class="shape shape-5"></div>

<div class="login-wrapper">
    <div class="login-illustration">
        <div class="tag">Student Performance</div>
        <h1>Welcome back to your dashboard</h1>
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
