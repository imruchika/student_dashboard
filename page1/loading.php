<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loading...</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }
        body{
            height:100vh;
            width:100%;
            display:flex;
            justify-content:center;
            align-items:center;
            flex-direction:column;
            /* same style gradient as dashboard */
            background:radial-gradient(circle at top,#4c6fff,#9333ea 45%,#111827 85%); /* full-page gradient [web:706][web:711] */
            background:radial-gradient(circle at top, #DDD0C8, #807777 45%, #685b5b 85%); /* full-page gradient [web:706][web:711] */
            color:#ffffff;
            font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            overflow:hidden;
        }
        .loader{
            width:60px;
            height:60px;
            border:6px solid rgba(255,255,255,0.3);
            border-top:6px solid #ffffff;
            border-radius:50%;
            animation:spin 1s linear infinite;
            margin-bottom:15px;
        }
        @keyframes spin{
            to{ transform:rotate(360deg); }
        }
        h1{
            font-size:26px;
            font-weight:700;
            margin-bottom:6px;
            text-align:center;
        }
        h2{
            font-size:16px;
            font-weight:400;
            opacity:0.9;
        }
    </style>
</head>
<body>

<div class="loader"></div>
<h1>Student Performance Dashboard</h1>
<h2>Loading...</h2>

<script>
    // After 3 seconds go to main dashboard
    setTimeout(function () {
        window.location.href = "index.php";
    }, 3000);
</script>

</body>
</html>
