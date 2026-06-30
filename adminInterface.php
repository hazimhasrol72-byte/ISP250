<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme = $_SESSION['theme'] ?? 'dark';

require_once 'db_connect.php';
require_once 'token.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: role_select.php");
    exit();
}

$adminID = $_SESSION['lecturerID'];
$adminName = $_SESSION['lecturerName'];
$page = isset($_GET['page']) ? $_GET['page'] : "dashboard";

if (isset($_POST['updateStatus'])) {

    $bookingID = verifyToken($_POST['bookingToken']);

    if ($bookingID === false) {
        $_SESSION['admin_message'] = "Invalid booking token.";
        header("Location: adminInterface.php");
        exit();
    }

    $bookingID = mysqli_real_escape_string($connAppointment, $bookingID);
    $status = mysqli_real_escape_string($connAppointment, $_POST['status']);

    $allowedStatus = ['Approved', 'Rejected'];

    if (!in_array($status, $allowedStatus)) {
        $_SESSION['admin_message'] = "Invalid booking status.";
        header("Location: adminInterface.php");
        exit();
    }

    $update = mysqli_query($connAppointment, "
        UPDATE bookings
        SET status = '$status'
        WHERE bookingID = '$bookingID'
        AND status = 'Pending'
    ");

    if ($update) {
        $_SESSION['admin_message'] = "Booking has been $status.";
    } else {
        $_SESSION['admin_message'] = "Failed to update booking.";
    }

    header("Location: adminInterface.php");
    exit();
}

$roleQ = mysqli_query($connAppointment, "SELECT roleID FROM roles WHERE roleTitle='Admin'");
$roleData = mysqli_fetch_assoc($roleQ);
$adminRoleID = $roleData['roleID'];

if (isset($_POST['assignAdmin'])) {
    $lecturerID = mysqli_real_escape_string($connAppointment, $_POST['lecturerID']);

    mysqli_query($connAppointment, "
        INSERT IGNORE INTO role_assign (lecturerID, roleID)
        VALUES ('$lecturerID', '$adminRoleID')
    ");

    $_SESSION['admin_message'] = "Lecturer has been added as admin.";
    header("Location: adminInterface.php?page=appoint");
    exit();
}

if (isset($_POST['removeAdmin'])) {
    $lecturerID = mysqli_real_escape_string($connAppointment, $_POST['lecturerID']);

    mysqli_query($connAppointment, "
        DELETE FROM role_assign
        WHERE lecturerID = '$lecturerID'
        AND roleID = '$adminRoleID'
    ");

    $_SESSION['admin_message'] = "Admin access has been removed.";
    header("Location: adminInterface.php?page=appoint");
    exit();
}

$total = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings"));
$pending = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE status='Pending'"));
$approved = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE status='Approved'"));
$rejected = mysqli_num_rows(mysqli_query($connAppointment, "SELECT * FROM bookings WHERE status='Rejected'"));

/* SEARCH + PAGINATION FOR APPOINTMENT LIST */
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$searchSafe = mysqli_real_escape_string($connAppointment, $search);

$pageNo = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pageNo < 1) {
    $pageNo = 1;
}

$limit = 5;
$offset = ($pageNo - 1) * $limit;

$whereSearch = "";

if ($search !== "") {
    $whereSearch = "
        WHERE studentname LIKE '%$searchSafe%'
        OR studentno LIKE '%$searchSafe%'
        OR lecturerName LIKE '%$searchSafe%'
        OR serviceName LIKE '%$searchSafe%'
        OR place LIKE '%$searchSafe%'
        OR bookingDate LIKE '%$searchSafe%'
        OR status LIKE '%$searchSafe%'
    ";
}

$countQ = mysqli_query($connAppointment, "
    SELECT COUNT(*) AS totalData
    FROM bookings
    $whereSearch
");

$countData = mysqli_fetch_assoc($countQ);
$totalRows = $countData['totalData'];
$totalPages = ceil($totalRows / $limit);

$bookingList = mysqli_query($connAppointment, "
    SELECT *
    FROM bookings
    $whereSearch
    ORDER BY bookingDate ASC, startTime ASC
    LIMIT $limit OFFSET $offset
");

/* PEAK HOUR GRAPH DATA - 8AM UNTIL 10PM ONLY */
$hourLabels = [];
$hourValues = [];

for ($h = 8; $h <= 22; $h++) {
    $hourLabels[$h] = date("h A", strtotime($h . ":00"));
    $hourValues[$h] = 0;
}

$analysisQ = mysqli_query($connAppointment, "
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

while ($row = mysqli_fetch_assoc($analysisQ)) {
    $hour = (int)$row['bookingHour'];

    if (isset($hourValues[$hour])) {
        $hourValues[$hour] = (int)$row['totalBooking'];
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

$lecturers = mysqli_query($connLecturer, "
    SELECT USER_ID, USER_NAME, USER_EMAIL
    FROM user
    ORDER BY USER_NAME ASC
");
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>

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

.admin-badge{
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:12px;
    padding:10px 20px;
    border-radius:999px;
    background:linear-gradient(135deg,#facc15,#f59e0b);
    color:#1f2937;
    font-size:11px;
    font-weight:800;
    letter-spacing:1px;
    text-transform:uppercase;
    box-shadow:
    0 0 18px rgba(250,204,21,.55),
    0 0 35px rgba(245,158,11,.35);
}

.admin-dot{
    width:8px;
    height:8px;
    border-radius:50%;
    background:white;
    box-shadow:0 0 12px white;
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
    box-shadow:
    inset 4px 0 #c084fc,
    0 0 22px rgba(168,85,247,.18);
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

.panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:24px;
}

.panel-header h2{
    margin-bottom:0;
}

.appointment-search{
    display:flex;
    align-items:center;
    gap:10px;
}

.appointment-search input{
    width:240px;
    height:44px;
    border:none;
    outline:none;
    border-radius:14px;
    padding:0 16px;
    background:rgba(255,255,255,.10);
    color:white;
    font-size:13px;
    font-weight:700;
    border:1px solid rgba(255,255,255,.12);
}

.appointment-search input::placeholder{
    color:#d8b4fe;
}

.appointment-search button{
    height:44px;
    border:none;
    border-radius:14px;
    padding:0 18px;
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    color:white;
    font-weight:800;
    cursor:pointer;
}

.clear-search{
    height:44px;
    display:flex;
    align-items:center;
    padding:0 16px;
    border-radius:14px;
    background:rgba(255,255,255,.10);
    color:#f3e8ff;
    text-decoration:none;
    font-size:13px;
    font-weight:800;
}

.card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.10);
    border-radius:22px;
    padding:24px;
    margin-bottom:18px;
}

.card h3{
    font-size:20px;
    margin-bottom:8px;
}

.card p{
    color:#e9d5ff;
    line-height:1.8;
    font-size:15px;
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
    font-size:22px;
    font-weight:900;
    cursor:pointer;
    transition:.25s;
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

.btn{
    border:none;
    padding:12px 18px;
    border-radius:14px;
    color:white;
    font-weight:800;
    cursor:pointer;
}

.green{
    background:#22c55e;
}

.red{
    background:#ef4444;
}

.analytics-box{
    background:linear-gradient(135deg,rgba(168,85,247,.35),rgba(124,58,237,.20));
    padding:30px;
    border-radius:24px;
    margin-bottom:22px;
}

.analytics-box h3{
    font-size:32px;
    margin-bottom:14px;
}

.analytics-box p{
    color:#e9d5ff;
    line-height:1.8;
    font-size:15px;
}

.pagination{
    display:flex;
    justify-content:center;
    align-items:center;
    gap:10px;
    margin-top:28px;
    flex-wrap:wrap;
}

.pagination a{
    min-width:42px;
    height:42px;
    padding:0 14px;
    border-radius:14px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.12);
    color:white;
    text-decoration:none;
    font-weight:800;
    display:flex;
    justify-content:center;
    align-items:center;
    transition:.25s;
}

.pagination a:hover,
.pagination a.active-page{
    background:linear-gradient(90deg,#a855f7,#7c3aed);
    box-shadow:0 0 18px rgba(168,85,247,.35);
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
body.light-mode .card,
body.light-mode .analytics-box,
body.light-mode .graph-card,
body.light-mode .insight-card{
    background:white;
    color:#1f1235;
    border:1px solid #e9d5ff;
}

body.light-mode .stat-card p,
body.light-mode .card p,
body.light-mode .analytics-box p,
body.light-mode .graph-card p,
body.light-mode .insight-card p,
body.light-mode .peak-box span{
    color:#6d28d9;
}

body.light-mode .peak-box{
    background:#f3e8ff;
    border:1px solid #e9d5ff;
}

body.light-mode .appointment-search input{
    background:#f3e8ff;
    color:#1f1235;
    border:1px solid #e9d5ff;
}

body.light-mode .appointment-search input::placeholder{
    color:#6d28d9;
}

body.light-mode .clear-search,
body.light-mode .pagination a{
    background:#f3e8ff;
    color:#6d28d9;
    border:1px solid #e9d5ff;
}

body.light-mode .pagination a:hover,
body.light-mode .pagination a.active-page{
    color:white;
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

    .system-footer{
        flex-direction:column;
        gap:10px;
        text-align:center;
    }

    .panel-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .appointment-search{
        width:100%;
        flex-wrap:wrap;
    }

    .appointment-search input{
        width:100%;
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
            <?php echo strtoupper(substr($adminName,0,1)); ?>
        </div>

        <div>
            <div class="small">WELCOME</div>
            <h3><?php echo $adminName; ?></h3>
            <div class="small">Admin</div>
            <div class="small"><?php echo $adminID; ?></div>

            <div class="admin-badge">
                <span class="admin-dot"></span>
                Admin Access
            </div>
        </div>
    </div>

    <?php include 'navbar.php'; ?>

</aside>

<main class="main">

<div class="page-header">
    <h1>
        <?php 
        if ($page == 'analysis') {
            echo 'Booking Analysis';
        } elseif ($page == 'appoint') {
            echo 'Appoint Admin';
        } else {
            echo 'Admin Dashboard';
        }
        ?>
    </h1>

    <p class="small">
        <?php 
        if ($page == 'analysis') {
            echo 'View overall student booking peak hours from 8:00 AM until 10:00 PM';
        } elseif ($page == 'appoint') {
            echo 'Manage lecturer admin access';
        } else {
            echo 'Manage appointments, admin access and booking analysis';
        }
        ?>
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

    <div class="panel-header">
        <h2>Student Appointment Requests</h2>

        <form method="GET" action="adminInterface.php" class="appointment-search">
            <input type="hidden" name="page" value="dashboard">

            <input 
                type="text" 
                name="search" 
                placeholder="Search appointment..." 
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">Search</button>

            <?php if ($search !== "") { ?>
                <a href="adminInterface.php?page=dashboard" class="clear-search">Clear</a>
            <?php } ?>
        </form>
    </div>

    <?php if ($search !== "") { ?>
        <p class="small" style="margin-bottom:18px;">
            Search result for: <strong><?php echo htmlspecialchars($search); ?></strong>
            — <?php echo $totalRows; ?> result(s) found.
        </p>
    <?php } ?>

    <?php if ($bookingList && mysqli_num_rows($bookingList) > 0) { ?>

        <?php while ($row = mysqli_fetch_assoc($bookingList)) { ?>

            <div class="card">
                <div style="display:flex;justify-content:space-between;gap:20px;align-items:center;">

                    <div>
                        <h3><?php echo $row['studentname']; ?></h3>

                        <p>
                            Lecturer: <?php echo $row['lecturerName']; ?><br>
                            Service: <?php echo $row['serviceName']; ?><br>
                            Place: <?php echo $row['place']; ?><br>
                            Date: <?php echo date("d M Y", strtotime($row['bookingDate'])); ?><br>
                            Time:
                            <?php echo date("h:i A", strtotime($row['startTime'])); ?>
                            -
                            <?php echo date("h:i A", strtotime($row['endTime'])); ?>
                        </p>
                    </div>

                    <div class="booking-actions">

                        <?php if($row['status']=="Pending"){ ?>

                            <span class="status Pending">Pending</span>

                            <form method="POST">
                                <input type="hidden" name="bookingToken" value="<?php echo createToken($row['bookingID']); ?>">
                                <input type="hidden" name="status" value="Approved">
                                <button type="submit" name="updateStatus" class="approve-btn">✓</button>
                            </form>

                            <form method="POST">
                                <input type="hidden" name="bookingToken" value="<?php echo createToken($row['bookingID']); ?>">
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" name="updateStatus" class="reject-btn">×</button>
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

        <div class="card">
            <h3>No appointment requests found.</h3>
            <p>No appointment matches your search or there are no appointments yet.</p>
        </div>

    <?php } ?>

    <?php if ($totalPages > 1) { ?>

        <div class="pagination">

            <?php if ($pageNo > 1) { ?>
                <a href="adminInterface.php?page=dashboard&p=<?php echo $pageNo - 1; ?>&search=<?php echo urlencode($search); ?>">
                    Previous
                </a>
            <?php } ?>

            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                <a 
                    href="adminInterface.php?page=dashboard&p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                    class="<?php echo ($i == $pageNo) ? 'active-page' : ''; ?>"
                >
                    <?php echo $i; ?>
                </a>
            <?php } ?>

            <?php if ($pageNo < $totalPages) { ?>
                <a href="adminInterface.php?page=dashboard&p=<?php echo $pageNo + 1; ?>&search=<?php echo urlencode($search); ?>">
                    Next
                </a>
            <?php } ?>

        </div>

    <?php } ?>

</section>

<aside>

    <div class="panel">
        <h2>Peak Hour Summary</h2>

        <div class="analytics-box">
            <h3><?php echo $peakHourText; ?></h3>
            <p>
                <?php
                if ($maxBooking > 0) {
                    echo "This is the most popular booking hour. Total bookings: <strong>$maxBooking</strong>.";
                } else {
                    echo "No booking data available yet.";
                }
                ?>
            </p>
        </div>
    </div>

</aside>

</div>

<?php } elseif ($page == "analysis") { ?>

<div class="analysis-grid">

    <section class="graph-card">
        <h2>Peak Booking Hour Graph</h2>
        <p>This graph shows the most common appointment booking hours from 8:00 AM until 10:00 PM.</p>

        <div class="chart-wrap">
            <canvas id="peakHourChart"></canvas>
        </div>
    </section>

    <aside class="insight-card">
        <h2>Analysis Insight</h2>
        <p>Use this information to understand overall booking peak hour in the system.</p>

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

<?php } else { ?>

<section class="panel">
    <h2>Assign Lecturer as Admin</h2>

    <?php while ($lec = mysqli_fetch_assoc($lecturers)) { 
        $lecID = $lec['USER_ID'];

        $check = mysqli_query($connAppointment, "
            SELECT *
            FROM role_assign
            WHERE lecturerID='$lecID'
            AND roleID='$adminRoleID'
        ");

        $isAdmin = mysqli_num_rows($check) > 0;
    ?>

        <div class="card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;">

                <div>
                    <h3><?php echo $lec['USER_NAME']; ?></h3>

                    <p>
                        Lecturer ID: <?php echo $lec['USER_ID']; ?><br>
                        Email: <?php echo $lec['USER_EMAIL']; ?>
                    </p>
                </div>

                <div>
                    <?php if ($isAdmin) { ?>

                        <form method="POST">
                            <input type="hidden" name="lecturerID" value="<?php echo $lec['USER_ID']; ?>">
                            <button type="submit" name="removeAdmin" class="btn red">
                                Remove Admin
                            </button>
                        </form>

                    <?php } else { ?>

                        <form method="POST">
                            <input type="hidden" name="lecturerID" value="<?php echo $lec['USER_ID']; ?>">
                            <button type="submit" name="assignAdmin" class="btn green">
                                Make Admin
                            </button>
                        </form>

                    <?php } ?>
                </div>

            </div>
        </div>

    <?php } ?>

</section>

<?php } ?>

<?php include 'footer.php'; ?>

</main>

</div>

<?php
if (isset($_SESSION['admin_message'])) {
    echo "
    <script>
        Swal.fire({
            title:'Updated!',
            text:'".$_SESSION['admin_message']."',
            icon:'success',
            confirmButtonColor:'#a855f7',
            confirmButtonText:'Okay'
        });
    </script>";
    unset($_SESSION['admin_message']);
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