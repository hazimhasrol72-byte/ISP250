<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';
require_once 'email_helper.php';

$error = "";

function maskEmailLecturer($email)
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

if (isset($_GET['error'])) {
    if ($_GET['error'] === "invalid") {
        $error = "Invalid Lecturer ID.";
    } elseif ($_GET['error'] === "noadmin") {
        $error = "Your account does not have admin access.";
    }
}

if (isset($_POST['btnLogin']) || isset($_POST['adminLogin'])) {

    $lecturerID = trim($_POST['lecturerID'] ?? '');
    $lecturerIDSafe = mysqli_real_escape_string($connLecturer, $lecturerID);

    if ($lecturerID === '') {
        $error = "Please enter your lecturer ID.";
    } else {

        $sql = "
            SELECT *
            FROM user
            WHERE USER_ID = '$lecturerIDSafe'
            LIMIT 1
        ";

        $result = mysqli_query($connLecturer, $sql);

        if ($result && mysqli_num_rows($result) > 0) {

            $row = mysqli_fetch_assoc($result);

            $lecturerEmail = $row['USER_EMAIL'] ?? '';
            $lecturerName = $row['USER_NAME'] ?? 'Lecturer';

            if ($lecturerEmail === '') {
                $error = "This lecturer account does not have an email in the database.";
            } elseif (!filter_var($lecturerEmail, FILTER_VALIDATE_EMAIL)) {
                $error = "Invalid lecturer email format in database.";
            } else {

                $checkAdmin = "
                    SELECT *
                    FROM role_assign ra
                    INNER JOIN roles r ON ra.roleID = r.roleID
                    WHERE ra.lecturerID = '$lecturerIDSafe'
                    AND r.roleTitle = 'Admin'
                    LIMIT 1
                ";

                $adminResult = mysqli_query($connAppointment, $checkAdmin);
                $isAdmin = $adminResult && mysqli_num_rows($adminResult) > 0;

                if (isset($_POST['adminLogin']) && !$isAdmin) {
                    header("Location: lecturer_login.php?error=noadmin");
                    exit();
                }

                $finalRole = $isAdmin ? "admin" : "lecturer";

                if (isset($_POST['btnLogin'])) {
                    $finalRole = $isAdmin ? "admin" : "lecturer";
                }

                if (isset($_POST['adminLogin'])) {
                    $finalRole = "admin";
                }

                $otpCode = (string) random_int(100000, 999999);

                $_SESSION['pending_login'] = [
                    'login_type' => 'lecturer',
                    'final_role' => $finalRole,
                    'id' => $row['USER_ID'],
                    'name' => $lecturerName,
                    'email' => $lecturerEmail,
                    'otp_hash' => password_hash($otpCode, PASSWORD_DEFAULT),
                    'expires_at' => time() + 300,
                    'attempts' => 0,
                    'last_sent_at' => time(),
                    'is_admin' => $isAdmin
                ];

                $sent = sendOTPEmail($lecturerEmail, $lecturerName, $otpCode);

                if ($sent) {
                    $_SESSION['otp_notice'] = "OTP has been sent to " . maskEmailLecturer($lecturerEmail) . ".";
                    header("Location: verify_otp.php");
                    exit();
                } else {
                    unset($_SESSION['pending_login']);
                    $emailError = $_SESSION['email_error'] ?? "Unknown email error.";
                    $error = "Failed to send OTP email. Error: " . $emailError;
                }
            }

        } else {
            header("Location: lecturer_login.php?error=invalid");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Lecturer OTP Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Montserrat',sans-serif;
}

body{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
        radial-gradient(circle at top left, rgba(192,132,252,.35), transparent 28%),
        radial-gradient(circle at bottom right, rgba(217,70,239,.25), transparent 32%),
        linear-gradient(135deg,#140021,#1e0033,#250042,#160024);
    color:white;
    padding:20px;
}

.login-card{
    width:100%;
    max-width:460px;
    padding:45px;
    border-radius:30px;
    background:rgba(255,255,255,.08);
    backdrop-filter:blur(22px);
    border:1px solid rgba(255,255,255,.12);
    box-shadow:
        0 0 45px rgba(168,85,247,.22),
        0 25px 80px rgba(0,0,0,.38);
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

h1{
    font-size:34px;
    font-weight:800;
    margin-bottom:10px;
}

.subtitle{
    color:#d8b4fe;
    margin-bottom:28px;
    font-size:14px;
    line-height:1.7;
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

label{
    display:block;
    margin-bottom:9px;
    font-size:14px;
    font-weight:700;
}

input{
    width:100%;
    height:56px;
    padding:0 16px;
    border-radius:15px;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.08);
    color:white;
    font-size:15px;
    margin-bottom:22px;
    font-weight:700;
}

input::placeholder{
    color:rgba(255,255,255,.55);
}

input:focus{
    outline:none;
    border-color:#c084fc;
    box-shadow:0 0 0 4px rgba(192,132,252,.14);
}

button{
    width:100%;
    height:56px;
    border:none;
    border-radius:15px;
    color:white;
    font-size:16px;
    font-weight:800;
    cursor:pointer;
    margin-bottom:14px;
    transition:.25s;
}

button:hover{
    transform:translateY(-2px);
}

.lecturer-btn{
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    box-shadow:0 0 22px rgba(168,85,247,.45);
}

.admin-btn{
    background:linear-gradient(135deg,#facc15,#f59e0b);
    color:#1f2937;
    box-shadow:0 0 22px rgba(250,204,21,.35);
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

.back{
    display:block;
    text-align:center;
    margin-top:12px;
    color:#d8b4fe;
    text-decoration:none;
    font-weight:700;
}
</style>
</head>

<body>

<div class="login-card">

    <div class="icon">👨‍🏫</div>

    <h1>Lecturer Login</h1>

    <p class="subtitle">
        Enter your lecturer ID. SmartQ will send an OTP code to your registered email.
    </p>

    <div class="info">
        Security upgrade: Lecturer/Admin access now requires email OTP verification.
    </div>

    <?php if ($error !== "") { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <form method="POST" action="lecturer_login.php">

        <label>Lecturer ID</label>

        <input type="text" name="lecturerID" placeholder="Example: 090667" required autofocus>

        <button type="submit" name="btnLogin" class="lecturer-btn">
            Send Lecturer OTP
        </button>

        <button type="submit" name="adminLogin" class="admin-btn">
            ⭐ Send Admin OTP
        </button>

    </form>

    <a href="role_select.php" class="back">Back to role selection</a>

</div>

</body>
</html>