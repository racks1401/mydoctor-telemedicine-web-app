<?php
require_once __DIR__ . '/../../helpers/auth.php';
$csrfToken = getCsrfToken();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>" />
    <title>MyDoctor - Doctor Dashboard</title>
    <script>window.csrfToken = "<?= htmlspecialchars($csrfToken) ?>";</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/doctor.css?v=21" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/responsive-sidebar.css?v=7" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/chat-call.css?v=4" rel="stylesheet" />
</head>
<body>
    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-icon">
                <span class="material-symbols-outlined">local_hospital</span>
            </div>
            <div class="brand-text">
                <h1>MyDoctor</h1>
                <p>Doctor Panel</p>
            </div>
            <button class="sidebar-collapse-btn" id="sidebar-collapse-btn" title="Collapse sidebar" aria-label="Collapse sidebar">
                <svg class="icon-collapse" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <svg class="icon-expand" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>
            <button class="sidebar-close-btn" id="sidebar-close-btn" title="Close navigation" aria-label="Close navigation">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <nav class="sidebar-nav">
            <a data-page="dashboard" class="nav-link active" href="#" title="Dashboard">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a data-page="appointments" class="nav-link" href="#" title="Appointments">
                <span class="material-symbols-outlined">calendar_today</span>
                <span>Appointments</span>
            </a>
            <a data-page="chats" class="nav-link" href="#" title="Chats">
                <span class="material-symbols-outlined">chat</span>
                <span>Chats</span>
            </a>
            <a data-page="calls" class="nav-link" href="#" title="Calls">
                <span class="material-symbols-outlined">call</span>
                <span>Calls</span>
            </a>
            <a data-page="settings" class="nav-link" href="#" title="Settings">
                <span class="material-symbols-outlined">settings</span>
                <span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="../logout.php" class="logout-btn" title="Logout">
                <span class="material-symbols-outlined">logout</span>
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <!-- Main Content Canvas -->
    <div class="main-content">
        <!-- ── Top Bar ── -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="topbar-toggle-btn" id="sidebar-toggle-btn" title="Toggle navigation" aria-label="Toggle navigation">
                    <span class="material-symbols-outlined">menu</span>
                </button>
                <div class="topbar-search">
                    <span class="material-symbols-outlined">search</span>
                    <input type="text" id="search" name="search" placeholder="Search patients, medical records, or reports..." />
                </div>
            </div>
            <div class="topbar-actions">
                <button class="topbar-icon-btn" id="topbar-notif-btn" onclick="if(window.openDoctorNotifications)window.openDoctorNotifications();" style="position: relative; cursor: pointer;" title="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    <span id="notif-badge-dot" style="display: none; position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: #2563eb; border-radius: 50%; border: 1.5px solid white;"></span>
                </button>
                
                <div class="topbar-divider"></div>
                <div class="topbar-avatar" id="header-profile-img-container" data-page="settings" title="Doctor Profile"></div>
            </div>
        </header>

        <main id="spa-content"></main>
    </div>

    <!-- Notification Modal (70% Width and 70% Height) -->
    <div id="modal-notifications" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div style="width: 70vw; height: 70vh; background: white; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-container-low, #f8f9fa);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">notifications</span>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: var(--text-heading);">Notifications</h3>
                        <p style="margin: 0; font-size: 12px; color: var(--text-muted);" id="modal-notif-subtitle">Recent activity & appointment updates</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" id="btn-mark-all-read" class="btn btn-outline btn-sm" style="background: white; font-size: 12px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid var(--border-subtle); border-radius: 8px; font-weight: 600; cursor: pointer;">
                        <span class="material-symbols-outlined" style="font-size: 16px;">done_all</span>
                        Mark All as Read
                    </button>
                    <button type="button" id="btn-close-notif-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;" title="Close">&times;</button>
                </div>
            </div>

            <!-- Body -->
            <div id="modal-notif-list" style="flex: 1; overflow-y: auto; padding: 16px 24px;">
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">Loading notifications...</div>
            </div>
        </div>
    </div>

    <!-- Firebase Compat SDKs & Client (Phase 5) -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>
    <script src="/my_doctor/public/assets/js/firebase-client.js?v=2"></script>
    <script src="/my_doctor/public/assets/js/call.js?v=2"></script>
    <script src="/my_doctor/public/assets/js/responsive-sidebar.js?v=3"></script>
    <script src="/my_doctor/public/assets/js/doctor.js?v=35"></script>
</body>
</html>
