<?php
session_start();
include 'db_connect.php';
include 'token.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: role_select.php");
    exit();
}

$studentno = $_SESSION['studentno'];
$studentname = $_SESSION['studentname'];

$sql = "SELECT * FROM bookings 
        WHERE studentno = '$studentno' 
        ORDER BY bookingDate ASC, startTime ASC";

$result = mysqli_query($connAppointment, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Smart Appointment</title>

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
    display:flex;
    min-height:100vh;
    background:
    radial-gradient(circle at top left, rgba(192,132,252,0.35), transparent 28%),
    radial-gradient(circle at bottom right, rgba(217,70,239,0.24), transparent 32%),
    linear-gradient(135deg,#140021,#1e0033,#250042,#160024);
    color:white;
}

.sidebar{
    width:280px;
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(18px);
    position:fixed;
    height:100vh;
    border-right:1px solid rgba(255,255,255,0.10);
    box-shadow:0 0 35px rgba(168,85,247,0.18);
}

.profile-section{
    display:flex;
    align-items:center;
    padding:30px 20px;
    gap:15px;
    border-bottom:1px solid rgba(255,255,255,0.10);
}

.profile-pic{
    width:65px;
    height:65px;
    border-radius:50%;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:26px;
    font-weight:800;
    box-shadow:0 0 25px rgba(168,85,247,0.45);
}

.profile-welcome{
    font-size:11px;
    color:#d8b4fe;
    text-transform:uppercase;
}

.profile-name{
    font-size:14px;
    font-weight:800;
    text-transform:uppercase;
}

.profile-id{
    font-size:13px;
    color:#c4b5fd;
}

.nav-header{
    font-size:11px;
    color:#c4b5fd;
    padding:22px 20px 10px;
    text-transform:uppercase;
    letter-spacing:1px;
}

.nav-item{
    display:block;
    padding:17px 25px;
    color:white;
    text-decoration:none;
    border-left:4px solid transparent;
    transition:0.3s;
}

.nav-item:hover,
.nav-item.active{
    background:linear-gradient(90deg,rgba(168,85,247,0.30),rgba(124,58,237,0.18));
    border-left-color:#c084fc;
    box-shadow:inset 0 0 18px rgba(168,85,247,0.18);
}

.main-content{
    margin-left:280px;
    width:calc(100% - 280px);
}

.top-header{
    background:rgba(255,255,255,0.08);
    backdrop-filter:blur(18px);
    padding:25px 35px;
    font-size:24px;
    font-weight:800;
    border-bottom:1px solid rgba(255,255,255,0.10);
}

.dashboard-container{
    padding:35px;
    max-width:1100px;
}

.section-title{
    font-size:28px;
    font-weight:800;
    margin-bottom:25px;
}

.appointment-card{
    background:rgba(255,255,255,0.10);
    backdrop-filter:blur(20px);
    border-left:5px solid #a855f7;
    border-radius:18px;
    padding:25px;
    margin-bottom:20px;
    box-shadow:
    0 0 35px rgba(168,85,247,0.16),
    0 20px 55px rgba(0,0,0,0.25);
    display:flex;
    justify-content:space-between;
    align-items:center;
    border:1px solid rgba(255,255,255,0.10);
}

.date{
    font-size:13px;
    color:#d8b4fe;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:8px;
}

.time{
    font-size:22px;
    font-weight:800;
    margin-bottom:8px;
    color:white;
}

.details{
    font-size:15px;
    color:#f3e8ff;
    line-height:1.7;
}

.status{
    padding:9px 18px;
    border-radius:10px;
    font-size:12px;
    font-weight:800;
    text-transform:uppercase;
}

.Pending{
    background:#fef3c7;
    color:#92400e;
}

.Approved{
    background:#dcfce7;
    color:#166534;
}

.Rejected{
    background:#fee2e2;
    color:#991b1b;
}

.Cancelled{
    background:#e5e7eb;
    color:#374151;
}

.action-area{
    display:flex;
    align-items:center;
    gap:12px;
}

.cancel-btn{
    text-decoration:none;
    padding:10px 16px;
    border-radius:10px;
    background:#fee2e2;
    color:#dc2626;
    font-size:13px;
    font-weight:800;
}

.cancel-btn:hover{
    background:#dc2626;
    color:white;
}

.action-btn{
    display:inline-block;
    margin-top:25px;
    padding:14px 24px;
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    color:white;
    text-decoration:none;
    border-radius:14px;
    font-weight:800;
    box-shadow:0 0 25px rgba(168,85,247,0.45);
}

.empty-box{
    background:rgba(255,255,255,0.10);
    backdrop-filter:blur(18px);
    padding:35px;
    border-radius:18px;
    color:#f3e8ff;
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="profile-section">
        <div class="profile-pic">
            <?php echo strtoupper(substr($studentname, 0, 1)); ?>
        </div>

        <div>
            <div class="profile-welcome">Welcome</div>
            <div class="profile-name"><?php echo $studentname; ?></div>
            <div class="profile-id"><?php echo $studentno; ?></div>
        </div>
    </div>

    <div class="nav-header">Navigation</div>
    <a href="dashboard.php" class="nav-item active">Dashboard</a>
    <a href="ispInterfaceExp.php" class="nav-item">New Booking</a>
    <a href="role_select.php" class="nav-item">Logout</a>
</aside>

<main class="main-content">

<header class="top-header">My Schedule & Appointments</header>

<div class="dashboard-container">
    <h2 class="section-title">Current Semester Appointments</h2>

    <?php if ($result && mysqli_num_rows($result) > 0) { ?>

        <?php while ($row = mysqli_fetch_assoc($result)) { 
            $date = date("l, d M Y", strtotime($row['bookingDate']));
            $start = date("h:i A", strtotime($row['startTime']));
            $end = date("h:i A", strtotime($row['endTime']));
            $status = $row['status'];
        ?>

        <div class="appointment-card">
            <div>
                <div class="date"><?php echo $date; ?></div>
                <div class="time"><?php echo $start . " - " . $end; ?></div>
                <div class="details">
                    Lecturer: <strong><?php echo $row['lecturerName']; ?></strong><br>
                    Service: <?php echo $row['serviceName']; ?><br>
                    Place: <?php echo $row['place']; ?>
                </div>
            </div>

            <div class="action-area">
                <div class="status <?php echo $status; ?>">
                    <?php echo $status; ?>
                </div>

                <?php if ($status == "Pending") { ?>
                    <form method="POST" action="cancel_booking.php" style="display:inline;">
                    <input type="hidden" name="bookingToken" value="<?php echo createToken($row['bookingID']); ?>">

                    <button type="submit"
                    class="cancel-btn"
                    onclick="return confirm('Are you sure you want to cancel this booking?')">
                    Cancel
                </button>
                </form>
                <?php } ?>
            </div>
        </div>

        <?php } ?>

    <?php } else { ?>
        <div class="empty-box">No appointment booking yet.</div>
    <?php } ?>

    <a href="ispInterfaceExp.php" class="action-btn">+ Request Another Appointment</a>
</div>

</main>

<?php
if (isset($_SESSION['booking_success'])) {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title:'Success!',
                text:'".$_SESSION['booking_success']."',
                icon:'success',
                confirmButtonColor:'#6C63FF'
            });
        });
    </script>";
    unset($_SESSION['booking_success']);
}
?>

</body>
</html>