<!DOCTYPE html>

<html lang="en"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Booking Confirmed - MediCentral</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<style>
        :root {
            /* Color Palette */
            --primary: #003d9b;
            --primary-container: #0052cc;
            --trust-blue: #0052CC;
            --success-green: #28A745;
            --error-red: #E74C3C;
            --background: #f8f9fb;
            --surface: #f8f9fb;
            --surface-container-lowest: #ffffff;
            --surface-container-low: #f3f4f6;
            --surface-container: #edeef0;
            --surface-container-high: #e7e8ea;
            --surface-dim: #d9dadc;
            --text-heading: #111827;
            --text-body: #4B5563;
            --text-muted: #8899A6;
            --on-surface: #191c1e;
            --on-surface-variant: #434654;
            --border-subtle: #E5E7EB;
            --outline: #737685;

            /* Spacing */
            --sidebar-width: 280px;
            --header-height: 64px;
            --gutter: 24px;
            --margin-desktop: 32px;
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;

            /* Border Radius */
            --radius-default: 0.25rem;
            --radius-lg: 0.5rem;
            --radius-xl: 0.75rem;
            --radius-full: 9999px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--on-surface);
            line-height: 1.5;
        }

        /* Typography */
        h1, h2, h3, h4 {
            color: var(--text-heading);
            font-weight: 700;
        }

        .text-display-lg { font-size: 32px; line-height: 40px; letter-spacing: -0.02em; }
        .text-headline-md { font-size: 24px; line-height: 32px; letter-spacing: -0.01em; }
        .text-headline-sm { font-size: 18px; line-height: 24px; font-weight: 600; }
        .text-label-md { font-size: 14px; line-height: 20px; font-weight: 600; letter-spacing: 0.01em; }
        .text-body-lg { font-size: 16px; line-height: 24px; font-weight: 400; }
        .text-body-md { font-size: 14px; line-height: 20px; font-weight: 400; }
        .text-caption { font-size: 12px; line-height: 16px; font-weight: 500; letter-spacing: 0.02em; }

        /* Icons */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            vertical-align: middle;
        }
        .icon-fill { font-variation-settings: 'FILL' 1; }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: var(--surface-container-lowest);
            border-right: 1px solid var(--border-subtle);
            display: flex;
            flex-direction: column;
            padding: 24px 0;
            z-index: 50;
        }

        .sidebar-logo {
            padding: 0 24px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 16px;
            color: var(--on-surface-variant);
            text-decoration: none;
            transition: background 0.2s;
            border-radius: var(--radius-default);
        }

        .nav-item:hover {
            background: var(--surface-container);
        }

        .nav-item.active {
            background: var(--surface-dim);
            color: var(--primary);
            border-left: 4px solid var(--primary);
            opacity: 0.9;
        }

        .sidebar-footer {
            padding: 24px;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar-lg { width: 40px; height: 40px; border-radius: var(--radius-full); object-fit: cover; }
        .avatar-md { width: 32px; height: 32px; border-radius: var(--radius-full); border: 1px solid var(--border-subtle); }

        /* Top Bar */
        .top-bar {
            position: fixed;
            top: 0;
            right: 0;
            width: calc(100% - var(--sidebar-width));
            height: var(--header-height);
            background: var(--surface-container-lowest);
            border-bottom: 1px solid var(--border-subtle);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 var(--gutter);
            z-index: 40;
        }

        .search-bar {
            background: var(--surface-container-low);
            border-radius: var(--radius-lg);
            padding: 6px 12px;
            display: flex;
            align-items: center;
            width: 384px;
            gap: 8px;
        }

        .search-bar input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .action-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: var(--radius-full);
            position: relative;
            color: inherit;
        }

        .action-btn:hover { color: var(--primary); }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            width: 8px;
            height: 8px;
            background: var(--error-red);
            border-radius: var(--radius-full);
        }

        .divider-v { width: 1px; height: 32px; background: var(--border-subtle); }

        /* Main Content */
        main {
            margin-left: var(--sidebar-width);
            padding-top: var(--header-height);
            min-height: 100vh;
        }

        .container {
            max-width: 1160px;
            margin: 0 auto;
            padding: var(--margin-desktop);
        }

        /* Status Header */
        .status-header {
            margin-bottom: var(--space-xl);
            text-align: left;
        }

        .status-icon-box {
            width: 64px;
            height: 64px;
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-green);
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: var(--space-md);
        }

        .status-icon-box span { font-size: 40px; }

        /* Grid */
        .bento-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: var(--gutter);
        }

        @media (max-width: 1024px) {
            .bento-grid { grid-template-columns: 1fr; }
        }

        .card-stack { display: flex; flex-direction: column; gap: var(--gutter); }

        /* Components */
        .card {
            background: var(--surface-container-lowest);
            padding: var(--space-lg);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-subtle);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transform: translateY(-2px);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .badge-status {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-green);
            padding: 4px 12px;
            border-radius: var(--radius-full);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: var(--success-green);
            border-radius: var(--radius-full);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--space-lg);
        }

        @media (max-width: 640px) {
            .detail-row { grid-template-columns: 1fr; }
        }

        .info-box {
            display: flex;
            gap: 16px;
            padding: 16px;
            background: var(--surface-container-low);
            border: 1px solid var(--border-subtle);
            border-radius: var(--radius-lg);
        }

        .date-box {
            width: 64px;
            height: 64px;
            background: rgba(0, 82, 204, 0.1);
            color: var(--trust-blue);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-lg);
        }

        .img-rounded { width: 64px; height: 64px; border-radius: var(--radius-lg); object-fit: cover; }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-top: var(--space-lg);
            padding-top: var(--space-lg);
            border-top: 1px solid var(--border-subtle);
        }

        .meta-item .label {
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        /* Buttons */
        .btn {
            width: 100%;
            padding: 12px;
            border-radius: var(--radius-lg);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: 0.2s;
            border: none;
            font-family: inherit;
        }

        .btn-primary { background: var(--trust-blue); color: #fff; }
        .btn-primary:hover { background: var(--primary); }

        .btn-outline { background: transparent; border: 1px solid var(--border-subtle); color: var(--text-heading); }
        .btn-outline:hover { background: var(--surface-container-low); }

        .btn-ghost-error { background: transparent; color: var(--error-red); }
        .btn-ghost-error:hover { background: rgba(231, 76, 60, 0.05); }

        /* Checklist */
        .checklist { display: flex; flex-direction: column; gap: 16px; }
        .check-item { display: flex; gap: 16px; align-items: flex-start; }
        .check-item span { color: var(--trust-blue); margin-top: 4px; }

        /* Map Card */
        .map-preview {
            width: 100%;
            height: 128px;
            border-radius: var(--radius-lg);
            overflow: hidden;
            margin-bottom: var(--space-md);
            position: relative;
        }
        .map-preview img { width: 100%; height: 100%; object-fit: cover; }
        .map-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.2), transparent);
        }

        /* Activity Log */
        .timeline {
            position: relative;
            padding-left: 32px;
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 8px;
            bottom: 8px;
            width: 2px;
            background: var(--border-subtle);
        }
        .timeline-item { position: relative; }
        .timeline-dot {
            position: absolute;
            left: -32px;
            top: 4px;
            width: 16px;
            height: 16px;
            border-radius: var(--radius-full);
            background: #fff;
            border: 4px solid var(--border-subtle);
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
            z-index: 1;
        }
        .dot-success { border-color: var(--success-green); background: var(--success-green); }
        .dot-primary { border-color: var(--trust-blue); background: var(--trust-blue); }
        .dot-dim { border-color: var(--surface-dim); background: var(--surface-dim); }

        /* Footer */
        .footer-nav {
            margin-top: var(--space-xl);
            padding: var(--space-lg) 0;
            border-top: 1px solid var(--border-subtle);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .footer-links { display: flex; gap: 24px; }
        .footer-links a { text-decoration: none; font-weight: 600; color: var(--on-surface-variant); }
        .footer-links a.active { color: var(--trust-blue); }
        .footer-links a:hover { color: var(--primary); }

        /* Utility Styles (to replace specific tailwind classes) */
        .truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .opacity-70 { opacity: 0.7; }
        .mb-1 { margin-bottom: 4px; }
    </style>
</head>
<body>
<!-- SideNavBar -->
<aside class="sidebar">
<div class="sidebar-logo">
<span class="material-symbols-outlined" style="color: var(--trust-blue); font-size: 32px;">medical_services</span>
<div>
<h1 class="text-headline-md" style="font-weight: 700; color: var(--primary);">MediCentral</h1>
<p class="text-label-md opacity-70">Clinical Admin Panel</p>
</div>
</div>
<nav class="sidebar-nav">
<a class="nav-item" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="text-label-md">Dashboard</span>
</a>
<a class="nav-item" href="#">
<span class="material-symbols-outlined">medical_services</span>
<span class="text-label-md">Doctor Management</span>
</a>
<a class="nav-item" href="#">
<span class="material-symbols-outlined">folder_shared</span>
<span class="text-label-md">Patient Records</span>
</a>
<a class="nav-item active" href="#">
<span class="material-symbols-outlined">event</span>
<span class="text-label-md">Appointments</span>
</a>
<a class="nav-item" href="#">
<span class="material-symbols-outlined">analytics</span>
<span class="text-label-md">Analytics</span>
</a>
<a class="nav-item" href="#">
<span class="material-symbols-outlined">verified_user</span>
<span class="text-label-md">User Verification</span>
</a>
<a class="nav-item" href="#">
<span class="material-symbols-outlined">block</span>
<span class="text-label-md">Blocked Users</span>
</a>
</nav>
<div class="sidebar-footer">
<img alt="Admin Sarah Miller" class="avatar-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAZwCODnDjbqIIOk0EVgb4Y_fLH_l7YB-0b05OkJL70a3aunZnwHm_ArW6fiL9XtApsciREETP1Q48E22ZqGvDDvnXqPOHB5N7397lj6EvM7lAeQHIx-4I2_iwWWdIaAkZ3Tm61xSdIZ-r2X7dF6haGen_PSLjVc1j-c_o7bt2HRR4Q_xeo98Q7qYaQLC1S2AoOMM4ngdY1bLpBJw7AQX5aTfJJZNIQlaNC-hzVGvv2ac6_rM2CZxhkS4fwo-3v8L3Tl-gD50rgss4"/>
<div class="truncate">
<p class="text-label-md truncate">Admin Sarah Miller</p>
<p class="text-caption" style="color: var(--on-surface-variant);">System Lead</p>
</div>
</div>
</aside>
<!-- TopAppBar -->
<header class="top-bar">
<div class="search-bar">
<span class="material-symbols-outlined" style="color: var(--outline); font-size: 20px;">search</span>
<input placeholder="Search patients, doctors, IDs..." type="text"/>
</div>
<div class="top-bar-actions">
<button class="action-btn">
<span class="material-symbols-outlined">notifications</span>
<span class="notification-badge"></span>
</button>
<button class="action-btn">
<span class="material-symbols-outlined">settings</span>
</button>
<div class="divider-v"></div>
<div style="display: flex; align-items: center; gap: 8px;">
<img alt="Admin Profile" class="avatar-md" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCBoIebV_xP8cSB3LaLMLDyWM-zojZsfDWVUHn2M71MRfa_0hVMRbqmk4hLxf9vlf3BwbBaWDvYD8dufnS2svj4JyVO6F0m69uHJQNiQZGM7em1YQ-aT7QJ7GvMzx5NSQa08h_H-KMRtY7fuJm2sI3iqeDUOAsk4LmYk_sVH7gBU1ddbouMwUHCCNLdHb0jLPAetxx0zjT432xVollyeqla_JmjVIN_VrI3CQ9ygoYIrMx5ohYsB1NkI9ny1pxsywv4RzNS53qi11I"/>
<span class="material-symbols-outlined" style="color: var(--on-surface-variant);">expand_more</span>
</div>
</div>
</header>
<!-- Main Content -->
<main>
<div class="container">
<!-- Success Header -->
<div class="status-header">
<div class="status-icon-box">
<span class="material-symbols-outlined icon-fill">check_circle</span>
</div>
<h2 class="text-display-lg mb-1">Booking Confirmed!</h2>
<p class="text-body-lg" style="color: var(--text-body);">Your appointment has been successfully scheduled. We've sent a confirmation email to the patient.</p>
</div>
<!-- Bento Grid -->
<div class="bento-grid">
<!-- Left Column -->
<div class="card-stack">
<!-- Appointment Details Card -->
<div class="card">
<div class="card-header">
<h3 class="text-headline-sm">Appointment Details</h3>
<div class="badge-status">
<span class="pulse-dot"></span>
<span class="text-label-md">Waiting for provider update</span>
</div>
</div>
<div class="detail-row">
<div class="info-box">
<img alt="Doctor" class="img-rounded" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBLK8hk5gXwTP03sPYtNRCSCd0SX14hUsNc5Qu-cVXyJRiGT5RCTXOxNCIUJZBsZc9rQ5V5nCc9M8KtSZ5SYW9JTL8c2CUwvhxa-VC0RJLCHMYOu75nWgW5mJzAbkTGIYsTyUvn_764vpcyEhxBtrgYkm3vjGnIZYW2lxQnr-gAr7JDH8HrTVba8Ad2wtlcIx0Y-3kAv6KsuX65Zn8hh1Yphy0k7_fcBvepmWUDBbsFPHNSRICNcM8zViV3qTLq4UYc6NLCID-ZM8s"/>
<div>
<p class="text-caption mb-1" style="color: var(--text-muted);">Assigned Specialist</p>
<p class="text-headline-sm mb-1">Dr. Jonathan Vance</p>
<p class="text-body-md" style="color: var(--text-body);">Senior Cardiologist</p>
</div>
</div>
<div class="info-box">
<div class="date-box">
<span class="text-label-md" style="text-transform: uppercase;">Oct</span>
<span class="text-headline-md">24</span>
</div>
<div>
<p class="text-caption mb-1" style="color: var(--text-muted);">Appointment Schedule</p>
<p class="text-headline-sm mb-1">Thursday, 10:30 AM</p>
<p class="text-body-md" style="color: var(--text-body);">Expected duration: 45 mins</p>
</div>
</div>
</div>
<div class="meta-grid">
<div class="meta-item">
<p class="text-caption label">Patient</p>
<p class="text-label-md">Eleanor Thompson</p>
<p class="text-body-md" style="color: var(--text-body);">ID: #MC-88210</p>
</div>
<div class="meta-item">
<p class="text-caption label">Service</p>
<p class="text-label-md">Cardiac Consultation</p>
<p class="text-body-md" style="color: var(--text-body);">Ref: CAR-990</p>
</div>
<div class="meta-item">
<p class="text-caption label">Room</p>
<p class="text-label-md">Consultation Suite B-12</p>
<p class="text-body-md" style="color: var(--text-body);">Main Wing, Level 2</p>
</div>
</div>
</div>
<!-- Preparation Card -->
<div class="card">
<h4 class="text-headline-sm" style="margin-bottom: var(--space-md);">Pre-Appointment Checklist</h4>
<div class="checklist">
<div class="check-item">
<span class="material-symbols-outlined">description</span>
<div>
<p class="text-label-md">Review Medical History</p>
<p class="text-body-md" style="color: var(--text-body);">Dr. Vance requires a review of the last 6 months of ECG reports before the visit.</p>
</div>
</div>
<div class="check-item">
<span class="material-symbols-outlined">medication</span>
<div>
<p class="text-label-md">Current Medications</p>
<p class="text-body-md" style="color: var(--text-body);">Confirm patient has brought a list of all current prescriptions.</p>
</div>
</div>
</div>
</div>
</div>
<!-- Right Column -->
<div class="card-stack">
<!-- Actions Card -->
<div class="card">
<button class="btn btn-primary" style="margin-bottom: 16px;">
<span class="material-symbols-outlined" style="font-size: 20px;">calendar_add_on</span>
                        Add to System Calendar
                    </button>
<button class="btn btn-outline" style="margin-bottom: 16px;">
<span class="material-symbols-outlined" style="font-size: 20px;">print</span>
                        Print Summary
                    </button>
<button class="btn btn-ghost-error">
<span class="material-symbols-outlined" style="font-size: 20px;">cancel</span>
                        Reschedule Appointment
                    </button>
</div>
<!-- Location Card -->
<div class="card">
<h4 class="text-label-md" style="margin-bottom: var(--space-md);">Facility Location</h4>
<div class="map-preview">
<img alt="Map Location" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCi9Gp83wReS5dxlPFIcpTonXF-_wKaYrxigZnxuDTAnyk_RfhMduo0OiKCSQRgp0lGq3a3st8hBm70fiEtB_oa0RWdKPJpCas_LpMoOMGm5No8zaR6FX1nuPmoMqn9U4QPZNy4zkqsq1raCvAHTz_zF9tmK1hKy36SbpWH71YuVnoou5grxRK-csPMsLnzRVjvOCxSg6WDxFtzzYBIOAia7ddz72_qu2NXKDBwHpGextWm65bVIa_xwksXFEJkdtSTPQYdtLzsXCw"/>
<div class="map-overlay"></div>
</div>
<div style="display: flex; align-items: center; gap: 12px;">
<span class="material-symbols-outlined" style="color: var(--on-surface-variant);">location_on</span>
<p class="text-body-md" style="color: var(--text-body);">MediCentral HQ, North Tower, Suite 402</p>
</div>
</div>
<!-- Activity Log -->
<div class="card">
<h4 class="text-label-md" style="margin-bottom: var(--space-lg);">Activity Log</h4>
<div class="timeline">
<div class="timeline-item">
<div class="timeline-dot dot-success"></div>
<p class="text-label-md">Booking Created</p>
<p class="text-caption" style="color: var(--text-muted);">Today at 09:12 AM by Admin</p>
</div>
<div class="timeline-item">
<div class="timeline-dot dot-primary"></div>
<p class="text-label-md">Email Confirmed</p>
<p class="text-caption" style="color: var(--text-muted);">Today at 09:15 AM</p>
</div>
<div class="timeline-item">
<div class="timeline-dot dot-dim"></div>
<p class="text-label-md">Provider Notified</p>
<p class="text-caption" style="color: var(--text-muted);">Pending confirmation</p>
</div>
</div>
</div>
</div>
</div>
<!-- Footer -->
<footer class="footer-nav">
<div class="footer-links">
<a class="active text-label-md" href="#">View All Appointments</a>
<a class="text-label-md" href="#">Patient Dashboard</a>
</div>
<p class="text-caption" style="color: var(--text-muted);">Booking Reference ID: #CONF-VANCE-2410-01</p>
</footer>
</div>
</main>
<script>
    // Search focus micro-interaction
    const searchInput = document.querySelector('.search-bar input');
    const searchContainer = document.querySelector('.search-bar');
    
    searchInput.addEventListener('focus', () => {
        searchContainer.style.boxShadow = '0 0 0 2px var(--trust-blue)';
    });
    
    searchInput.addEventListener('blur', () => {
        searchContainer.style.boxShadow = 'none';
    });
</script>
</body></html>