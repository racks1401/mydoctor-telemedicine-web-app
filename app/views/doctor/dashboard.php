

        <div class="page-container">
            <!-- Welcome Header -->
            <div class="page-header">
                <h2 class="page-title">Good Morning, Dr. Jenkins</h2>
                <p class="page-subtitle">Here is a summary of your clinic's performance today.</p>
            </div>

            <!-- Stat Cards Grid -->
            <div class="grid grid-cols-4">
                <!-- Total Patients -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-primary">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                        <span class="badge badge-success">
                            <span class="material-symbols-outlined icon-xs">trending_up</span>
                            12%
                        </span>
                    </div>
                    <h3 class="stat-label">Total Patients</h3>
                    <p class="stat-value">1,284</p>
                </div>

                <!-- Appointments Today -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-indigo">
                            <span class="material-symbols-outlined">event_available</span>
                        </div>
                        <span class="stat-badge">Target: 25</span>
                    </div>
                    <h3 class="stat-label">Appointments Today</h3>
                    <p class="stat-value">18</p>
                </div>

                <!-- Pending Reports -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-danger">
                            <span class="material-symbols-outlined">assignment_late</span>
                        </div>
                        <span class="badge badge-danger">URGENT</span>
                    </div>
                    <h3 class="stat-label">Pending Reports</h3>
                    <p class="stat-value">09</p>
                </div>

                <!-- Patient Satisfaction -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-warning">
                            <span class="material-symbols-outlined material-fill-1">star</span>
                        </div>
                        <span class="stat-badge stat-badge-warning">98th Percentile</span>
                    </div>
                    <h3 class="stat-label">Satisfaction</h3>
                    <p class="stat-value">4.8/5</p>
                </div>
            </div>

            <!-- Middle Section -->
            <div class="grid grid-split-2-1">
                <!-- Latest Activities / Alerts -->
                <div class="card">
                    <div class="card-title">Recent Patient Activity</div>
                    <div class="alert-item">
                        <div class="alert-icon">
                            <span class="material-symbols-outlined">medical_information</span>
                        </div>
                        <div class="alert-content">
                            <p class="alert-title">Lab Results Ready</p>
                            <p class="alert-message">John Doe's complete metabolic panel results are ready for review.</p>
                            <p class="alert-time">45 minutes ago</p>
                        </div>
                    </div>

                    <div class="alert-item">
                        <div class="alert-icon">
                            <span class="material-symbols-outlined">medical_information</span>
                        </div>
                        <div class="alert-content">
                            <p class="alert-title">Urgent Message</p>
                            <p class="alert-message">"Doctor, I'm experiencing side effects from the new medication." — Robert S.</p>
                            <p class="alert-time">1 hour ago</p>
                        </div>
                    </div>

                    <div class="mt-24-center">
                        <button class="btn btn-outline">View All Activity</button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-title">Quick Overview</div>
                    <div class="flex-col-gap-16">
                        <div>
                            <div class="stat-label">Avg. Patient Wait Time</div>
                            <div class="stat-value">8 mins</div>
                        </div>
                        <div>
                            <div class="stat-label">Consultations Today</div>
                            <div class="stat-value">12</div>
                        </div>
                        <div>
                            <div class="stat-label">Follow-ups Needed</div>
                            <div class="stat-value">5</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="grid grid-2-1">
                <!-- Patient Flow Chart -->
                <div class="card">
                    <div class="card-title">
                        <span>Patient Flow Analysis</span>
                        <div class="flex gap-4">
                            <button class="badge badge-blue badge-btn-borderless">Weekly</button>
                            <button class="badge badge-monthly">Monthly</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <div class="chart-bar h-pct-60"></div>
                        <div class="chart-bar h-pct-40"></div>
                        <div class="chart-bar h-pct-85"></div>
                        <div class="chart-bar h-pct-55"></div>
                        <div class="chart-bar chart-bar-light h-pct-70"></div>
                        <div class="chart-bar chart-bar-light h-pct-30"></div>
                        <div class="chart-bar chart-bar-light h-pct-10"></div>
                        
                        <div class="chart-grid-line bottom-pct-25"></div>
                        <div class="chart-grid-line bottom-pct-50"></div>
                        <div class="chart-grid-line bottom-pct-75"></div>
                    </div>
                    <div class="chart-labels">
                        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                    </div>
                </div>

                <!-- Telehealth Card -->
                <div class="card gradient-card">
                    <div class="gradient-card-content">
                        <h3 class="gradient-card-title">Telehealth Ready</h3>
                        <p class="gradient-card-text">You have 4 virtual consultations scheduled for this afternoon.</p>
                        <button class="btn btn-gradient-card">Start Next Session</button>
                    </div>
                    <span class="material-symbols-outlined gradient-card-icon material-fill-1">monitor_heart</span>
                </div>
            </div>
        </div>

