<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php';

$theme = $_SESSION['theme'] ?? 'dark';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: role_select.php");
    exit();
}

$studentno = $_SESSION['studentno'] ?? '';
$studentname = $_SESSION['studentname'] ?? '';

$success = "";
$error = "";

/* FSKM lecturers only */
$lecturers = mysqli_query($connLecturer, "
    SELECT USER_ID, USER_NAME
    FROM user
    WHERE USER_DEPARTMENT = 'C0001'
       OR USER_BKJABHAKIKI = 'C0001'
    ORDER BY USER_NAME ASC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $serviceName = mysqli_real_escape_string($connAppointment, $_POST['serviceName']);
    $lecturerID = mysqli_real_escape_string($connAppointment, $_POST['lecturerID']);
    $place = mysqli_real_escape_string($connAppointment, $_POST['place']);
    $bookingDate = mysqli_real_escape_string($connAppointment, $_POST['bookingDate']);
    $startTime = mysqli_real_escape_string($connAppointment, $_POST['startTime']);
    $endTime = mysqli_real_escape_string($connAppointment, $_POST['endTime']);

    $startHour = (int)date("H", strtotime($startTime));
    $endHour = (int)date("H", strtotime($endTime));

    if ($serviceName == "" || $lecturerID == "" || $place == "" || $bookingDate == "" || $startTime == "" || $endTime == "") {
        $error = "Please complete all booking details.";
    } elseif ($startTime >= $endTime) {
        $error = "End time must be later than start time.";
    } elseif ($startHour < 8 || $endHour > 18) {
        $error = "Booking is only allowed from 8:00 AM until 6:00 PM.";
    } else {

        $lecQuery = mysqli_query($connLecturer, "
            SELECT USER_NAME
            FROM user
            WHERE USER_ID = '$lecturerID'
            LIMIT 1
        ");

        $lecRow = mysqli_fetch_assoc($lecQuery);
        $lecturerName = $lecRow ? $lecRow['USER_NAME'] : '';

        $conflict = mysqli_query($connAppointment, "
            SELECT bookingID
            FROM bookings
            WHERE bookingDate = '$bookingDate'
            AND lecturerID = '$lecturerID'
            AND place = '$place'
            AND status != 'Cancelled'
            AND status != 'Rejected'
            AND ('$startTime' < endTime AND '$endTime' > startTime)
            LIMIT 1
        ");

        if (mysqli_num_rows($conflict) > 0) {
            $error = "This slot is not available. Please choose another time, lecturer, or room.";
        } else {

            $insert = mysqli_query($connAppointment, "
                INSERT INTO bookings
                (
                    studentno,
                    studentname,
                    lecturerID,
                    lecturerName,
                    serviceName,
                    place,
                    bookingDate,
                    startTime,
                    endTime,
                    status,
                    created_at
                )
                VALUES
                (
                    '$studentno',
                    '$studentname',
                    '$lecturerID',
                    '$lecturerName',
                    '$serviceName',
                    '$place',
                    '$bookingDate',
                    '$startTime',
                    '$endTime',
                    'Pending',
                    NOW()
                )
            ");

            if ($insert) {
                $success = "Booking request submitted successfully.";
            } else {
                $error = "Booking failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>New Booking</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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

.layout{
    display:grid;
    grid-template-columns:320px 1fr;
    min-height:100vh;
}

.sidebar{
    width:320px;
    background:rgba(255,255,255,.08);
    backdrop-filter:blur(22px);
    border-right:1px solid rgba(255,255,255,.12);
    padding:35px 24px;
}

.main{
    padding:42px 46px 0 46px;
}

.page-header{
    margin-bottom:34px;
}

.page-header h1{
    font-size:44px;
    font-weight:900;
}

.page-header p{
    color:#d8b4fe;
    margin-top:8px;
}

.booking-card{
    max-width:980px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:32px;
    overflow:hidden;
    box-shadow:0 0 40px rgba(168,85,247,.18);
    display:grid;
    grid-template-columns:.9fr 1.1fr;
}

.booking-left{
    padding:50px;
    background:linear-gradient(135deg,#8b5cf6,#6d28d9);
    display:flex;
    flex-direction:column;
    justify-content:center;
}

.booking-left h2{
    font-size:42px;
    font-weight:900;
    line-height:1.1;
    margin-bottom:18px;
}

.booking-left p{
    color:#ede9fe;
    line-height:1.8;
}

.booking-form{
    padding:42px;
}

.form-group{
    margin-bottom:20px;
}

label{
    display:block;
    margin-bottom:8px;
    color:#d8b4fe;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
}

input,
select{
    width:100%;
    height:55px;
    border:none;
    outline:none;
    border-radius:16px;
    background:rgba(255,255,255,.11);
    border:1px solid rgba(255,255,255,.12);
    color:white;
    padding:0 16px;
    font-size:15px;
    font-weight:700;
}

select option{
    color:#1f1235;
}

.time-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.btn-submit{
    width:100%;
    height:58px;
    border:none;
    border-radius:18px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    font-size:16px;
    font-weight:900;
    cursor:pointer;
    margin-top:10px;
    box-shadow:0 0 25px rgba(168,85,247,.40);
}

.back-link{
    display:block;
    text-align:center;
    margin-top:18px;
    color:#d8b4fe;
    text-decoration:none;
    font-weight:800;
}

.alert{
    padding:16px 20px;
    border-radius:16px;
    margin-bottom:20px;
    font-weight:800;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
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

@media(max-width:1000px){
    .layout,
    .booking-card,
    .time-grid{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:100%;
    }

    .main{
        padding:28px 22px 0 22px;
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

        <div class="page-header">
            <h1>New Appointment</h1>
            <p>Submit a consultation request with your preferred FSKM lecturer.</p>
        </div>

        <section class="booking-card">

            <div class="booking-left">
                <h2>Book Your Slot</h2>
                <p>
                    Select your consultation service, lecturer, room, date and time.
                    Booking is only available from 8:00 AM until 6:00 PM.
                </p>
            </div>

            <form class="booking-form" method="POST">

                <?php if($success != ""){ ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php } ?>

                <?php if($error != ""){ ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php } ?>

                <div class="form-group">
                    <label>Purpose / Service</label>
                    <select name="serviceName" required>
                        <option value="">-- Choose Service --</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Presentation">Presentation</option>
                        <option value="Project Discussion">Project Discussion</option>
                        <option value="Academic Advice">Academic Advice</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Select Lecturer</label>
                    <select name="lecturerID" required>
                        <option value="">-- Choose Lecturer --</option>

                        <?php while($lec = mysqli_fetch_assoc($lecturers)){ ?>
                            <option value="<?php echo $lec['USER_ID']; ?>">
                                <?php echo $lec['USER_NAME']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Place</label>
                    <select name="place" required>
                        <option value="">-- Choose Place --</option>
                        <option value="AEC Room">AEC Room</option>
                        <option value="Office">Office</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Booking Date</label>
                    <input type="date" name="bookingDate" required>
                </div>

                <div class="time-grid">
                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="startTime" min="08:00" max="18:00" required>
                    </div>

                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="endTime" min="08:00" max="18:00" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Submit Booking</button>

                <a href="dashboard.php" class="back-link">Back to Dashboard</a>

            </form>

        </section>

        <?php include 'footer.php'; ?>

    </main>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    document.body.classList.remove("sidebar-collapsed");

    const toggleBtn = document.getElementById("sidebarToggle");

    if(toggleBtn){
        toggleBtn.addEventListener("click", function(){
            document.body.classList.toggle("sidebar-collapsed");
        });
    }
});
</script>

</body>
</html>