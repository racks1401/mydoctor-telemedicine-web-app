
  <div class="page-body">

    <div class="page-header">
      <div>
        <h2>My Profile</h2>
        <p>Manage your account information and security settings.</p>
      </div>
      <button class="edit-btn">
        <span class="material-symbols-outlined">edit</span>
        Edit Profile
      </button>
    </div>

    <div class="grid-top">

      <div class="card profile-card">
        <div class="profile-pic-wrap">
          <div class="profile-pic">
            <span class="material-symbols-outlined">person</span>
          </div>
          <div class="camera-badge">
            <span class="material-symbols-outlined">photo_camera</span>
          </div>
        </div>
        <h3 class="profile-name"></h3>
        <div class="role-label">Administrator</div>
        <span class="status-pill profile-status"></span>

        <div class="profile-meta">
          <div class="meta-row">
            <span class="material-symbols-outlined">calendar_month</span>
            <div>
              <div id="profile-member-since" class="meta-label">Member Since</div>
              <div class="meta-value profile-joined"></div>
            </div>
          </div>
          <div class="meta-row">
            <span class="material-symbols-outlined">schedule</span>
            <div>
              <div class="meta-label">Last Login</div>
              <div class="meta-value profile-last-login-value"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Personal information -->
      <div class="card">
        <div class="card-title">
          <div class="card-icon">
            <span class="material-symbols-outlined">person</span>
          </div>
          Personal Information
        </div>

        <div class="info-row"><span class="label">Full Name</span><span class="value profile-name"></span></div>
        <div class="info-row"><span class="label">Email Address</span><span id="profile-email" class="value"></span></div>
        <div class="info-row"><span class="label">Phone Number</span><span id="profile-phone" class="value"></span></div>
        <div class="info-row"><span class="label">Role</span><span id="profile-role" class="value profile-role"></span></div>
        <div class="info-row"><span class="label">Employee ID</span><span id="profile-id" class="value profile-id"></span></div>
        <div class="info-row"><span class="label">Joined Date</span><span id="profile-joined" class="value profile-joined"></span></div>
      </div>

      <!-- Change password -->
      <div class="card">
        <div class="card-title">
          <div class="card-icon">
            <span class="material-symbols-outlined">lock</span>
          </div>
          Change Password
        </div>

        <form id="passwordForm" data-form="change-password">
          <div class="field">
            <label for="currentPassword">Current Password</label>
            <div class="field-input-wrap">
              <input type="password" id="currentPassword" name="currentPassword" placeholder="enter your current password">
              <span class="material-symbols-outlined eye-toggle" data-page="profile" data-action="togglePassword" data-target="currentPassword">visibility</span>
            </div>
          </div>
          <div class="field">
            <label for="newPassword">New Password</label>
            <div class="field-input-wrap">
              <input type="password" id="newPassword" name="newPassword" placeholder="enter your new password">
              <span class="material-symbols-outlined eye-toggle" data-page="profile" data-action="togglePassword" data-target="newPassword">visibility</span>
            </div>
          </div>
          <div class="field">
            <label for="confirmPassword">Confirm New Password</label>
            <div class="field-input-wrap">
              <input type="password" id="confirmPassword" name="confirmPassword" placeholder="confirm your new password">
              <span class="material-symbols-outlined eye-toggle" data-page="profile" data-action="togglePassword" data-target="confirmPassword">visibility</span>
            </div>
          </div>

          <button type="submit" class="update-btn">Update Password</button>
        </form>
      </div>
    </div>

    <div class="grid-bottom">

      <!-- Preferences -->
      <div class="card">
        <div class="card-title">
          <div class="card-icon">
            <span class="material-symbols-outlined">tune</span>
          </div>
          Preferences
        </div>


        <div class="theme-toggle">
          <span class="label">Theme</span>
          <div class="theme-buttons" id="themeButtons">
            <button type="button" data-value="light" data-page="profile" data-action="handleThemePreference">
              <span class="material-symbols-outlined">light_mode</span>
              Light
            </button>
            <button type="button" data-value="dark" data-page="profile" data-action="handleThemePreference" class="active">
              <span class="material-symbols-outlined">dark_mode</span>
              Dark
            </button>
          </div>
        </div>

        <div class="pref-row">
          <div>
            <div class="pref-title">Email Notifications</div>
            <div class="pref-desc">Receive important updates and alerts via email.</div>
          </div>
          <label class="switch">
            <input type="checkbox" data-setting="email_notifications" id="emailNotif">
            <span class="slider"></span>
          </label>
        </div>

        <div class="pref-row">
          <div>
            <div class="pref-title">SMS Notifications</div>
            <div class="pref-desc">Receive important alerts via SMS.</div>
          </div>
          <label class="switch">
            <input type="checkbox" data-setting="sms_notifications" id="smsNotif">
            <span class="slider"></span>
          </label>
        </div>
      </div>

      <!-- Account information -->
      <div class="card">
        <div class="card-title">
          <div class="card-icon">
            <span class="material-symbols-outlined">info</span>
          </div>
          Account Information
        </div>

        <div class="info-row with-icon">
          <span class="label"><span class="material-symbols-outlined">person</span>User ID</span>
          <span class="value profile-id"></span>
        </div>
        <div class="info-row with-icon">
          <span class="label"><span class="material-symbols-outlined">shield_person</span>Role</span>
          <span class="value profile-role"></span>
        </div>
        <div class="info-row with-icon">
          <span class="label"><span class="material-symbols-outlined">schedule</span>Last Login</span>
          <span class="value profile-last-login-value"></span>
        </div>
        <div class="info-row with-icon">
          <span class="label"><span class="material-symbols-outlined">computer</span>Last Login IP</span>
          <span class="value profile-last-login-ip"></span>
        </div>
        <div class="info-row with-icon">
          <span class="label"><span class="material-symbols-outlined">check_circle</span>Account Status</span>
          <span class="badge-active profile-status"></span>
        </div>
      </div>

    </div>
  </div>