<?php
session_start();
include 'db_connect.php';
include 'token.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: role_select.php");
    exit();
}

if (isset($_POST['bookingToken'])) {

    $bookingID = verifyToken($_POST['bookingToken']);

    if ($bookingID === false) {
        $_SESSION['booking_success'] = "Invalid booking token.";
        header("Location: dashboard.php");
        exit();
    }

    $bookingID = mysqli_real_escape_string($connAppointment, $bookingID);
    $studentno = mysqli_real_escape_string($connAppointment, $_SESSION['studentno']);

    $sql = "
        UPDATE bookings
        SET status = 'Cancelled'
        WHERE bookingID = '$bookingID'
        AND studentno = '$studentno'
        AND status = 'Pending'
    ";

    if (mysqli_query($connAppointment, $sql)) {
        $_SESSION['booking_success'] = "Appointment cancelled successfully!";
    } else {
        $_SESSION['booking_success'] = "Failed to cancel appointment.";
    }

    header("Location: dashboard.php");
    exit();
}

header("Location: dashboard.php");
exit();
?>