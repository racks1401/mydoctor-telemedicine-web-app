<div class="page-container">
    <!-- Header Section -->
    <div class="page-header-flex">
        <div>
            <h2 class="page-title">Appointments Management</h2>
            <p class="page-subtitle">Review and manage your daily clinical schedule.</p>
        </div>
        <div class="flex-center-gap">
            <div class="flex-gap" id="doctor-appointment-filters">
                <button class="btn btn-primary btn-sm doc-filter-btn" data-status="all">All</button>
                <button class="btn btn-outline btn-sm doc-filter-btn" data-status="pending">Pending</button>
                <button class="btn btn-outline btn-sm doc-filter-btn" data-status="confirmed">Confirmed</button>
                <button class="btn btn-outline btn-sm doc-filter-btn" data-status="resheduled">Rescheduled</button>
                <button class="btn btn-outline btn-sm doc-filter-btn" data-status="completed">Completed</button>
                <button class="btn btn-outline btn-sm doc-filter-btn" data-status="cancelled">Cancelled</button>
            </div>
        </div>
    </div>

    <!-- Calendar / Table Layout -->
    <div class="card" style="margin-top: 16px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span id="doc-appointments-title">Appointments List</span>
            <span id="doc-appointments-count" style="font-size: 13px; color: var(--text-muted); font-weight: 500;"></span>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient Name</th>
                        <th>Reason for Visit</th>
                        <th>Type / Mode</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="doctor-appointments-tbody">
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 28px; color: var(--text-muted);">
                            Loading appointments...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="doctor-appointments-pagination" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-top: 1px solid var(--border-subtle);">
            <div id="doc-pagination-info" style="font-size: 13px; color: var(--text-muted);"></div>
            <div id="doc-pagination-btns" style="display: flex; gap: 8px;"></div>
        </div>
    </div>
</div>

<!-- Modal: Doctor Reschedule Appointment -->
<div id="modal-doc-reschedule" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="width: min(600px, 92vw); max-height: 88vh; background: white; border-radius: 16px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); display: flex; flex-direction: column; overflow: hidden;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 24px; border-bottom: 1px solid var(--border-subtle); background: var(--surface-container-low, #f8f9fa);">
            <div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 700; color: var(--text-heading);">Reschedule Appointment</h3>
                <p style="margin: 2px 0 0 0; font-size: 12.5px; color: var(--text-muted);" id="reschedule-modal-subtitle">Propose a new date and available time slot for this patient</p>
            </div>
            <button type="button" id="btn-close-reschedule-modal" style="background: none; border: none; font-size: 26px; cursor: pointer; color: var(--text-muted); line-height: 1;" title="Close">&times;</button>
        </div>

        <!-- Body -->
        <div style="flex: 1; overflow-y: auto; padding: 20px 24px;">
            <!-- Patient Info Card -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; margin-bottom: 18px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div id="reschedule-patient-avatar" style="width: 44px; height: 44px; border-radius: 50%; background: #dbeafe; color: #1e40af; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 16px; flex-shrink: 0;">
                        P
                    </div>
                    <div>
                        <h4 id="reschedule-patient-name" style="margin: 0; font-size: 15px; font-weight: 700; color: #1e3a8a;">Patient Name</h4>
                        <p id="reschedule-current-slot-info" style="margin: 2px 0 0 0; font-size: 12px; color: #3b82f6;">Select date & time to reschedule</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span id="reschedule-mode-badge" style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #dbeafe; color: #1e40af; text-transform: capitalize; margin-bottom: 2px;">Consultation</span>
                    <div style="font-size: 11px; font-weight: 600; color: #854d0e;">Needs Reschedule</div>
                </div>
            </div>

            <form id="form-doc-reschedule">
                <input type="hidden" id="reschedule-appointment-id" value="">
                <input type="hidden" id="reschedule-proposed-time" value="">

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--text-heading); margin-bottom: 6px;">Select New Date</label>
                    <input type="date" id="reschedule-proposed-date" style="width: 100%; padding: 9px 12px; border: 1.5px solid var(--border-subtle); border-radius: 8px; font-size: 13.5px; font-family: inherit; outline: none; box-sizing: border-box;" required>
                </div>

                <div style="margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-heading); margin: 0;">Select Available Time Slot</label>
                        <span id="doc-reschedule-selected-display" style="font-size: 12px; font-weight: 600; color: #2563eb;"></span>
                    </div>
                    <div id="doc-reschedule-slots-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(95px, 1fr)); gap: 8px; max-height: 180px; overflow-y: auto; padding: 6px; border: 1.5px solid var(--border-subtle); border-radius: 8px; background: #fafafa;">
                        <div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px; text-align: center; padding: 14px;">Please pick a date to view available slots.</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; border-top: 1px solid var(--border-subtle); padding-top: 16px;">
                    <button type="button" id="btn-cancel-reschedule-modal" class="btn btn-outline" style="padding: 8px 18px; border-radius: 8px; font-size: 13px;">Cancel</button>
                    <button type="submit" id="btn-submit-reschedule-modal" class="btn btn-primary" style="padding: 8px 22px; border-radius: 8px; font-size: 13px; background: #2563eb; color: white; font-weight: 600;">Propose New Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>
