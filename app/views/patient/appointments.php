
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h2>My Appointments</h2>
                    <p>Review and manage your upcoming and past medical visits.</p>
                </div>
                <div class="header-btns">
                    <button class="btn btn-outline">
                        <span class="material-symbols-outlined">filter_list</span>
                        Filter
                    </button>
                    <button class="btn btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        New Appointment
                    </button>
                </div>
            </div>

            <!-- Appointments Table Card -->
            <div class="card">
                <div class="card-toolbar">
                    <div class="filter-tabs">
                        <button class="tab active" data-page="appointments" data-action="appointments-filter" data-filter="all">All</button>
                        <button class="tab" data-page="appointments" data-action="appointments-filter" data-filter="upcoming">Upcoming</button>
                        <button class="tab" data-page="appointments" data-action="appointments-filter" data-filter="pending">Pending</button>
                        <button class="tab" data-page="appointments" data-action="appointments-filter" data-filter="confirmed">Confirmed</button>
                        <button class="tab" data-page="appointments" data-action="appointments-filter" data-filter="completed">Completed</button>
                        <button class="tab" data-page="appointments" data-action="appointments-filter" data-filter="cancelled">Cancelled</button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Doctor</th>
                                <th>Specialization</th>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="appointments-tbody">
                            
                        </tbody>
                    </table>
                </div>

                <div class="pagination" id="appointments-pagination">
                    <p id="appointments-pagination-header" >Showing 1 to 4 of 12 appointments</p>
                    <div class="pagination-controls" id="pagination-controls">
                        
                    </div>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="info-grid">
                <div class="info-card">
                    <div class="info-icon-wrapper bg-blue-light">
                        <span class="material-symbols-outlined">info</span>
                    </div>
                    <div class="info-content">
                        <h4>Appointment Policies</h4>
                        <p>Cancellations must be made at least 24 hours in advance to avoid a no-show fee.</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon-wrapper bg-indigo-light">
                        <span class="material-symbols-outlined">video_chat</span>
                    </div>
                    <div class="info-content">
                        <h4>Telehealth Support</h4>
                        <p>Check your email for the virtual consultation link 15 minutes before your time.</p>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-icon-wrapper bg-green-light">
                        <span class="material-symbols-outlined">verified</span>
                    </div>
                    <div class="info-content">
                        <h4>Health Records</h4>
                        <p>All follow-up results are automatically synced to your medical portal profile.</p>
                    </div>
                </div>
            </div>
        </div>
