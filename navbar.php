<?php
$currentFile = basename($_SERVER['PHP_SELF']);
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

$role = $_SESSION['role'] ?? '';

$displayName = "User";
$displayID = "";
$accessText = "USER ACCESS";
$avatarLetter = "U";

if ($role == "student") {
    $displayName = $_SESSION['studentname'] ?? "Student";
    $displayID = $_SESSION['studentno'] ?? "";
    $accessText = "STUDENT ACCESS";
    $avatarLetter = strtoupper(substr($displayName, 0, 1));
} elseif ($role == "lecturer") {
    $displayName = $_SESSION['lecturerName'] ?? "Lecturer";
    $displayID = $_SESSION['lecturerID'] ?? "";
    $accessText = "LECTURER ACCESS";
    $avatarLetter = strtoupper(substr($displayName, 0, 1));
} elseif ($role == "admin") {
    $displayName = $_SESSION['lecturerName'] ?? "Admin";
    $displayID = $_SESSION['lecturerID'] ?? "";
    $accessText = "ADMIN ACCESS";
    $avatarLetter = strtoupper(substr($displayName, 0, 1));
}
?>

<style>
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
    min-width:86px;
    border-radius:50%;
    background:linear-gradient(135deg,#a855f7,#7c3aed);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:38px;
    font-weight:800;
    color:white;
    box-shadow:0 0 30px rgba(168,85,247,.50);
}

.welcome{
    color:#d8b4fe;
    font-size:13px;
    text-transform:uppercase;
    letter-spacing:1px;
}

.name{
    color:white;
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
    box-shadow:inset 4px 0 #c084fc,0 0 20px rgba(168,85,247,.20);
}

.nav-icon{
    font-size:20px;
    min-width:24px;
    text-align:center;
}

.nav-text{
    display:inline-block;
}

body.light-mode .welcome,
body.light-mode .id,
body.light-mode .nav-title{
    color:#6d28d9;
}

body.light-mode .name,
body.light-mode .nav a{
    color:#1f1235;
}

body.light-mode .nav a.active,
body.light-mode .nav a:hover{
    background:linear-gradient(90deg,#c084fc,#a855f7);
    color:white;
}

body.sidebar-collapsed .profile{
    justify-content:center;
    gap:0;
}

body.sidebar-collapsed .profile .avatar{
    width:58px !important;
    height:58px !important;
    min-width:58px !important;
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
</style>

<div class="profile">
    <div class="avatar">
        <?php echo htmlspecialchars($avatarLetter); ?>
    </div>

    <div>
        <div class="welcome">Welcome</div>
        <div class="name"><?php echo htmlspecialchars($displayName); ?></div>
        <div class="id"><?php echo htmlspecialchars($displayID); ?></div>

        <div class="access-badge">
            <span class="access-dot"></span>
            <?php echo htmlspecialchars($accessText); ?>
        </div>
    </div>
</div>

<div class="nav-title">Navigation</div>

<nav class="nav">

<?php if ($role == "student") { ?>

    <a href="dashboard.php" class="<?php echo ($currentFile == 'dashboard.php') ? 'active' : ''; ?>">
        <span class="nav-icon">⌂</span>
        <span class="nav-text">Dashboard</span>
    </a>

    <a href="studentAnalysis.php" class="<?php echo ($currentFile == 'studentAnalysis.php') ? 'active' : ''; ?>">
        <span class="nav-icon">◉</span>
        <span class="nav-text">Analysis</span>
    </a>

    <a href="availability.php" class="<?php echo ($currentFile == 'availability.php') ? 'active' : ''; ?>">
        <span class="nav-icon">◌</span>
        <span class="nav-text">Availability</span>
    </a>

    <a href="ispInterfaceExp.php" class="<?php echo ($currentFile == 'ispInterfaceExp.php' || $currentFile == 'edit_booking.php') ? 'active' : ''; ?>">
        <span class="nav-icon">◇</span>
        <span class="nav-text">New Booking</span>
    </a>

    <a href="settings.php" class="<?php echo ($currentFile == 'settings.php') ? 'active' : ''; ?>">
        <span class="nav-icon">⚙</span>
        <span class="nav-text">Settings</span>
    </a>

    <a href="logout.php">
        <span class="nav-icon">→</span>
        <span class="nav-text">Logout</span>
    </a>

<?php } elseif ($role == "lecturer") { ?>

    <a href="lecturerInterface.php" class="<?php echo ($currentFile == 'lecturerInterface.php' && $currentPage == 'dashboard') ? 'active' : ''; ?>">
        <span class="nav-icon">⌂</span>
        <span class="nav-text">Dashboard</span>
    </a>

    <a href="lecturerInterface.php?page=analysis" class="<?php echo ($currentFile == 'lecturerInterface.php' && $currentPage == 'analysis') ? 'active' : ''; ?>">
        <span class="nav-icon">◉</span>
        <span class="nav-text">Analysis</span>
    </a>

    <a href="availability.php" class="<?php echo ($currentFile == 'availability.php') ? 'active' : ''; ?>">
        <span class="nav-icon">◌</span>
        <span class="nav-text">Availability</span>
    </a>

    <a href="settings.php" class="<?php echo ($currentFile == 'settings.php') ? 'active' : ''; ?>">
        <span class="nav-icon">⚙</span>
        <span class="nav-text">Settings</span>
    </a>

    <a href="logout.php">
        <span class="nav-icon">→</span>
        <span class="nav-text">Logout</span>
    </a>

<?php } elseif ($role == "admin") { ?>

    <a href="adminInterface.php" class="<?php echo ($currentFile == 'adminInterface.php' && $currentPage == 'dashboard') ? 'active' : ''; ?>">
        <span class="nav-icon">⌂</span>
        <span class="nav-text">Dashboard</span>
    </a>

    <a href="adminInterface.php?page=analysis" class="<?php echo ($currentFile == 'adminInterface.php' && $currentPage == 'analysis') ? 'active' : ''; ?>">
        <span class="nav-icon">◉</span>
        <span class="nav-text">Analysis</span>
    </a>

    <a href="availability.php" class="<?php echo ($currentFile == 'availability.php') ? 'active' : ''; ?>">
        <span class="nav-icon">◌</span>
        <span class="nav-text">Availability</span>
    </a>

    <a href="adminInterface.php?page=appoint" class="<?php echo ($currentFile == 'adminInterface.php' && $currentPage == 'appoint') ? 'active' : ''; ?>">
        <span class="nav-icon">◇</span>
        <span class="nav-text">Appoint</span>
    </a>

    <a href="settings.php" class="<?php echo ($currentFile == 'settings.php') ? 'active' : ''; ?>">
        <span class="nav-icon">⚙</span>
        <span class="nav-text">Settings</span>
    </a>

    <a href="logout.php">
        <span class="nav-icon">→</span>
        <span class="nav-text">Logout</span>
    </a>

<?php } else { ?>

    <a href="role_select.php">
        <span class="nav-icon">⌂</span>
        <span class="nav-text">Home</span>
    </a>

<?php } ?>

</nav>
