<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';
require_once 'email_helper.php';

$error = "";

function findEmailInRow($row)
{
        $possibleColumns = [
        'studentemailuitm',
        'studentemail',
        'studentEmail',
        'student_email',
        'studemail',
        'studEmail',
        'email',
        'EMAIL',
        'USER_EMAIL'
    ];

    foreach ($possibleColumns as $column) {
        if (isset($row[$column]) && trim($row[$column]) !== '') {
            return trim($row[$column]);
        }
    }

    foreach ($row as $key => $value) {
        if (stripos($key, 'email') !== false && trim($value) !== '') {
            return trim($value);
        }
    }

    return "";
}

function maskEmail($email)
{
    if (strpos($email, '@') === false) {
        return $email;
    }

    [$name, $domain] = explode('@', $email, 2);

    if (strlen($name) <= 2) {
        $maskedName = substr($name, 0, 1) . "***";
    } else {
        $maskedName = substr($name, 0, 2) . str_repeat("*", max(strlen($name) - 2, 3));
    }

    return $maskedName . "@" . $domain;
}

if (isset($_POST['requestOTP'])) {

    $studentno = trim($_POST['studentno'] ?? '');
    $studentnoSafe = mysqli_real_escape_string($connStudent, $studentno);

    if ($studentno === '') {
        $error = "Please enter your student number.";
    } else {

        $studentQuery = mysqli_query($connStudent, "
            SELECT *
            FROM students
            WHERE studentno = '$studentnoSafe'
            LIMIT 1
        ");

        if ($studentQuery && mysqli_num_rows($studentQuery) > 0) {

            $student = mysqli_fetch_assoc($studentQuery);

            $studentName = $student['studentname'] ?? 'Student';
            $studentEmail = findEmailInRow($student);

            if ($studentEmail === '') {
                $error = "This student account does not have an email in the database. Please contact admin.";
            } elseif (!filter_var($studentEmail, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid email format found in database. Please contact admin.";
            } else {

                $otpCode = (string) random_int(100000, 999999);

                $_SESSION['pending_login'] = [
                    'login_type' => 'student',
                    'final_role' => 'student',
                    'id' => $student['studentno'],
                    'name' => $studentName,
                    'email' => $studentEmail,
                    'otp_hash' => password_hash($otpCode, PASSWORD_DEFAULT),
                    'expires_at' => time() + 300,
                    'attempts' => 0,
                    'last_sent_at' => time()
                ];

                $sent = sendOTPEmail($studentEmail, $studentName, $otpCode);

                if ($sent) {
                    $_SESSION['otp_notice'] = "OTP has been sent to " . maskEmail($studentEmail) . ".";
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    unset($_SESSION['pending_login']);
                    $emailError = $_SESSION['email_error'] ?? "Unknown email error.";
                    $error = "Failed to send OTP email. Error: " . $emailError;
                }
            }

        } else {
            $error = "Invalid Student Number.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student OTP Login</title>

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
    padding:20px;
    color:white;
}

.login-box{
    width:100%;
    max-width:470px;
    background:rgba(255,255,255,0.10);
    backdrop-filter:blur(22px);
    padding:45px;
    border-radius:28px;
    border:1px solid rgba(255,255,255,0.12);
    box-shadow:
    0 0 45px rgba(168,85,247,0.22),
    0 25px 80px rgba(0,0,0,0.38);
}

.icon{
    width:72px;
    height:72px;
    border-radius:22px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:32px;
    margin-bottom:25px;
    box-shadow:0 0 28px rgba(168,85,247,.45);
}

h2{
    color:white;
    font-size:32px;
    font-weight:800;
    margin-bottom:8px;
}

.subtitle{
    color:#d8b4fe;
    margin-bottom:28px;
    line-height:1.7;
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
    font-weight:700;
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
    transition:.25s;
}

button:hover{
    transform:translateY(-2px);
}

.error{
    background:#fee2e2;
    color:#991b1b;
    padding:13px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:13px;
    font-weight:800;
    line-height:1.6;
}

.info{
    background:rgba(34,211,238,.12);
    color:#cffafe;
    padding:14px;
    border-radius:14px;
    margin-bottom:20px;
    font-size:13px;
    line-height:1.7;
    border:1px solid rgba(34,211,238,.25);
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

    <div class="icon">🎓</div>

    <h2>Student Login</h2>

    <p class="subtitle">
        Enter your student number. SmartQ will send an OTP code to your registered email.
    </p>

    <div class="info">
        Security upgrade: ID-only login is disabled. Email OTP verification is required.
    </div>

    <?php if ($error !== "") { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST">
        <input type="text" name="studentno" placeholder="Student Number" required autofocus>
        <button type="submit" name="requestOTP">Send OTP</button>
    </form>

    <a href="role_select.php">Back to role selection</a>

</div>

</body>
</html>