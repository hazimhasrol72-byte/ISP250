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
$studentname = $_SESSION['studentname'];

/* SEARCH + PAGINATION */
$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$searchSafe = mysqli_real_escape_string($connAppointment, $search);

$pageNo = isset($_GET['p']) ? (int)$_GET['p'] : 1;
if ($pageNo < 1) {
    $pageNo = 1;
}

$limit = 5;
$offset = ($pageNo - 1) * $limit;

$whereSearch = "WHERE studentno = '$studentno'";

if ($search !== "") {
    $whereSearch .= "
        AND (
            lecturerName LIKE '%$searchSafe%'
            OR serviceName LIKE '%$searchSafe%'
            OR place LIKE '%$searchSafe%'
            OR bookingDate LIKE '%$searchSafe%'
            OR startTime LIKE '%$searchSafe%'
            OR endTime LIKE '%$searchSafe%'
            OR status LIKE '%$searchSafe%'
        )
    ";
}

$countQ = mysqli_query($connAppointment, "
    SELECT COUNT(*) AS totalData
    FROM bookings
    $whereSearch
");

$countData = mysqli_fetch_assoc($countQ);
$totalRows = $countData['totalData'] ?? 0;
$totalPages = ceil($totalRows / $limit);

$query = "
    SELECT *
    FROM bookings
    $whereSearch
    ORDER BY bookingDate ASC, startTime ASC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($connAppointment, $query);

$total = mysqli_num_rows(mysqli_query($connAppointment, "
    SELECT bookingID FROM bookings WHERE studentno='$studentno'
"));

$pending = mysqli_num_rows(mysqli_query($connAppointment, "
    SELECT bookingID FROM bookings WHERE studentno='$studentno' AND status='Pending'
"));

$approved = mysqli_num_rows(mysqli_query($connAppointment, "
    SELECT bookingID FROM bookings WHERE studentno='$studentno' AND status='Approved'
"));
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

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

.wrapper{
    display:grid;
    grid-template-columns:320px 1fr;
    min-height:calc(100vh - 78px);
}

.sidebar{
    width:320px;
    background:rgba(255,255,255,.07);
    backdrop-filter:blur(22px);
    border-right:1px solid rgba(255,255,255,.10);
    padding:35px 24px;
    transition:.3s ease;
}

body.light-mode .sidebar{
    background:rgba(255,255,255,.85);
    border-right:1px solid #d8c7ff;
}

.profile{
    display:flex;
    align-items:center;
    gap:18px;
    padding-bottom:30px;
    border-bottom:1px solid rgba(255,255,255,.10);
}

.avatar{
    width:86px;
    height:86px;
    border-radius:50%;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:38px;
    font-weight:800;
    box-shadow:0 0 30px rgba(168,85,247,.50);
}

.welcome{
    color:#d8b4fe;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:1px;
}

.name{
    font-size:16px;
    font-weight:800;
    line-height:1.35;
    text-transform:uppercase;
    margin-top:3px;
}

.id{
    font-size:14px;
    color:#c4b5fd;
    margin-top:5px;
}

.access-badge{
    display:inline-flex;
    align-items:center;
    gap:7px;
    margin-top:12px;
    padding:10px 20px;
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
    background:#ffffff;
    box-shadow:0 0 10px #ffffff;
}

.main{
    padding:34px 42px;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:28px;
    gap:20px;
}

.header h1{
    font-size:36px;
    font-weight:800;
    letter-spacing:-1px;
}

.header p{
    margin-top:8px;
    color:#d8b4fe;
    font-size:15px;
}

.new-btn{
    display:inline-block;
    padding:14px 22px;
    border-radius:16px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    text-decoration:none;
    font-size:14px;
    font-weight:800;
    box-shadow:0 0 25px rgba(168,85,247,.45);
}

.summary{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:26px;
}

.summary-card{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:22px;
    padding:22px;
    box-shadow:0 0 30px rgba(168,85,247,.12);
}

.summary-card h2{
    font-size:28px;
}

.summary-card p{
    color:#d8b4fe;
    margin-top:6px;
    font-weight:600;
}

.section-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:18px;
}

.section-title{
    font-size:24px;
    font-weight:800;
}

.appointment-search{
    display:flex;
    align-items:center;
    gap:10px;
}

.appointment-search input{
    width:260px;
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

.search-result-text{
    color:#d8b4fe;
    margin-bottom:18px;
    font-size:14px;
    font-weight:600;
}

.appointment-list{
    display:grid;
    gap:18px;
}

.appointment-card{
    position:relative;
    overflow:hidden;
    border-radius:24px;
    padding:28px;
    box-shadow:0 0 30px rgba(168,85,247,.12);
}

.appointment-card.Pending{
    background:rgba(250,204,21,.14);
    border:1px solid rgba(250,204,21,.35);
    border-left:8px solid #facc15;
}

.appointment-card.Approved{
    background:rgba(34,197,94,.14);
    border:1px solid rgba(34,197,94,.35);
    border-left:8px solid #22c55e;
}

.appointment-card.Cancelled,
.appointment-card.Rejected{
    background:rgba(239,68,68,.16);
    border:1px solid rgba(239,68,68,.42);
    border-left:8px solid #ef4444;
}

.status-box{
    position:absolute;
    top:22px;
    right:22px;
    padding:11px 18px;
    border-radius:999px;
    font-size:12px;
    font-weight:900;
    letter-spacing:.7px;
    text-transform:uppercase;
}

.status-box.Pending{
    background:#facc15;
    color:#422006;
}

.status-box.Approved{
    background:#22c55e;
    color:white;
}

.status-box.Cancelled,
.status-box.Rejected{
    background:#ef4444;
    color:white;
}

.card-top{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    padding-right:150px;
}

.date{
    color:#d8b4fe;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    margin-bottom:10px;
}

.time{
    font-size:28px;
    font-weight:800;
    margin-bottom:14px;
}

.details{
    color:#f3e8ff;
    line-height:1.8;
    font-size:15px;
}

.action-icons{
    position:absolute;
    right:22px;
    bottom:22px;
    display:flex;
    gap:10px;
    align-items:center;
}

.action-icons form{
    margin:0;
}

.icon-btn{
    width:44px;
    height:44px;
    border:none;
    border-radius:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    cursor:pointer;
    font-size:20px;
    font-weight:800;
    transition:.25s;
}

.edit-icon{
    background:#facc15;
    color:#422006;
}

.delete-icon{
    background:#ef4444;
    color:white;
}

.icon-btn:hover{
    transform:scale(1.08);
}

.empty{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:24px;
    padding:55px;
    text-align:center;
    color:#d8b4fe;
}

.empty h2{
    color:white;
    margin-bottom:10px;
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

body.light-mode .summary-card,
body.light-mode .appointment-card,
body.light-mode .empty{
    background:white;
    border:1px solid #e9d5ff;
    color:#1f1235;
}

body.light-mode .header h1,
body.light-mode .section-title,
body.light-mode .name,
body.light-mode .time,
body.light-mode .details,
body.light-mode strong{
    color:#1f1235;
}

body.light-mode .header p,
body.light-mode .summary-card p,
body.light-mode .date,
body.light-mode .welcome,
body.light-mode .id,
body.light-mode .search-result-text{
    color:#6d28d9;
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

body.sidebar-collapsed .wrapper{
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

body.sidebar-collapsed .nav{
    margin-top:35px;
    align-items:center;
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

body.sidebar-collapsed .nav-icon{
    font-size:23px !important;
    display:block;
}

body.sidebar-collapsed .nav-text{
    display:none !important;
}

.nav-title{
    margin-top:38px;
    margin-bottom:18px;
    font-size:13px;
    color:#d8b4fe;
    text-transform:uppercase;
    letter-spacing:1px;
    font-weight:700;
}

.nav{
    display:flex;
    flex-direction:column;
}

.nav a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:18px 22px;
    margin-bottom:14px;
    border-radius:18px;
    color:white;
    text-decoration:none;
    font-size:15px;
    font-weight:800;
    transition:.25s;
}

.nav a.active,
.nav a:hover{
    background:linear-gradient(90deg,rgba(168,85,247,.48),rgba(124,58,237,.25));
    box-shadow:
    inset 4px 0 #c084fc,
    0 0 20px rgba(168,85,247,.20);
}

.nav-icon{
    font-size:20px;
    min-width:24px;
    text-align:center;
}

.nav-text{
    display:inline-block;
}

body.light-mode .nav-title{
    color:#6d28d9;
}

body.light-mode .nav a{
    color:#1f1235;
}

body.light-mode .nav a.active,
body.light-mode .nav a:hover{
    background:linear-gradient(90deg,#c084fc,#a855f7);
    color:white;
}

@media(max-width:900px){
    .wrapper{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:100%;
    }

    .main{
        padding:28px 22px;
    }

    .header{
        flex-direction:column;
        align-items:flex-start;
    }

    .summary{
        grid-template-columns:1fr;
    }

    .section-head{
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

    .card-top{
        padding-right:0;
        padding-top:50px;
    }

    .action-icons{
        position:static;
        margin-top:20px;
    }
}
</style>
</head>

<body class="<?php echo ($theme === 'light') ? 'light-mode' : ''; ?>">

<?php include 'topbar.php'; ?>

<div class="wrapper">

    <aside class="sidebar">
<?php include 'navbar.php'; ?>

    </aside>

    <main class="main">

        <div class="header">
            <div>
                <h1>My Schedule & Appointments</h1>
                <p>View and manage your lecturer appointment bookings.</p>
            </div>

            <a href="ispInterfaceExp.php" class="new-btn">+ New Booking</a>
        </div>

        <div class="summary">
            <div class="summary-card">
                <h2><?php echo $total; ?></h2>
                <p>Total Bookings</p>
            </div>

            <div class="summary-card">
                <h2><?php echo $pending; ?></h2>
                <p>Pending</p>
            </div>

            <div class="summary-card">
                <h2><?php echo $approved; ?></h2>
                <p>Approved</p>
            </div>
        </div>

        <div class="section-head">
            <div class="section-title">Current Semester Appointments</div>

            <form method="GET" action="dashboard.php" class="appointment-search">
                <input 
                    type="text" 
                    name="search" 
                    placeholder="Search appointment..." 
                    value="<?php echo htmlspecialchars($search); ?>"
                >

                <button type="submit">Search</button>

                <?php if ($search !== "") { ?>
                    <a href="dashboard.php" class="clear-search">Clear</a>
                <?php } ?>
            </form>
        </div>

        <?php if ($search !== "") { ?>
            <div class="search-result-text">
                Search result for: <strong><?php echo htmlspecialchars($search); ?></strong>
                — <?php echo $totalRows; ?> result(s) found.
            </div>
        <?php } ?>

        <div class="appointment-list">

        <?php if ($result && mysqli_num_rows($result) > 0) { ?>

            <?php while ($row = mysqli_fetch_assoc($result)) { ?>

                <div class="appointment-card <?php echo htmlspecialchars($row['status']); ?>">

                    <div class="status-box <?php echo htmlspecialchars($row['status']); ?>">
                        <?php echo htmlspecialchars($row['status']); ?>
                    </div>

                    <div class="card-top">
                        <div>
                            <div class="date">
                                <?php echo date("l, d F Y", strtotime($row['bookingDate'])); ?>
                            </div>

                            <div class="time">
                                <?php echo date("h:i A", strtotime($row['startTime'])); ?>
                                -
                                <?php echo date("h:i A", strtotime($row['endTime'])); ?>
                            </div>

                            <div class="details">
                                Lecturer:
                                <strong><?php echo htmlspecialchars($row['lecturerName']); ?></strong><br>

                                Service:
                                <?php echo htmlspecialchars($row['serviceName']); ?><br>

                                Place:
                                <?php echo htmlspecialchars($row['place']); ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($row['status'] === "Pending") { ?>

                        <div class="action-icons">

                            <a href="edit_booking.php?bookingToken=<?php echo urlencode(createToken($row['bookingID'])); ?>"
                               class="icon-btn edit-icon"
                               title="Edit Booking">
                                ✎
                            </a>

                            <form method="POST" action="cancel_booking.php">
                                <input type="hidden" name="bookingToken" value="<?php echo htmlspecialchars(createToken($row['bookingID'])); ?>">

                                <button type="submit"
                                        class="icon-btn delete-icon"
                                        title="Cancel Booking"
                                        onclick="return confirm('Are you sure you want to cancel this booking?')">
                                    🗑
                                </button>
                            </form>

                        </div>

                    <?php } ?>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty">
                <h2>No appointment booking found.</h2>
                <p>No appointment matches your search or you have no bookings yet.</p>
            </div>

        <?php } ?>

        </div>

        <?php if ($totalPages > 1) { ?>

            <div class="pagination">

                <?php if ($pageNo > 1) { ?>
                    <a href="dashboard.php?p=<?php echo $pageNo - 1; ?>&search=<?php echo urlencode($search); ?>">
                        Previous
                    </a>
                <?php } ?>

                <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                    <a 
                        href="dashboard.php?p=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"
                        class="<?php echo ($i == $pageNo) ? 'active-page' : ''; ?>"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php } ?>

                <?php if ($pageNo < $totalPages) { ?>
                    <a href="dashboard.php?p=<?php echo $pageNo + 1; ?>&search=<?php echo urlencode($search); ?>">
                        Next
                    </a>
                <?php } ?>

            </div>

        <?php } ?>

        <?php include 'footer.php'; ?>

    </main>

</div>

<?php
if (isset($_SESSION['booking_success'])) {

    $popupTitle = $_SESSION['booking_success_title'] ?? "Success!";
    $popupText = $_SESSION['booking_success'];

    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({
                title: ".json_encode($popupTitle).",
                text: ".json_encode($popupText).",
                icon: 'success',
                confirmButtonColor: '#a855f7',
                confirmButtonText: 'Okay',
                timer: 2600,
                timerProgressBar: true
            });
        });
    </script>";

    unset($_SESSION['booking_success']);
    unset($_SESSION['booking_success_title']);
}

if (isset($_SESSION['booking_cancelled'])) {

    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({
                title: 'Booking Cancelled!',
                text: ".json_encode($_SESSION['booking_cancelled']).",
                icon: 'success',
                confirmButtonColor: '#a855f7',
                confirmButtonText: 'Okay',
                timer: 2600,
                timerProgressBar: true
            });
        });
    </script>";

    unset($_SESSION['booking_cancelled']);
}

if (isset($_SESSION['booking_error'])) {

    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            Swal.fire({
                title: 'Booking Failed',
                text: ".json_encode($_SESSION['booking_error']).",
                icon: 'error',
                confirmButtonColor: '#a855f7',
                confirmButtonText: 'Okay'
            });
        });
    </script>";

    unset($_SESSION['booking_error']);
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
});
</script>

</body>
</html>