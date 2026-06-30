<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$theme = $_SESSION['theme'] ?? 'dark';

require_once 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: role_select.php");
    exit();
}

$studentno = $_SESSION['studentno'];
$studentname = $_SESSION['studentname'];

/* PEAK HOUR GRAPH DATA - 8AM UNTIL 10PM */
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

while ($row = mysqli_fetch_assoc($peakQuery)) {
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
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Booking Analysis</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
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
    min-height:calc(100vh - 80px);
}

.sidebar{
    width:320px;
    background:rgba(255,255,255,.07);
    backdrop-filter:blur(22px);
    border-right:1px solid rgba(255,255,255,.10);
    padding:35px 24px;
    transition:.3s ease;
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

.main{
    padding:34px 42px;
}

.header{
    margin-bottom:34px;
}

.header h1{
    font-size:42px;
    font-weight:800;
}

.header p{
    margin-top:8px;
    color:#d8b4fe;
    font-size:15px;
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
    position:relative;
}

#peakHourChart{
    width:100% !important;
    height:100% !important;
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

.system-footer{
    width:100%;
    height:70px;
    background:#ececef;
    display:flex;
    justify-content:center;
    align-items:center;
    margin-top:50px;
    border-top:1px solid #dcdcdc;
}

.footer-content{
    color:#4c1d95;
    font-size:16px;
    font-weight:800;
    letter-spacing:.3px;
}

body.light-mode .sidebar,
body.light-mode .graph-card,
body.light-mode .insight-card{
    background:white;
    border:1px solid #e9d5ff;
    color:#1f1235;
}

body.light-mode .header h1,
body.light-mode .graph-card h2,
body.light-mode .insight-card h2,
body.light-mode .peak-box h3{
    color:#1f1235;
}

body.light-mode .header p,
body.light-mode .graph-card p,
body.light-mode .insight-card p,
body.light-mode .peak-box span,
body.light-mode .welcome,
body.light-mode .id,
body.light-mode .nav-title{
    color:#6d28d9;
}

body.light-mode .name{
    color:#1f1235;
}

body.light-mode .peak-box{
    background:#f3e8ff;
    border:1px solid #e9d5ff;
}

body.light-mode .nav a{
    color:#1f1235;
}

body.light-mode .nav a.active,
body.light-mode .nav a:hover{
    background:linear-gradient(90deg,#c084fc,#a855f7);
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

@media(max-width:900px){
    .wrapper,
    .analysis-grid{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:100%;
    }

    .main{
        padding:28px 22px;
    }
}
</style>
</head>

<body class="<?php echo ($theme == 'light') ? 'light-mode' : ''; ?>">

<?php include 'topbar.php'; ?>

<div class="wrapper">

    <aside class="sidebar">
<?php include 'navbar.php'; ?>

    </aside>

    <main class="main">

        <div class="header">
            <h1>Booking Analysis</h1>
            <p>View booking peak hours from 8:00 AM until 10:00 PM.</p>
        </div>

        <div class="analysis-grid">

            <section class="graph-card">
                <h2>Peak Booking Hour</h2>
                <p>This graph shows the most common booking hours in a day.</p>

                <div class="chart-wrap">
                    <canvas id="peakHourChart"></canvas>
                </div>
            </section>

            <aside class="insight-card">
                <h2>Analysis Insight</h2>
                <p>Use this information to avoid crowded consultation hours.</p>

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