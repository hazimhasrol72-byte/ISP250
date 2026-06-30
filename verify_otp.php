<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'email_helper.php';

$error = "";
$notice = $_SESSION['otp_notice'] ?? "";

if (!isset($_SESSION['pending_login'])) {
    header("Location: role_select.php?error=no_otp_session");
    exit();
}

$pending = $_SESSION['pending_login'];

if (time() > $pending['expires_at']) {
    unset($_SESSION['pending_login']);
    $_SESSION['otp_expired'] = "Your OTP has expired. Please request a new OTP.";
    header("Location: role_select.php");
    exit();
}

function maskEmailOTP($email)
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

if (isset($_POST['verifyOTP'])) {

    $otpInput = trim($_POST['otp'] ?? '');

    if ($otpInput === '') {
        $error = "Please enter the OTP code.";
    } elseif (!preg_match('/^[0-9]{6}$/', $otpInput)) {
        $error = "OTP must be 6 digits.";
    } else {

        $_SESSION['pending_login']['attempts']++;

        if ($_SESSION['pending_login']['attempts'] > 5) {
            unset($_SESSION['pending_login']);
            $_SESSION['otp_expired'] = "Too many incorrect attempts. Please request a new OTP.";
            header("Location: role_select.php");
            exit();
        }

        if (password_verify($otpInput, $_SESSION['pending_login']['otp_hash'])) {

            $loginData = $_SESSION['pending_login'];
            unset($_SESSION['pending_login']);
            unset($_SESSION['otp_notice']);

            session_regenerate_id(true);

            if ($loginData['login_type'] === 'student') {

                $_SESSION['role'] = "student";
                $_SESSION['studentno'] = $loginData['id'];
                $_SESSION['studentname'] = $loginData['name'];

                header("Location: dashboard.php");
                exit();

            } elseif ($loginData['login_type'] === 'lecturer') {

                $_SESSION['role'] = $loginData['final_role'];
                $_SESSION['lecturerID'] = $loginData['id'];
                $_SESSION['lecturerName'] = $loginData['name'];

                if ($loginData['final_role'] === "admin") {
                    header("Location: adminInterface.php");
                    exit();
                } else {
                    header("Location: lecturerInterface.php");
                    exit();
                }

            } else {
                $error = "Invalid login session.";
            }

        } else {
            $remaining = 5 - $_SESSION['pending_login']['attempts'];
            $error = "Incorrect OTP code. Attempts remaining: " . $remaining;
        }
    }
}

if (isset($_POST['resendOTP'])) {

    $lastSent = $_SESSION['pending_login']['last_sent_at'] ?? 0;

    if (time() - $lastSent < 60) {
        $error = "Please wait 60 seconds before requesting a new OTP.";
    } else {

        $newOTP = (string) random_int(100000, 999999);

        $_SESSION['pending_login']['otp_hash'] = password_hash($newOTP, PASSWORD_DEFAULT);
        $_SESSION['pending_login']['expires_at'] = time() + 300;
        $_SESSION['pending_login']['attempts'] = 0;
        $_SESSION['pending_login']['last_sent_at'] = time();

        $sent = sendOTPEmail(
            $_SESSION['pending_login']['email'],
            $_SESSION['pending_login']['name'],
            $newOTP
        );

        if ($sent) {
            $notice = "A new OTP has been sent to " . maskEmailOTP($_SESSION['pending_login']['email']) . ".";
            $_SESSION['otp_notice'] = $notice;
        } else {
            $emailError = $_SESSION['email_error'] ?? "Unknown email error.";
            $error = "Failed to resend OTP. Error: " . $emailError;
        }
    }
}

$maskedEmail = maskEmailOTP($_SESSION['pending_login']['email']);
$expiresIn = max($_SESSION['pending_login']['expires_at'] - time(), 0);
?>

<!DOCTYPE html>
<html>
<head>
<title>Verify OTP</title>
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

.otp-card{
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
    margin-bottom:25px;
    font-size:14px;
    line-height:1.7;
}

.notice{
    background:rgba(34,211,238,.12);
    color:#cffafe;
    padding:14px;
    border-radius:14px;
    margin-bottom:18px;
    font-size:13px;
    line-height:1.7;
    border:1px solid rgba(34,211,238,.25);
    font-weight:700;
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

.timer{
    background:rgba(250,204,21,.14);
    color:#fef3c7;
    padding:13px;
    border-radius:12px;
    margin-bottom:18px;
    font-size:13px;
    font-weight:800;
    border:1px solid rgba(250,204,21,.25);
    text-align:center;
}

label{
    display:block;
    margin-bottom:9px;
    font-size:14px;
    font-weight:700;
}

input{
    width:100%;
    height:60px;
    padding:0 16px;
    border-radius:15px;
    border:1px solid rgba(255,255,255,.12);
    background:rgba(255,255,255,.08);
    color:white;
    font-size:22px;
    margin-bottom:22px;
    font-weight:800;
    text-align:center;
    letter-spacing:8px;
}

input::placeholder{
    color:rgba(255,255,255,.45);
    letter-spacing:2px;
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

.verify-btn{
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    box-shadow:0 0 22px rgba(168,85,247,.45);
}

.resend-btn{
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.16);
    color:#e9d5ff;
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

<div class="otp-card">

    <div class="icon">🔐</div>

    <h1>Verify OTP</h1>

    <p class="subtitle">
        Enter the 6-digit OTP code sent to your registered email.
    </p>

    <div class="notice">
        OTP has been sent to <?php echo htmlspecialchars($maskedEmail); ?>.
    </div>

    <?php if ($notice !== "") { ?>
        <div class="notice"><?php echo htmlspecialchars($notice); ?></div>
    <?php } ?>

    <?php if ($error !== "") { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>

    <div class="timer">
        OTP expires in <span id="countdown"><?php echo $expiresIn; ?></span> seconds
    </div>

    <form method="POST" action="verify_otp.php">

        <label>OTP Code</label>

        <input type="text" name="otp" maxlength="6" placeholder="123456" required autofocus>

        <button type="submit" name="verifyOTP" class="verify-btn">
            Verify OTP
        </button>

    </form>

    <form method="POST" action="verify_otp.php">
        <button type="submit" name="resendOTP" class="resend-btn">
            Resend OTP
        </button>
    </form>

    <a href="role_select.php" class="back">Back to role selection</a>

</div>

<script>
let seconds = <?php echo (int)$expiresIn; ?>;
const countdown = document.getElementById("countdown");

const timer = setInterval(() => {
    seconds--;

    if (seconds <= 0) {
        countdown.textContent = "0";
        clearInterval(timer);
        return;
    }

    countdown.textContent = seconds;
}, 1000);
</script>

</body>
</html>