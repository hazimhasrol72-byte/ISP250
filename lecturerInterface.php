<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme = $_SESSION['theme'] ?? 'dark';

require_once 'db_connect.php';
require_once 'token.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != "lecturer") {
    header("Location: role_select.php");
    exit();
}

$lecturerID = $_SESSION['lecturerID'];
$lecturerName = $_SESSION['lecturerName'];
$page = isset($_GET['page']) ? $_GET['page'] : "dashboard";

if (isset($_POST['updateStatus'])) {

    $bookingID = verifyToken($_POST['bookingToken']);

    if ($bookingID === false) {
        $_SESSION['lecturer_message'] = "Invalid booking token.";
        header("Location: lecturerInterface.php");
        exit();
    }

    $bookingID = mysqli_real_escape_string($connAppointment, $bookingID);
    $status = mysqli_real_escape_string($connAppointment, $_POST['status']);

    $allowedStatus = ['Approved', 'Rejected'];

if (!in_array($status, $allowedStatus)) {
    $_SESSION['lecturer_message'] = "Invalid booking status.";
    header("Location: lecturerInterface.php");
    exit();
}

$lecturerIDSafe = mysqli_real_escape_string($connAppointment, $lecturerID);

$update = mysqli_query($connAppointment, "
    UPDATE bookings
    SET status = '$status'
    WHERE bookingID = '$bookingID'
    AND lecturerID = '$lecturerIDSafe'
    AND status = 'Pending'
");

    if ($update) {
        $_SESSION['lecturer_message'] = "Booking has been $status.";
    } else {
        $_SESSION['lecturer_message'] = "Failed to update booking.";
    }

    header("Location: lecturerInterface.php");
    exit();
}

$result = mysqli_query($connAppointment, "
    SELECT *
    FROM bookings
    WHERE lecturerID = '$lecturerID'
    ORDER BY bookingDate ASC, startTime ASC
");

$total = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID'"));
$pending = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID' AND status='Pending'"));
$approved = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID' AND status='Approved'"));
$rejected = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID' AND status='Rejected'"));

/* PEAK HOUR GRAPH DATA - ALL BOOKINGS FROM 8AM UNTIL 10PM */
$hourLabels = [];
$hourValues = [];

for ($h = 8; $h <= 22; $h++) {
    $hourLabels[$h] = date("h A", strtotime($h . ":00"));
    $hourValues[$h] = 0;
}

$peakQuery = mysqli_query($connAppointment, "
    SELECT 
        HOUR(startTime) AS bookingHour,
        COUNT(*) AS totalBooking
    FROM bookings
    WHERE status != 'Cancelled'
    AND status != 'Rejected'
    AND TIME(startTime) >= '08:00:00'
    AND TIME(startTime) <= '22:00:00'
    GROUP BY HOUR(startTime)
    ORDER BY bookingHour ASC
");

while ($peakRow = mysqli_fetch_assoc($peakQuery)) {
    $hour = (int)$peakRow['bookingHour'];

    if (isset($hourValues[$hour])) {
        $hourValues[$hour] = (int)$peakRow['totalBooking'];
    }
}

$chartLabels = array_values($hourLabels);
$chartValues = array_values($hourValues);

$maxBooking = 0;
$peakHourText = "No data yet";

foreach ($hourValues as $hour => $value) {
    if ($value > $maxBooking) {
        $maxBooking = $value;
        $peakHourText = date("h:i A", strtotime($hour . ":00"));
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Lecturer Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
    radial-gradient(circle at top left,rgba(192,132,252,.28),transparent 30%),
    radial-gradient(circle at bottom right,rgba(168,85,247,.22),transparent 35%),
    linear-gradient(135deg,#160024,#250042,#140021);
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
    min-height:calc(100vh - 80px);
}

.sidebar{
    width:320px;
    background:rgba(255,255,255,.08);
    backdrop-filter:blur(22px);
    border-right:1px solid rgba(255,255,255,.12);
    padding:35px 24px;
    transition:.3s ease;
}

body.light-mode .sidebar{
    background:rgba(255,255,255,.85);
    border-right:1px solid #d8c7ff;
}

.profile{
    display:flex;
    gap:18px;
    align-items:center;
    margin-bottom:55px;
}

.avatar{
    width:82px;
    height:82px;
    border-radius:50%;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:36px;
    font-weight:800;
    box-shadow:0 0 30px rgba(168,85,247,.5);
}

.small{
    color:#d8b4fe;
    font-size:13px;
}

body.light-mode .small{
    color:#6d28d9;
}

.profile h3{
    font-size:17px;
    line-height:1.3;
    text-transform:uppercase;
}

body.light-mode .profile h3{
    color:#1f1235;
}

.access-badge{
    display:inline-flex;
    align-items:center;
    gap:7px;
    margin-top:12px;
    padding:9px 18px;
    border-radius:999px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    font-size:11px;
    font-weight:800;
    letter-spacing:1px;
    text-transform:uppercase;
    box-shadow:0 0 18px rgba(168,85,247,.45);
}

.access-dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:white;
    box-shadow:0 0 10px white;
}

.nav-title{
    color:#c4b5fd;
    font-size:13px;
    margin-bottom:18px;
    text-transform:uppercase;
    letter-spacing:1px;
}

body.light-mode .nav-title{
    color:#6d28d9;
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

body.light-mode .nav a{
    color:#1f1235;
}

.nav a.active,
.nav a:hover{
    background:linear-gradient(90deg,rgba(168,85,247,.48),rgba(124,58,237,.25));
    box-shadow:inset 4px 0 #c084fc,0 0 22px rgba(168,85,247,.18);
}

body.light-mode .nav a.active,
body.light-mode .nav a:hover{
    background:linear-gradient(90deg,#c084fc,#a855f7);
    color:white;
}

.nav-icon{
    font-size:20px;
    min-width:24px;
    text-align:center;
}

.nav-text{
    display:inline-block;
}

.main{
    padding:42px 46px;
}

.page-header{
    margin-bottom:36px;
}

.page-header h1{
    font-size:42px;
    font-weight:800;
}

body.light-mode .page-header h1,
body.light-mode h2,
body.light-mode h3{
    color:#1f1235;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:22px;
    margin-bottom:34px;
}

.stat-card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:26px;
    padding:28px;
    min-height:145px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    box-shadow:0 0 38px rgba(168,85,247,.13);
}

.stat-card h2{
    font-size:42px;
    font-weight:800;
}

.stat-card p{
    color:#d8b4fe;
    margin-top:8px;
    font-weight:700;
}

.grid{
    display:grid;
    grid-template-columns:1.5fr .9fr;
    gap:28px;
}

.panel{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:28px;
    padding:34px;
    box-shadow:0 0 38px rgba(168,85,247,.13);
}

.panel h2{
    margin-bottom:24px;
    font-size:28px;
}

.request-card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.10);
    border-radius:22px;
    padding:24px;
    margin-bottom:18px;
}

.request-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
}

.request-card p{
    color:#e9d5ff;
    line-height:1.8;
    font-size:14px;
}

.status{
    padding:9px 15px;
    border-radius:13px;
    font-size:12px;
    font-weight:800;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    white-space:nowrap;
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

.booking-actions{
    display:flex;
    gap:10px;
    align-items:center;
    justify-content:flex-end;
}

.booking-actions form{
    margin:0;
    display:inline;
}

.approve-btn,
.reject-btn{
    width:42px;
    height:42px;
    border:none;
    border-radius:14px;
    color:white;
    font-size:24px;
    font-weight:900;
    cursor:pointer;
    transition:.25s;
    display:flex;
    align-items:center;
    justify-content:center;
}

.approve-btn{
    background:#22c55e;
}

.reject-btn{
    background:#ef4444;
}

.approve-btn:hover,
.reject-btn:hover{
    transform:scale(1.08);
}

.info-card{
    background:linear-gradient(135deg,rgba(168,85,247,.28),rgba(124,58,237,.16));
    border:1px solid rgba(255,255,255,.12);
    border-radius:26px;
    padding:30px;
    margin-bottom:28px;
}

.info-card h3{
    font-size:24px;
    margin-bottom:14px;
}

.note{
    background:rgba(255,255,255,.08);
    border-radius:18px;
    padding:18px;
    margin-bottom:14px;
    color:#f3e8ff;
    font-weight:700;
}

.analysis-grid{
    display:grid;
    grid-template-columns:1.4fr .7fr;
    gap:28px;
}

.graph-card,
.insight-card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:28px;
    padding:34px;
    box-shadow:0 0 38px rgba(168,85,247,.13);
}

.graph-card h2,
.insight-card h2{
    font-size:30px;
    margin-bottom:10px;
}

.graph-card p,
.insight-card p{
    color:#d8b4fe;
    line-height:1.7;
    font-size:15px;
}

.chart-wrap{
    height:430px;
    margin-top:30px;
}

.peak-box{
    margin-top:28px;
    padding:30px;
    border-radius:26px;
    background:linear-gradient(135deg,rgba(168,85,247,.42),rgba(34,211,238,.18));
    border:1px solid rgba(255,255,255,.14);
}

.peak-box h3{
    font-size:42px;
    margin-bottom:10px;
}

.peak-box span{
    color:#d8b4fe;
    font-weight:700;
}

body.light-mode .stat-card,
body.light-mode .panel,
body.light-mode .request-card,
body.light-mode .info-card,
body.light-mode .note,
body.light-mode .graph-card,
body.light-mode .insight-card{
    background:white;
    color:#1f1235;
    border:1px solid #e9d5ff;
}

body.light-mode .stat-card p,
body.light-mode .request-card p,
body.light-mode .info-card p,
body.light-mode .graph-card p,
body.light-mode .insight-card p,
body.light-mode .peak-box span{
    color:#6d28d9;
}

body.light-mode .peak-box{
    background:#f3e8ff;
    border:1px solid #e9d5ff;
}

.system-footer{
    margin-top:50px;
    width:100%;
    min-height:75px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08);
    backdrop-filter:blur(18px);
    border-radius:22px;
    padding:20px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    color:#d8b4fe;
    font-size:14px;
    font-weight:700;
}

.footer-left{
    color:white;
    font-weight:800;
}

.footer-center{
    color:#c4b5fd;
}

.footer-right{
    color:#d8b4fe;
}

body.light-mode .system-footer{
    background:white;
    border:1px solid #e9d5ff;
}

body.light-mode .footer-left{
    color:#1f1235;
}

body.light-mode .footer-center{
    color:#6d28d9;
}

body.light-mode .footer-right{
    color:#7c3aed;
}

body.sidebar-collapsed .layout{
    grid-template-columns:90px 1fr !important;
}

body.sidebar-collapsed .sidebar{
    width:90px !important;
    padding:35px 18px !important;
}

body.sidebar-collapsed .profile{
    justify-content:center;
    gap:0;
}

body.sidebar-collapsed .profile .avatar{
    width:58px !important;
    height:58px !important;
    font-size:26px !important;
}

body.sidebar-collapsed .profile > div:not(.avatar){
    display:none !important;
}

body.sidebar-collapsed .nav-title{
    display:none !important;
}

body.sidebar-collapsed .nav a{
    width:54px;
    height:54px;
    padding:0 !important;
    border-radius:18px;
    display:flex;
    justify-content:center;
    align-items:center;
}

body.sidebar-collapsed .nav-text{
    display:none !important;
}

@media(max-width:1000px){
    .layout,
    .stats,
    .grid,
    .analysis-grid{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:100%;
    }

    .request-top{
        flex-direction:column;
    }

    .booking-actions{
        justify-content:flex-start;
    }

    .system-footer{
        flex-direction:column;
        gap:10px;
        text-align:center;
    }
}
</style>
</head>

<body class="<?php echo ($theme == 'light') ? 'light-mode' : ''; ?>">

<?php include 'topbar.php'; ?>

<div class="layout">

<aside class="sidebar">

    <div class="profile">
        <div class="avatar">
            <?php echo strtoupper(substr($lecturerName,0,1)); ?>
        </div>

        <div>
            <div class="small">WELCOME</div>
            <h3><?php echo $lecturerName; ?></h3>
            <div class="small">Lecturer</div>
            <div class="small"><?php echo $lecturerID; ?></div>

            <div class="access-badge">
                <span class="access-dot"></span>
                Lecturer Access
            </div>
        </div>
    </div>

    <?php include 'navbar.php'; ?>

</aside>

<main class="main">

    <div class="page-header">
        <h1><?php echo ($page == 'analysis') ? 'Booking Analysis' : 'Lecturer Dashboard'; ?></h1>
        <p class="small">
            <?php echo ($page == 'analysis') ? 'View overall student booking peak hours from 8:00 AM until 10:00 PM' : 'Manage student appointment requests'; ?>
        </p>
    </div>

<?php if ($page == "dashboard") { ?>

    <section class="stats">

        <div class="stat-card">
            <h2><?php echo $total; ?></h2>
            <p>Total Requests</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $pending; ?></h2>
            <p>Pending</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $approved; ?></h2>
            <p>Approved</p>
        </div>

        <div class="stat-card">
            <h2><?php echo $rejected; ?></h2>
            <p>Rejected</p>
        </div>

    </section>

    <div class="grid">

        <section class="panel">
            <h2>Student Appointment Requests</h2>

            <?php if ($result && mysqli_num_rows($result) > 0) { ?>

                <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                    <div class="request-card">

                        <div class="request-top">

                            <div>
                                <h3><?php echo $row['studentname']; ?></h3>

                                <p>
                                    Student No: <?php echo $row['studentno']; ?><br>
                                    Service: <?php echo $row['serviceName']; ?><br>
                                    Place: <?php echo $row['place']; ?><br>
                                    Date: <?php echo date("d M Y", strtotime($row['bookingDate'])); ?><br>
                                    Time: <?php echo date("h:i A", strtotime($row['startTime'])); ?> -
                                    <?php echo date("h:i A", strtotime($row['endTime'])); ?>
                                </p>
                            </div>

                            <div class="booking-actions">

                                <?php if ($row['status'] == "Pending") { ?>

                                    <span class="status Pending">Pending</span>

                                    <form method="POST">
                                        <input type="hidden" name="bookingToken" value="<?php echo createToken($row['bookingID']); ?>">
                                        <input type="hidden" name="status" value="Approved">

                                        <button type="submit"
                                                name="updateStatus"
                                                class="approve-btn"
                                                title="Approve Booking">
                                            ✓
                                        </button>
                                    </form>

                                    <form method="POST">
                                        <input type="hidden" name="bookingToken" value="<?php echo createToken($row['bookingID']); ?>">
                                        <input type="hidden" name="status" value="Rejected">

                                        <button type="submit"
                                                name="updateStatus"
                                                class="reject-btn"
                                                title="Reject Booking">
                                            ×
                                        </button>
                                    </form>

                                <?php } else { ?>

                                    <span class="status <?php echo $row['status']; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>

                                <?php } ?>

                            </div>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <div class="panel">
                    <h3>No appointment requests yet.</h3>
                    <p class="small">When students book appointments with you, they will appear here.</p>
                </div>

            <?php } ?>

        </section>

        <aside>

            <div class="info-card">
                <h3>Today’s Overview</h3>
                <p>Keep track of student consultation requests, approve suitable slots, or reject unavailable sessions.</p>
            </div>

            <div class="panel">
                <h2>Quick Notes</h2>
                <div class="note">Approve only available slots</div>
                <div class="note">Check place before approval</div>
                <div class="note">Review pending requests early</div>
            </div>

        </aside>

    </div>

<?php } else { ?>

    <div class="analysis-grid">

        <section class="graph-card">
            <h2>Peak Booking Hour</h2>
            <p>This graph shows the most common booking hours from 8:00 AM until 10:00 PM.</p>

            <div class="chart-wrap">
                <canvas id="peakHourChart"></canvas>
            </div>
        </section>

        <aside class="insight-card">
            <h2>Analysis Insight</h2>
            <p>Use this information to understand when students usually book consultation slots.</p>

            <div class="peak-box">
                <h3><?php echo $peakHourText; ?></h3>

                <span>
                    <?php
                    if ($maxBooking > 0) {
                        echo $maxBooking . " booking(s) recorded during this hour.";
                    } else {
                        echo "No booking data available yet.";
                    }
                    ?>
                </span>
            </div>
        </aside>

    </div>

<?php } ?>

<?php include 'footer.php'; ?>

</main>

</div>

<?php
if (isset($_SESSION['lecturer_message'])) {
    echo "
    <script>
        Swal.fire({
            title:'Updated!',
            text:'".$_SESSION['lecturer_message']."',
            icon:'success',
            confirmButtonColor:'#a855f7',
            confirmButtonText:'Okay'
        });
    </script>";
    unset($_SESSION['lecturer_message']);
}
?>

<script>
document.addEventListener("DOMContentLoaded", function(){

    const toggleBtn = document.getElementById("sidebarToggle");

    if(toggleBtn){
        toggleBtn.addEventListener("click", function(){
            document.body.classList.toggle("sidebar-collapsed");
        });
    }

    const ctx = document.getElementById("peakHourChart");

    if(ctx){
        new Chart(ctx, {
            type:"line",
            data:{
                labels:<?php echo json_encode($chartLabels); ?>,
                datasets:[{
                    label:"Total Bookings",
                    data:<?php echo json_encode($chartValues); ?>,
                    tension:.45,
                    fill:true,
                    borderWidth:3,
                    pointRadius:5,
                    pointHoverRadius:8,
                    borderColor:"#22d3ee",
                    pointBackgroundColor:"#a855f7",
                    pointBorderColor:"#ffffff",
                    backgroundColor:"rgba(34,211,238,.15)"
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                interaction:{
                    mode:"index",
                    intersect:false
                },
                plugins:{
                    legend:{
                        labels:{
                            color:"#d8b4fe",
                            font:{
                                weight:"bold"
                            }
                        }
                    },
                    tooltip:{
                        backgroundColor:"#1e0033",
                        titleColor:"#ffffff",
                        bodyColor:"#d8b4fe",
                        padding:14,
                        cornerRadius:12,
                        displayColors:false,
                        callbacks:{
                            title:function(context){
                                return "Time: " + context[0].label;
                            },
                            label:function(context){
                                return "Total Bookings: " + context.raw;
                            }
                        }
                    }
                },
                scales:{
                    x:{
                        title:{
                            display:true,
                            text:"Time in a Day",
                            color:"#d8b4fe",
                            font:{
                                weight:"bold"
                            }
                        },
                        ticks:{
                            color:"#d8b4fe"
                        },
                        grid:{
                            color:"rgba(255,255,255,.08)"
                        }
                    },
                    y:{
                        beginAtZero:true,
                        ticks:{
                            color:"#d8b4fe",
                            stepSize:1
                        },
                        title:{
                            display:true,
                            text:"Number of Bookings",
                            color:"#d8b4fe",
                            font:{
                                weight:"bold"
                            }
                        },
                        grid:{
                            color:"rgba(255,255,255,.08)"
                        }
                    }
                }
            }
        });
    }

});
</script>

</body>
</html>