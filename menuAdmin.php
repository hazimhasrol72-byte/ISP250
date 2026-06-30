<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: role_select.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Menu</title>
</head>
<body>
    <h2>Admin Menu</h2>

    <p>
        Hi, <?php echo htmlspecialchars($_SESSION['lecturerName'] ?? 'Administrator'); ?>
    </p>

    <a href="adminInterface.php">Go to Admin Dashboard</a><br>
    <a href="logout.php">Logout</a>
</body>
</html>