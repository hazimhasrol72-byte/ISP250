<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="topbar-main">

    <div class="topbar-left">

        <button class="topbar-btn" id="sidebarToggle" type="button" title="Toggle Sidebar">
            ☰
        </button>

        <div class="search-box">
            <input type="text" placeholder="Search..." readonly>
            <span>⌕</span>
        </div>

    </div>

    <div class="topbar-right">

        <a href="settings.php" class="topbar-btn" title="Settings">
            ⚙
        </a>

    </div>

</div>


<style>
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
    cursor:not-allowed;
}

.search-box input::placeholder{
    color:#ddd6fe;
}

.search-box span{
    color:white;
    font-size:16px;
}

@media(max-width:768px){
    .topbar-main{
        padding:0 18px;
    }

    .search-box{
        display:none;
    }
}
</style>