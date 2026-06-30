<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['role'])) {
    header("Location: role_select.php");
    exit();
}

if (isset($_POST['toggle_theme'])) {

    if (!isset($_SESSION['theme'])) {
        $_SESSION['theme'] = 'dark';
    }

    $_SESSION['theme'] = ($_SESSION['theme'] == 'dark') ? 'light' : 'dark';

    header("Location: settings.php");
    exit();
}

$theme = $_SESSION['theme'] ?? 'dark';
$role = $_SESSION['role'];

$name = "";
$id = "";
$email = "-";
$title = "";

if ($role == "student") {

    $studentno = $_SESSION['studentno'];

    $studentQuery = mysqli_query($connStudent, "
        SELECT *
        FROM students
        WHERE studentno = '$studentno'
    ");

    $student = mysqli_fetch_assoc($studentQuery);

    $name = $student['studentname'];
    $id = $student['studentno'];
    $email = $student['studentemailuitm'] ?? "-";
    $title = "Student";
}

if ($role == "lecturer" || $role == "admin") {

    $lecturerID = $_SESSION['lecturerID'];

    $lecturerQuery = mysqli_query($connLecturer, "
        SELECT *
        FROM user
        WHERE USER_ID = '$lecturerID'
    ");

    $lecturer = mysqli_fetch_assoc($lecturerQuery);

    $name = $lecturer['USER_NAME'];
    $id = $lecturer['USER_ID'];
    $email = isset($lecturer['USER_EMAIL']) ? $lecturer['USER_EMAIL'] : "-";
    $title = ($role == "admin") ? "Admin" : "Lecturer";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Settings</title>

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

.topbar-main{
    height:78px;
    background:linear-gradient(90deg,#2a1046,#4c1d95,#6d28d9);
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 35px;
    position:sticky;
    top:0;
    z-index:9999;
    box-shadow:0 10px 35px rgba(0,0,0,.22);
}

.topbar-left,
.topbar-right{
    display:flex;
    align-items:center;
    gap:18px;
}

.topbar-btn{
    width:48px;
    height:48px;
    border:none;
    border-radius:14px;
    background:transparent;
    color:white;
    font-size:23px;
    cursor:pointer;
    position:relative;
    transition:.25s ease;
    display:flex;
    justify-content:center;
    align-items:center;
    text-decoration:none;
}

.topbar-btn:hover{
    transform:scale(1.08);
    background:rgba(255,255,255,.12);
}

.search-box{
    width:320px;
    height:46px;
    background:rgba(255,255,255,.10);
    border-radius:999px;
    display:flex;
    align-items:center;
    padding:0 18px;
}

.search-box input{
    flex:1;
    background:transparent;
    border:none;
    outline:none;
    color:white;
    font-size:15px;
}

.search-box input::placeholder{
    color:#ddd6fe;
}

.notification-count{
    position:absolute;
    top:-4px;
    right:-4px;
    background:#ef4444;
    color:white;
    font-size:11px;
    font-weight:800;
    padding:4px 7px;
    border-radius:999px;
}

.notification-panel{
    display:none;
    position:absolute;
    top:88px;
    right:25px;
    width:390px;
    background:#2a1046;
    border:1px solid rgba(255,255,255,.12);
    border-radius:20px;
    box-shadow:0 20px 55px rgba(0,0,0,.35);
    z-index:10000;
    overflow:hidden;
}

.notification-panel.show{
    display:block;
}

.notification-title{
    padding:18px 22px;
    font-weight:800;
    color:white;
    border-bottom:1px solid rgba(255,255,255,.10);
}

.notification-item{
    padding:16px 22px;
    color:white;
    font-size:14px;
    line-height:1.6;
}

.notification-item:hover{
    background:rgba(255,255,255,.08);
}

.layout{
    display:grid;
    grid-template-columns:320px 1fr;
    min-height:calc(100vh - 78px);
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
    transition:.3s ease;
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

.header{
    margin-bottom:34px;
}

.header h1{
    font-size:42px;
    font-weight:800;
}

body.light-mode .header h1{
    color:#1f1235;
}

.header p{
    color:#d8b4fe;
    margin-top:8px;
}

body.light-mode .header p{
    color:#6d28d9;
}

.settings-grid{
    display:grid;
    grid-template-columns:1.3fr .8fr;
    gap:28px;
}

.panel{
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    border-radius:28px;
    padding:34px;
    box-shadow:0 0 38px rgba(168,85,247,.13);
}

body.light-mode .panel{
    background:white;
    color:#1f1235;
    border:1px solid #e5d9ff;
}

.panel h2{
    margin-bottom:24px;
    font-size:28px;
}

body.light-mode .panel h2{
    color:#1f1235;
}

.info-row{
    background:rgba(255,255,255,.08);
    border-radius:18px;
    padding:18px 20px;
    margin-bottom:16px;
}

body.light-mode .info-row{
    background:#f3e8ff;
}

.info-row label{
    display:block;
    font-size:12px;
    color:#d8b4fe;
    text-transform:uppercase;
    font-weight:800;
    margin-bottom:6px;
}

body.light-mode .info-row label{
    color:#7c3aed;
}

.info-row div{
    font-size:17px;
    font-weight:700;
}

body.light-mode .info-row div{
    color:#111827;
}

.mode-card{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:rgba(255,255,255,.08);
    padding:18px 20px;
    border-radius:18px;
    margin-bottom:18px;
}

body.light-mode .mode-card{
    background:#f3e8ff;
}

body.light-mode .mode-card strong{
    color:#111827;
}

body.light-mode .mode-card span{
    color:#6d28d9;
}

.toggle-btn{
    border:none;
    padding:12px 20px;
    border-radius:999px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 0 18px rgba(168,85,247,.45);
}

.back-btn{
    display:inline-block;
    margin-top:18px;
    padding:14px 22px;
    border-radius:16px;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    color:white;
    text-decoration:none;
    font-weight:800;
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

body.sidebar-collapsed .nav-text{
    display:none !important;
}

body.sidebar-collapsed .nav-icon{
    font-size:23px !important;
    display:block;
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

    <div class="header">
        <h1>Personal Settings</h1>
        <p>View your account information and change display mode.</p>
    </div>

    <div class="settings-grid">

        <section class="panel">
            <h2>Account Information</h2>

            <div class="info-row">
                <label>Name</label>
                <div><?php echo $name; ?></div>
            </div>

            <div class="info-row">
                <label>ID</label>
                <div><?php echo $id; ?></div>
            </div>

            <div class="info-row">
                <label>Role</label>
                <div><?php echo $title; ?></div>
            </div>

            <div class="info-row">
                <label>Email</label>
                <div><?php echo $email; ?></div>
            </div>
        </section>

        <aside class="panel">
            <h2>Display Mode</h2>

            <div class="mode-card">
                <div>
                    <strong>Appearance</strong><br>
                    <span class="small">Change your interface theme.</span>
                </div>

                <form method="POST">
                    <button type="submit" name="toggle_theme" class="toggle-btn">
                        <?php echo ($theme == 'light') ? 'Switch To Dark Mode' : 'Switch To Light Mode'; ?>
                    </button>
                </form>
            </div>

            <?php if ($role == "student") { ?>
                <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
            <?php } elseif ($role == "admin") { ?>
                <a href="adminInterface.php" class="back-btn">Back to Dashboard</a>
            <?php } else { ?>
                <a href="lecturerInterface.php" class="back-btn">Back to Dashboard</a>
            <?php } ?>

        </aside>

    </div>

    <?php include 'footer.php'; ?>

</main>

</div>

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