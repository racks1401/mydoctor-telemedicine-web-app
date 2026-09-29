
  <div class="page-body">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2>Doctors</h2>
        <p>Manage and review all registered medical practitioners on the platform.</p>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-outline" data-action="filter">
          <span class="material-symbols-outlined">filter_list</span>
          Filter
        </button>
        <button class="btn btn-primary" data-action="add-doctor">
          <span class="material-symbols-outlined">person_add</span>
          Add Doctor
        </button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stat-grid">
      <div class="stat-card active" data-page="doctors" data-action="doctors-filter" data-filter="all">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-primary">
            <span class="material-symbols-outlined text-primary">medical_services</span>
          </div>
          <span class="stat-badge neutral">Total</span>
        </div>
        <p class="stat-label">Registered Doctors</p>
        <p class="stat-value" id="registered-doctors">138</p>
      </div>
      <div class="stat-card" data-page="doctors" data-action="doctors-filter" data-filter="active">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-success">
            <span class="material-symbols-outlined text-success-green">check_circle</span>
          </div>
          <span class="stat-badge down" id="active-doctors-trend-doctor">+3 this week</span>
        </div>
        <p class="stat-label">Active</p>
        <p class="stat-value" id="active-doctors-count-doctor">121</p>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-secondary">
            <span class="material-symbols-outlined" style="color:var(--secondary);">pending</span>
          </div>
          <span class="stat-badge neutral">Awaiting</span>
        </div>
        <p class="stat-label">Pending Verification</p>
        <p class="stat-value" id="pending-verification">12</p>
      </div>
      <div class="stat-card" data-page="doctors" data-action="doctors-filter" data-filter="blocked">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-error">
            <span class="material-symbols-outlined text-error-red">block</span>
          </div>
          <span class="stat-badge up" id="blocked-trend-doctor">+2%</span>
        </div>
        <p class="stat-label">Blocked</p>
        <p class="stat-value" id="blocked-doctor">15</p>
      </div>
    </div>

    <!-- Doctors Table -->
    <div class="panel">
      <div class="panel-header">
        <h3>Doctor Registry</h3>
        <span class="panel-header-meta" id="doctors-pagination-header">Showing 1–10 of 138 doctors</span>
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Specialisation</th>
              <th>Department</th>
              <th>Status</th>
              <th>Joined</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="doctors-table-body">
            
          </tbody>
        </table>
      </div>
      <div class="pagination" id="doctors-pagination">
        <button class="btn-prev-next disabled" disabled>Previous</button>

        <div class="pagination-pages">
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <span class="page-ellipsis">...</span>
          <button class="page-btn">14</button>
        </div>

        <button class="btn-prev-next">Next</button>
      </div>
    </div>

  </div>

