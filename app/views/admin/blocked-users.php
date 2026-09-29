
<!-- ── Main Content ── -->
  <div class="page-body">

    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h2>Blocked Users</h2>
        <p>Review and manage accounts restricted for policy violations or security concerns.</p>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-outline" data-action="filter">
          <span class="material-symbols-outlined">filter_list</span>
          Filter
        </button>
        <button class="btn btn-outline" data-action="export">
          <span class="material-symbols-outlined">download</span>
          Export Log
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
        <div class="timeline">
          <div class="timeline-item">
            <div class="timeline-indicator">
              <div class="timeline-dot error"></div>
              <div class="timeline-line"></div>
            </div>
            <div>
              <p class="timeline-title">Benjamin Wu (PAT-90082) was blocked</p>
              <p class="timeline-body">Reason: Documented verbal harassment toward clinic staff during checkout. Action taken by Admin Rivera.</p>
              <p class="timeline-time">2 hours ago</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator">
              <div class="timeline-dot success"></div>
              <div class="timeline-line"></div>
            </div>
            <div>
              <p class="timeline-title">Elena Rodriguez (PAT-4402) was unblocked</p>
              <p class="timeline-body">Appeal approved after verification of legitimate travel login from IP: 192.168.1.1.</p>
              <p class="timeline-time">Yesterday, 4:32 PM</p>
            </div>
          </div>
          <div class="timeline-item">
            <div class="timeline-indicator">
              <div class="timeline-dot error"></div>
            </div>
            <div>
              <p class="timeline-title">Dr. Linda Aris (DOC-44122) was blocked</p>
              <p class="timeline-body">System flagged 5 consecutive failed MFA attempts from an unrecognized location (Varna, BG).</p>
              <p class="timeline-time">Oct 18, 2023</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Policy Quick-view -->
      <div class="policy-card">
        <div class="policy-card-content">
          <h3>Restriction Policies</h3>
          <p>Review the standard operating procedures for account restrictions and unblocking requests.</p>
          <ul class="policy-list">
            <li>
              <span class="material-symbols-outlined">security</span>
              <span>Security blocks require 2FA re-verification.</span>
            </li>
            <li>
              <span class="material-symbols-outlined">gavel</span>
              <span>Policy violations require manager sign-off for unblocking.</span>
            </li>
            <li>
              <span class="material-symbols-outlined">history</span>
              <span>Audit logs are retained for 7 years as per HIPAA.</span>
            </li>
          </ul>
          <button class="policy-card-btn">View Full Handbook</button>
        </div>
        <div class="policy-card-deco"></div>
      </div>

    </div>
    <!-- End bottom-grid -->

  </div>
