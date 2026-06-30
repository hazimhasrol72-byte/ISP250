<?php 
session_start(); 
include 'db_connect.php'; // Updated to match your exact file name!
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Appointment Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Poppins', sans-serif; }
        body { background-color: #f0f3f8; min-height: 100vh; display: flex; flex-direction: column; }
        
        .dashboard-header { background-color: #ffffff; color: #333; padding: 10px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.03); border-bottom: 1px solid #eaeaea; }
        .logo { font-weight: 700; font-size: 20px; color: #6C63FF; }
        .logo i { margin-right: 8px; }
        .student-profile { display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 500; }
        .profile-pic { width: 40px; height: 40px; border-radius: 50%; background-color: #eaeaea; border: 2px solid #6C63FF; object-fit: cover; }

        .main-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 40px; }
        .content-split { display: flex; background-color: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1); width: 100%; max-width: 950px; min-height: 550px; }

        .visual-panel { flex: 1; background: linear-gradient(135deg, #6C63FF 0%, #4a148c 100%); color: white; padding: 50px; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; position: relative; }
        .visual-panel h1 { font-size: 32px; font-weight: 700; margin-bottom: 15px; }
        .visual-panel p { font-size: 16px; font-weight: 300; opacity: 0.9; max-width: 300px; margin-bottom: 40px; }
        
        .form-panel { flex: 1.2; padding: 50px; overflow-y: auto; }
        .form-panel h2 { color: #333; margin-bottom: 30px; font-weight: 600; font-size: 26px; }

        label { font-weight: 500; display: flex; align-items: center; gap: 8px; margin-top: 18px; color: #555; font-size: 14px; }
        label i { color: #6C63FF; font-size: 16px; }

        select, input[type="date"], input[type="time"] { width: 100%; padding: 14px 18px; margin-top: 10px; border: 2px solid #e1e5ee; border-radius: 10px; font-size: 14px; color: #333; transition: all 0.3s ease; background-color: #fafbfe; }
        select:focus, input[type="date"]:focus, input[type="time"]:focus { border-color: #6C63FF; outline: none; box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.15); background-color: #ffffff; }

        .time-group { display: flex; gap: 18px; }
        .time-box { flex: 1; }

        button { width: 100%; padding: 16px; background: linear-gradient(90deg, #6C63FF 0%, #a29bfe 100%); color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; margin-top: 35px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 6px 12px rgba(108, 99, 255, 0.25); }
        button:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(108, 99, 255, 0.4); }

        @media (max-width: 800px) {
            .content-split { flex-direction: column; }
            .visual-panel { padding: 40px; }
            .form-panel { padding: 30px; }
        }
        
        .swal2-popup { font-family: 'Poppins', sans-serif !important; }
    </style>
</head>
<body>

    <header class="dashboard-header">
        <div class="logo"><i class="fas fa-calendar-alt"></i> SMART APPOINTMENT</div>
        <div class="student-profile">
            <img src="https://via.placeholder.com/150/6C63FF/ffffff?text=H" alt="Student Profile" class="profile-pic">
            <div>
                Welcome, <span style="font-weight: 600;">Hazim!</span><br>
                <span style="font-size: 12px; color: #888;">2024443012</span>
            </div>
        </div>
    </header>

    <main class="main-wrapper">
        <div class="content-split">
            <div class="visual-panel">
                <h1>Book Your Slot!</h1>
                <p>Plan your semester effortlessly by reserving time with your preferred lecturers.</p>
            </div>
            
            <div class="form-panel">
                <h2>New Booking</h2>
                
                <form action="ispInterfaceExp.php" method="POST">
                    
                    <label for="service"><i class="fas fa-briefcase"></i> Select Service</label>
                    <select name="serviceID" id="service" required>
                        <option value="" disabled selected>-- Choose Service --</option>
                        <?php
                        $services = $conn->query("SELECT * FROM SERVICE");
                        while($s = $services->fetch_assoc()) {
                            echo "<option value='".$s['serviceID']."'>".$s['serviceName']." (".$s['duration'].")</option>";
                        }
                        ?>
                    </select>
                    
                    <label for="lecturer"><i class="fas fa-user-tie"></i> Select Lecturer</label>
                    <select name="lecturerID" id="lecturer" required>
                        <option value="" disabled selected>-- Choose Lecturer --</option>
                        <?php
                        $lecturers = $conn->query("SELECT * FROM LECTURER");
                        while($l = $lecturers->fetch_assoc()) {
                            echo "<option value='".$l['lecturerID']."'>".$l['lectName']." (Office: ".$l['OfficeNum'].")</option>";
                        }
                        ?>
                    </select>

                    <label for="date"><i class="fas fa-calendar-day"></i> Booking Date</label>
                    <input type="date" name="bookingDate" id="date" required>

                    <div class="time-group">
                        <div class="time-box">
                            <label for="startTime"><i class="fas fa-clock"></i> Start Time</label>
                            <input type="time" name="startTime" id="startTime" step="300" required>
                        </div>
                        <div class="time-box">
                            <label for="endTime"><i class="fas fa-business-time"></i> End Time</label>
                            <input type="time" name="endTime" id="endTime" step="300" required>
                        </div>
                    </div>

                    <button type="submit" name="submitBooking">Confirm Booking Request</button>
                </form>

            </div>
        </div>
    </main>

    <?php
    if (isset($_SESSION['booking_success'])) {
        $date = $_SESSION['booking_success']['date'];
        $start = $_SESSION['booking_success']['start'];
        $end = $_SESSION['booking_success']['end'];

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Request Sent Successfully!',
                    html: 'Slot requested on <strong>$date</strong>.<br>From <strong>$start</strong> to <strong>$end</strong>.<br><br>Your request is currently <span style=\"color: #f39c12; font-weight: 600;\">Pending approval</span>.',
                    icon: 'success',
                    confirmButtonColor: '#6C63FF',
                    confirmButtonText: 'Understood'
                });
            });
        </script>";

        unset($_SESSION['booking_success']);
    }
    ?>

</body>
</html>