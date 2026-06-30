<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SmartQ Booking System</title>
<link href="[fonts.googleapis.com](https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Orbitron:wght@500;700;800;900&display=swap)" rel="stylesheet">
<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}
html{
    scroll-behavior:smooth;
}
body{
    min-height:100vh;
    overflow-x:hidden;
    overflow-y:auto;
    font-family:'Montserrat',sans-serif;
    background:
    radial-gradient(circle at top left,rgba(14,165,233,.30),transparent 26%),
    radial-gradient(circle at bottom right,rgba(168,85,247,.34),transparent 34%),
    linear-gradient(135deg,#050816,#0f172a,#1e0638,#160024);
    color:white;
}
.wrapper{
    min-height:100vh;
    display:flex;
    flex-direction:column;
}
.header{
    width:100%;
    height:110px;
    background:rgba(255,255,255,.96);
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:0 90px;
    box-shadow:0 12px 35px rgba(0,0,0,.12);
    position:relative;
    z-index:9999;
}
.logo-area{
    display:flex;
    align-items:center;
    gap:16px;
}
.logo-mark{
    width:72px;
    height:72px;
    border-radius:20px;
    background:linear-gradient(135deg,#7c3aed,#06b6d4);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:32px;
    font-weight:900;
    font-family:'Orbitron',sans-serif;
    box-shadow:
    0 0 25px rgba(124,58,237,.35),
    0 0 35px rgba(6,182,212,.20);
}
.logo-text{
    line-height:1.1;
}
.small-logo{
    color:#6b21a8;
    font-size:13px;
    font-weight:800;
    letter-spacing:1px;
    text-transform:uppercase;
}
.main-logo{
    font-family:'Orbitron',sans-serif;
    font-size:34px;
    font-weight:900;
    background:linear-gradient(135deg,#6d28d9,#0891b2,#9333ea);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}
.menu{
    display:flex;
    align-items:center;
    gap:38px;
}
.menu a{
    color:#334155;
    text-decoration:none;
    font-size:15px;
    font-weight:800;
    letter-spacing:.3px;
    transition:.25s;
}
.menu a:hover{
    color:#7c3aed;
}
.hero{
    width:100%;
    min-height:calc(100vh - 110px);
    display:grid;
    grid-template-columns:1fr .9fr;
    position:relative;
}
.hero-left{
    position:relative;
    padding:90px 80px 80px 120px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    background:
    linear-gradient(
        120deg,
        rgba(6,182,212,.90) 0%,
        rgba(14,165,233,.86) 35%,
        rgba(99,102,241,.75) 68%,
        rgba(124,58,237,.82) 100%
    );
    overflow:hidden;
}
.hero-left::before{
    content:"";
    position:absolute;
    width:780px;
    height:780px;
    top:-260px;
    right:-260px;
    border-radius:50%;
    background:rgba(255,255,255,.08);
}
.hero-left::after{
    content:"";
    position:absolute;
    width:420px;
    height:420px;
    bottom:-170px;
    left:-120px;
    border-radius:50%;
    background:rgba(255,255,255,.10);
}
.cyber-lines{
    position:absolute;
    inset:0;
    opacity:.20;
    background-image:
    linear-gradient(90deg,rgba(255,255,255,.16) 1px,transparent 1px),
    linear-gradient(rgba(255,255,255,.16) 1px,transparent 1px);
    background-size:70px 70px;
}
.hero-content{
    position:relative;
    z-index:2;
    max-width:760px;
}
.badge{
    display:inline-flex;
    align-items:center;
    gap:10px;
    padding:12px 18px;
    border-radius:999px;
    background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.22);
    backdrop-filter:blur(15px);
    color:white;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:1px;
    margin-bottom:28px;
}
.badge-dot{
    width:10px;
    height:10px;
    border-radius:50%;
    background:#22d3ee;
    box-shadow:0 0 15px #22d3ee;
}
.brand-title{
    font-family:'Orbitron',sans-serif;
    font-size:76px;
    font-weight:900;
    line-height:1.05;
    margin-bottom:26px;
    color:white;
    text-shadow:
    0 0 25px rgba(255,255,255,.18),
    0 0 42px rgba(124,58,237,.25);
}
.hero-desc{
    font-size:19px;
    line-height:1.9;
    color:#eef2ff;
    max-width:680px;
    margin-bottom:42px;
}
.feature-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
}
.feature-card{
    min-height:145px;
    padding:22px;
    border-radius:24px;
    background:rgba(255,255,255,.16);
    border:1px solid rgba(255,255,255,.20);
    backdrop-filter:blur(18px);
    box-shadow:0 20px 45px rgba(15,23,42,.14);
    transition:.3s;
}
.feature-card:hover{
    transform:translateY(-6px);
    background:rgba(255,255,255,.22);
}
.feature-icon{
    width:46px;
    height:46px;
    border-radius:15px;
    background:linear-gradient(135deg,#7c3aed,#06b6d4);
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:20px;
    font-weight:900;
    margin-bottom:16px;
    box-shadow:0 0 20px rgba(124,58,237,.35);
}
.feature-card h3{
    font-size:16px;
    margin-bottom:8px;
}
.feature-card p{
    color:#e0f2fe;
    font-size:13px;
    line-height:1.6;
}
.cool-box{
    margin-top:24px;
    padding:28px;
    border-radius:28px;
    background:
    linear-gradient(135deg,rgba(255,255,255,.18),rgba(255,255,255,.08));
    border:1px solid rgba(255,255,255,.22);
    backdrop-filter:blur(18px);
    box-shadow:0 25px 55px rgba(15,23,42,.18);
}
.cool-box h3{
    font-size:24px;
    margin-bottom:12px;
}
.cool-box p{
    color:#e0f2fe;
    font-size:15px;
    line-height:1.8;
}
.cool-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    margin-top:22px;
}
.cool-item{
    padding:16px;
    border-radius:18px;
    background:rgba(255,255,255,.12);
    border:1px solid rgba(255,255,255,.14);
}
.cool-item strong{
    display:block;
    font-size:18px;
    margin-bottom:4px;
}
.cool-item span{
    color:#e0f2fe;
    font-size:13px;
}
.hero-right{
    position:relative;
    background:
    radial-gradient(circle at top right,rgba(34,211,238,.40),transparent 30%),
    linear-gradient(145deg,#08111f,#0f172a,#111827);
    display:flex;
    justify-content:center;
    align-items:center;
    padding:70px 60px;
    overflow:hidden;
}
.hero-right::before{
    content:"";
    position:absolute;
    width:520px;
    height:520px;
    border-radius:50%;
    background:
    radial-gradient(circle,rgba(34,211,238,.28),rgba(124,58,237,.08),transparent 70%);
    top:40px;
    right:-170px;
    filter:blur(4px);
}
.hero-right::after{
    content:"";
    position:absolute;
    width:360px;
    height:360px;
    border-radius:50%;
    background:rgba(124,58,237,.18);
    bottom:-120px;
    left:-130px;
}
.hologram-card{
    width:100%;
    max-width:520px;
    position:relative;
    z-index:2;
    padding:36px;
    border-radius:34px;
    background:
    linear-gradient(145deg,rgba(255,255,255,.14),rgba(255,255,255,.06));
    border:1px solid rgba(255,255,255,.18);
    backdrop-filter:blur(24px);
    box-shadow:
    0 0 70px rgba(34,211,238,.18),
    0 30px 80px rgba(0,0,0,.42);
}
.dashboard-preview{
    height:260px;
    border-radius:28px;
    background:
    radial-gradient(circle at top left,rgba(34,211,238,.34),transparent 30%),
    linear-gradient(135deg,rgba(124,58,237,.84),rgba(15,23,42,.92));
    border:1px solid rgba(255,255,255,.16);
    padding:24px;
    position:relative;
    overflow:hidden;
    margin-bottom:28px;
}
.preview-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:relative;
    z-index:2;
    margin-bottom:26px;
}
.preview-title{
    font-family:'Orbitron',sans-serif;
    font-size:18px;
    font-weight:800;
}
.preview-pill{
    padding:8px 13px;
    border-radius:999px;
    background:rgba(34,197,94,.18);
    color:#86efac;
    font-size:12px;
    font-weight:800;
}
.preview-stats{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px;
    position:relative;
    z-index:2;
}
.preview-stat{
    padding:18px 14px;
    border-radius:18px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.12);
}
.preview-stat h4{
    font-size:24px;
    margin-bottom:5px;
}
.preview-stat span{
    font-size:11px;
    color:#c4b5fd;
    font-weight:700;
}
.preview-bars{
    display:flex;
    gap:8px;
    align-items:flex-end;
    height:58px;
    margin-top:28px;
    position:relative;
    z-index:2;
}
.preview-bars div{
    flex:1;
    border-radius:999px 999px 4px 4px;
    background:linear-gradient(180deg,#22d3ee,#7c3aed);
    box-shadow:0 0 18px rgba(34,211,238,.25);
}
.preview-bars div:nth-child(1){height:38%;}
.preview-bars div:nth-child(2){height:70%;}
.preview-bars div:nth-child(3){height:48%;}
.preview-bars div:nth-child(4){height:88%;}
.preview-bars div:nth-child(5){height:62%;}
.preview-bars div:nth-child(6){height:95%;}
.login-box h2{
    font-size:48px;
    font-weight:800;
    margin-bottom:14px;
}
.login-box p{
    color:#cbd5e1;
    line-height:1.8;
    font-size:16px;
    margin-bottom:30px;
}
.start-btn{
    width:100%;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:22px;
    border-radius:22px;
    text-decoration:none;
    color:white;
    font-size:17px;
    font-weight:800;
    background:
    linear-gradient(135deg,#7c3aed,#06b6d4);
    border:1px solid rgba(255,255,255,.18);
    box-shadow:
    0 0 35px rgba(6,182,212,.22),
    0 15px 35px rgba(0,0,0,.28);
    transition:.3s;
}
.start-btn:hover{
    transform:translateY(-5px) scale(1.02);
    box-shadow:
    0 0 55px rgba(6,182,212,.32),
    0 25px 55px rgba(0,0,0,.32);
}
.about-section{
    padding:90px 120px;
    background:
    radial-gradient(circle at top right,rgba(34,211,238,.22),transparent 30%),
    radial-gradient(circle at bottom left,rgba(124,58,237,.25),transparent 34%),
    linear-gradient(135deg,#08111f,#111827,#1e0638);
}
.about-card{
    max-width:1100px;
    margin:0 auto;
    padding:48px;
    border-radius:34px;
    background:
    linear-gradient(135deg,rgba(255,255,255,.13),rgba(255,255,255,.06));
    border:1px solid rgba(255,255,255,.18);
    backdrop-filter:blur(22px);
    box-shadow:
    0 0 60px rgba(34,211,238,.10),
    0 30px 80px rgba(0,0,0,.28);
}
.about-title{
    font-family:'Orbitron',sans-serif;
    font-size:42px;
    font-weight:900;
    margin-bottom:18px;
    background:linear-gradient(135deg,#ffffff,#22d3ee,#c084fc,#ffffff);
    background-size:300% 300%;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    animation:glowText 3.5s ease-in-out infinite;
}
@keyframes glowText{
    0%{
        background-position:0% 50%;
        filter:drop-shadow(0 0 8px rgba(34,211,238,.22));
    }
    50%{
        background-position:100% 50%;
        filter:drop-shadow(0 0 18px rgba(192,132,252,.42));
    }
    100%{
        background-position:0% 50%;
        filter:drop-shadow(0 0 8px rgba(34,211,238,.22));
    }
}
.about-card p{
    color:#e0f2fe;
    font-size:17px;
    line-height:1.9;
}
.about-highlight{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-top:34px;
}
.about-mini{
    padding:22px;
    border-radius:24px;
    background:rgba(255,255,255,.10);
    border:1px solid rgba(255,255,255,.14);
}
.about-mini h4{
    font-size:18px;
    margin-bottom:8px;
}
.about-mini span{
    color:#cbd5e1;
    font-size:14px;
    line-height:1.7;
    display:block;
}
.explain-section{
    padding:80px 120px;
    background:
    radial-gradient(circle at top left,rgba(14,165,233,.24),transparent 28%),
    radial-gradient(circle at bottom right,rgba(168,85,247,.28),transparent 30%),
    linear-gradient(135deg,#050816,#0f172a,#160024);
}
.section-title{
    text-align:center;
    margin-bottom:38px;
}
.section-title h2{
    font-family:'Orbitron',sans-serif;
    font-size:40px;
    margin-bottom:10px;
}
.section-title p{
    color:#cbd5e1;
    font-size:16px;
}
.explain-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:24px;
}
.explain-box{
    padding:30px;
    border-radius:28px;
    background:
    linear-gradient(135deg,rgba(255,255,255,.13),rgba(255,255,255,.06));
    border:1px solid rgba(255,255,255,.18);
    backdrop-filter:blur(18px);
    box-shadow:0 25px 55px rgba(0,0,0,.22);
    transition:.3s;
}
.explain-box:hover{
    transform:translateY(-8px);
    box-shadow:
    0 0 45px rgba(34,211,238,.12),
    0 30px 70px rgba(0,0,0,.28);
}
.explain-icon{
    width:58px;
    height:58px;
    border-radius:18px;
    background:linear-gradient(135deg,#7c3aed,#06b6d4);
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:24px;
    margin-bottom:18px;
    box-shadow:0 0 24px rgba(34,211,238,.18);
}
.explain-box h3{
    font-size:24px;
    margin-bottom:14px;
}
.explain-box p{
    color:#cbd5e1;
    font-size:15px;
    line-height:1.8;
}
.footer{
    width:100%;
    padding:24px 40px;
    text-align:center;
    background:rgba(255,255,255,.96);
    color:#4c1d95;
    font-weight:700;
    box-shadow:0 -10px 35px rgba(0,0,0,.08);
}
@media(max-width:1050px){
    .header{
        padding:0 28px;
    }
    .menu{
        display:none;
    }
    .hero{
        grid-template-columns:1fr;
    }
    .hero-left{
        padding:70px 35px;
    }
    .hero-right{
        padding:50px 35px;
    }
    .brand-title{
        font-size:52px;
    }
    .feature-grid,
    .cool-grid,
    .about-highlight,
    .explain-grid{
        grid-template-columns:1fr;
    }
    .about-section,
    .explain-section{
        padding:60px 35px;
    }
}
</style>
</head>
<body>
<div class="wrapper">
    <header class="header">
        <div class="logo-area">
            <div class="logo-mark">SQ</div>
            <div class="logo-text">
                <div class="small-logo">UiTM Smart Platform</div>
                <div class="main-logo">SmartQ</div>
            </div>
        </div>
        <nav class="menu">
            <a href="about.php">ABOUT</a>
            <a href="#features">FEATURES</a>
            <a href="role_select.php">LOGIN</a>
        </nav>
    </header>
    <section class="hero">
        <div class="hero-left">
            <div class="cyber-lines"></div>
            <div class="hero-content">
                <div class="badge">
                    <span class="badge-dot"></span>
                    Futuristic Appointment Platform
                </div>
                <h1 class="brand-title">
                    SmartQ<br>
                    Booking<br>
                    System
                </h1>
                <p class="hero-desc">
                    A next-generation lecturer consultation booking system designed for students,
                    lecturers and administrators with smart scheduling, approval management and
                    booking analytics.
                </p>
                <div class="feature-grid" id="features">
                    <div class="feature-card">
                        <div class="feature-icon">⌁</div>
                        <h3>Student Booking</h3>
                        <p>Submit consultation requests quickly with a clean booking flow.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">◉</div>
                        <h3>Lecturer Approval</h3>
                        <p>Review, approve or reject appointment requests with ease.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⌬</div>
                        <h3>Smart Analytics</h3>
                        <p>Identify peak booking hours through visual analysis.</p>
                    </div>
                </div>
                <div class="cool-box">
                    <h3>Built For Faster Consultation Flow</h3>
                    <p>
                        SmartQ helps reduce manual appointment handling by organising requests,
                        role-based access and booking status in one digital system.
                    </p>
                    <div class="cool-grid">
                        <div class="cool-item">
                            <strong>Real-Time</strong>
                            <span>Track updated appointment status.</span>
                        </div>
                        <div class="cool-item">
                            <strong>Role Access</strong>
                            <span>Separate access for student, lecturer and admin.</span>
                        </div>
                        <div class="cool-item">
                            <strong>Clean UI</strong>
                            <span>Modern interface for easier system usage.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <div class="hologram-card">
                <div class="dashboard-preview">
                    <div class="preview-top">
                        <div class="preview-title">Live Dashboard</div>
                        <div class="preview-pill">ONLINE</div>
                    </div>
                    <div class="preview-stats">
                        <div class="preview-stat">
                            <h4>24</h4>
                            <span>Requests</span>
                        </div>
                        <div class="preview-stat">
                            <h4>16</h4>
                            <span>Approved</span>
                        </div>
                        <div class="preview-stat">
                            <h4>08</h4>
                            <span>Pending</span>
                        </div>
                    </div>
                    <div class="preview-bars">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
                <div class="login-box">
                    <h2>Get Started</h2>
                    <p>
                        Continue into the SmartQ Booking System and select your role to access
                        the correct dashboard.
                    </p>
                    <a href="role_select.php" class="start-btn">
                        Start Your Smart Booking Journey →
                    </a>
                </div>
            </div>
        </div>
    </section>
    <section class="about-section" id="about">
        <div class="about-card">
            <h2 class="about-title">About SmartQ</h2>
            <p>
                SmartQ Booking System is a web-based appointment platform that helps students
                book consultation sessions with lecturers. Lecturers can manage requests,
                while administrators can monitor appointments, manage admin access and view
                peak booking hours through the analytics dashboard.
            </p>
            <div class="about-highlight">
                <div class="about-mini">
                    <h4>Simple Booking</h4>
                    <span>Students can submit appointment requests without complicated steps.</span>
                </div>
                <div class="about-mini">
                    <h4>Smart Management</h4>
                    <span>Lecturers and admins can manage requests clearly in one dashboard.</span>
                </div>
                <div class="about-mini">
                    <h4>Useful Analytics</h4>
                    <span>Peak hour data helps identify common appointment booking times.</span>
                </div>
            </div>
        </div>
    </section>
    <section class="explain-section">
        <div class="section-title">
            <h2>System Functions</h2>
            <p>Each function is designed to support a smoother appointment process.</p>
        </div>
        <div class="explain-grid">
            <div class="explain-box">
                <div class="explain-icon">⌁</div>
                <h3>Student Booking</h3>
                <p>
                    Students can choose a lecturer, select appointment date, time and place,
                    then submit a consultation request. The booking will appear in the student
                    dashboard with its latest status.
                </p>
            </div>
            <div class="explain-box">
                <div class="explain-icon">◉</div>
                <h3>Lecturer Approval</h3>
                <p>
                    Lecturers can view student appointment requests and decide whether to approve
                    or reject them. This helps lecturers organise consultation sessions more clearly.
                </p>
            </div>
            <div class="explain-box">
                <div class="explain-icon">⌬</div>
                <h3>Smart Analytics</h3>
                <p>
                    The system provides booking analysis based on peak appointment hours so lecturers
                    and administrators can understand busy periods and manage schedules better.
                </p>
            </div>
        </div>
    </section>
    <footer class="footer">
        UiTM SmartQ Booking System © 2026
    </footer>
</div>
</body>
</html>