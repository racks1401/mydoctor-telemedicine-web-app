        <div class="page-container">
            <!-- Welcome Header -->
            <div class="page-header">
                <h2 class="page-title" id="doc-dash-greeting">Good Morning, Doctor</h2>
                <p class="page-subtitle" id="doc-dash-subgreeting">Here is a summary of your clinic's performance today.</p>
            </div>

            <!-- Stat Cards Grid -->
            <div class="grid grid-cols-4">
                <!-- Total Patients -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-primary">
                            <span class="material-symbols-outlined">groups</span>
                        </div>
                        <span class="badge badge-success" id="doc-dash-patients-trend">
                            <span class="material-symbols-outlined icon-xs">trending_up</span>
                            <span id="doc-dash-patients-trend-text">+12%</span>
                        </span>
                    </div>
                    <h3 class="stat-label">Total Patients</h3>
                    <p class="stat-value" id="doc-dash-total-patients">--</p>
                </div>

                <!-- Appointments Today -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-indigo">
                            <span class="material-symbols-outlined">event_available</span>
                        </div>
                        <span class="stat-badge" id="doc-dash-appts-today-badge">Today</span>
                    </div>
                    <h3 class="stat-label">Appointments Today</h3>
                    <p class="stat-value" id="doc-dash-appts-today">--</p>
                </div>

                <!-- Pending Reports / Requests -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-danger">
                            <span class="material-symbols-outlined">assignment_late</span>
                        </div>
                        <span class="badge badge-danger" id="doc-dash-pending-badge">Pending</span>
                    </div>
                    <h3 class="stat-label">Pending Requests</h3>
                    <p class="stat-value" id="doc-dash-pending-appts">--</p>
                </div>

                <!-- Patient Satisfaction -->
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-warning">
                            <span class="material-symbols-outlined material-fill-1">star</span>
                        </div>
                        <span class="stat-badge stat-badge-warning" id="doc-dash-reviews-badge">Rating</span>
                    </div>
                    <h3 class="stat-label">Satisfaction</h3>
                    <p class="stat-value" id="doc-dash-rating">--</p>
                </div>
            </div>

            <!-- Middle Section -->
            <div class="grid grid-split-2-1">
                <!-- Latest Activities / Alerts -->
                <div class="card">
                    <div class="card-title">Recent Patient Activity</div>
                    <div id="doc-dash-activities-list">
                        <div style="padding: 24px; text-align: center; color: var(--text-muted, #64748b);">
                            Loading recent activities...
                        </div>
                    </div>

                    <div class="mt-24-center">
                        <button class="btn btn-outline" id="btn-dash-view-all-appts">
                            <span class="material-symbols-outlined icon-sm">calendar_month</span>
                            View All Appointments
                        </button>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-title">Quick Overview</div>
                    <div class="flex-col-gap-16">
                        <div>
                            <div class="stat-label">Total Completed Consultations</div>
                            <div class="stat-value" id="doc-dash-completed-total">--</div>
                        </div>
                        <div>
                            <div class="stat-label">Consultations Today</div>
                            <div class="stat-value" id="doc-dash-completed-today">--</div>
                        </div>
                        <div>
                            <div class="stat-label">Follow-ups Needed</div>
                            <div class="stat-value" id="doc-dash-followups">--</div>
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
                            <button class="badge badge-blue badge-btn-borderless" id="btn-flow-weekly" style="cursor: pointer;">Weekly</button>
                            <button class="badge badge-monthly" id="btn-flow-monthly" style="cursor: pointer;">Monthly</button>
                        </div>
                    </div>
                    <div class="chart-container" id="doc-dash-chart-container">
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
                    <div class="chart-labels" id="doc-dash-chart-labels">
                        <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
                    </div>
                </div>

                <!-- Telehealth Card -->
                <div class="card gradient-card">
                    <div class="gradient-card-content">
                        <h3 class="gradient-card-title">Telehealth Ready</h3>
                        <p class="gradient-card-text" id="doc-dash-telehealth-text">Loading virtual consultations...</p>
                        <button class="btn btn-gradient-card" id="btn-dash-telehealth-action">Start Next Session</button>
                    </div>
                    <span class="material-symbols-outlined gradient-card-icon material-fill-1">monitor_heart</span>
                </div>
            </div>
        </div>
