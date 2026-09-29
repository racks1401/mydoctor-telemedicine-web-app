<!-- ── Main Content ── -->
  <div class="page-body">

    <div class="page-header">
      <div>
        <h2>Appointments</h2>
        <p>Track, manage, and review all scheduled and past appointments.</p>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-outline" data-action="filter">
          <span class="material-symbols-outlined">filter_list</span>
          Filter
        </button>
        <button class="btn btn-outline" data-action="export">
          <span class="material-symbols-outlined">download</span>
          Export
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card active" data-page="appointments" data-action="appointments-filter" data-filter="all">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-primary">
            <span class="material-symbols-outlined text-primary">event</span>
          </div>
          <span class="stat-badge up" id="total-today-appointment-trend">+0% <span class="material-symbols-outlined" style="font-size:12px;">trending_up</span></span>
        </div>
        <p class="stat-label">Today's Appointments</p>
        <p class="stat-value" id="today-appointment-count">0</p>
      </div>
      <div class="stat-card" data-page="appointments" data-action="appointments-filter" data-filter="confirmed">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-success">
            <span class="material-symbols-outlined text-success-green">event_available</span>
          </div>
          <span class="stat-badge neutral" id="confirmed-today-appointment-trend">0% of today</span>
        </div>
        <p class="stat-label">Confirmed</p>
        <p class="stat-value" id="confirmed-appointment-count">0</p>
      </div>
      <div class="stat-card" data-page="appointments" data-action="appointments-filter" data-filter="pending">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-secondary">
            <span class="material-symbols-outlined" style="color:var(--secondary);">pending_actions</span>
          </div>
          <span class="stat-badge neutral" id="pending-today-appointment-trend">0% of today</span>
        </div>
        <p class="stat-label">Pending</p>
        <p class="stat-value" id="pending-appointment-count">0</p>
      </div>
      <div class="stat-card" data-page="appointments" data-action="appointments-filter" data-filter="cancelled">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-error">
            <span class="material-symbols-outlined text-error-red">event_busy</span>
          </div>
          <span class="stat-badge up" id="cancelled-today-appointment-trend">0% of today</span>
        </div>
        <p class="stat-label">Cancelled</p>
        <p class="stat-value" id="cancelled-appointment-count">0</p>
      </div>
    </div>

    <!-- Appointments Table -->
    <div class="panel">
      <div class="panel-header">
        <h3>Appointment Schedule</h3>
        <span class="panel-header-meta" id="appointments-pagination-header">Showing 1-10 of 10 data</span>
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Patient</th>
              <th>Doctor</th>
              <th>Date &amp; Time</th>
              <th>Type</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="appointments-table-body">
            
          </tbody>
        </table>
      </div>
      <div class="pagination" id="appointments-pagination">
        <button class="btn-prev-next disabled" disabled>Previous</button>

        <div class="pagination-pages">
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <span class="page-ellipsis">...</span>
          <button class="page-btn">8</button>
        </div>
        
        <button class="btn-prev-next">Next</button>
      </div>
    </div>

  </div>
