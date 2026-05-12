<?php
session_start();
include 'db_connect.php';

$error = "";

if (isset($_POST['btnLogin'])) {
    $studentno = mysqli_real_escape_string($connStudent, $_POST['studentno']);

    $sql = "SELECT * FROM students WHERE studentno = '$studentno'";
    $result = mysqli_query($connStudent, $sql);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['role'] = "student";
        $_SESSION['studentno'] = $row['studentno'];
        $_SESSION['studentname'] = $row['studentname'];

        header("Location: ispInterfaceExp.php");
        exit();
    } else {
        $error = "Invalid Student Number.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Montserrat',sans-serif;
}

body{
    min-height:100vh;
    background:
    radial-gradient(circle at top left, rgba(192,132,252,0.35), transparent 28%),
    radial-gradient(circle at bottom right, rgba(217,70,239,0.24), transparent 32%),
    linear-gradient(135deg,#140021,#1e0033,#250042,#160024);
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-box{
    width:430px;
    background:rgba(255,255,255,0.10);
    backdrop-filter:blur(22px);
    padding:45px;
    border-radius:28px;
    border:1px solid rgba(255,255,255,0.12);
    box-shadow:
    0 0 45px rgba(168,85,247,0.22),
    0 25px 80px rgba(0,0,0,0.38);
}

h2{
    color:white;
    font-size:32px;
    font-weight:800;
    margin-bottom:8px;
}

p{
    color:#d8b4fe;
    margin-bottom:28px;
}

input{
    width:100%;
    height:58px;
    padding:0 18px;
    border-radius:15px;
    border:1px solid rgba(255,255,255,0.10);
    background:rgba(255,255,255,0.08);
    color:white;
    font-size:15px;
    margin-bottom:22px;
}

input::placeholder{
    color:rgba(255,255,255,0.55);
}

input:focus{
    outline:none;
    border-color:#c084fc;
    box-shadow:
    0 0 0 4px rgba(192,132,252,0.14),
    0 0 18px rgba(168,85,247,0.25);
}

button{
    width:100%;
    height:58px;
    border:none;
    border-radius:15px;
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    color:white;
    font-size:16px;
    font-weight:800;
    cursor:pointer;
    box-shadow:
    0 0 18px rgba(168,85,247,0.55),
    0 12px 28px rgba(124,58,237,0.35);
}

button:hover{
    transform:translateY(-2px);
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:13px;
    font-weight:700;
}

a{
    display:block;
    margin-top:20px;
    text-align:center;
    color:#d8b4fe;
    text-decoration:none;
    font-weight:700;
}
</style>
</head>

<body>
    <div class="login-box">
        <h2>Student Login</h2>
        <p>Enter your student number</p>

        <?php if ($error != "") echo "<div class='error'>$error</div>"; ?>

        <form method="POST">
            <input type="text" name="studentno" placeholder="Student Number" required>
            <button type="submit" name="btnLogin">Continue</button>
        </form>

        <a href="role_select.php">Back</a>
    </div>
</body>
</html>