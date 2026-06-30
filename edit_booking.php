<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme = $_SESSION['theme'] ?? 'dark';

require_once 'db_connect.php';
require_once 'token.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "student") {
    header("Location: role_select.php");
    exit();
}

$studentno = mysqli_real_escape_string($connAppointment, $_SESSION['studentno']);
$studentname = $_SESSION['studentname'] ?? "Student";

$bookingToken = "";
$bookingID = false;

if (isset($_GET['bookingToken'])) {
    $bookingToken = $_GET['bookingToken'];
    $bookingID = verifyToken($bookingToken);
}

if (isset($_POST['bookingToken'])) {
    $bookingToken = $_POST['bookingToken'];
    $bookingID = verifyToken($bookingToken);
}

if ($bookingID === false) {
    $_SESSION['booking_error'] = "Invalid booking token. Please try again.";
    header("Location: dashboard.php");
    exit();
}

$bookingID = mysqli_real_escape_string($connAppointment, $bookingID);

$bookingQuery = mysqli_query($connAppointment, "
    SELECT *
    FROM bookings
    WHERE bookingID = '$bookingID'
    AND studentno = '$studentno'
    LIMIT 1
");

if (!$bookingQuery || mysqli_num_rows($bookingQuery) == 0) {
    $_SESSION['booking_error'] = "Booking not found or you do not have permission to edit this booking.";
    header("Location: dashboard.php");
    exit();
}

$booking = mysqli_fetch_assoc($bookingQuery);

if ($booking['status'] !== "Pending") {
    $_SESSION['booking_error'] = "Only pending bookings can be edited.";
    header("Location: dashboard.php");
    exit();
}

$lecturers = mysqli_query($connLecturer, "
    SELECT USER_ID, USER_NAME
    FROM user
    WHERE USER_DEPARTMENT = 'C0001'
       OR USER_BKJABHAKIKI = 'C0001'
    ORDER BY USER_NAME ASC
");

if (isset($_POST['updateBooking'])) {

    $serviceName = mysqli_real_escape_string($connAppointment, $_POST['serviceName'] ?? '');
    $lecturerID = mysqli_real_escape_string($connAppointment, $_POST['lecturerID'] ?? '');
    $place = mysqli_real_escape_string($connAppointment, $_POST['place'] ?? '');
    $bookingDate = mysqli_real_escape_string($connAppointment, $_POST['bookingDate'] ?? '');
    $startTime = mysqli_real_escape_string($connAppointment, $_POST['startTime'] ?? '');
    $endTime = mysqli_real_escape_string($connAppointment, $_POST['endTime'] ?? '');

    $openingTime = "08:00";
    $closingTime = "18:00";

    if ($serviceName == "" || $lecturerID == "" || $place == "" || $bookingDate == "" || $startTime == "" || $endTime == "") {
        $_SESSION['booking_error'] = "Please complete all booking details.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }

    if ($bookingDate < date("Y-m-d")) {
        $_SESSION['booking_error'] = "You cannot choose a past date.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }

    if ($startTime < $openingTime || $endTime > $closingTime) {
        $_SESSION['booking_error'] = "Booking is only available from 8:00 AM until 6:00 PM.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }

    if ($startTime >= $endTime) {
        $_SESSION['booking_error'] = "End time must be later than start time.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }

    $lecturerQuery = mysqli_query($connLecturer, "
        SELECT USER_NAME
        FROM user
        WHERE USER_ID = '$lecturerID'
        LIMIT 1
    ");

    if (!$lecturerQuery || mysqli_num_rows($lecturerQuery) == 0) {
        $_SESSION['booking_error'] = "Lecturer not found. Please choose another lecturer.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }

    $lecturerData = mysqli_fetch_assoc($lecturerQuery);
    $lecturerName = mysqli_real_escape_string($connAppointment, $lecturerData['USER_NAME']);

    $checkBooking = mysqli_query($connAppointment, "
        SELECT bookingID
        FROM bookings
        WHERE bookingID != '$bookingID'
        AND bookingDate = '$bookingDate'
        AND status != 'Cancelled'
        AND status != 'Rejected'
        AND ('$startTime' < endTime AND '$endTime' > startTime)
        AND (
            studentno = '$studentno'
            OR lecturerID = '$lecturerID'
            OR (
                '$place' = 'AEC Room'
                AND place = 'AEC Room'
            )
        )
        LIMIT 1
    ");

    if ($checkBooking && mysqli_num_rows($checkBooking) > 0) {
        $_SESSION['booking_error'] = "This updated slot is unavailable. Please choose another time, lecturer, or room.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }

    $updateBooking = mysqli_query($connAppointment, "
        UPDATE bookings
        SET
            lecturerID = '$lecturerID',
            lecturerName = '$lecturerName',
            serviceName = '$serviceName',
            place = '$place',
            bookingDate = '$bookingDate',
            startTime = '$startTime',
            endTime = '$endTime',
            status = 'Pending'
        WHERE bookingID = '$bookingID'
        AND studentno = '$studentno'
        AND status = 'Pending'
    ");

    if ($updateBooking) {
        $_SESSION['booking_success_title'] = "Booking Updated!";
        $_SESSION['booking_success'] = "Your booking details have been updated successfully and are still pending lecturer approval.";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['booking_error'] = "Failed to update booking. Please try again.";
        header("Location: edit_booking.php?bookingToken=" . urlencode($bookingToken));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Booking | SmartQ</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
    radial-gradient(circle at top left,rgba(168,85,247,.25),transparent 30%),
    radial-gradient(circle at bottom right,rgba(124,58,237,.18),transparent 35%),
    linear-gradient(135deg,#170022,#26003f,#12001f);
    color:white;
}

body.light-mode{
    background:
    radial-gradient(circle at top left,rgba(168,85,247,.16),transparent 30%),
    radial-gradient(circle at bottom right,rgba(124,58,237,.10),transparent 35%),
    linear-gradient(135deg,#f8f3ff,#eee6ff,#ffffff);
    color:#1f1235;
}

.layout{
    display:grid;
    grid-template-columns:320px 1fr;
    min-height:100vh;
    transition:.3s ease;
}

.sidebar{
    width:320px;
    background:rgba(255,255,255,.08);
    backdrop-filter:blur(22px);
    border-right:1px solid rgba(255,255,255,.12);
    padding:35px 24px;
    transition:.3s ease;
}

.main{
    padding:45px 42px 0 42px;
}

.page-wrapper{
    width:100%;
}

.edit-container{
    width:100%;
    max-width:1180px;
    margin:0 auto;
    display:grid;
    grid-template-columns:.9fr 1.2fr;
    border-radius:32px;
    overflow:hidden;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    box-shadow:0 0 50px rgba(168,85,247,.20),0 25px 80px rgba(0,0,0,.38);
}

.info-panel{
    background:linear-gradient(145deg,#7c3aed,#9333ea,#4c1d95);
    padding:60px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    position:relative;
    overflow:hidden;
}

.info-panel::before{
    content:"";
    position:absolute;
    width:420px;
    height:420px;
    border-radius:50%;
    background:rgba(255,255,255,.08);
    top:-170px;
    right:-170px;
}

.info-panel::after{
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    border-radius:50%;
    background:rgba(255,255,255,.06);
    bottom:-100px;
    left:-100px;
}

.info-content{
    position:relative;
    z-index:2;
}

.info-icon{
    width:86px;
    height:86px;
    border-radius:26px;
    background:rgba(255,255,255,.16);
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:38px;
    margin-bottom:30px;
}

.info-panel h1{
    font-size:48px;
    line-height:1.1;
    font-weight:800;
    margin-bottom:20px;
}

.info-panel p{
    color:#f3e8ff;
    font-size:16px;
    line-height:1.8;
    max-width:430px;
}

.current-box{
    margin-top:28px;
    padding:22px;
    border-radius:22px;
    background:rgba(255,255,255,.13);
    border:1px solid rgba(255,255,255,.15);
}

.current-box h3{
    font-size:20px;
    margin-bottom:12px;
}

.current-box p{
    font-size:14px;
    line-height:1.8;
}

.form-panel{
    padding:55px 60px;
}

.form-panel h2{
    font-size:38px;
    font-weight:800;
    margin-bottom:10px;
}

.subtitle{
    color:#d8b4fe;
    margin-bottom:34px;
    line-height:1.7;
}

.form-group{
    margin-bottom:20px;
}

label{
    display:block;
    margin-bottom:9px;
    font-size:13px;
    font-weight:800;
    color:#f3e8ff;
    text-transform:uppercase;
    letter-spacing:.8px;
}

input,
select{
    width:100%;
    height:56px;
    border:none;
    outline:none;
    border-radius:16px;
    background:rgba(255,255,255,.09);
    border:1px solid rgba(255,255,255,.12);
    color:white;
    padding:0 18px;
    font-size:15px;
    font-weight:600;
}

select option{
    color:#1f1235;
}

.row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.btn-row{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:16px;
    margin-top:18px;
}

.update-btn,
.cancel-btn{
    height:58px;
    border:none;
    border-radius:18px;
    font-size:16px;
    font-weight:800;
    cursor:pointer;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:.25s;
}

.update-btn{
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    box-shadow:0 0 25px rgba(168,85,247,.45);
}

.cancel-btn{
    background:rgba(255,255,255,.10);
    color:white;
    border:1px solid rgba(255,255,255,.15);
}

.note{
    margin-top:20px;
    padding:18px 20px;
    border-radius:18px;
    background:rgba(250,204,21,.14);
    border:1px solid rgba(250,204,21,.35);
    color:#fde68a;
    font-size:14px;
    line-height:1.7;
    font-weight:700;
}

.nav-title{
    color:#c4b5fd;
    font-size:13px;
    margin-bottom:18px;
    text-transform:uppercase;
    letter-spacing:1px;
}

.nav{
    display:flex;
    flex-direction:column;
}

.nav a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:17px 24px;
    margin-bottom:13px;
    border-radius:18px;
    color:white;
    text-decoration:none;
    font-weight:800;
    transition:.25s;
}

.nav a.active,
.nav a:hover{
    background:linear-gradient(90deg,rgba(168,85,247,.48),rgba(124,58,237,.25));
    box-shadow:inset 4px 0 #c084fc,0 0 22px rgba(168,85,247,.18);
}

.nav-icon{
    font-size:20px;
    min-width:24px;
    text-align:center;
}

.nav-text{
    display:inline-block;
}

body.light-mode .sidebar,
body.light-mode .edit-container{
    background:white;
    border:1px solid #e9d5ff;
}

body.light-mode .form-panel h2{
    color:#1f1235;
}

body.light-mode .subtitle,
body.light-mode label{
    color:#6d28d9;
}

body.light-mode input,
body.light-mode select{
    background:#f3e8ff;
    border:1px solid #e9d5ff;
    color:#1f1235;
}

body.light-mode .cancel-btn{
    background:#f3e8ff;
    color:#6d28d9;
    border:1px solid #e9d5ff;
}

body.light-mode .note{
    background:#fffbeb;
    border:1px solid #fde68a;
    color:#92400e;
}

body.light-mode .nav a{
    color:#1f1235;
}

body.light-mode .nav a.active,
body.light-mode .nav a:hover{
    color:white;
}

body.sidebar-collapsed .layout{
    grid-template-columns:90px 1fr;
}

body.sidebar-collapsed .sidebar{
    width:90px;
    padding:35px 18px;
}

body.sidebar-collapsed .nav-title{
    display:none;
}

body.sidebar-collapsed .nav a{
    width:54px;
    height:54px;
    padding:0;
    justify-content:center;
}

body.sidebar-collapsed .nav-text{
    display:none;
}

@media(max-width:950px){
    .layout,
    .edit-container{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:100%;
    }

    .main{
        padding:30px 20px 0 20px;
    }

    .info-panel,
    .form-panel{
        padding:42px 28px;
    }

    .info-panel h1{
        font-size:38px;
    }

    .row,
    .btn-row{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body class="<?php echo ($theme == 'light') ? 'light-mode' : ''; ?>">

<?php include 'topbar.php'; ?>

<div class="layout">

    <aside class="sidebar">
        <?php include 'navbar.php'; ?>
    </aside>

    <main class="main">

        <div class="page-wrapper">

            <div class="edit-container">

                <div class="info-panel">
                    <div class="info-content">
                        <div class="info-icon">✎</div>

                        <h1>Edit Booking</h1>

                        <p>
                            You can edit your appointment while it is still pending.
                            Once the lecturer approves or rejects the request, editing will be disabled.
                        </p>

                        <div class="current-box">
                            <h3>Current Booking</h3>
                            <p>
                                Lecturer: <?php echo htmlspecialchars($booking['lecturerName']); ?><br>
                                Service: <?php echo htmlspecialchars($booking['serviceName']); ?><br>
                                Room: <?php echo htmlspecialchars($booking['place']); ?><br>
                                Date: <?php echo date("d M Y", strtotime($booking['bookingDate'])); ?><br>
                                Time:
                                <?php echo date("h:i A", strtotime($booking['startTime'])); ?>
                                -
                                <?php echo date("h:i A", strtotime($booking['endTime'])); ?><br>
                                Status: <?php echo htmlspecialchars($booking['status']); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="form-panel">

                    <h2>Update Details</h2>

                    <p class="subtitle">
                        Change your booking details below. The system will check availability before saving.
                    </p>

                    <form method="POST" id="editBookingForm">

                        <input type="hidden" name="bookingToken" value="<?php echo htmlspecialchars($bookingToken); ?>">

                        <div class="form-group">
                            <label>Service</label>

                            <select name="serviceName" required>
                                <?php
                                $services = [
                                    "Consultation",
                                    "Presentation",
                                    "Exam Retake",
                                    "Project Discussion",
                                    "Assignment Guidance",
                                    "Internship Discussion",
                                    "Other"
                                ];

                                foreach ($services as $service) {
                                    $selected = ($booking['serviceName'] == $service) ? "selected" : "";
                                    echo "<option value='".htmlspecialchars($service)."' $selected>".htmlspecialchars($service)."</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Lecturer</label>

                            <select name="lecturerID" required>
                                <?php if ($lecturers && mysqli_num_rows($lecturers) > 0) { ?>
                                    <?php while($lec = mysqli_fetch_assoc($lecturers)){ ?>
                                        <option value="<?php echo htmlspecialchars($lec['USER_ID']); ?>"
                                            <?php if($booking['lecturerID'] == $lec['USER_ID']) echo "selected"; ?>>
                                            <?php echo htmlspecialchars($lec['USER_NAME']); ?>
                                        </option>
                                    <?php } ?>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Room</label>

                            <select name="place" required>
                                <option value="AEC Room" <?php if($booking['place'] == "AEC Room") echo "selected"; ?>>
                                    AEC Room
                                </option>
                                <option value="Office" <?php if($booking['place'] == "Office") echo "selected"; ?>>
                                    Office
                                </option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Date</label>

                            <input type="date"
                                   name="bookingDate"
                                   min="<?php echo date('Y-m-d'); ?>"
                                   value="<?php echo htmlspecialchars($booking['bookingDate']); ?>"
                                   required>
                        </div>

                        <div class="row">
                            <div class="form-group">
                                <label>Start Time</label>

                                <input type="time"
                                       name="startTime"
                                       min="08:00"
                                       max="18:00"
                                       step="300"
                                       value="<?php echo date('H:i', strtotime($booking['startTime'])); ?>"
                                       required>
                            </div>

                            <div class="form-group">
                                <label>End Time</label>

                                <input type="time"
                                       name="endTime"
                                       min="08:00"
                                       max="18:00"
                                       step="300"
                                       value="<?php echo date('H:i', strtotime($booking['endTime'])); ?>"
                                       required>
                            </div>
                        </div>

                        <div class="btn-row">
                            <button type="submit" name="updateBooking" class="update-btn">
                                Save Changes
                            </button>

                            <a href="dashboard.php" class="cancel-btn">
                                Cancel
                            </a>
                        </div>

                        <div class="note">
                            Booking time is only allowed from 08:00 AM until 06:00 PM.
                            Your updated booking will remain pending until approved by the lecturer.
                        </div>

                    </form>

                </div>

            </div>

        </div>

        <?php include 'footer.php'; ?>

    </main>

</div>

<?php
if (isset($_SESSION['booking_error'])) {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({
                title:'Unable To Edit',
                text: ".json_encode($_SESSION['booking_error']).",
                icon:'error',
                confirmButtonColor:'#a855f7',
                confirmButtonText:'Okay'
            });
        });
    </script>";
    unset($_SESSION['booking_error']);
}
?>

<script>
document.addEventListener("DOMContentLoaded", function(){

    document.body.classList.remove("sidebar-collapsed");

    const toggleBtn = document.getElementById("sidebarToggle");

    if(toggleBtn){
        toggleBtn.addEventListener("click", function(){
            document.body.classList.toggle("sidebar-collapsed");
        });
    }

    const form = document.getElementById("editBookingForm");
    const startInput = document.querySelector("input[name='startTime']");
    const endInput = document.querySelector("input[name='endTime']");

    if(form){
        form.addEventListener("submit", function(e){

            const startTime = startInput.value;
            const endTime = endInput.value;

            if(startTime < "08:00" || endTime > "18:00"){
                e.preventDefault();

                Swal.fire({
                    title: "Invalid Booking Time",
                    text: "Booking is only available from 8:00 AM until 6:00 PM.",
                    icon: "warning",
                    confirmButtonColor: "#a855f7",
                    confirmButtonText: "Choose Again"
                });

                return false;
            }

            if(startTime >= endTime){
                e.preventDefault();

                Swal.fire({
                    title: "Invalid Time Range",
                    text: "End time must be later than start time.",
                    icon: "warning",
                    confirmButtonColor: "#a855f7",
                    confirmButtonText: "Fix Time"
                });

                return false;
            }

        });
    }

});
</script>

</body>
</html> 