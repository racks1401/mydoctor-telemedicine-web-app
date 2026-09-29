
    <main class="main-content">
        <header class="topbar">
            <div class="search-bar">
                <span class="material-symbols-outlined">search</span>
                <input placeholder="Search patients, appointments, messages..." type="text" />
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                     <span class="material-symbols-outlined">notifications</span>
                </button>
                <button class="icon-btn">
                     <span class="material-symbols-outlined">help</span>
                </button>
                <button class="icon-btn" onclick="window.location.href='settings.html'">
                     <span class="material-symbols-outlined">settings</span>
                </button>
                <div class="user-profile" onclick="window.location.href='settings.html'">
                    <img alt="Dr. Sarah Jenkins" class="avatar" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAzQur896wjb3Hq8t0EGjO_mcb0UwZMKDLF1P4x2Nc6L1XToSJ7L0ENLDVXHoyY8OEUSIgxDbB7kJ_XHfw4SeIC9vPPTM7S3olZokon2StjgJnm2Xuxgik7i_cI_CEPFQ2FzrRzIobvcoFJYAkxYtUCXdSUL7pCAc7TVCIsfvAzjaZQPTnfi6NBr4hHQ-J2WkL_FpO5WFZ66vRee-CpJYi8EC6vI4Pk8Dy34ChRYsYag_EMrDaZpEthQNAP3JCIP2IvetenLmglAJs" />
                    <div>
                        <div class="topbar-user-name">Dr. Sarah Jenkins</div>
                        <div class="topbar-user-role">Senior Cardiologist</div>
                    </div>
                </div>
            </div>
        </header>

        <div class="page-container">
            <div class="page-header">
                <h1 class="page-title">Clinic Availability</h1>
                <p class="page-subtitle">Configure your work days, shift times, and vacation schedules.</p>
            </div>

            <!-- Tabs Navigation -->
            <div class="settings-tabs">
                <a href="settings.html" class="tab-link">Profile Info</a>
                <a href="availability.html" class="tab-link active">Clinic Availability</a>
                <a href="#" class="tab-link">Security</a>
            </div>

            <!-- Quick Status Dashboard Panel -->
            <div class="quick-status-card">
                <div class="quick-status-left">
                    <span class="material-symbols-outlined color-primary" style="font-size: 2.2rem;">clinical_notes</span>
                    <div class="quick-status-info">
                        <h4>Current Duty Status</h4>
                        <p id="duty-description">Dr. Sarah Jenkins is currently active and accepting telehealth &amp; in-person consultations.</p>
                    </div>
                </div>
                <div>
                    <span class="status-pill status-pill-green" id="current-status-badge">
                        <span class="material-symbols-outlined icon-xs">check_circle</span>On Duty
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-12 gap-6">
                <!-- Add Availability Form Card (col-span-4) -->
                <div class="card col-span-4">
                    <h2 class="card-title">Manage Availability</h2>
                    <p class="color-muted font-size-8 mb-20">Set a recurring weekday slot or choose specific individual leave/on-duty dates.</p>

                    <!-- Type Selector: Weekly vs Specific Date -->
                    <div class="schedule-type-selector">
                        <button class="schedule-type-btn active" id="type-weekly" onclick="toggleScheduleType('weekly')">Recurring Day</button>
                        <button class="schedule-type-btn" id="type-date" onclick="toggleScheduleType('date')">Specific Date</button>
                    </div>

                    <form id="availability-form">
                        <!-- Left Group: Weekday Selector (Default) -->
                        <div class="form-group" id="group-day">
                            <label class="form-label" for="avail-day">Day of Week</label>
                            <select class="form-control" id="avail-day" style="width: 100%;">
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>

                        <!-- Grid Group: Calendar Date Selector (Hidden initially) -->
                        <div class="form-group hidden" id="group-date">
                            <label class="form-label" for="avail-date">Specific Date</label>
                            <input class="form-control" id="avail-date" type="date" value="2026-05-25" style="width: 100%;" />
                        </div>

                        <!-- Hours Row -->
                        <div class="form-group">
                            <label class="form-label">Working Hours</label>
                            <div class="time-inputs">
                                <div>
                                    <span class="color-muted font-size-7 mb-4 display-block">Start Time</span>
                                    <input class="form-control" id="avail-start" type="time" value="09:00" style="width: 100%; font-size: 0.85rem;" />
                                </div>
                                <div>
                                    <span class="color-muted font-size-7 mb-4 display-block">End Time</span>
                                    <input class="form-control" id="avail-end" type="time" value="17:00" style="width: 100%; font-size: 0.85rem;" />
                                </div>
                            </div>
                        </div>

                        <!-- Status Selector -->
                        <div class="form-group">
                            <label class="form-label" for="avail-status">Duty Status</label>
                            <select class="form-control" id="avail-status" style="width: 100%;" onchange="onStatusChange()">
                                <option value="On Duty">On Duty</option>
                                <option value="On Call">On Call</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Out of Office">Out of Office</option>
                            </select>
                        </div>

                        <!-- Location Selector -->
                        <div class="form-group" id="location-form-group">
                            <label class="form-label" for="avail-location">Location / Suite</label>
                            <select class="form-control" id="avail-location" style="width: 100%;">
                                <option value="Clinic Suite 302">Clinic Suite 302</option>
                                <option value="Emergency Department">Emergency Department</option>
                                <option value="Cardio Lab Suite B">Cardio Lab Suite B</option>
                                <option value="Remote Consultation">Remote Consultation</option>
                            </select>
                        </div>

                        <button class="btn btn-primary" type="button" onclick="handleAddRule()" style="width: 100%; justify-content: center; margin-top: 10px;">
                            <span class="material-symbols-outlined">add_circle</span>
                            Apply Schedule Rule
                        </button>
                    </form>
                </div>

                <!-- Current Schedule Rules List (col-span-8) -->
                <div class="card col-span-8">
                    <div class="card-title-container flex justify-between items-center mb-20">
                        <h2 class="card-title" style="margin: 0;">Active Clinic Schedules</h2>
                        <button class="btn btn-outline btn-sm" onclick="resetDefaultSchedule()">
                            <span class="material-symbols-outlined icon-sm">restart_alt</span>
                            Reset to Default
                        </button>
                    </div>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Day / Date</th>
                                    <th>Working Hours</th>
                                    <th>Location / Note</th>
                                    <th>Status</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="schedule-tbody">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Interactive Custom Status Toast Notification -->
    <div class="toast-msg" id="action-toast">
        <span class="material-symbols-outlined" id="toast-icon">check_circle</span>
        <span id="toast-text">Schedule updated successfully.</span>
    </div>
