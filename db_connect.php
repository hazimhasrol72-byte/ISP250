<?php
$servername = "localhost";
$username   = "root";
$password   = "";


$connStudent = new mysqli($servername, $username, $password, "dbstudentsphg");
$connLecturer = new mysqli($servername, $username, $password, "classbook_backup_jengka");
$connAppointment = new mysqli($servername, $username, $password, "smart_appointment");

if ($connStudent->connect_error) {
    die("Student Database Connection Failed: " . $connStudent->connect_error);
}

if ($connLecturer->connect_error) {
    die("Lecturer Database Connection Failed: " . $connLecturer->connect_error);
}

if ($connAppointment->connect_error) {
    die("Appointment Database Connection Failed: " . $connAppointment->connect_error);
}

$connStudent->set_charset("utf8mb4");
$connLecturer->set_charset("utf8mb4");
$connAppointment->set_charset("utf8mb4");
?>
