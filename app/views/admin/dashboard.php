<!-- ── Main Content ── -->
  <div class="page-body">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2>Overview</h2>
        <p id="greet-with-user-name"></p>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-primary" data-page="dashboard" data-action="export">
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
        <div class="timeline" id="system-alerts-timeline">
          <!-- Dynamic System Alerts rendered here -->
        </div>
      </div>

    </div>
  </div>

