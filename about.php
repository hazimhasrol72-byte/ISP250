<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>About SmartQ</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Montserrat',sans-serif;
}

html{
    scroll-behavior:smooth;
}

body{
    background:
        radial-gradient(circle at top left,rgba(34,211,238,.18),transparent 30%),
        radial-gradient(circle at bottom right,rgba(139,92,246,.20),transparent 35%),
        linear-gradient(135deg,#061733,#10235f,#432b96);
    color:white;
    overflow-x:hidden;
}

.navbar{
    width:100%;
    height:105px;
    background:#f7f7f9;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 90px;
    position:sticky;
    top:0;
    z-index:999;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.logo{
    display:flex;
    align-items:center;
    gap:16px;
}

.logo-icon{
    width:78px;
    height:78px;
    border-radius:20px;
    background:linear-gradient(135deg,#8b5cf6,#06b6d4);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:32px;
    font-weight:900;
    box-shadow:0 15px 35px rgba(139,92,246,.28);
}

.logo-text p{
    color:#5b21b6;
    font-size:13px;
    font-weight:900;
    letter-spacing:2px;
    text-transform:uppercase;
}

.logo-text h2{
    color:#4f46e5;
    font-size:38px;
    font-weight:900;
    line-height:1;
}

.nav-links{
    display:flex;
    gap:42px;
}

.nav-links a{
    text-decoration:none;
    color:#1f2937;
    font-weight:900;
    letter-spacing:.8px;
    transition:.25s;
}

.nav-links a:hover{
    color:#7c3aed;
}

.hero{
    min-height:calc(100vh - 105px);
    padding:90px 90px;
    display:grid;
    grid-template-columns:1.1fr .9fr;
    gap:70px;
    align-items:center;
    position:relative;
}

.hero::before{
    content:"";
    position:absolute;
    width:520px;
    height:520px;
    border-radius:50%;
    background:rgba(255,255,255,.07);
    right:-170px;
    top:130px;
}

.hero::after{
    content:"";
    position:absolute;
    width:390px;
    height:390px;
    border-radius:50%;
    background:rgba(255,255,255,.06);
    left:-130px;
    bottom:-120px;
}

.hero-content,
.hero-card{
    position:relative;
    z-index:2;
}

.badge{
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:14px 24px;
    border-radius:999px;
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.18);
    font-weight:800;
    color:#dffbff;
    margin-bottom:28px;
    letter-spacing:.6px;
}

.badge span{
    width:10px;
    height:10px;
    border-radius:50%;
    background:#22d3ee;
    box-shadow:0 0 15px #22d3ee;
}

.hero h1{
    font-size:72px;
    line-height:1.02;
    font-weight:900;
    margin-bottom:25px;
}

.hero p{
    max-width:720px;
    font-size:19px;
    line-height:1.9;
    color:#e5e7eb;
}

.hero-card{
    padding:38px;
    border-radius:34px;
    background:rgba(255,255,255,.09);
    border:1px solid rgba(255,255,255,.14);
    backdrop-filter:blur(18px);
    box-shadow:0 25px 60px rgba(0,0,0,.28);
}

.hero-card h2{
    font-size:32px;
    margin-bottom:18px;
}

.hero-card p{
    font-size:16px;
    line-height:1.9;
    color:#e5e7eb;
}

.hero-mini{
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:18px;
    margin-top:28px;
}

.mini-card{
    padding:22px;
    border-radius:22px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.10);
}

.mini-card h3{
    font-size:28px;
    color:#22d3ee;
}

.mini-card p{
    font-size:13px;
    line-height:1.5;
    margin-top:6px;
}

.section{
    padding:100px 90px;
}

.section-heading{
    text-align:center;
    max-width:900px;
    margin:0 auto 65px;
}

.section-heading h2{
    font-size:52px;
    font-weight:900;
    margin-bottom:18px;
}

.section-heading p{
    color:#d1d5db;
    font-size:18px;
    line-height:1.8;
}

.feature-grid{
    max-width:1280px;
    margin:auto;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:28px;
}

.feature-card{
    min-height:250px;
    padding:34px;
    border-radius:30px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.12);
    backdrop-filter:blur(16px);
    transition:.3s;
}

.feature-card:hover{
    transform:translateY(-10px);
    background:rgba(255,255,255,.12);
    box-shadow:0 20px 50px rgba(0,0,0,.22);
}

.feature-icon{
    width:58px;
    height:58px;
    border-radius:18px;
    background:linear-gradient(135deg,#06b6d4,#8b5cf6);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    margin-bottom:22px;
}

.feature-card h3{
    font-size:23px;
    margin-bottom:15px;
}

.feature-card p{
    color:#e5e7eb;
    line-height:1.8;
    font-size:15px;
}

.workflow{
    max-width:1250px;
    margin:auto;
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:28px;
}

.step{
    text-align:center;
    padding:20px;
}

.number{
    width:92px;
    height:92px;
    margin:0 auto 22px;
    border-radius:50%;
    background:linear-gradient(135deg,#06b6d4,#8b5cf6);
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:36px;
    font-weight:900;
    box-shadow:0 18px 40px rgba(0,0,0,.22);
}

.step h4{
    font-size:22px;
    margin-bottom:12px;
}

.step p{
    color:#d1d5db;
    line-height:1.7;
    font-size:15px;
}

.team-container{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:30px;
}

.team-card{
    height:560px;
    border-radius:30px;
    overflow:hidden;
    position:relative;
    background:#243c84;
    border:1px solid rgba(255,255,255,.15);
    box-shadow:0 20px 40px rgba(0,0,0,.25);
    transition:.3s ease;
}

.team-card:hover{
    transform:translateY(-8px);
}

.member-photo{
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    object-fit:cover;
}

.team-card::after{
    content:"";
    position:absolute;
    inset:0;
    background:
        linear-gradient(
            to bottom,
            rgba(0,0,0,0) 20%,
            rgba(0,0,0,.35) 55%,
            rgba(12,25,74,.95) 100%
        );
}

.member-content{
    position:absolute;
    left:25px;
    right:25px;
    bottom:25px;
    z-index:2;
}

.member-content h3{
    font-size:30px;
    font-weight:800;
    margin-bottom:20px;
    color:white;
}

.member-info p{
    color:white;
    margin-bottom:10px;
    line-height:1.6;
    font-size:15px;
}

.member-info strong{
    color:#ffffff;
}

.project-showcase{
    padding:100px 90px;
}

.showcase-grid{
    max-width:1250px;
    margin:auto;
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:25px;
}

.showcase-card{
    padding:40px 28px;
    border-radius:28px;
    background:rgba(255,255,255,.09);
    border:1px solid rgba(255,255,255,.12);
    text-align:center;
    transition:.3s;
}

.showcase-card:hover{
    transform:translateY(-8px);
}

.showcase-card h3{
    font-size:46px;
    color:#22d3ee;
    margin-bottom:10px;
}

.showcase-card p{
    color:#e5e7eb;
    font-weight:700;
}

.footer{
    width:100%;
    height:78px;
    background:#ececef;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#4c1d95;
    font-size:16px;
    font-weight:900;
    letter-spacing:.3px;
}

@media(max-width:1100px){
    .hero{
        grid-template-columns:1fr;
    }

    .feature-grid,
    .team-container,
    .showcase-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .workflow{
        grid-template-columns:repeat(2,1fr);
    }
}

@media(max-width:700px){
    .navbar{
        padding:0 25px;
    }

    .nav-links{
        gap:18px;
    }

    .hero,
    .section,
    .team-section,
    .project-showcase{
        padding:70px 25px;
    }

    .hero h1{
        font-size:48px;
    }

    .section-heading h2{
        font-size:38px;
    }

    .feature-grid,
    .team-container,
    .showcase-grid,
    .workflow,
    .hero-mini{
        grid-template-columns:1fr;
    }
}
</style>
</head>

<body>

<nav class="navbar">

    <div class="logo">
        <div class="logo-icon">SQ</div>

        <div class="logo-text">
            <p>UiTM Smart Platform</p>
            <h2>SmartQ</h2>
        </div>
    </div>

    <div class="nav-links">
        <a href="index.php">HOME</a>
        <a href="role_select.php">LOGIN</a>
    </div>

</nav>

<section class="hero">

    <div class="hero-content">

        <div class="badge">
            <span></span>
            SMART APPOINTMENT MANAGEMENT SYSTEM
        </div>

        <h1>About SmartQ</h1>

        <p>
            SmartQ is a lecturer consultation booking system designed to help students,
            lecturers and administrators manage appointments in a more organized,
            transparent and efficient way. The platform focuses on simple booking,
            availability checking, approval management and booking analysis.
        </p>

    </div>

    <div class="hero-card">

        <h2>Why SmartQ Was Built</h2>

        <p>
            Many consultation appointments are usually arranged through informal messages,
            which can lead to missed requests, clashing schedules and unclear availability.
            SmartQ brings the whole process into one digital space so users can book,
            monitor and manage appointments with confidence.
        </p>

        <div class="hero-mini">
            <div class="mini-card">
                <h3>3</h3>
                <p>User roles for student, lecturer and admin.</p>
            </div>

            <div class="mini-card">
                <h3>1</h3>
                <p>Central platform for booking management.</p>
            </div>
        </div>

    </div>

</section>

<section class="section">

    <div class="section-heading">
        <h2>System Features</h2>
        <p>
            SmartQ combines booking, approval, availability checking and analytics
            to make appointment management easier and more reliable.
        </p>
    </div>

    <div class="feature-grid">

        <div class="feature-card">
            <div class="feature-icon">◇</div>
            <h3>Student Booking</h3>
            <p>
                Students can submit consultation requests by selecting lecturer,
                room, date and appointment time.
            </p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">✓</div>
            <h3>Lecturer Approval</h3>
            <p>
                Lecturers can review requests and update appointment status
                through a simple approval workflow.
            </p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">◌</div>
            <h3>Availability Check</h3>
            <p>
                Users can view available and booked slots before making a new appointment.
            </p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">▣</div>
            <h3>Room Management</h3>
            <p>
                Booking conflicts for AEC Room and Office can be reduced through
                slot checking.
            </p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">⌁</div>
            <h3>Booking Analysis</h3>
            <p>
                The system visualizes peak booking hours to help users understand
                common appointment patterns.
            </p>
        </div>

        <div class="feature-card">
            <div class="feature-icon">⚙</div>
            <h3>Admin Monitoring</h3>
            <p>
                Admin users can monitor appointments, manage access and oversee
                booking activity.
            </p>
        </div>

    </div>

</section>

<section class="section">

    <div class="section-heading">
        <h2>How SmartQ Works</h2>
        <p>
            A clear and simple appointment flow from booking request to approved consultation.
        </p>
    </div>

    <div class="workflow">

        <div class="step">
            <div class="number">1</div>
            <h4>Select Lecturer</h4>
            <p>Choose the lecturer and service needed for the consultation.</p>
        </div>

        <div class="step">
            <div class="number">2</div>
            <h4>Choose Time</h4>
            <p>Select a suitable appointment time based on availability.</p>
        </div>

        <div class="step">
            <div class="number">3</div>
            <h4>Approval Process</h4>
            <p>The lecturer reviews and approves or rejects the request.</p>
        </div>

        <div class="step">
            <div class="number">4</div>
            <h4>Attend Meeting</h4>
            <p>The student attends the consultation during the approved slot.</p>
        </div>

    </div>

</section>

<section class="team-section">

    <div class="section-heading">
        <h2>Meet The Development Team</h2>
        <p>
            SmartQ was developed as a student project with the goal of creating
            a practical and user-friendly appointment booking experience.
        </p>
    </div>

    <div class="team-container">

        <div class="team-card">

    <img src="hazim.jpg" alt="Muhammad Hazim" class="member-photo">

    <div class="member-content">

        <h3>Project Leader</h3>

                <div class="member-info">
                    <p><strong>Name:</strong> Muhammad Hazim bin Hasrol Alias</p>
                    <p><strong>Contact:</strong> 011-39804670</p>
                    <p><strong>Email:</strong> hazimhasrol72@gmail.com</p>
                 </div>

                </div>

        </div>

        <div class="team-card">

    <img src="khairil.jpeg" alt="Khairil Amri" class="member-photo">

    <div class="member-content">

        <h3>Back-End Developer</h3>

                <div class="member-info">
                    <p><strong>Name:</strong> Khairil Amri bin Khalid</p>
                    <p><strong>Contact:</strong> 011-11307239</p>
                    <p><strong>Email:</strong> khairilkhalid19@gmail.com</p>
                 </div>

                </div>

        </div>

        <div class="team-card">

    <img src="danish.jpeg" alt="Muhammad Danish" class="member-photo">

    <div class="member-content">

        <h3>UI/UX Designer</h3>

                <div class="member-info">
                    <p><strong>Name:</strong> Muhammad Danish bin Saudi</p>
                    <p><strong>Contact:</strong> 0134237400</p>
                    <p><strong>Email:</strong> danishsaudi7@gmail.com</p>
                 </div>

                </div>

        </div>

        <div class="team-card">

    <img src="harraz.jpeg" alt="Muhammad Harraz" class="member-photo">

    <div class="member-content">

        <h3>Front-End Developer</h3>

                <div class="member-info">
                    <p><strong>Name:</strong> Muhammad Harraz bin Hamran</p>
                    <p><strong>Contact:</strong> 018-6606453</p>
                    <p><strong>Email:</strong> harraz9897@gmail.com</p>
                 </div>

                </div>

        </div>

    </div>

</section>

<section class="project-showcase">

    <div class="section-heading">
        <h2>Project Highlights</h2>
        <p>
            SmartQ is designed to show practical system functions with a clean interface,
            useful workflow and meaningful booking data.
        </p>
    </div>

    <div class="showcase-grid">

        <div class="showcase-card">
            <h3>3</h3>
            <p>User Roles</p>
        </div>

        <div class="showcase-card">
            <h3>2</h3>
            <p>Booking Rooms</p>
        </div>

        <div class="showcase-card">
            <h3>100%</h3>
            <p>Digital Process</p>
        </div>

        <div class="showcase-card">
            <h3>Live</h3>
            <p>Booking Analysis</p>
        </div>

    </div>

</section>

<footer class="footer">
    UiTM SmartQ Booking System © 2026
</footer>

</body>
</html>