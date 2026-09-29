
  <div class="page-body">

    <div class="page-header">
      <div>
        <h2>Patients</h2>
        <p>View and manage all patient records registered on the platform.</p>
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
      <div class="stat-card active" data-page="patients" data-action="patients-filter" data-filter="all">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-primary">
            <span class="material-symbols-outlined text-primary">people</span>
          </div>
          <span class="stat-badge neutral">Total</span>
        </div>
        <p class="stat-label">Total Patients</p>
        <p class="stat-value" id="total-patients-count">0</p>
      </div>
      <div class="stat-card" data-page="patients" data-action="patients-filter" data-filter="active">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-success">
            <span class="material-symbols-outlined text-success-green">person_check</span>
          </div>
          <span class="stat-badge down" id="active-patients-trend-patient">0% of total</span>
        </div>
        <p class="stat-label">Active Patients</p>
        <p class="stat-value" id="active-patients-count-patient">0</p>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-secondary">
            <span class="material-symbols-outlined" style="color:var(--secondary);">person_add</span>
          </div>
          <span class="stat-badge up" id="this-month-trend">
          </span>
        </div>
        <p class="stat-label">New This Month</p>
        <p class="stat-value" id="new-this-month-patients-count">0</p>
      </div>
      <div class="stat-card" data-page="patients" data-action="patients-filter" data-filter="blocked">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-error">
            <span class="material-symbols-outlined text-error-red">block</span>
          </div>
          <span class="stat-badge neutral" id="blocked-trend-patient">0% of Total</span>
        </div>
        <p class="stat-label">Blocked</p>
        <p class="stat-value" id="blocked-patients-count">0</p>
      </div>
    </div>

    <!-- Patients Table -->
    <div class="panel">
      <div class="panel-header">
        <h3>Patient Records</h3>
        <span class="panel-header-meta" id="patients-pagination-header"></span>
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Patient</th>
              <th>Date of Birth</th>
              <th>Last Visit</th>
              <th>Email</th>
              <th>Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="patients-table-body">
            
          </tbody>
        </table>
      </div>
      <div class="pagination" id="patients-pagination">
        <button class="btn-prev-next disabled" data-action="prev" disabled>Previous</button>

        <div class="pagination-pages">
          <button class="page-btn active" data-page="1">1</button>
          <button class="page-btn" data-page="1">2</button>
          <button class="page-btn" data-page="1">3</button>
          <span class="page-ellipsis">...</span>
          <button class="page-btn" data-page="483">483</button>
        </div>

        <button class="btn-prev-next" data-action="next">Next</button>
      </div>
    </div>

  </div>

