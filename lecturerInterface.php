<?php
session_start();
include 'db_connect.php';
include 'token.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != "lecturer") {
    header("Location: role_select.php");
    exit();
}

$lecturerID = $_SESSION['lecturerID'];
$lecturerName = $_SESSION['lecturerName'];

if (isset($_POST['updateStatus'])) {

    $bookingID = verifyToken($_POST['bookingToken']);

    if ($bookingID === false) {
        $_SESSION['lecturer_message'] = "Invalid booking token.";
        header("Location: lecturerInterface.php");
        exit();
    }

    $bookingID = mysqli_real_escape_string($connAppointment, $bookingID);
    $status = mysqli_real_escape_string($connAppointment, $_POST['status']);

    $update = "
        UPDATE bookings
        SET status = '$status'
        WHERE bookingID = '$bookingID'
        AND lecturerID = '$lecturerID'
    ";

    mysqli_query($connAppointment, $update);

    $_SESSION['lecturer_message'] = "Booking has been $status.";
    header("Location: lecturerInterface.php");
    exit();
}

$sql = "
    SELECT * FROM bookings
    WHERE lecturerID = '$lecturerID'
    ORDER BY bookingDate ASC, startTime ASC
";

$result = mysqli_query($connAppointment, $sql);

$total = mysqli_num_rows($result);

$pendingQ = mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID' AND status='Pending'");
$approvedQ = mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID' AND status='Approved'");
$rejectedQ = mysqli_query($connAppointment, "SELECT * FROM bookings WHERE lecturerID='$lecturerID' AND status='Rejected'");

$pending = mysqli_num_rows($pendingQ);
$approved = mysqli_num_rows($approvedQ);
$rejected = mysqli_num_rows($rejectedQ);

$result = mysqli_query($connAppointment, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lecturer Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
    background:#f7f5fb;
    color:#1f1f2e;
}

.layout{
    display:flex;
    min-height:100vh;
}

.sidebar{
    width:260px;
    background:#ffffff;
    padding:35px 28px;
    border-right:1px solid #eeeaf5;
}

.logo{
    font-size:22px;
    font-weight:800;
    margin-bottom:45px;
}

.menu a{
    display:flex;
    align-items:center;
    gap:14px;
    padding:15px 18px;
    margin-bottom:12px;
    border-radius:16px;
    color:#555;
    text-decoration:none;
    font-weight:700;
    transition:.25s;
}

.menu a.active,
.menu a:hover{
    background:#f0eaff;
    color:#7c3aed;
}

.main{
    flex:1;
    padding:35px 42px;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:35px;
}

.topbar h1{
    font-size:34px;
    font-weight:800;
}

.userbox{
    display:flex;
    align-items:center;
    gap:15px;
}

.avatar{
    width:52px;
    height:52px;
    border-radius:50%;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-weight:800;
    box-shadow:0 12px 25px rgba(124,58,237,.25);
}

.logout{
    padding:12px 18px;
    background:#241c24;
    color:white;
    text-decoration:none;
    border-radius:14px;
    font-weight:700;
}

.stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:22px;
    margin-bottom:35px;
}

.stat-card{
    background:#ffffff;
    border-radius:24px;
    padding:25px;
    box-shadow:0 12px 35px rgba(35,24,56,.07);
}

.stat-card h3{
    font-size:34px;
    margin-bottom:8px;
}

.stat-card p{
    color:#777;
    font-weight:600;
}

.content-grid{
    display:grid;
    grid-template-columns:1.5fr .9fr;
    gap:28px;
}

.panel{
    background:white;
    border-radius:28px;
    padding:30px;
    box-shadow:0 12px 35px rgba(35,24,56,.07);
}

.panel h2{
    font-size:24px;
    margin-bottom:22px;
}

.request-card{
    background:#faf8ff;
    border:1px solid #eee7ff;
    border-radius:22px;
    padding:22px;
    margin-bottom:18px;
}

.request-top{
    display:flex;
    justify-content:space-between;
    gap:20px;
}

.request-card h3{
    margin-bottom:10px;
    font-size:18px;
}

.request-card p{
    line-height:1.8;
    color:#555;
    font-size:14px;
}

.status{
    padding:9px 15px;
    border-radius:14px;
    font-size:12px;
    font-weight:800;
    display:inline-block;
    margin-bottom:12px;
}

.Pending{
    background:#fff3cd;
    color:#856404;
}

.Approved{
    background:#dcfce7;
    color:#166534;
}

.Rejected{
    background:#fee2e2;
    color:#991b1b;
}

.action-form{
    display:flex;
    gap:10px;
    margin-top:15px;
}

.approve{
    border:none;
    padding:11px 15px;
    border-radius:14px;
    background:#22c55e;
    color:white;
    font-weight:800;
    cursor:pointer;
}

.reject{
    border:none;
    padding:11px 15px;
    border-radius:14px;
    background:#ef4444;
    color:white;
    font-weight:800;
    cursor:pointer;
}

.empty{
    text-align:center;
    padding:65px 20px;
    color:#888;
}

.empty-icon{
    font-size:55px;
    margin-bottom:15px;
}

.info-card{
    background:linear-gradient(135deg,#241c24,#3b223d);
    color:white;
    border-radius:24px;
    padding:28px;
    margin-bottom:24px;
}

.info-card h3{
    margin-bottom:12px;
    font-size:22px;
}

.info-card p{
    color:#e9d5ff;
    line-height:1.7;
}

.mini-list{
    display:flex;
    flex-direction:column;
    gap:14px;
}

.mini-item{
    background:#faf8ff;
    padding:18px;
    border-radius:18px;
    color:#555;
    font-weight:600;
}

@media(max-width:1000px){
    .layout{
        flex-direction:column;
    }

    .sidebar{
        width:100%;
    }

    .stats,
    .content-grid{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<div class="layout">

    <aside class="sidebar">
        <div class="logo">Smart Appointment</div>

        <div class="menu">
            <a href="lecturerInterface.php" class="active">📊 Dashboard</a>
            <a href="role_select.php">🚪 Logout</a>
        </div>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Lecturer Dashboard</h1>
                <p style="color:#777;margin-top:8px;">Manage student appointment requests</p>
            </div>

            <div class="userbox">
                <div style="text-align:right;">
                    <strong><?php echo $lecturerName; ?></strong><br>
                    <span style="font-size:13px;color:#777;"><?php echo $lecturerID; ?></span>
                </div>

                <div class="avatar">
                    <?php echo strtoupper(substr($lecturerName,0,1)); ?>
                </div>

                <a href="role_select.php" class="logout">Logout</a>
            </div>
        </div>

        <section class="stats">
            <div class="stat-card">
                <h3><?php echo $total; ?></h3>
                <p>Total Requests</p>
            </div>

            <div class="stat-card">
                <h3><?php echo $pending; ?></h3>
                <p>Pending</p>
            </div>

            <div class="stat-card">
                <h3><?php echo $approved; ?></h3>
                <p>Approved</p>
            </div>

            <div class="stat-card">
                <h3><?php echo $rejected; ?></h3>
                <p>Rejected</p>
            </div>
        </section>

        <div class="content-grid">

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

                                <div style="text-align:right;">
                                    <span class="status <?php echo $row['status']; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>

                                    <?php if ($row['status'] == "Pending") { ?>

                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="bookingID" value="<?php echo $row['bookingID']; ?>">
                                            <input type="hidden" name="status" value="Approved">
                                            <button type="submit" name="updateStatus" class="approve">Approve</button>
                                        </form>

                                        <form method="POST" class="action-form">
                                            <input type="hidden" name="bookingID" value="<?php echo $row['bookingID']; ?>">
                                            <input type="hidden" name="status" value="Rejected">
                                            <button type="submit" name="updateStatus" class="reject">Reject</button>
                                        </form>

                                    <?php } ?>
                                </div>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <div class="empty">
                        <div class="empty-icon">📅</div>
                        <h3>No appointment requests yet.</h3>
                        <p style="margin-top:8px;">When students book appointments with you, they will appear here.</p>
                    </div>

                <?php } ?>

            </section>

            <aside>
                <div class="info-card">
                    <h3>Today’s Overview</h3>
                    <p>
                        Keep track of student consultation requests, approve suitable slots, or reject unavailable sessions.
                    </p>
                </div>

                <div class="panel">
                    <h2>Quick Notes</h2>

                    <div class="mini-list">
                        <div class="mini-item">✅ Approve only available slots</div>
                        <div class="mini-item">📍 Check place before approval</div>
                        <div class="mini-item">⏰ Review pending requests early</div>
                    </div>
                </div>
            </aside>

        </div>

    </main>

</div>

<?php
if (isset($_SESSION['lecturer_message'])) {
    echo "
    <script>
        Swal.fire({
            title: 'Updated!',
            text: '".$_SESSION['lecturer_message']."',
            icon: 'success',
            confirmButtonColor: '#7c3aed'
        });
    </script>
    ";

    unset($_SESSION['lecturer_message']);
}
?>

</body>
</html>