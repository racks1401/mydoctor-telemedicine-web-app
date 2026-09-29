<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Overview | MediCentral Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1" rel="stylesheet">
    <link rel="stylesheet" href="/my_doctor/public/assets/css/admin.css">

</head>

<body>

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
    <div class="sidebar-brand">
        <h1>MyDoctor</h1>
        <p>Admin Panel</p>
    </div>
    <nav class="sidebar-nav">
        <a data-page="dashboard" class="nav-link active" href="#">
        <span class="material-symbols-outlined">dashboard</span>
        <span>Dashboard</span>
        </a>
        <a data-page="doctors" class="nav-link" href="#">
        <span class="material-symbols-outlined">medical_services</span>
        <span>Doctors</span>
        </a>
        <a data-page="patients" class="nav-link" href="#">
        <span class="material-symbols-outlined">folder_shared</span>
        <span>Patients</span>
        </a>
        <a data-page="appointments" class="nav-link" href="#">
        <span class="material-symbols-outlined">event</span>
        <span>Appointments</span>
        </a>
        <a data-page="user-verification" class="nav-link" href="#">
        <span class="material-symbols-outlined">verified_user</span>
        <span>User Verification</span>
        </a>
        <a data-page="blocked-users" class="nav-link" href="#">
        <span class="material-symbols-outlined">block</span>
        <span>Blocked User</span>
        </a>
        <a data-page="profile" class="nav-link" href="#">
        <span class="material-symbols-outlined">person</span>
        <span>Profile</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="../logout.php" class="logout-btn">Logout</a>
    </div>
    </aside>

    <div class="main-wrapper">

        <!-- ── Top Bar ── -->
        <header class="topbar">
        <div class="topbar-search">
            <span class="material-symbols-outlined">search</span>
            <input type="text" id="search" name="search" placeholder="Search patients, doctors, appointments..."/>
        </div>
        <div class="topbar-actions">
            <button class="topbar-icon-btn"><span class="material-symbols-outlined">notifications</span></button>
            
            <div class="topbar-divider"></div>
            <img class="topbar-avatar" data-page="common" data-action="open-profile" id="dashboard-user-image" src="" alt="Admin Profile"/>
        </div>
        </header>

        <!-- Dynamic Content -->
        <main id="spa-content" class="main-content">

        </main>

    </div>

    <script src="/my_doctor/public/assets/js/admin.js"></script>
    
</body>
</html>