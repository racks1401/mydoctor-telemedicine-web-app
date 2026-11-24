
<!-- ── Main Content ── -->
  <div class="page-body">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2>Blocked Users</h2>
        <p>Review and manage accounts restricted for policy violations or security concerns.</p>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-outline" data-page="blockedUsers" data-action="export">
          <span class="material-symbols-outlined">download</span>
          Export
        </button>
      </div>
    </div>

    <!-- Stats Overview -->
    <div class="stat-grid">

      <div class="stat-card active"
          data-page="blockedUsers"
          data-action="blocked-filter"
          data-filter="all">

        <div class="stat-card-top">
          <div class="stat-icon stat-icon-error">
            <span class="material-symbols-outlined text-error-red">block</span>
          </div>

          <span class="stat-badge up" id="total-blocked-trend">
            +0 Today
            <span class="material-symbols-outlined" style="font-size:12px;">
              trending_up
            </span>
          </span>
        </div>

        <p class="stat-label">Total Blocked</p>
        <p class="stat-value" id="total-blocked-count">0</p>
      </div>


      <div class="stat-card"
          data-page="blockedUsers"  
          data-action="blocked-filter"
          data-filter="doctor">

        <div class="stat-card-top">
          <div class="stat-icon stat-icon-primary">
            <span class="material-symbols-outlined text-primary">
              medical_services
            </span>
          </div>

          <span class="stat-badge neutral" id="doctor-blocked-trend">
            0% of total
          </span>
        </div>

        <p class="stat-label">Doctors Blocked</p>
        <p class="stat-value" id="doctor-blocked-count">0</p>
      </div>


      <div class="stat-card"
          data-page="blockedUsers"
          data-action="blocked-filter"
          data-filter="patient">

        <div class="stat-card-top">
          <div class="stat-icon stat-icon-tertiary">
            <span class="material-symbols-outlined text-tertiary">
              groups
            </span>
          </div>

          <span class="stat-badge neutral" id="patient-blocked-trend">
            88% of total
          </span>
        </div>

        <p class="stat-label">Patients Blocked</p>
        <p class="stat-value" id="patient-blocked-count">0</p>
      </div>


      <div class="stat-card"
          data-page="blockedUsers"
          data-action="blocked-filter"
          data-filter="appeal">

        <div class="stat-card-top">
          <div class="stat-icon stat-icon-success">
            <span class="material-symbols-outlined text-success-green">
              verified
            </span>
          </div>

          <span class="stat-badge down" id="blocked-appeal-trend">
            0%
            <span class="material-symbols-outlined" style="font-size:12px;">
              trending_down
            </span>
          </span>
        </div>

        <p class="stat-label">Appeals Pending</p>
        <p class="stat-value" id="blocked-appeal-count">0</p>
      </div>

    </div>

    <!-- Blocked Users Registry Table -->
    <div class="panel">
      <div class="panel-header">
        <h3>Registry</h3>
        <span class="panel-header-meta" id="blockedUsers-pagination-header"></span>
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>User Name</th>
              <th>Role</th>
              <th>Reason for Block</th>
              <th>Date Blocked</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="blocked-users-table-body">
            <!-- Row 1 -->
            
          </tbody>
        </table>
      </div>
      <div class="pagination" id="blockedUsers-pagination">
        <button class="btn-prev-next disabled" disabled>Previous</button>
        <div class="pagination-pages">
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
          <span class="page-ellipsis">...</span>
          <button class="page-btn">13</button>
        </div>
        <button class="btn-prev-next">Next</button>
      </div>
    </div>

    <!-- Bottom Grid: Audit Log + Policy Card -->
    <div class="bottom-grid">

      <!-- Restriction Audit Log -->
      <div class="audit-panel">
        <h3>Restriction Audit Log</h3>
        <div class="timeline" id="restriction-audit-timeline">
          <!-- Dynamic Audit Logs loaded here -->
        </div>
      </div>

      <!-- Policy Quick-view -->
      <div class="policy-card">
        <div class="policy-card-content">
          <h3>Restriction Policies</h3>
          <p>Review the standard operating procedures for account restrictions and unblocking requests.</p>
          <ul class="policy-list" id="restriction-policy-list">
            <!-- Dynamic Restriction Policies loaded here -->
          </ul>
          <button class="policy-card-btn" type="button">View Full Handbook</button>
        </div>
        <div class="policy-card-deco"></div>
      </div>

    </div>
    <!-- End bottom-grid -->

  </div>
