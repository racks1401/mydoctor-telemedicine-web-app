
        <!-- Page Content -->
        <div class="page-container">
            <div class="page-header-flex">
                <div>
                    <h1 class="page-title">Call History &amp; Logs</h1>
                    <p class="page-subtitle">Review past consultations and manage upcoming appointments.</p>
                </div>
                <button class="btn btn-primary">
                    <span class="material-symbols-outlined">video_call</span>
                    Join Waiting Room
                </button>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-3">
                <div class="card stat-card">
                    <div class="card-title">
                        Total Call Minutes
                        <span class="material-symbols-outlined color-primary">timer</span>
                    </div>
                    <div class="stat-value color-primary">14,280</div>
                    <div class="stat-label stat-label-lowercase">Total across 342 consultations this month</div>
                </div>

                <div class="card stat-card">
                    <div class="card-title">
                        Avg. Connection Quality
                        <span class="material-symbols-outlined color-warning">star</span>
                    </div>
                    <div class="stat-value color-primary">
                        4.9
                        <span class="stat-value-sub">/ 5.0</span>
                    </div>
                    <div class="stat-label stat-label-lowercase">Based on post-call ratings</div>
                </div>

                <div class="card stat-card">
                    <div class="card-title">
                        Consultation Type Mix
                        <span class="material-symbols-outlined color-muted">pie_chart</span>
                    </div>
                    <div class="flex-gap mt-8">
                        <div>
                            <div class="stat-value stat-value-md">65%</div>
                            <div class="stat-label">Video</div>
                        </div>
                        <div>
                            <div class="stat-value stat-value-md">35%</div>
                            <div class="stat-label">Audio</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call Log Table -->
            <div class="card mt-32">
                <div class="card-title">
                    Past Consultations
                    <div class="flex-gap">
                        <button class="btn btn-outline btn-sm">
                            <span class="material-symbols-outlined icon-md">filter_list</span>
                            Filter
                        </button>
                        <button class="btn btn-outline btn-sm">
                            <span class="material-symbols-outlined icon-md">download</span>
                            Export CSV
                        </button>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Date &amp; Time</th>
                                <th>Patient</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Quality</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr>
                                <td>
                                    <div class="txt-medium color-text-main">Oct 24, 2023</div>
                                    <div class="color-muted font-size-8 mt-2">06:30 PM</div>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <img alt="MD" class="avatar avatar-sm" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDjciblsl1ERPJwuCfFlPxsoFml5hkG_G5V2VZBfx9VuDlzjpR2SD4sVLeeV2rDGnBSS3ls6FWx4zOj4qBlQpDW3r1F4fOsiYhkcov_Cux_ZxLkPbjyzyFZKo6IrQKfGyCWo8vGQBYD5FtonNFWyOG4JCFPRLZrARwO2WTPKWL3f_zgB8jxvHKFw4GsIlVi6VxE5YvAc28J9dtTFqaJvbFau6zCSGK81KTmYHOs-p2K89MuG2Kqe2WZEcfvxz-Jy9SEGKS_-LulPUI" />
                                        <span class="txt-medium">Marcus Davis</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-blue flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">videocam</span>Video
                                    </span>
                                </td>
                                <td>28 mins</td>
                                <td>
                                    <span class="badge badge-success flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">star</span>Excellent
                                    </span>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <a href="#" class="action-link">
                                            <span class="material-symbols-outlined icon-16">description</span>
                                            Prescription
                                        </a>
                                        <button class="icon-btn">
                                            <span class="material-symbols-outlined icon-md">more_vert</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 2 -->
                            <tr>
                                <td>
                                    <div class="txt-medium color-text-main">Oct 24, 2023</div>
                                    <div class="color-muted font-size-8 mt-2">04:00 PM</div>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <div class="patient-avatar-initial">TC</div>
                                        <span class="txt-medium">Tiffany Carter</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-blue flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">call</span>Audio
                                    </span>
                                </td>
                                <td>12 mins</td>
                                <td>
                                    <span class="badge badge-warning flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">star_half</span>Fair
                                    </span>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <a href="#" class="action-link">
                                            <span class="material-symbols-outlined icon-16">history_edu</span>
                                            Notes
                                        </a>
                                        <button class="icon-btn">
                                            <span class="material-symbols-outlined icon-md">more_vert</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 3 -->
                            <tr>
                                <td>
                                    <div class="txt-medium color-text-main">Oct 23, 2023</div>
                                    <div class="color-muted font-size-8 mt-2">04:45 PM</div>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <div class="patient-avatar-initial">EL</div>
                                        <span class="txt-medium">Emma Lopez</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-blue flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">videocam</span>Video
                                    </span>
                                </td>
                                <td>45 mins</td>
                                <td>
                                    <span class="badge badge-success flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">star</span>Excellent
                                    </span>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <a href="#" class="action-link">
                                            <span class="material-symbols-outlined icon-16">description</span>
                                            Prescription
                                        </a>
                                        <button class="icon-btn">
                                            <span class="material-symbols-outlined icon-md">more_vert</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Row 4 -->
                            <tr>
                                <td>
                                    <div class="txt-medium color-text-main">Oct 23, 2023</div>
                                    <div class="color-muted font-size-8 mt-2">02:30 PM</div>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <div class="patient-avatar-initial orange">AW</div>
                                        <span class="txt-medium">Alan Wright</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-blue flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">videocam</span>Video
                                    </span>
                                </td>
                                <td>18 mins</td>
                                <td>
                                    <span class="badge badge-success flex items-center badge-fit">
                                        <span class="material-symbols-outlined icon-xs">star</span>Excellent
                                    </span>
                                </td>
                                <td>
                                    <div class="flex-center-gap">
                                        <a href="#" class="action-link">
                                            <span class="material-symbols-outlined icon-16">history_edu</span>
                                            Notes
                                        </a>
                                        <button class="icon-btn">
                                            <span class="material-symbols-outlined icon-md">more_vert</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

