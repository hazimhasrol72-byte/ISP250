<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme = $_SESSION['theme'] ?? 'dark';

require_once 'db_connect.php';

if (!isset($_SESSION['role'])) {
    header("Location: role_select.php");
    exit();
}

$lecturers = mysqli_query($connLecturer, "
    SELECT USER_ID, USER_NAME
    FROM user
    ORDER BY USER_NAME ASC
");

$selectedDate = isset($_GET['bookingDate']) ? $_GET['bookingDate'] : date("Y-m-d");
$selectedLecturer = isset($_GET['lecturerID']) ? $_GET['lecturerID'] : "";
$selectedPlace = isset($_GET['place']) ? $_GET['place'] : "";

$selectedDateSafe = mysqli_real_escape_string($connAppointment, $selectedDate);
$selectedLecturerSafe = mysqli_real_escape_string($connAppointment, $selectedLecturer);
$selectedPlaceSafe = mysqli_real_escape_string($connAppointment, $selectedPlace);

$places = ["AEC Room", "Office"];

$timeSlots = [];

for ($h = 8; $h < 18; $h++) {
    $timeSlots[] = [
        "start" => sprintf("%02d:00:00", $h),
        "end" => sprintf("%02d:00:00", $h + 1)
    ];
}

$where = "
    WHERE bookingDate = '$selectedDateSafe'
    AND status != 'Cancelled'
    AND status != 'Rejected'
";

if ($selectedLecturer != "") {
    $where .= " AND lecturerID = '$selectedLecturerSafe'";
}

if ($selectedPlace != "") {
    $where .= " AND place = '$selectedPlaceSafe'";
}

$bookingList = mysqli_query($connAppointment, "
    SELECT lecturerName, place, serviceName, startTime, endTime, status
    FROM bookings
    $where
    ORDER BY startTime ASC
");

function getSlotBookings($connAppointment, $date, $start, $end, $lecturerID, $place)
{
    $date = mysqli_real_escape_string($connAppointment, $date);
    $start = mysqli_real_escape_string($connAppointment, $start);
    $end = mysqli_real_escape_string($connAppointment, $end);

    $extra = "";

    if ($lecturerID != "") {
        $lecturerID = mysqli_real_escape_string($connAppointment, $lecturerID);
        $extra .= " AND lecturerID = '$lecturerID'";
    }

    if ($place != "") {
        $place = mysqli_real_escape_string($connAppointment, $place);
        $extra .= " AND place = '$place'";
    }

    $q = mysqli_query($connAppointment, "
        SELECT lecturerName, place, serviceName, startTime, endTime, status
        FROM bookings
        WHERE bookingDate = '$date'
        AND status != 'Cancelled'
        AND status != 'Rejected'
        AND ('$start' < endTime AND '$end' > startTime)
        $extra
        ORDER BY startTime ASC
    ");

    $bookings = [];

    if ($q && mysqli_num_rows($q) > 0) {
        while ($row = mysqli_fetch_assoc($q)) {
            $bookings[] = $row;
        }
    }

    return $bookings;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Check Availability</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
    padding:42px 46px 0 46px;
}

.page-header{
    margin-bottom:34px;
}

.page-header h1{
    font-size:42px;
    font-weight:800;
}

.page-header p{
    color:#d8b4fe;
    margin-top:8px;
    line-height:1.7;
}

.card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:28px;
    padding:30px;
    margin-bottom:28px;
    box-shadow:0 0 35px rgba(168,85,247,.15);
}

.card:last-of-type{
    margin-bottom:0;
}

.privacy-alert{
    background:rgba(34,211,238,.12);
    border:1px solid rgba(34,211,238,.25);
    color:#e0faff;
    border-radius:22px;
    padding:20px 24px;
    margin-bottom:28px;
    line-height:1.7;
    font-weight:700;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
}

label{
    display:block;
    margin-bottom:9px;
    font-size:13px;
    font-weight:800;
    color:#d8b4fe;
    text-transform:uppercase;
    letter-spacing:.8px;
}

input,
select{
    width:100%;
    height:54px;
    border:none;
    outline:none;
    border-radius:16px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.12);
    color:white;
    padding:0 16px;
    font-size:15px;
    font-weight:700;
}

select option{
    color:#1f1235;
}

button{
    width:100%;
    height:54px;
    border:none;
    border-radius:16px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 0 20px rgba(168,85,247,.35);
}

.slot-grid{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
}

.slot{
    padding:24px;
    border-radius:22px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.10);
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:18px;
}

.slot h3{
    font-size:22px;
}

.slot p{
    color:#d8b4fe;
    margin-top:6px;
    font-weight:700;
}

.available{
    border-left:7px solid #22c55e;
}

.booked{
    border-left:7px solid #ef4444;
}

.badge{
    padding:10px 16px;
    border-radius:999px;
    font-size:13px;
    font-weight:800;
    white-space:nowrap;
}

.badge-green{
    background:#dcfce7;
    color:#166534;
}

.badge-red{
    background:#fee2e2;
    color:#991b1b;
}

.booked-time-list{
    margin-top:14px;
    display:grid;
    gap:8px;
}

.booked-time{
    display:inline-block;
    padding:9px 13px;
    border-radius:12px;
    background:rgba(239,68,68,.18);
    border:1px solid rgba(239,68,68,.28);
    color:#fecaca;
    font-size:13px;
    font-weight:800;
    line-height:1.5;
}

.booked-time span{
    color:#ffffff;
}

.booking-item{
    padding:22px;
    border-radius:22px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.10);
    margin-bottom:16px;
}

.booking-item h3{
    font-size:20px;
    margin-bottom:8px;
}

.booking-item p{
    color:#e9d5ff;
    line-height:1.8;
}

.private-badge{
    display:inline-block;
    padding:8px 14px;
    border-radius:999px;
    background:rgba(255,255,255,.13);
    color:#ffffff;
    font-size:12px;
    font-weight:800;
    margin-bottom:10px;
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
body.light-mode .card,
body.light-mode .slot,
body.light-mode .booking-item{
    background:white;
    border:1px solid #e9d5ff;
    color:#1f1235;
}

body.light-mode .privacy-alert{
    background:#ecfeff;
    color:#155e75;
    border:1px solid #a5f3fc;
}

body.light-mode .page-header p,
body.light-mode label,
body.light-mode .slot p,
body.light-mode .booking-item p{
    color:#6d28d9;
}

body.light-mode input,
body.light-mode select{
    background:#f3e8ff;
    color:#1f1235;
}

body.light-mode .nav a{
    color:#1f1235;
}

body.light-mode .nav a.active,
body.light-mode .nav a:hover{
    background:linear-gradient(90deg,#c084fc,#a855f7);
    color:white;
}

body.light-mode .private-badge{
    background:#f3e8ff;
    color:#6d28d9;
}

body.light-mode .booked-time{
    background:#fee2e2;
    color:#991b1b;
    border:1px solid #fecaca;
}

body.light-mode .booked-time span{
    color:#7f1d1d;
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

body.sidebar-collapsed .nav{
    margin-top:35px;
    align-items:center;
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
    .form-grid,
    .slot-grid{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:100%;
    }

    .main{
        padding:28px 22px 0 22px;
    }

    .slot{
        flex-direction:column;
        align-items:flex-start;
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
        <h1>Check Availability</h1>
        <p>
            View available lecturer and room booking slots from 8:00 AM until 6:00 PM.
        </p>
    </div>

    <div class="privacy-alert">
        Privacy notice: student names are hidden on this page. Users can only view slot availability, actual booked time, lecturer, room and status.
    </div>

    <section class="card">
        <form method="GET">
            <div class="form-grid">

                <div>
                    <label>Date</label>
                    <input type="date" name="bookingDate" value="<?php echo htmlspecialchars($selectedDate); ?>" required>
                </div>

                <div>
                    <label>Lecturer</label>
                    <select name="lecturerID">
                        <option value="">All Lecturers</option>

                        <?php if ($lecturers && mysqli_num_rows($lecturers) > 0) { ?>
                            <?php while($lec = mysqli_fetch_assoc($lecturers)){ ?>
                                <option value="<?php echo htmlspecialchars($lec['USER_ID']); ?>"
                                    <?php if($selectedLecturer == $lec['USER_ID']) echo "selected"; ?>>
                                    <?php echo htmlspecialchars($lec['USER_NAME']); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label>Room</label>
                    <select name="place">
                        <option value="">All Rooms</option>

                        <?php foreach($places as $place){ ?>
                            <option value="<?php echo htmlspecialchars($place); ?>"
                                <?php if($selectedPlace == $place) echo "selected"; ?>>
                                <?php echo htmlspecialchars($place); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label>&nbsp;</label>
                    <button type="submit">Check Availability</button>
                </div>

            </div>
        </form>
    </section>

    <section class="card">
        <h2 style="margin-bottom:22px;">Availability Slot List</h2>

        <div class="slot-grid">

            <?php foreach($timeSlots as $slot){ 

                $slotBookings = getSlotBookings(
                    $connAppointment,
                    $selectedDate,
                    $slot['start'],
                    $slot['end'],
                    $selectedLecturer,
                    $selectedPlace
                );

                $totalBooked = count($slotBookings);
                $isBooked = $totalBooked > 0;
            ?>

                <div class="slot <?php echo $isBooked ? 'booked' : 'available'; ?>">

                    <div>
                        <h3>
                            <?php echo date("h:i A", strtotime($slot['start'])); ?>
                            -
                            <?php echo date("h:i A", strtotime($slot['end'])); ?>
                        </h3>

                        <p><?php echo $totalBooked; ?> booking(s) recorded</p>

                        <?php if($isBooked){ ?>
                            <div class="booked-time-list">
                                <?php foreach($slotBookings as $booked){ ?>
                                    <div class="booked-time">
                                        Booked Time:
                                        <span>
                                            <?php echo date("h:i A", strtotime($booked['startTime'])); ?>
                                            -
                                            <?php echo date("h:i A", strtotime($booked['endTime'])); ?>
                                        </span>
                                        <br>
                                        Lecturer:
                                        <span><?php echo htmlspecialchars($booked['lecturerName']); ?></span>
                                        <br>
                                        Room:
                                        <span><?php echo htmlspecialchars($booked['place']); ?></span>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>

                    <?php if($isBooked){ ?>
                        <span class="badge badge-red">Booked</span>
                    <?php } else { ?>
                        <span class="badge badge-green">Available</span>
                    <?php } ?>

                </div>

            <?php } ?>

        </div>
    </section>

    <section class="card">
        <h2 style="margin-bottom:22px;">Already Booked List</h2>

        <?php if($bookingList && mysqli_num_rows($bookingList) > 0){ ?>

            <?php while($row = mysqli_fetch_assoc($bookingList)){ ?>

                <div class="booking-item">

                    <span class="private-badge">Private Booking</span>

                    <h3>Booked Slot</h3>

                    <p>
                        Lecturer: <?php echo htmlspecialchars($row['lecturerName']); ?><br>
                        Room: <?php echo htmlspecialchars($row['place']); ?><br>
                        Service: <?php echo htmlspecialchars($row['serviceName']); ?><br>
                        Actual Time:
                        <?php echo date("h:i A", strtotime($row['startTime'])); ?>
                        -
                        <?php echo date("h:i A", strtotime($row['endTime'])); ?><br>
                        Status: <?php echo htmlspecialchars($row['status']); ?>
                    </p>
                </div>

            <?php } ?>

        <?php } else { ?>

            <p style="color:#d8b4fe;">No bookings found for this date.</p>

        <?php } ?>

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