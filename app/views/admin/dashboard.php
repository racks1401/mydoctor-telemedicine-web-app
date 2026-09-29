<!-- ── Main Content ── -->
  <div class="page-body">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2>Overview</h2>
        <p id="greet-with-user-name"></p>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-outline" data-action="filter">
          <span class="material-symbols-outlined">calendar_today</span>
          Today
        </button>
        <button class="btn btn-primary" data-action="report">
          <span class="material-symbols-outlined">download</span>
          Export Report
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-primary">
            <span class="material-symbols-outlined text-primary">people</span>
          </div>
          <span class="stat-badge neutral">All time</span>
        </div>
        <p class="stat-label">Total Patients</p>
        <p class="stat-value" id="total-patients">0</p>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-success">
            <span class="material-symbols-outlined text-success-green">medical_services</span>
          </div>
          <span class="stat-badge down">Total</span>
        </div>
        <p class="stat-label">Active Doctors</p>
        <p class="stat-value" id="active-doctors">0</p>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-secondary">
            <span class="material-symbols-outlined" style="color:var(--secondary);">event</span>
          </div>
          <span class="stat-badge up">Today</span>
        </div>
        <p class="stat-label">Appointments Today</p>
        <p class="stat-value" id="appointments-today">0</p>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-error">
            <span class="material-symbols-outlined text-error-red">block</span>
          </div>
          <span class="stat-badge up">Total</span>
        </div>
        <p class="stat-label">Blocked Users</p>
        <p class="stat-value" id="blocked-users">0</p>
      </div>
    </div>

    <!-- Cards Row -->
    <div class="overview-grid">

      <!-- Recent Appointments -->
      <div class="card">
        <h3>Recent Appointments</h3>
        <div class="data-table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody id="recent-appointments-body">
              
            </tbody>
          </table>
        </div>
      </div>

      <!-- System Alerts -->
      <div class="card">
        <h3>System Alerts</h3>
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-indicator">
              <div class="timeline-dot error"></div>
              <div class="timeline-line"></div>
            </div>
            <div>
              <p class="timeline-title">5 failed login attempts detected</p>
              <p class="timeline-body">Account DOC-44122 flagged for suspicious activity from Varna, BG.</p>
              <p class="timeline-time">2 hours ago</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator">
              <div class="timeline-dot success"></div>
              <div class="timeline-line"></div>
            </div>
            <div>
              <p class="timeline-title">New doctor verification submitted</p>
              <p class="timeline-body">Dr. Priya Nair (DOC-55311) submitted credentials for review.</p>
              <p class="timeline-time">Yesterday, 3:15 PM</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator">
              <div class="timeline-dot error"></div>
            </div>
            <div>
              <p class="timeline-title">Billing record discrepancy</p>
              <p class="timeline-body">Invoice #INV-0082 flagged for manual review by accounts team.</p>
              <p class="timeline-time">Oct 19, 2023</p>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

