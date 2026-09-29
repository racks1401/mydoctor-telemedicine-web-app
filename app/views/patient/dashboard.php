
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h2>Welcome back, Alexander</h2>
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
                        <div class="appointment-header">
                            <p class="appointment-header-label">Next Appointment</p>
                            <h4 class="appointment-time" id="upcoming-appointment-date"></h4>
                        </div>
                        <div class="appointment-body">
                            <div class="appointment-doctor">
                                <div class="user-avatar" id="doctor-profile-photo"></div>
                                <div class="appointment-doctor-info">
                                    <h4 id="upcoming-appointment-doctor"></h4>
                                    <p id="upcoming-appointment-specialization"></p>
                                </div>
                            </div>
                            <div class="appointment-details">
                                <div class="appointment-detail-item">
                                    <span id="appointment-mode-icon" class="material-symbols-outlined appointment-detail-icon">videocam</span>
                                    <span class="appointment-detail-text" id="upcoming-appointment-mode"></span>
                                </div>
                                <div class="appointment-detail-item">
                                    <span class="material-symbols-outlined appointment-detail-icon">timer</span>
                                    <span class="appointment-detail-text" id="upcoming-appointment-duration"></span>
                                </div>
                            </div>
                            <div class="appointment-actions">
                                <button class="btn btn-primary" data-page="common" data-action="join-appointment" id="upcoming-appointment-join">Join Video Call</button>
                                <button class="btn btn-outline" data-page="common" data-action="reschedule-appointment" id="upcoming-appointment-reschedule">Reschedule</button>
                            </div>
                        </div>
                    </div>

                    
                </div>
            </div>
        </div>
