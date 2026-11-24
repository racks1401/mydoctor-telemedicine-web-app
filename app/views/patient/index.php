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
    <title>MyDoctor - Patient Dashboard</title>
    <script>window.csrfToken = "<?= htmlspecialchars($csrfToken) ?>";</script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/patient.css?v=23" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/responsive-sidebar.css?v=7" rel="stylesheet" />
    <link href="/my_doctor/public/assets/css/chat-call.css?v=4" rel="stylesheet" />
</head>
<body>
    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-icon">
                <span class="material-symbols-outlined">health_and_safety</span>
            </div>
            <div class="brand-text">
                <h1>MyDoctor</h1>
                <p>Patient Portal</p>
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
            <a data-page="doctors" class="nav-link" href="#" title="Doctors">
                <span class="material-symbols-outlined">stethoscope</span>
                <span>Doctors</span>
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
            <a data-page="prescriptions" class="nav-link" href="#" title="Prescriptions">
                <span class="material-symbols-outlined">prescriptions</span>
                <span>Prescriptions</span>
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
                    <input type="text" id="search" name="search" placeholder="Search health records..."/>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="topbar-icon-btn" id="topbar-notif-btn" onclick="if(window.openPatientNotifications)window.openPatientNotifications();" style="position: relative; cursor: pointer;" title="Notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    <span id="notif-badge-dot" style="display: none; position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: #2563eb; border-radius: 50%; border: 1.5px solid white;"></span>
                </button>
                
                <div class="topbar-divider"></div>
                <div class="topbar-avatar" id="header-profile-img-container" data-page="settings" title="Patient Profile"></div>
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

    <!-- Free Follow-up Consultation Modal -->
    <div id="modal-followup" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div style="width: min(650px, 92vw); max-height: 90vh; background: white; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-container-low, #f8f9fa);">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-heading);">Book Free Follow-up</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12.5px; color: var(--text-muted);">Consultation within 3 days is 100% free (₹0.00)</p>
                </div>
                <button type="button" id="btn-close-followup-modal" style="background: none; border: none; font-size: 26px; cursor: pointer; color: var(--text-muted); line-height: 1;" title="Close">&times;</button>
            </div>
            <div style="flex: 1; overflow-y: auto; padding: 20px 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <img id="followup-doc-avatar" src="/my_doctor/public/assets/images/doctor-profile-male.png" alt="Doctor" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;">
                        <div>
                            <h4 id="followup-doc-name" style="margin: 0; font-size: 15px; font-weight: 700; color: #1e3a8a;">Dr. Doctor Name</h4>
                            <p id="followup-doc-spec" style="margin: 2px 0 0 0; font-size: 12px; color: #3b82f6;">Specialization</p>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <span id="followup-mode-badge" style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #dbeafe; color: #1e40af; text-transform: capitalize; margin-bottom: 2px;">Video Call</span>
                        <div style="font-size: 12px; font-weight: 700; color: #16a34a;">FREE (₹0.00)</div>
                    </div>
                </div>

                <form id="form-book-followup">
                    <input type="hidden" id="followup-parent-id" value="">
                    <input type="hidden" id="followup-doctor-id" value="">
                    <input type="hidden" id="followup-selected-time" value="">

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-heading); margin-bottom: 6px;">Select Follow-up Date</label>
                        <input type="date" id="followup-date-input" style="width: 100%; padding: 9px 12px; border: 1.5px solid var(--border-subtle); border-radius: 8px; font-size: 13.5px; font-family: inherit; outline: none; box-sizing: border-box;" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-heading); margin-bottom: 6px;">Select Time Slot</label>
                        <div id="followup-slots-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(95px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto; padding: 4px;">
                            <div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px;">Please pick a date to view available slots.</div>
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-heading); margin-bottom: 6px;">Current Problem / Updates for Doctor</label>
                        <textarea id="followup-problem" rows="3" placeholder="Briefly describe your recovery status or any continuing symptoms..." style="width: 100%; padding: 10px 12px; border: 1.5px solid var(--border-subtle); border-radius: 8px; font-size: 13px; font-family: inherit; outline: none; resize: vertical; box-sizing: border-box;"></textarea>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
                        <button type="button" id="btn-cancel-followup" class="btn btn-outline" style="padding: 8px 18px; border-radius: 8px; font-size: 13px;">Cancel</button>
                        <button type="submit" id="btn-submit-followup" class="btn btn-primary" style="padding: 8px 22px; border-radius: 8px; font-size: 13px; background: #2563eb; color: white; font-weight: 600;">Confirm Free Follow-up</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alternate Slot Modal for Rescheduled Appointment -->
    <div id="modal-alternate-slot" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div style="width: min(600px, 92vw); max-height: 88vh; background: white; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-container-low, #f8f9fa);">
                <div>
                    <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-heading);">Pick Alternate Slot</h3>
                    <p style="margin: 2px 0 0 0; font-size: 12.5px; color: var(--text-muted);" id="alt-slot-doctor-subtitle">Choose another date and time slot for your appointment</p>
                </div>
                <button type="button" id="btn-close-alt-slot-modal" style="background: none; border: none; font-size: 26px; cursor: pointer; color: var(--text-muted); line-height: 1;" title="Close">&times;</button>
            </div>
            <div style="flex: 1; overflow-y: auto; padding: 20px 24px;">
                <form id="form-alternate-slot">
                    <input type="hidden" id="alt-appointment-id" value="">
                    <input type="hidden" id="alt-doctor-id" value="">
                    <input type="hidden" id="alt-selected-time" value="">

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-heading); margin-bottom: 6px;">Select Date</label>
                        <input type="date" id="alt-date-input" style="width: 100%; padding: 9px 12px; border: 1.5px solid var(--border-subtle); border-radius: 8px; font-size: 13.5px; font-family: inherit; outline: none; box-sizing: border-box;" required>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                            <label style="font-size: 13px; font-weight: 600; color: var(--text-heading); margin: 0;">Select Time Slot</label>
                            <span id="alt-selected-time-display" style="font-size: 12px; font-weight: 600; color: #2563eb;"></span>
                        </div>
                        <div id="alt-slots-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(95px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto; padding: 6px; border: 1.5px solid var(--border-subtle); border-radius: 8px; background: #fafafa;">
                            <div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px; text-align: center; padding: 14px;">Please pick a date to view available slots.</div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
                        <button type="button" id="btn-cancel-alt-slot" class="btn btn-outline" style="padding: 8px 18px; border-radius: 8px; font-size: 13px;">Cancel</button>
                        <button type="submit" id="btn-submit-alt-slot" class="btn btn-primary" style="padding: 8px 22px; border-radius: 8px; font-size: 13px; background: #2563eb; color: white; font-weight: 600;">Propose Slot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Doctor Profile Modal (Details from Database) ── -->
    <div id="modal-doctor-profile" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div style="width: min(860px, 92vw); max-height: 90vh; background: white; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-container-low, #f8f9fa);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #3730a3; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">stethoscope</span>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: var(--text-heading);">Doctor Profile</h3>
                        <p style="margin: 0; font-size: 12px; color: var(--text-muted);">Verified Medical Professional</p>
                    </div>
                </div>
                <button type="button" id="btn-close-doctor-profile" style="background: none; border: none; font-size: 26px; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;" title="Close">&times;</button>
            </div>

            <!-- Scrollable Body -->
            <div id="doctor-profile-content" style="flex: 1; overflow-y: auto; padding: 24px;">
                <div style="text-align: center; padding: 40px; color: var(--text-muted);">Loading doctor details...</div>
            </div>

            <!-- Footer -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; border-top: 1px solid var(--border-subtle); background: var(--surface-container-low, #f8f9fa);">
                <button type="button" class="btn btn-outline" id="btn-profile-modal-close" style="padding: 8px 18px; border-radius: 8px; font-size: 13px;">Close</button>
                <button type="button" class="btn btn-primary" id="btn-profile-modal-book" style="padding: 9px 22px; border-radius: 8px; font-size: 13.5px; background: #2563eb; color: white; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">calendar_today</span>
                    <span id="btn-profile-modal-book-text">Book Appointment</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ── Book Appointment Modal (Universal Modal for Doctors and Appointments Tabs) ── -->
    <div class="modal-overlay" id="book-appointment-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div class="modal-content" style="max-width: 860px; width: min(860px, 92vw); padding: 28px; max-height: 88vh; border-radius: 16px; box-sizing: border-box; background: white; overflow-y: auto;">
            <!-- Modal Header -->
            <div class="modal-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="font-size: 20px; font-weight: 700; color: var(--text-heading); margin: 0 0 4px 0;" id="modal-doc-header-name">Book Appointment</h3>
                    <p style="font-size: 13px; color: var(--on-surface-variant); margin: 0;" id="modal-doc-header-specialty">Select consultation mode, date, and preferred slot</p>
                </div>
                <button type="button" class="modal-close" id="btn-close-booking-modal" style="font-size: 28px; line-height: 1; cursor: pointer; color: var(--on-surface-variant); background: none; border: none;">&times;</button>
            </div>

            <!-- Doctor Mini Summary Banner (With Doctor Switcher) -->
            <div id="modal-doctor-banner" style="display: flex; align-items: center; gap: 16px; padding: 14px 18px; background: var(--surface-container-low); border-radius: 12px; margin-bottom: 20px; flex-wrap: wrap;">
                <img id="modal-doc-avatar" src="/my_doctor/public/assets/images/doctor-profile-male.png" alt="Doctor" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover;" />
                <div style="flex: 1; min-width: 200px;">
                    <div id="modal-doc-selector-container" style="display: none; margin-bottom: 6px;">
                        <label for="modal-doctor-select" style="font-size: 11.5px; font-weight: 700; color: #2563eb; display: block; margin-bottom: 3px; text-transform: uppercase;">Change / Select Doctor</label>
                        <select id="modal-doctor-select" class="form-select" style="font-size: 13.5px; font-weight: 600; padding: 6px 10px; border-radius: 8px; width: 100%; max-width: 360px; border: 1.5px solid #bfdbfe; background: white;"></select>
                    </div>
                    <h4 id="modal-doc-card-name" style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-heading);">Dr. Name</h4>
                    <div style="font-size: 13px; color: var(--on-surface-variant);" id="modal-doc-card-info">Cardiology • City Central Hospital</div>
                </div>
                <div style="text-align: right;">
                    <div style="display: flex; align-items: center; gap: 4px; color: #f59e0b; font-weight: 700; font-size: 14px;">
                        <span>★</span> <span id="modal-doc-rating" style="color: var(--text-heading);">4.8</span>
                        <span id="modal-doc-reviews" style="font-size: 12px; color: var(--on-surface-variant); font-weight: 400;">(120 reviews)</span>
                    </div>
                </div>
            </div>

            <!-- Stepper Progress Header -->
            <div style="display: flex; align-items: center; justify-content: center; gap: 24px; margin-bottom: 24px;">
                <div id="booking-stepper-1" class="booking-step-badge active" style="display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: var(--primary); cursor: pointer;">
                    <span style="width: 28px; height: 28px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 13px;">1</span>
                    <span>1. Mode &amp; Time Slot</span>
                </div>
                <div style="width: 48px; height: 2px; background: var(--border-subtle);"></div>
                <div id="booking-stepper-2" class="booking-step-badge" style="display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: 13px; color: var(--on-surface-variant); cursor: pointer;">
                    <span id="step-badge-circle-2" style="width: 28px; height: 28px; border-radius: 50%; background: var(--surface-container-high); color: var(--on-surface-variant); display: flex; align-items: center; justify-content: center; font-size: 13px;">2</span>
                    <span>2. Patient Information</span>
                </div>
            </div>

            <!-- Form container -->
            <form id="appointment-booking-form" onsubmit="return false;">
                <!-- STEP 1: Consultation Mode & Slot Selection -->
                <div id="booking-step-1" class="booking-step-pane">
                    <!-- Mode Selection (Video, Chat, Offline, Audio) -->
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 10px; color: var(--text-heading);">Choose Consultation Mode</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px;" id="booking-modes-grid">
                            <!-- Cards populated dynamically with mode-specific fees -->
                        </div>
                        <input type="hidden" id="selected-booking-mode" value="video_call" />
                        <input type="hidden" id="selected-booking-fee" value="100.00" />
                    </div>

                    <!-- Date Selection (Calendar Picker) -->
                    <div style="margin-bottom: 20px;">
                        <label for="booking-appointment-date" style="display: block; font-size: 14px; font-weight: 600; margin-bottom: 8px; color: var(--text-heading);">
                            Select Date (Available Next 60 Days)
                        </label>
                        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                            <input type="date" id="booking-appointment-date" class="form-input" style="max-width: 260px; padding: 10px 14px; font-weight: 500;" />
                            <span id="booking-date-status-badge" style="font-size: 12px; padding: 6px 12px; border-radius: 6px; font-weight: 600; display: none;"></span>
                        </div>
                        <div id="booking-date-reason" style="font-size: 12px; color: var(--on-surface-variant); margin-top: 6px;">Select a date to check doctor availability and schedule exceptions</div>
                    </div>

                    <!-- Time Slots Grid -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <label style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin: 0;">Available Time Slots</label>
                            <span id="slots-count-badge" style="font-size: 12px; color: var(--primary); font-weight: 600;"></span>
                        </div>
                        <div id="booking-slots-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; max-height: 180px; overflow-y: auto; padding: 4px;">
                            <div style="color: var(--on-surface-variant); font-size: 13px;">Please select an active date above to view slots.</div>
                        </div>
                        <input type="hidden" id="selected-slot-time" value="" />
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-subtle);">
                        <button type="button" class="btn btn-outline" id="btn-cancel-booking-step1">Cancel</button>
                        <button type="button" class="btn btn-primary" id="btn-goto-step-2">Continue to Patient Details &rarr;</button>
                    </div>
                </div>

                <!-- STEP 2: Patient Details Fill Fields -->
                <div id="booking-step-2" class="booking-step-pane" style="display: none;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="booking-patient-name" style="font-weight: 600;">Patient Full Name *</label>
                            <input type="text" id="booking-patient-name" class="form-input" placeholder="e.g. John Doe" required />
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="booking-patient-phone" style="font-weight: 600;">Contact Phone Number *</label>
                            <input type="tel" id="booking-patient-phone" class="form-input" placeholder="e.g. +91 9876543210" required />
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="booking-patient-email" style="font-weight: 600;">Email Address *</label>
                            <input type="email" id="booking-patient-email" class="form-input" placeholder="e.g. patient@example.com" required />
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="booking-patient-gender" style="font-weight: 600;">Gender</label>
                            <select id="booking-patient-gender" class="form-select">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="booking-patient-dob" style="font-weight: 600;">Date of Birth / Age</label>
                            <input type="date" id="booking-patient-dob" class="form-input" />
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="booking-appointment-type" style="font-weight: 600;">Appointment Type</label>
                            <select id="booking-appointment-type" class="form-select">
                                <option value="consultation" selected>General Consultation</option>
                                <option value="follow-up">Follow-up Visit</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" for="booking-patient-reason" style="font-weight: 600;">Reason for Visit / Symptoms *</label>
                        <textarea id="booking-patient-reason" class="form-input" rows="2" placeholder="Briefly describe your symptoms or primary health concern..." required style="resize: vertical;"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label class="form-label" for="booking-patient-notes" style="font-weight: 600;">Medical History / Notes (Optional)</label>
                        <textarea id="booking-patient-notes" class="form-input" rows="2" placeholder="Any allergies, previous surgeries, or ongoing medications..." style="resize: vertical;"></textarea>
                    </div>

                    <!-- Payment Method Selection -->
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label" style="font-weight: 600; margin-bottom: 8px; display: block;">Payment Method *</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px;" id="booking-payment-methods-grid">
                            <label class="payment-method-card active" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 2px solid var(--primary); background: rgba(0, 82, 204, 0.04); border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;">
                                <input type="radio" name="booking_payment_method" value="upi" checked style="accent-color: var(--primary);" />
                                <span>UPI / QR</span>
                            </label>
                            <label class="payment-method-card" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border-subtle); background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;">
                                <input type="radio" name="booking_payment_method" value="card" style="accent-color: var(--primary);" />
                                <span>Card</span>
                            </label>
                            <label class="payment-method-card" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border-subtle); background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;">
                                <input type="radio" name="booking_payment_method" value="net_banking" style="accent-color: var(--primary);" />
                                <span>Net Banking</span>
                            </label>
                            <label class="payment-method-card" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border-subtle); background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;">
                                <input type="radio" name="booking_payment_method" value="wallet" style="accent-color: var(--primary);" />
                                <span>Wallet</span>
                            </label>
                            <label class="payment-method-card" style="display: flex; align-items: center; gap: 8px; padding: 10px 12px; border: 1px solid var(--border-subtle); background: white; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s;">
                                <input type="radio" name="booking_payment_method" value="cash" style="accent-color: var(--primary);" />
                                <span>Cash / Clinic</span>
                            </label>
                        </div>
                    </div>

                    <!-- Booking Summary Strip -->
                    <div id="booking-summary-strip" style="background: var(--surface-container-low); padding: 14px 18px; border-radius: 10px; font-size: 13px; color: var(--text-heading); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-subtle);">
                        <div>
                            <span style="font-weight: 600;">Selected:</span> 
                            <span id="summary-mode-text" style="font-weight: 700; color: var(--primary);">Video Consultation</span> on 
                            <span id="summary-datetime-text" style="font-weight: 700;">Select slot</span>
                        </div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--primary);" id="summary-fee-text">
                            Fee: ₹100.00
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-subtle);">
                        <button type="button" class="btn btn-outline" id="btn-cancel-booking-all">Cancel</button>
                        <div style="display: flex; gap: 12px;">
                            <button type="button" class="btn btn-outline" id="btn-backto-step-1">&larr; Back to Slot</button>
                            <button type="button" class="btn btn-primary" id="btn-submit-booking" style="background: var(--trust-blue); padding: 10px 22px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px;">
                                <span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle;">check_circle</span>
                                <span id="btn-submit-booking-text">Confirm & Book Appointment</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Firebase Compat SDKs & Client (Phase 5) -->
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-auth-compat.js"></script>
    <script src="https://www.gstatic.com/firebasejs/10.12.0/firebase-database-compat.js"></script>
    <script src="/my_doctor/public/assets/js/firebase-client.js?v=2"></script>
    <script src="/my_doctor/public/assets/js/call.js?v=2"></script>
    <script src="/my_doctor/public/assets/js/responsive-sidebar.js?v=3"></script>
    <script src="/my_doctor/public/assets/js/patient.js?v=44"></script>
</body>
</html>
