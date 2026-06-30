<div class="notification-panel">

    <div class="notification-title">
        Notification Smart Appointment
    </div>

    <div class="notification-item">
        <div class="notification-icon">🔔</div>
        <div class="notification-text">New student appointment request received.</div>
    </div>

    <div class="notification-item">
        <div class="notification-icon">🔔</div>
        <div class="notification-text">One appointment is still pending approval.</div>
    </div>

    <div class="notification-item">
        <div class="notification-icon">🔔</div>
        <div class="notification-text">Booking analysis has been updated.</div>
    </div>

</div>

<style>
.notification-panel{
    position:absolute;
    top:90px;
    right:25px;
    width:390px;
    max-height:500px;
    overflow-y:auto;
    background:#2a1046;
    border-radius:22px;
    border:1px solid rgba(255,255,255,.08);
    box-shadow:0 20px 55px rgba(0,0,0,.35);
    display:none;
    z-index:10000;
}

.notification-panel.active{
    display:block;
}

.notification-title{
    padding:22px;
    border-bottom:1px solid rgba(255,255,255,.06);
    font-size:18px;
    font-weight:800;
    color:white;
}

.notification-item{
    display:flex;
    gap:15px;
    padding:18px 22px;
    transition:.3s;
    cursor:pointer;
}

.notification-item:hover{
    background:rgba(255,255,255,.06);
}

.notification-icon{
    width:46px;
    height:46px;
    border-radius:14px;
    background:linear-gradient(135deg,#7c3aed,#9333ea);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:18px;
}

.notification-text{
    flex:1;
    color:white;
    font-size:14px;
    line-height:1.6;
}
</style>