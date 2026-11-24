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
    <link rel="stylesheet" href="/my_doctor/public/assets/css/admin.css?v=11">
    <link rel="stylesheet" href="/my_doctor/public/assets/css/responsive-sidebar.css?v=8">
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch (e) {}
        })();
    </script>
</head>

<body>

    <!-- ── Sidebar ── -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-icon">
                <span class="material-symbols-outlined">admin_panel_settings</span>
            </div>
            <div class="brand-text">
                <h1>MyDoctor</h1>
                <p>Admin Panel</p>
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
                <span class="material-symbols-outlined">medical_services</span>
                <span>Doctors</span>
            </a>
            <a data-page="patients" class="nav-link" href="#" title="Patients">
                <span class="material-symbols-outlined">folder_shared</span>
                <span>Patients</span>
            </a>
            <a data-page="appointments" class="nav-link" href="#" title="Appointments">
                <span class="material-symbols-outlined">event</span>
                <span>Appointments</span>
            </a>
            <a data-page="user-verification" class="nav-link" href="#" title="User Verification">
                <span class="material-symbols-outlined">verified_user</span>
                <span>User Verification</span>
            </a>
            <a data-page="blocked-users" class="nav-link" href="#" title="Blocked Users">
                <span class="material-symbols-outlined">block</span>
                <span>Blocked User</span>
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
                    <input type="text" id="search" name="search" placeholder="Search patients, doctors, appointments..."/>
                </div>
            </div>
            <div class="topbar-actions">
                <button class="topbar-icon-btn" id="topbar-notif-btn" title="Notifications" style="position: relative;">
                    <span class="material-symbols-outlined">notifications</span>
                    <span id="notif-badge-dot" style="display: none; position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; border-radius: 50%; background-color: #2563eb; border: 2px solid white;"></span>
                </button>
                
                <div class="topbar-divider"></div>
                <div class="topbar-avatar" id="header-profile-img-container" data-page="common" data-action="open-settings" title="Admin Settings" style="cursor: pointer;"></div>
            </div>
        </header>

        <!-- Dynamic Content -->
        <main id="spa-content">

        </main>

    </div>

    <!-- Notification Modal (70vw x 70vh) -->
    <div id="modal-notifications" class="modal-overlay">
        <div class="admin-modal-card" style="width: 70vw; height: 70vh; max-width: 70vw; max-height: 70vh;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-subtle, #e5e7eb); background: var(--surface-container-low, #f8f9fa);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #dbeafe; color: #1d4ed8; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">notifications</span>
                    </div>
                    <div>
                        <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: var(--text-heading, #1f2937);">Admin Notifications</h3>
                        <p style="margin: 0; font-size: 12px; color: var(--text-muted, #6b7280);" id="modal-notif-subtitle">Recent security & verification alerts</p>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" id="btn-mark-all-read" class="btn btn-outline btn-sm" style="background: var(--surface, #ffffff); font-size: 12px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 4px; border: 1px solid var(--border-subtle, #e5e7eb); border-radius: 8px; font-weight: 600; cursor: pointer; color: var(--text-main, #374151);">
                        <span class="material-symbols-outlined" style="font-size: 16px;">done_all</span>
                        Mark All as Read
                    </button>
                    <button type="button" id="btn-close-notif-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted, #6b7280); line-height: 1; padding: 4px;" title="Close">&times;</button>
                </div>
            </div>

            <!-- Body -->
            <div id="modal-notif-list" style="flex: 1; overflow-y: auto; padding: 16px 24px;">
                <div style="text-align: center; padding: 40px; color: var(--text-muted, #6b7280);">Loading notifications...</div>
            </div>
        </div>
    </div>

    <!-- Add Doctor Modal -->
    <div id="modal-add-doctor" class="modal-overlay">
        <div class="admin-modal-card">
            <div class="admin-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">person_add</span>
                    </div>
                    <div>
                        <h3>Add New Doctor</h3>
                        <p>Create a verified doctor profile in the system</p>
                    </div>
                </div>
                <button type="button" id="btn-close-add-doctor-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;">&times;</button>
            </div>
            <form id="form-add-doctor">
                <div class="admin-modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" placeholder="e.g. Dr. Jane Smith" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" placeholder="doctor@hospital.com" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Password *</label>
                            <input type="password" name="password" placeholder="Doctor@123" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" placeholder="+91 9876543210" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" />
                        </div>
                        <div class="admin-form-group">
                            <label>Medical Reg. No.</label>
                            <input type="text" name="medical_reg_no" placeholder="e.g. MCI-98742" />
                        </div>
                        <div class="admin-form-group">
                            <label>Specialization *</label>
                            <input type="text" name="specialization" placeholder="e.g. Cardiology" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Department</label>
                            <input type="text" name="department" placeholder="e.g. Cardiology" />
                        </div>
                        <div class="admin-form-group">
                            <label>Qualification</label>
                            <input type="text" name="qualification" placeholder="e.g. MBBS, MD (Cardiology)" />
                        </div>
                        <div class="admin-form-group">
                            <label>Experience (Years)</label>
                            <input type="number" name="experience" min="0" max="60" placeholder="e.g. 8" />
                        </div>
                        <div class="admin-form-group">
                            <label>Working Hours</label>
                            <input type="text" name="working_time" placeholder="09:00 AM - 05:00 PM" />
                        </div>
                        <div class="admin-form-group">
                            <label>Online Fee (₹)</label>
                            <input type="number" name="online_fee" step="0.01" placeholder="100.00" value="100.00" />
                        </div>
                        <div class="admin-form-group">
                            <label>Offline / Clinic Fee (₹)</label>
                            <input type="number" name="offline_fee" step="0.01" placeholder="150.00" value="150.00" />
                        </div>
                        <div class="admin-form-group admin-form-full">
                            <label>Residential Address</label>
                            <input type="text" name="address" placeholder="Street / Clinic address" />
                        </div>
                        <div class="admin-form-group">
                            <label>City</label>
                            <input type="text" name="city" placeholder="City" />
                        </div>
                        <div class="admin-form-group">
                            <label>State</label>
                            <input type="text" name="state" placeholder="State" />
                        </div>
                        <div class="admin-form-group">
                            <label>PIN Code</label>
                            <input type="text" name="pin" placeholder="PIN / Zip" />
                        </div>
                        <div class="admin-form-group">
                            <label>Available For</label>
                            <select name="available_mode">
                                <option value="video_call,chat,offline,audio_call">All Modes (Video, Audio, Chat, Clinic)</option>
                                <option value="video_call,chat">Online Only (Video & Chat)</option>
                                <option value="offline">In-Person Clinic Only</option>
                            </select>
                        </div>
                        <div class="admin-form-group admin-form-full">
                            <label>About Doctor</label>
                            <textarea name="about" rows="2" placeholder="Brief professional biography and expertise..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-outline" id="btn-cancel-add-doctor">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-add-doctor">Save Doctor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Patient Modal -->
    <div id="modal-add-patient" class="modal-overlay">
        <div class="admin-modal-card">
            <div class="admin-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: #dcfce7; color: #15803d; display: flex; align-items: center; justify-content: center;">
                        <span class="material-symbols-outlined" style="font-size: 20px;">person_add</span>
                    </div>
                    <div>
                        <h3>Add New Patient</h3>
                        <p>Register a patient account in the system</p>
                    </div>
                </div>
                <button type="button" id="btn-close-add-patient-modal" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--text-muted); line-height: 1; padding: 4px;">&times;</button>
            </div>
            <form id="form-add-patient">
                <div class="admin-modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" placeholder="e.g. John Doe" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" placeholder="patient@example.com" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Password *</label>
                            <input type="password" name="password" placeholder="Patient@123" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="phone" placeholder="+91 9876543210" required />
                        </div>
                        <div class="admin-form-group">
                            <label>Gender</label>
                            <select name="gender">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" />
                        </div>
                        <div class="admin-form-group">
                            <label>Blood Group</label>
                            <select name="blood_type">
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="admin-form-group admin-form-full">
                            <label>Residential Address</label>
                            <input type="text" name="address" placeholder="House / Flat / Street address" />
                        </div>
                        <div class="admin-form-group">
                            <label>City</label>
                            <input type="text" name="city" placeholder="City" />
                        </div>
                        <div class="admin-form-group">
                            <label>State</label>
                            <input type="text" name="state" placeholder="State" />
                        </div>
                        <div class="admin-form-group">
                            <label>PIN Code</label>
                            <input type="text" name="pin" placeholder="PIN / Zip" />
                        </div>
                    </div>
                </div>
                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-outline" id="btn-cancel-add-patient">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-add-patient">Save Patient</button>
                </div>
            </form>
        </div>
    </div>

    <script src="/my_doctor/public/assets/js/responsive-sidebar.js?v=4"></script>
    <script src="/my_doctor/public/assets/js/admin.js?v=11"></script>
    
</body>
</html>
