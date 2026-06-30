<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Appointment | Select Role</title>
    <link href="[fonts.googleapis.com](https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap)" rel="stylesheet">
    <style>
        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
            font-family:'Montserrat',sans-serif;
        }
        body{
            min-height:100vh;
            background:
                radial-gradient(circle at top left, rgba(192,132,252,.35), transparent 28%),
                radial-gradient(circle at bottom right, rgba(217,70,239,.24), transparent 32%),
                linear-gradient(135deg,#140021,#1e0033,#250042,#160024);
            display:flex;
            justify-content:center;
            align-items:center;
            color:white;
            padding:20px;
        }
        .container{
            width:100%;
            max-width:950px;
            background:rgba(255,255,255,.10);
            backdrop-filter:blur(22px);
            border:1px solid rgba(255,255,255,.15);
            border-radius:30px;
            padding:50px;
            box-shadow:0 25px 80px rgba(0,0,0,.35);
            text-align:center;
        }
        h1{
            font-size:42px;
            font-weight:800;
            margin-bottom:12px;
        }
        .subtitle{
            color:#d8b4fe;
            margin-bottom:40px;
            font-size:15px;
        }
        .role-box{
            display:grid;
            grid-template-columns:repeat(2,1fr);
            gap:24px;
        }
        .role-card{
            text-decoration:none;
            color:white;
            padding:38px 30px;
            border-radius:24px;
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.14);
            transition:.3s ease;
            box-shadow:0 0 25px rgba(168,85,247,.12);
        }
        .role-card:hover{
            transform:translateY(-8px);
            background:rgba(255,255,255,.16);
            box-shadow:0 18px 40px rgba(124,58,237,.28);
        }
        .icon{
            font-size:54px;
            margin-bottom:18px;
        }
        .role-card h2{
            font-size:26px;
            margin-bottom:10px;
            font-weight:800;
        }
        .role-card p{
            color:#e9d5ff;
            font-size:14px;
            line-height:1.7;
        }
        @media(max-width:760px){
            .container{
                padding:35px 24px;
            }
            .role-box{
                grid-template-columns:1fr;
            }
            h1{
                font-size:34px;
            }
        }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['otp_expired'])) { ?>
    <script>
        alert("<?php echo addslashes($_SESSION['otp_expired']); ?>");
    </script>
    <?php unset($_SESSION['otp_expired']); } ?>
<div class="container">
    <h1>Smart Appointment</h1>
    <p class="subtitle">Please select your role to continue into the system.</p>
    <div class="role-box">
        <a href="student_login.php" class="role-card">
            <div class="icon"></div>
            <h2>Student</h2>

        </a>
        <a href="lecturer_login.php" class="role-card">
            <div class="icon"></div>
            <h2>Lecturer / Admin</h2>
            
        </a>
    </div>
</div>
</body>
</html>