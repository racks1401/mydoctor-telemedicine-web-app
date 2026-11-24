        <!-- Page Content -->
        <div class="page-container">
            <div class="page-header-flex">
                <div>
                    <h1 class="page-title">Call History &amp; Logs</h1>
                    <p class="page-subtitle">Review past consultations, call durations, and start video/audio calls.</p>
                </div>
                <div class="calls-header-actions">
                    <div class="calls-search-bar">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="calls-search-input" placeholder="Search by patient name..." />
                    </div>
                    <button class="btn btn-primary" id="btn-calls-waiting-room">
                        <span class="material-symbols-outlined">video_call</span>
                        Open Active Call
                    </button>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-3">
                <div class="card stat-card">
                    <div class="card-title">
                        Total Call Minutes
                        <span class="material-symbols-outlined color-primary">timer</span>
                    </div>
                    <div class="stat-value color-primary" id="calls-metric-total-minutes">--</div>
                    <div class="stat-label stat-label-lowercase" id="calls-metric-total-subtitle">Loading consultation stats...</div>
                </div>

                <div class="card stat-card">
                    <div class="card-title">
                        Avg. Connection Quality
                        <span class="material-symbols-outlined color-warning">star</span>
                    </div>
                    <div class="stat-value color-primary">
                        <span id="calls-metric-quality">--</span>
                        <span class="stat-value-sub">/ 5.0</span>
                    </div>
                    <div class="stat-label stat-label-lowercase" id="calls-metric-completion-rate">Based on post-call ratings</div>
                </div>

                <div class="card stat-card">
                    <div class="card-title">
                        Consultation Type Mix
                        <span class="material-symbols-outlined color-muted">pie_chart</span>
                    </div>
                    <div class="flex-gap mt-8">
                        <div>
                            <div class="stat-value stat-value-md" id="calls-metric-video-pct">--%</div>
                            <div class="stat-label" id="calls-metric-video-count">Video</div>
                        </div>
                        <div>
                            <div class="stat-value stat-value-md" id="calls-metric-audio-pct">--%</div>
                            <div class="stat-label" id="calls-metric-audio-count">Audio</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Style Call Log Card -->
            <div class="card mt-32">
                <div class="card-title" style="margin-bottom: 16px;">
                    <span>Past Consultations</span>
                    <span class="badge badge-blue badge-fit" id="calls-count-badge" style="font-size: 12px;">0 Calls</span>
                </div>

                <!-- WhatsApp Filter Pills -->
                <div class="calls-filter-bar" id="calls-filter-bar">
                    <button class="calls-filter-pill active" data-call-filter="all">
                        <span class="material-symbols-outlined">call</span>
                        All Calls
                    </button>
                    <button class="calls-filter-pill" data-call-filter="video">
                        <span class="material-symbols-outlined">videocam</span>
                        Video
                    </button>
                    <button class="calls-filter-pill" data-call-filter="audio">
                        <span class="material-symbols-outlined">phone</span>
                        Audio
                    </button>
                    <button class="calls-filter-pill" data-call-filter="missed">
                        <span class="material-symbols-outlined">call_missed</span>
                        Missed
                    </button>
                </div>

                <!-- WhatsApp Calls List -->
                <div class="whatsapp-calls-list" id="whatsapp-calls-container">
                    <div class="whatsapp-empty-state">
                        <span class="material-symbols-outlined">hourglass_empty</span>
                        <p>Loading consultation calls...</p>
                    </div>
                </div>
            </div>
        </div>
