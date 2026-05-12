    <?php
    // Start the session
    session_start();

    // Include the database connection
    include 'db_connect.php';

    $error_message = "";

    if (isset($_POST['btnLogin'])) {
        $studentID = $_POST['studentID'];

        // Security: Prevent SQL Injection
        $studentID = mysqli_real_escape_string($conn, $studentID);

        // SQL Query
        $sql = "SELECT * FROM STUDENTS WHERE studentID = '$studentID'";
        $result = mysqli_query($conn, $sql);

        // Check if the student ID exists
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            
            // Save student info into Session
            $_SESSION['loggedIn'] = true;
            $_SESSION['studentID'] = $row['studentID'];
            $_SESSION['studName'] = $row['studName'];
            
            // Redirect to the beautiful booking page!
            header("Location: booking_form.php");
            exit();
        } else {
            $error_message = "Invalid Student ID. Please try again.";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Smart Appointment - Sign In</title>
        
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.min.css">
        
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
                font-family: 'Poppins', sans-serif;
            }

            body {
                background-color: #f0f3f8; /* Matches the dashboard background */
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                padding: 20px;
            }

            .login-wrapper {
                display: flex;
                background-color: #ffffff;
                border-radius: 20px;
                overflow: hidden;
                box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
                width: 100%;
                max-width: 850px;
                min-height: 450px;
            }

            /* --- Left Side: Branding / Visual --- */
            .login-visual {
                flex: 1;
                background: linear-gradient(135deg, #6C63FF 0%, #4a148c 100%);
                color: white;
                padding: 50px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
            }

            .login-visual i.main-icon {
                font-size: 60px;
                margin-bottom: 20px;
                opacity: 0.9;
            }

            .login-visual h1 {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 15px;
            }

            .login-visual p {
                font-size: 15px;
                font-weight: 300;
                opacity: 0.85;
                line-height: 1.6;
            }

            /* --- Right Side: Form --- */
            .login-form-container {
                flex: 1;
                padding: 60px 50px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .login-form-container h2 {
                color: #333;
                font-size: 24px;
                font-weight: 600;
                margin-bottom: 5px;
            }

            .subtitle {
                color: #888;
                font-size: 14px;
                margin-bottom: 30px;
            }

            /* Upgraded Error Alert */
            .error-alert {
                background-color: #fff1f0;
                border-left: 4px solid #ff4d4f;
                color: #cf1322;
                padding: 12px 15px;
                border-radius: 6px;
                font-size: 13px;
                margin-bottom: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 500;
            }

            .input-group {
                position: relative;
                margin-bottom: 25px;
            }

            .input-group i {
                position: absolute;
                left: 15px;
                top: 50%;
                transform: translateY(-50%);
                color: #6C63FF;
                font-size: 16px;
            }

            input[type="text"] {
                width: 100%;
                padding: 14px 15px 14px 45px; /* Extra left padding for the icon */
                border: 2px solid #e1e5ee;
                border-radius: 10px;
                font-size: 14px;
                color: #333;
                transition: all 0.3s ease;
                background-color: #fafbfe;
            }

            input[type="text"]:focus {
                border-color: #6C63FF;
                outline: none;
                box-shadow: 0 0 0 4px rgba(108, 99, 255, 0.15);
                background-color: #ffffff;
            }

            button {
                width: 100%;
                padding: 15px;
                background: linear-gradient(90deg, #6C63FF 0%, #a29bfe 100%);
                color: white;
                border: none;
                border-radius: 10px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 6px 12px rgba(108, 99, 255, 0.25);
            }

            button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 18px rgba(108, 99, 255, 0.4);
            }

            /* Mobile Responsiveness */
            @media (max-width: 768px) {
                .login-wrapper {
                    flex-direction: column;
                }
                .login-visual {
                    padding: 40px 20px;
                }
                .login-form-container {
                    padding: 40px 30px;
                }
            }
        </style>
    </head>
    <body>

        <div class="login-wrapper">
            <div class="login-visual">
                <i class="fas fa-calendar-check main-icon"></i>
                <h1>Smart Appointment</h1>
                <p>Connect with your lecturers and manage your academic schedule seamlessly.</p>
            </div>

            <div class="login-form-container">
                <h2>Student Login</h2>
                <p class="subtitle">Please enter your ID to continue.</p>

                <?php if($error_message != "") { 
                    echo "<div class='error-alert'>
                            <i class='fas fa-exclamation-circle'></i> 
                            $error_message
                        </div>"; 
                } ?>

                <form method="POST" action="">
                    <div class="input-group">
                        <i class="fas fa-id-badge"></i>
                        <input type="text" name="studentID" placeholder="Student ID (e.g. 2024443012)" required autofocus>
                    </div>
                    <button type="submit" name="btnLogin">Sign In <i class="fas fa-arrow-right" style="margin-left: 8px;"></i></button>
                </form>
            </div>
        </div>

    </body>
    </html>