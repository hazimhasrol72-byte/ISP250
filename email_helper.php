<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('sendSmartQEmail')) {
    function sendSmartQEmail($toEmail, $toName, $subject, $body)
    {
        $_SESSION['email_error'] = "";

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $_SESSION['email_error'] = "PHPMailer is not installed. Run: composer require phpmailer/phpmailer";
            return false;
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;

            
            $systemEmail = 'hazimhasrol72@gmail.com';
            $appPassword = 'mrbk kske kzvu iwnb';

            $mail->Username = $systemEmail;
            $mail->Password = $appPassword;

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($systemEmail, 'SmartQ Booking System');
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;

        } catch (Exception $e) {
            $_SESSION['email_error'] = $mail->ErrorInfo;
            echo "Email failed: " . $mail->ErrorInfo;
            return false;
        }
    }
}

if (!function_exists('sendOTPEmail')) {
    function sendOTPEmail($toEmail, $toName, $otpCode)
    {
        $subject = "SmartQ Login OTP Code";

        $safeName = htmlspecialchars($toName);
        $safeOTP = htmlspecialchars($otpCode);

        $body = "
        <div style='font-family:Arial,sans-serif;background:#f6f3ff;padding:30px;'>
            <div style='max-width:600px;margin:auto;background:#ffffff;border-radius:18px;padding:30px;border:1px solid #e9d5ff;'>
                <h2 style='color:#6d28d9;margin-top:0;'>SmartQ Login Verification</h2>

                <p>Hello <strong>$safeName</strong>,</p>

                <p>Your One-Time Password (OTP) for SmartQ Booking System login is:</p>

                <div style='font-size:34px;font-weight:bold;letter-spacing:8px;color:#6d28d9;background:#f3e8ff;padding:18px;border-radius:14px;text-align:center;margin:25px 0;'>
                    $safeOTP
                </div>

                <p>This OTP is valid for <strong>5 minutes</strong>. Please do not share this code with anyone.</p>

                <p style='color:#6b7280;font-size:13px;margin-top:30px;'>
                    This is an automated email from SmartQ Booking System.
                </p>
            </div>
        </div>
        ";

        return sendSmartQEmail($toEmail, $toName, $subject, $body);
    }
}
?>