<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>MediCentral - Doctor's Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/doctor.css" rel="stylesheet" />
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a class="brand" href="dashboard.html">MediCentral</a>
        </div>
        <nav class="sidebar-nav">
            <a data-page="dashboard" class="nav-link active" href="#">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a data-page="appointments" class="nav-link" href="#">
                <span class="material-symbols-outlined">calendar_today</span>
                <span>Appointments</span>
            </a>
            <a data-page="chats" class="nav-link" href="#">
                <span class="material-symbols-outlined">chat</span>
                <span>Chats</span>
            </a>
            <a data-page="calls" class="nav-link" href="#">
                <span class="material-symbols-outlined">call</span>
                <span>Calls</span>
            </a>
            <a data-page="settings" class="nav-link" href="#">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="status-active">
                <span class="status-dot"></span>
                All Services Active
            </div>
        </div>
    </aside>

    <!-- Main Content Canvas -->
    <div class="main-content">
        <!-- Top Navigation Bar -->
        <header class="topbar">
            <div class="search-bar">
                <span class="material-symbols-outlined">search</span>
                <input placeholder="Search patients, medical records, or reports..." type="text" />
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <span class="material-symbols-outlined">notifications</span>
                </button>
                <button class="icon-btn">
                    <span class="material-symbols-outlined">help</span>
                </button>
                <button class="icon-btn" onclick="window.location.href='settings.php'">
                    <span class="material-symbols-outlined">settings</span>
                </button>
                <div class="user-profile" onclick="window.location.href='settings.php'">
                    <img alt="Dr. Sarah Jenkins" class="avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAzQur896wjb3Hq8t0EGjO_mcb0UwZMKDLF1P4x2Nc6L1XToSJ7L0ENLDVXHoyY8OEUSIgxDbB7kJ_XHfw4SeIC9vPPTM7S3olZokon2StjgJnm2Xuxgik7i_cI_CEPFQ2FzrRzIobvcoFJYAkxYtUCXdSUL7pCAc7TVCIsfvAzjaZQPTnfi6NBr4hHQ-J2WkL_FpO5WFZ66vRee-CpJYi8EC6vI4Pk8Dy34ChRYsYag_EMrDaZpEthQNAP3JCIP2IvetenLmglAJs" />
                    <div>
                        <div class="topbar-user-name">Dr. Sarah Jenkins</div>
                        <div class="topbar-user-role">Senior Cardiologist</div>
                    </div>
                </div>
            </div>
        </header>

        <main id="spa-content"></main>
    </div>

    <script src="/my_doctor/public/assets/js/doctor.js"></script>
</body>
</html>
