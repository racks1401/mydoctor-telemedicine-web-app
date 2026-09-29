

        <div class="page-container">
            <!-- Header Section -->
            <div class="page-header-flex">
                <div>
                    <h2 class="page-title">Appointments Management</h2>
                    <p class="page-subtitle">Review and manage your daily clinical schedule.</p>
                </div>
                <div class="flex-center-gap">
                    <div class="flex-gap">
                        <button class="btn btn-primary">Today</button>
                        <button class="btn btn-outline">Week</button>
                        <button class="btn btn-outline">Month</button>
                    </div>
                    <button class="btn btn-primary">
                        <span class="material-symbols-outlined">event_busy</span>
                        Block Time
                    </button>
                </div>
            </div>

            <!-- Calendar / Table Layout -->
            <div class="card">
                <div class="card-title">
                    <span>Schedule for Oct 24, 2023</span>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Patient Name</th>
                                <th>Reason for Visit</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr>
                                <td>
                                    <div class="appointment-time">09:00 AM</div>
                                    <div class="appointment-duration">45 mins</div>
                                </td>
                                <td class="appointment-patient">Eleanor Kade</td>
                                <td class="appointment-reason">Hypertension Follow-up</td>
                                <td>
                                    <div class="appointment-type">
                                        <span class="material-symbols-outlined">person</span>
                                        In-Person
                                    </div>
                                </td>
                                <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                <td>
                                    <button class="btn btn-outline btn-sm">View Records</button>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td>
                                    <div class="appointment-time">10:15 AM</div>
                                    <div class="appointment-duration">30 mins</div>
                                </td>
                                <td class="appointment-patient">James Miller</td>
                                <td class="appointment-reason">Post-Op Review (Telehealth)</td>
                                <td>
                                    <div class="appointment-type">
                                        <span class="material-symbols-outlined">videocam</span>
                                        Video Call
                                    </div>
                                </td>
                                <td><span class="status-badge status-confirmed">Confirmed</span></td>
                                <td>
                                    <div class="flex-gap">
                                        <button class="btn btn-primary btn-sm">
                                            <span class="material-symbols-outlined icon-sm">call</span>
                                            Start Call
                                        </button>
                                        <button class="btn btn-outline btn-sm">View Records</button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 3 - Blocked Time -->
                            <tr>
                                <td>
                                    <div class="appointment-time">11:00 AM</div>
                                    <div class="appointment-duration">60 mins</div>
                                </td>
                                <td colspan="4" class="blocked-time-label">
                                    <div class="flex-center-gap">
                                        <span class="material-symbols-outlined">lock_clock</span>
                                        Time Blocked: Departmental Meeting
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-outline btn-sm">Unblock</button>
                                </td>
                            </tr>

                            <!-- Row 4 -->
                            <tr>
                                <td>
                                    <div class="appointment-time">12:30 PM</div>
                                    <div class="appointment-duration">20 mins</div>
                                </td>
                                <td class="appointment-patient">Amara Singh</td>
                                <td class="appointment-reason">Lab Results Discussion</td>
                                <td>
                                    <div class="appointment-type">
                                        <span class="material-symbols-outlined">person</span>
                                        In-Person
                                    </div>
                                </td>
                                <td><span class="status-badge status-completed">Completed</span></td>
                                <td>
                                    <button class="btn btn-outline btn-sm">View Records</button>
                                </td>
                            </tr>

                            <!-- Row 5 -->
                            <tr>
                                <td>
                                    <div class="appointment-time">01:45 PM</div>
                                    <div class="appointment-duration">45 mins</div>
                                </td>
                                <td class="appointment-patient">Lucas Bennett</td>
                                <td class="appointment-reason">New Patient Consultation</td>
                                <td>
                                    <div class="appointment-type">
                                        <span class="material-symbols-outlined">person</span>
                                        In-Person
                                    </div>
                                </td>
                                <td><span class="status-badge status-rescheduled">Rescheduled</span></td>
                                <td class="appointment-note">Move to Oct 26</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Secondary Insights / Cards -->
            <div class="grid grid-cols-3 mt-32">
                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="icon-bg-primary">
                            <span class="material-symbols-outlined">analytics</span>
                        </div>
                        <span class="stat-badge stat-badge-success">+12% vs last week</span>
                    </div>
                    <div class="stat-label">Patient Volume</div>
                    <div class="stat-value">142</div>
                </div>

                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon-container stat-icon-muted">
                            <span class="material-symbols-outlined">timer</span>
                        </div>
                        <span class="stat-badge">On Track</span>
                    </div>
                    <div class="stat-label">Avg. Consultation</div>
                    <div class="stat-value">28m</div>
                </div>

                <div class="card stat-card">
                    <div class="stat-card-header">
                        <div class="stat-icon-container stat-icon-success">
                            <span class="material-symbols-outlined">thumb_up</span>
                        </div>
                        <span class="stat-badge stat-badge-success">High</span>
                    </div>
                    <div class="stat-label">Patient Satisfaction</div>
                    <div class="stat-value">4.9/5</div>
                </div>
            </div>
        </div>
