        <!-- Prescriptions Page Content -->
        <div class="page-container">
            <div class="page-header-flex">
                <div>
                    <h1 class="page-title">My Prescriptions</h1>
                    <p class="page-subtitle">Access your digital medical prescriptions, medications, and advice from certified doctors.</p>
                </div>
                <div class="calls-header-actions">
                    <div class="calls-search-bar">
                        <span class="material-symbols-outlined">search</span>
                        <input type="text" id="prescriptions-search-input" placeholder="Search by doctor, medicine, or diagnosis..." />
                    </div>
                    <button class="btn btn-primary" id="btn-refresh-prescriptions">
                        <span class="material-symbols-outlined">refresh</span>
                        Refresh
                    </button>
                </div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-3">
                <div class="card stat-card">
                    <div class="card-title">
                        Total Prescriptions
                        <span class="material-symbols-outlined color-primary">receipt_long</span>
                    </div>
                    <div class="stat-value color-primary" id="prescriptions-metric-total">--</div>
                    <div class="stat-label stat-label-lowercase" id="prescriptions-metric-subtitle">Digital consultation slips</div>
                </div>

                <div class="card stat-card">
                    <div class="card-title">
                        Prescribed Medicines
                        <span class="material-symbols-outlined color-warning">medication</span>
                    </div>
                    <div class="stat-value color-primary">
                        <span id="prescriptions-metric-medicines">--</span>
                        <span class="stat-value-sub">Total</span>
                    </div>
                    <div class="stat-label stat-label-lowercase">Dosages &amp; medical courses</div>
                </div>

                <div class="card stat-card">
                    <div class="card-title">
                        Attending Doctors
                        <span class="material-symbols-outlined color-muted">medical_services</span>
                    </div>
                    <div class="stat-value color-primary" id="prescriptions-metric-doctors">--</div>
                    <div class="stat-label stat-label-lowercase">Specialists consulted</div>
                </div>
            </div>

            <!-- Prescriptions Cards List -->
            <div class="mt-32">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--on-surface, #1e293b); margin: 0;">Prescription Records</h3>
                    <span class="badge badge-blue badge-fit" id="prescriptions-count-badge" style="font-size: 12px;">0 Records</span>
                </div>

                <div id="patient-prescriptions-container">
                    <div class="whatsapp-empty-state">
                        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">progress_activity</span>
                        <p>Loading your prescriptions...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prescription Slip Printable Modal -->
        <div id="modal-prescription-slip" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 10000; align-items: center; justify-content: center; backdrop-filter: blur(3px); padding: 20px;">
            <div style="width: 100%; max-width: 780px; max-height: 90vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.3); display: flex; flex-direction: column; overflow: hidden;">
                <!-- Modal Action Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="material-symbols-outlined" style="color: #2563eb; font-size: 22px;">verified</span>
                        <span style="font-weight: 700; font-size: 15px; color: #0f172a;">Official Digital Medical Prescription</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <button type="button" class="btn btn-primary" id="btn-print-slip" style="padding: 6px 14px; font-size: 13px;">
                            <span class="material-symbols-outlined" style="font-size: 16px;">print</span>
                            Print Slip
                        </button>
                        <button type="button" id="btn-close-slip" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b; line-height: 1; padding: 4px;" title="Close">&times;</button>
                    </div>
                </div>

                <!-- Printable Body -->
                <div id="prescription-slip-printable-area" style="flex: 1; overflow-y: auto; padding: 28px 32px; font-family: 'Inter', -apple-system, sans-serif; color: #1e293b; background: #ffffff;">
                    <!-- Content injected dynamically by JS -->
                </div>
            </div>
        </div>
