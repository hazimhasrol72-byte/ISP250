<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Appointment | Select Role</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top left, #6C63FF, #1b1b3a 55%, #0f1020);
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .container {
            width: 90%;
            max-width: 900px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 28px;
            padding: 50px;
            box-shadow: 0 25px 80px rgba(0,0,0,0.35);
            text-align: center;
        }

        h1 {
            font-size: 38px;
            margin-bottom: 10px;
        }

        p {
            color: #dcdcff;
            margin-bottom: 40px;
        }

        .role-box {
            display: flex;
            gap: 25px;
            justify-content: center;
        }

        .role-card {
            flex: 1;
            padding: 35px;
            border-radius: 22px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.22);
            text-decoration: none;
            color: white;
            transition: 0.3s;
        }

        .role-card:hover {
            transform: translateY(-8px);
            background: rgba(255,255,255,0.22);
            box-shadow: 0 15px 35px rgba(108,99,255,0.35);
        }

        .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .role-card h2 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .role-card span {
            font-size: 14px;
            color: #dcdcff;
        }

        @media(max-width: 750px) {
            .role-box {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Smart Appointment</h1>
    <p>Please select your role to continue</p>

    <div class="role-box">
        <a href="student_login.php" class="role-card">
            <div class="icon">🎓</div>
            <h2>Student</h2>
            <span>Login using Student Number</span>
        </a>

        <a href="lecturer_login.php" class="role-card">
            <div class="icon">👨‍🏫</div>
            <h2>Lecturer</h2>
            <span>Login using Lecturer ID</span>
        </a>
    </div>
</div>

</body>
</html>