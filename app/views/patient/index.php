<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCentral - Patient Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/my_doctor/public/assets/css/patient.css">
</head>
<body>
    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h1>MediCentral</h1>
            <p>Clinical Portal</p>
        </div>
        <nav class="nav-list">
            <a data-page="dashboard" href="#" class="nav-item active">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a data-page="appointments" href="#" class="nav-item">
                <span class="material-symbols-outlined">event_note</span>
                <span>Appointments</span>
            </a>
            <a data-page="doctors" href="#" class="nav-item">
                <span class="material-symbols-outlined">medical_services</span>
                <span>Doctors</span>
            </a>
            <a data-page="calls" href="#" class="nav-item">
                <span class="material-symbols-outlined">videocam</span>
                <span>Calls</span>
            </a>
            <a data-page="chats" href="#" class="nav-item">
                <span class="material-symbols-outlined">chat</span>
                <span>Chats</span>
            </a>
            <a data-page="settings" href="#" class="nav-item">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <button class="logout-btn">
                <span class="material-symbols-outlined">logout</span>
                <span>Logout</span>
            </button>
        </div>
    </aside>

    <!-- Top Header -->
    <header class="header">
        <div class="search-container">
            <span class="material-symbols-outlined search-icon" >search</span>
            <input class="search-input" type="text" placeholder="Search health records...">
        </div>
        <div class="header-actions">
            <button class="icon-btn">
                <span class="material-symbols-outlined">notifications</span>
                <span class="notification-dot"></span>
            </button>
            <button class="icon-btn">
                <span class="material-symbols-outlined">help</span>
            </button>
            <div class="divider" ></div>
            <div class="header-profile-img-container profile-img" id="header-profile-img-container" alt="Profile">
            </div>
            
        </div>
    </header>

    <!-- Main Content -->
    <main id="spa-content" class="main-content"></main>

    <script src="/my_doctor/public/assets/js/patient.js"></script>
</body>
</html>
