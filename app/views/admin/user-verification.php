
  <div class="page-body">

    <div class="page-header">
      <div>
        <h2>User Verification</h2>
        <p>Review and approve identity and credential verification requests from doctors and patients.</p>
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
      <div class="stat-card active"
      data-page="userVerification"
       data-action="verification-filter" 
       data-filter="pending">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-primary">
            <span class="material-symbols-outlined text-primary">pending_actions</span>
          </div>
          <span class="stat-badge up" id="total-pending-review-trend">+0 today</span>
        </div>
        <p class="stat-label">Pending Review</p>
        <p class="stat-value" id="total-pending-review-count">0</p>
      </div>
      <div class="stat-card" data-page="userVerification" data-action="verification-filter" data-filter="verified">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-success">
            <span class="material-symbols-outlined text-success-green">verified</span>
          </div>
          <span class="stat-badge down">This week</span>
        </div>
        <p class="stat-label">Approved</p>
        <p class="stat-value" id="approved-review-count">0</p>
      </div>
      <div class="stat-card" data-page="userVerification" data-action="verification-filter" data-filter="rejected">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-error">
            <span class="material-symbols-outlined text-error-red">cancel</span>
          </div>
          <span class="stat-badge neutral">This week</span>
        </div>
        <p class="stat-label">Rejected</p>
        <p class="stat-value" id="rejected-review-count">0</p>
      </div>
      <div class="stat-card">
        <div class="stat-card-top">
          <div class="stat-icon stat-icon-secondary">
            <span class="material-symbols-outlined" style="color:var(--secondary);">hourglass_top</span>
          </div>
          <span class="stat-badge neutral">Avg.</span>
        </div>
        <p class="stat-label">Review Time</p>
        <p class="stat-value" id="avg-review-time">0 h</p>
      </div>
    </div>

    <!-- Verification Table -->
    <div class="panel">
      <div class="panel-header">
        <h3>Pending Verifications</h3>
        <span class="panel-header-meta" id="userVerification-pagination-header"></span>
      </div>
      <div class="data-table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Applicant</th>
              <th>Document Submitted</th>
              <th>Submitted On</th>
              <th>Department</th>
              <th>specialization</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody id="user-verification-table-body">
            
          </tbody>
        </table>
      </div>
      <div class="pagination" id="userVerification-pagination">
        <button class="btn-prev-next disabled" disabled>Previous</button>
        <div class="pagination-pages">
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">3</button>
        </div>
        <button class="btn-prev-next">Next</button>
      </div>
    </div>

  </div>
