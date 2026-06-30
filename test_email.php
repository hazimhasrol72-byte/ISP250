<?php
session_start();
require_once 'email_helper.php';

$toEmail = "hazimhasrol72@gmail.com";
$toName = "Hazim";

$sent = sendOTPEmail($toEmail, $toName, "123456");

if ($sent) {
    echo "Email sent successfully.";
} else {
    echo "Email failed.<br>";
    echo "Error: " . ($_SESSION['email_error'] ?? "Unknown error");
}
?>