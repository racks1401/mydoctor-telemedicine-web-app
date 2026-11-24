
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$patientWelcomeName = $_SESSION['name'] ?? '';
if (empty($patientWelcomeName) && !empty($_SESSION['user_id'])) {
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/UserModel.php';
    $u = UserModel::getUserById($conn, (int)$_SESSION['user_id']);
    $patientWelcomeName = $u['name'] ?? 'Patient';
}
if (empty($patientWelcomeName)) {
    $patientWelcomeName = 'Patient';
}
?>

        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h2 id="patient-dashboard-welcome">Welcome back, <?= htmlspecialchars($patientWelcomeName) ?></h2>
                    <p>Here's an overview of your health metrics and upcoming appointments.</p>
                </div>
            </div>

            <!-- Health Metrics Grid -->
            <div class="metric-grid">

                <div class="metric-card">
                    <div class="metric-top">
                        <div class="metric-icon-box appointment">
                            <span class="material-symbols-outlined">calendar_month</span>
                        </div>

                        <span class="metric-badge blue">Total</span>
                    </div>

                    <div class="metric-body">
                        <h2 class="metric-number" id="total-appointments-count">0</h2>
                        <p class="metric-label">Appointments</p>
                    </div>
                </div>

                <div class="metric-card">
                    <div class="metric-top">
                        <div class="metric-icon-box doctor">
                            <span class="material-symbols-outlined">stethoscope</span>
                        </div>

                        <span class="metric-badge green">Unique</span>
                    </div>

                    <div class="metric-body">
                        <h2 class="metric-number" id="total-doctors-consulted">0</h2>
                        <p class="metric-label">Doctors Consulted</p>
                    </div>

                </div>

                <div class="metric-card">
                    <div class="metric-top">
                        <div class="metric-icon-box pending">
                            <span class="material-symbols-outlined">schedule</span>
                        </div>

                        <span class="metric-badge orange">Pending</span>
                    </div>

                    <div class="metric-body">
                        <h2 class="metric-number" id="pending-appointments-count">0</h2>
                        <p class="metric-label">Awaiting Confirmation</p>
                    </div>

                </div>

                <div class="metric-card">
                    <div class="metric-top">
                        <div class="metric-icon-box completed">
                            <span class="material-symbols-outlined">task_alt</span>
                        </div>

                        <span class="metric-badge success">Done</span>
                    </div>

                    <div class="metric-body">
                        <h2 class="metric-number" id="completed-appointments-count">0</h2>
                        <p class="metric-label">Completed Appointments</p>
                    </div>

                </div>
            </div>

            <!-- Recent Activity & Sidebar Content -->
            <div class="two-col-grid">
                <!-- Recent Activity -->
                <div class="card activity-section">
                    <div class="activity-header">
                        <h3 class="activity-title">Recent Activity</h3>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">View All</button>
                    </div>
                    <div class="activity-content" id="recent-activity-container">
                        <!-- Activity 1 -->
                        <table class="data-table">
                            <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Mode</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody id="recent-appointments-body">
                            
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Right Sidebar -->
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    <!-- Next Appointment -->
                    <div class="appointment-card card-hover">
                        <div class="appointment-header" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 22px 24px;">
                            <p class="appointment-header-label" style="margin: 0 0 6px 0; font-size: 11px; letter-spacing: 0.06em; opacity: 0.9; text-transform: uppercase;">Next Appointment</p>
                            <h4 class="appointment-time" id="upcoming-appointment-date" style="margin: 0; font-size: 17px; font-weight: 700;">No Scheduled Session</h4>
                        </div>
                        <div class="appointment-body" style="padding: 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 16px;">
                            <div style="width: 54px; height: 54px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37,99,235,0.12); margin-top: 4px;">
                                <span class="material-symbols-outlined" style="font-size: 28px;">event_available</span>
                            </div>
                            <div style="text-align: center;">
                                <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: var(--on-surface, #1e293b);">No Upcoming Appointments</h4>
                                <p style="margin: 0 auto; font-size: 12.5px; color: var(--text-muted, #64748b); line-height: 1.5; max-width: 250px;">
                                    You have no pending consultations scheduled right now. Need medical consultation?
                                </p>
                            </div>
                            <button class="btn btn-primary" type="button" onclick="loadPage('doctors')" style="width: 100%; justify-content: center; margin-top: 4px; padding: 10px 18px; font-weight: 600; font-size: 13.5px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
                                <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
                                Book New Consultation
                            </button>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
