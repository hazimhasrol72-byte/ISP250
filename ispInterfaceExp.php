<?php
session_start();
include 'db_connect.php';
include 'token.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: role_select.php");
    exit();
}

if (isset($_POST['submitBooking'])) {

    $studentno = $_SESSION['studentno'];
    $studentname = $_SESSION['studentname'];

    $serviceName = mysqli_real_escape_string($connAppointment, $_POST['serviceName']);
    $lecturerID = mysqli_real_escape_string($connAppointment, $_POST['lecturerID']);
    $place = mysqli_real_escape_string($connAppointment, $_POST['place']);
    $bookingDate = mysqli_real_escape_string($connAppointment, $_POST['bookingDate']);
    $startTime = mysqli_real_escape_string($connAppointment, $_POST['startTime']);
    $endTime = mysqli_real_escape_string($connAppointment, $_POST['endTime']);

    if ($startTime >= $endTime) {
        $_SESSION['booking_error'] = "End time must be later than start time.";
        header("Location: ispInterfaceExp.php");
        exit();
    }

    $lecturerQuery = mysqli_query($connLecturer, "
        SELECT USER_NAME 
        FROM user 
        WHERE USER_ID = '$lecturerID'
    ");

    $lecturerData = mysqli_fetch_assoc($lecturerQuery);

    if (!$lecturerData) {
        $_SESSION['booking_error'] = "Lecturer not found. Please choose another lecturer.";
        header("Location: ispInterfaceExp.php");
        exit();
    }

    $lecturerName = mysqli_real_escape_string($connAppointment, $lecturerData['USER_NAME']);

    /*
        Booking Rules:
        1. Same student cannot book another appointment at the same time.
        2. Same lecturer cannot be booked at the same time.
        3. AEC Room cannot be double-booked at the same time.
        4. Office can be booked at the same time if lecturer is different.
    */

    $checkBooking = "
        SELECT * FROM bookings
        WHERE bookingDate = '$bookingDate'
        AND status != 'Cancelled'
        AND ('$startTime' < endTime AND '$endTime' > startTime)
        AND (
            studentno = '$studentno'
            OR lecturerID = '$lecturerID'
            OR (
                '$place' = 'AEC Room'
                AND place = 'AEC Room'
            )
        )
    ";

    $checkResult = mysqli_query($connAppointment, $checkBooking);

    if (mysqli_num_rows($checkResult) > 0) {
        $_SESSION['booking_error'] = "This booking is not available. Please choose another time, lecturer, or place.";
        header("Location: ispInterfaceExp.php");
        exit();
    }

    $insertBooking = "
        INSERT INTO bookings
        (studentno, studentname, lecturerID, lecturerName, serviceName, place, bookingDate, startTime, endTime, status)
        VALUES
        ('$studentno', '$studentname', '$lecturerID', '$lecturerName', '$serviceName', '$place', '$bookingDate', '$startTime', '$endTime', 'Pending')
    ";

    if (mysqli_query($connAppointment, $insertBooking)) {
        $_SESSION['booking_success'] = "Your booking request has been submitted successfully!";
        header("Location: dashboard.php");
        exit();
    } else {
        die("Booking insert failed: " . mysqli_error($connAppointment));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Smart Appointment | New Booking</title>

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
    background:
    radial-gradient(circle at top left, rgba(192,132,252,0.35), transparent 28%),
    radial-gradient(circle at top right, rgba(168,85,247,0.30), transparent 30%),
    radial-gradient(circle at bottom left, rgba(139,92,246,0.28), transparent 30%),
    radial-gradient(circle at bottom right, rgba(217,70,239,0.24), transparent 32%),
    linear-gradient(135deg,#140021 0%,#1e0033 25%,#250042 50%,#160024 100%);
    min-height:100vh;
    overflow-x:hidden;
}

.dashboard-header{
    width:100%;
    padding:18px 45px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:rgba(255,255,255,0.06);
    backdrop-filter:blur(14px);
    border-bottom:1px solid rgba(255,255,255,0.08);
    box-shadow:0 0 35px rgba(168,85,247,0.08);
}

.logo{
    font-size:22px;
    font-weight:800;
    color:white;
    letter-spacing:-0.6px;
    text-shadow:0 0 18px rgba(192,132,252,0.5);
}

.logo-section{
    display:flex;
    align-items:center;
    gap:28px;
}

.top-nav{
    display:flex;
    gap:14px;
}

.nav-btn{
    text-decoration:none;
    padding:11px 18px;
    border-radius:14px;
    background:rgba(255,255,255,0.08);
    color:white;
    font-size:14px;
    font-weight:700;
    transition:0.25s;
    border:1px solid rgba(255,255,255,0.08);
}

.nav-btn:hover,
.nav-btn.active{
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    box-shadow:0 0 18px rgba(168,85,247,0.4);
}

.student-profile{
    display:flex;
    align-items:center;
    gap:14px;
}

.profile-circle{
    width:52px;
    height:52px;
    border-radius:50%;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:18px;
    font-weight:800;
    box-shadow:0 0 24px rgba(168,85,247,0.45);
}

.profile-info{
    color:white;
}

.profile-info span{
    font-size:13px;
    opacity:0.7;
}

.main-wrapper{
    width:100%;
    min-height:calc(100vh - 90px);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
}

.content-split{
    width:100%;
    max-width:1180px;
    min-height:680px;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(24px);
    border-radius:32px;
    overflow:hidden;
    display:flex;
    border:1px solid rgba(255,255,255,0.08);
    box-shadow:
    0 0 0 1px rgba(255,255,255,0.03),
    0 0 45px rgba(168,85,247,0.18),
    0 0 90px rgba(192,132,252,0.12),
    0 25px 80px rgba(0,0,0,0.38);
}

.visual-panel{
    flex:0.95;
    background:linear-gradient(145deg,rgba(168,85,247,0.92),rgba(99,102,241,0.88),rgba(139,92,246,0.92));
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    padding:60px;
    position:relative;
    overflow:hidden;
    text-align:center;
}

.visual-panel h1{
    color:white;
    font-size:52px;
    font-weight:800;
    line-height:1.1;
    margin-bottom:20px;
}

.visual-panel p{
    color:white;
    font-size:19px;
    line-height:1.7;
    max-width:420px;
    opacity:0.92;
}

.form-panel{
    flex:1.05;
    padding:55px 60px;
}

.form-panel h2{
    color:white;
    font-size:42px;
    font-weight:800;
    margin-bottom:30px;
    letter-spacing:-1px;
}

label{
    display:block;
    margin-top:18px;
    margin-bottom:9px;
    color:white;
    font-size:14px;
    font-weight:700;
}

select,
input[type="date"],
input[type="time"]{
    width:100%;
    height:58px;
    padding:0 18px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,0.08);
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(12px);
    color:white;
    font-size:15px;
    font-weight:500;
    transition:0.25s;
}

select option{
    color:black;
}

select:focus,
input:focus{
    outline:none;
    border-color:#c084fc;
    background:rgba(255,255,255,0.12);
    box-shadow:
    0 0 0 4px rgba(192,132,252,0.14),
    0 0 18px rgba(168,85,247,0.25);
}

.time-group{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:26px;
    margin-top:5px;
}

button{
    width:100%;
    height:60px;
    margin-top:35px;
    border:none;
    border-radius:16px;
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    color:white;
    font-size:16px;
    font-weight:700;
    cursor:pointer;
    transition:0.28s;
    box-shadow:
    0 0 18px rgba(168,85,247,0.55),
    0 0 35px rgba(192,132,252,0.38),
    0 12px 28px rgba(124,58,237,0.35);
}

button:hover{
    transform:translateY(-2px);
}

@media(max-width:950px){
    .content-split{
        flex-direction:column;
    }

    .visual-panel,
    .form-panel{
        padding:40px;
    }

    .time-group{
        grid-template-columns:1fr;
    }

    .dashboard-header{
        flex-direction:column;
        gap:20px;
    }

    .logo-section{
        flex-direction:column;
    }
}
</style>
</head>

<body>

<header class="dashboard-header">

    <div class="logo-section">
        <div class="logo">SMART APPOINTMENT</div>

        <div class="top-nav">
            <a href="ispInterfaceExp.php" class="nav-btn active">+ New Booking</a>
            <a href="dashboard.php" class="nav-btn">My Dashboard</a>
        </div>
    </div>

    <div class="student-profile">
        <div class="profile-circle">
            <?php echo strtoupper(substr($_SESSION['studentname'],0,1)); ?>
        </div>

        <div class="profile-info">
            <strong><?php echo $_SESSION['studentname']; ?></strong><br>
            <span><?php echo $_SESSION['studentno']; ?></span>
        </div>
    </div>

</header>

<main class="main-wrapper">

<div class="content-split">

    <div class="visual-panel">
        <h1>Book Your Slot!</h1>
        <p>Plan your semester effortlessly by reserving time with your preferred lecturer.</p>
    </div>

    <div class="form-panel">

        <h2>New Booking</h2>

        <form method="POST" action="">

            <label>Purpose / Service</label>
            <select name="serviceName" required>
                <option value="">-- Choose Service --</option>
                <option value="Consultation">Consultation</option>
                <option value="Presentation">Presentation</option>
                <option value="Exam Retake">Exam Retake</option>
                <option value="Project Discussion">Project Discussion</option>
                <option value="Assignment Guidance">Assignment Guidance</option>
                <option value="Internship Discussion">Internship Discussion</option>
            </select>

            <label>Select Lecturer</label>
            <select name="lecturerID" required>
                <option value="">-- Choose Lecturer --</option>

                <?php
                $lecturerList = mysqli_query($connLecturer, "
                    SELECT USER_ID, USER_NAME 
                    FROM user 
                    ORDER BY USER_NAME ASC
                ");

                while ($row = mysqli_fetch_assoc($lecturerList)) {
                    echo "<option value='".$row['USER_ID']."'>".$row['USER_NAME']."</option>";
                }
                ?>
            </select>

            <label>Place</label>
            <select name="place" required>
                <option value="">-- Choose Place --</option>
                <option value="AEC Room">AEC Room</option>
                <option value="Office">Office</option>
            </select>

            <label>Booking Date</label>
            <input type="date" name="bookingDate" required>

            <div class="time-group">
                <div>
                    <label>Start Time</label>
                    <input type="time" name="startTime" required>
                </div>

                <div>
                    <label>End Time</label>
                    <input type="time" name="endTime" required>
                </div>
            </div>

            <button type="submit" name="submitBooking">
                Confirm Booking Request
            </button>

        </form>

    </div>

</div>

</main>

<?php
if (isset($_SESSION['booking_error'])) {
    echo "
    <script>
        Swal.fire({
            title: 'Booking Not Available!',
            text: '".$_SESSION['booking_error']."',
            icon: 'error',
            confirmButtonColor: '#a855f7',
            confirmButtonText: 'Choose Another Option'
        });
    </script>
    ";

    unset($_SESSION['booking_error']);
}
?>

</body>
</html>