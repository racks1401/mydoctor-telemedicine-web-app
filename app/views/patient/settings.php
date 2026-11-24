<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h2>Settings</h2>
            <p>Manage your personal profile, security credentials, notifications, and privacy preferences.</p>
        </div>
    </div>

    <!-- Settings Layout -->
    <div class="settings-layout">
        <!-- Settings Sidebar Menu -->
        <div class="settings-sidebar">
            <ul class="settings-menu" id="settings-nav-menu">
                <li class="settings-menu-item active" data-tab="profile">
                    <span class="material-symbols-outlined">person</span>
                    Profile
                </li>
                <li class="settings-menu-item" data-tab="security">
                    <span class="material-symbols-outlined">lock</span>
                    Security
                </li>
                <li class="settings-menu-item" data-tab="notifications">
                    <span class="material-symbols-outlined">notifications</span>
                    Notifications
                </li>
                <li class="settings-menu-item" data-tab="privacy">
                    <span class="material-symbols-outlined">privacy_tip</span>
                    Privacy
                </li>
            </ul>
        </div>

        <!-- Settings Content -->
        <div class="settings-content">
            <!-- 1. PROFILE SECTION -->
            <div class="settings-tab-pane" id="pane-profile">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Profile Information</h3>
                        <p class="settings-section-desc">Update your personal details and contact information</p>
                    </div>
                    <div class="settings-section-body">
                        <form id="settings-profile-form">
                            <div class="form-group">
                                <label class="form-label" for="setting-name">Full Name</label>
                                <input class="form-input" type="text" id="setting-name" name="name" placeholder="Enter your full name" required>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="setting-email">Email Address</label>
                                <input class="form-input" type="email" id="setting-email" name="email" placeholder="Your registered email address" readonly style="opacity: 0.8; cursor: not-allowed; background-color: var(--surface-container);" title="Email cannot be changed directly for security reasons">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="setting-phone">Phone Number</label>
                                    <input class="form-input" type="tel" id="setting-phone" name="phone" placeholder="+91 XXXXX XXXXX">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="setting-dob">Date of Birth</label>
                                    <input class="form-input" type="date" id="setting-dob" name="dob">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="setting-gender">Gender</label>
                                    <select class="form-select" id="setting-gender" name="gender">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="setting-blood-type">Blood Type</label>
                                    <select class="form-select" id="setting-blood-type" name="blood_type">
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="setting-address">Street Address</label>
                                <input class="form-input" type="text" id="setting-address" name="address" placeholder="e.g. 123 Health Ave, Apt 4B">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="setting-city">City</label>
                                    <input class="form-input" type="text" id="setting-city" name="city" placeholder="City">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="setting-state">State</label>
                                    <input class="form-input" type="text" id="setting-state" name="state" placeholder="State">
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="setting-pin">PIN Code</label>
                                    <input class="form-input" type="text" id="setting-pin" name="pin" placeholder="PIN Code">
                                </div>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="btn btn-primary" id="btn-save-profile">Save Changes</button>
                                <button type="button" class="btn btn-outline" id="btn-cancel-profile">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 2. SECURITY SECTION -->
            <div class="settings-tab-pane" id="pane-security" style="display: none;">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Change Password</h3>
                        <p class="settings-section-desc">Ensure your account uses a secure password to protect your medical records</p>
                    </div>
                    <div class="settings-section-body">
                        <form id="settings-password-form">
                            <div class="form-group">
                                <label class="form-label" for="setting-current-password">Current Password</label>
                                <input class="form-input" type="password" id="setting-current-password" placeholder="Enter your current password" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="setting-new-password">New Password</label>
                                <input class="form-input" type="password" id="setting-new-password" placeholder="Enter new password (min. 6 characters)" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="setting-confirm-password">Confirm New Password</label>
                                <input class="form-input" type="password" id="setting-confirm-password" placeholder="Confirm your new password" required>
                            </div>
                            <div class="button-group">
                                <button type="submit" class="btn btn-primary" id="btn-save-password">Update Password</button>
                                <button type="button" class="btn btn-outline" id="btn-cancel-password">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="settings-section" style="margin-top: 24px;">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Two-Factor Authentication (2FA)</h3>
                        <p class="settings-section-desc">Protect your health information by requiring an extra verification step</p>
                    </div>
                    <div class="settings-section-body">
                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Two-Factor Authentication</div>
                                <div class="settings-item-desc" id="setting-2fa-desc">Require verification when logging in from new devices</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-toggle-2fa">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="settings-section" style="margin-top: 24px;">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Recent Session Details</h3>
                        <p class="settings-section-desc">Account access history for security audit</p>
                    </div>
                    <div class="settings-section-body">
                        <div style="font-size: 13px; color: var(--on-surface-variant); display: flex; flex-direction: column; gap: 10px;">
                            <div><strong>Last Login Time:</strong> <span id="setting-last-login-at" style="color: var(--on-surface); font-weight: 500;">--</span></div>
                            <div><strong>Last Login IP Address:</strong> <span id="setting-last-login-ip" style="color: var(--on-surface); font-weight: 500;">--</span></div>
                            <div><strong>Account Created:</strong> <span id="setting-created-at" style="color: var(--on-surface); font-weight: 500;">--</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. NOTIFICATIONS SECTION -->
            <div class="settings-tab-pane" id="pane-notifications" style="display: none;">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Notification Preferences</h3>
                        <p class="settings-section-desc">Choose how and when you receive reminders, reports, and health alerts</p>
                    </div>
                    <div class="settings-section-body">
                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Appointment Reminders</div>
                                <div class="settings-item-desc">Get notified about upcoming appointments, delays, and scheduling confirmations</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-notif-appointments" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Doctor Message Notifications</div>
                                <div class="settings-item-desc">Get notified when a doctor replies to your consultation messages</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-notif-messages" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Critical Health Alerts</div>
                                <div class="settings-item-desc">Get immediate notifications for uploaded lab reports and prescriptions</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-notif-alerts" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Email Notifications</div>
                                <div class="settings-item-desc">Receive receipts, detailed reports, and summaries via email</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-notif-email" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">SMS Notifications</div>
                                <div class="settings-item-desc">Receive instant reminders via SMS on your registered mobile number</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-notif-sms">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Health Newsletter & Tips</div>
                                <div class="settings-item-desc">Receive weekly health advice, nutrition tips, and wellness newsletters</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-notif-newsletter">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="button-group">
                            <button type="button" class="btn btn-primary" id="btn-save-notifications">Save Notification Settings</button>
                            <button type="button" class="btn btn-outline" id="btn-cancel-notifications">Reset to Defaults</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. PRIVACY & DANGER ZONE SECTION -->
            <div class="settings-tab-pane" id="pane-privacy" style="display: none;">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Privacy Controls</h3>
                        <p class="settings-section-desc">Control your data accessibility and clinical record sharing</p>
                    </div>
                    <div class="settings-section-body">
                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Share Health Records with Assigned Doctors</div>
                                <div class="settings-item-desc">Allow doctors whom you book appointments with to view your medical history</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="setting-privacy-share" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Account Activity & Login History</div>
                                <div class="settings-item-desc">Check your recent login times, IP addresses, and active sessions</div>
                            </div>
                            <button class="btn btn-outline" type="button" id="btn-view-activity-logs" style="padding: 8px 16px; font-size: 12px;">View Activity</button>
                        </div>

                        <div class="button-group">
                            <button type="button" class="btn btn-primary" id="btn-save-privacy">Save Privacy Settings</button>
                            <button type="button" class="btn btn-outline" id="btn-cancel-privacy">Reset</button>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="danger-zone" style="margin-top: 24px;">
                    <div class="danger-title">Danger Zone</div>
                    <div class="danger-desc">Once you delete your account, there is no going back. All your appointments, consultations, and personal information will be permanently removed from our servers.</div>
                    <button type="button" class="danger-btn" id="btn-open-delete-modal">Delete Account</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div class="modal-overlay" id="delete-account-modal">
    <div class="modal-content" style="max-width: 440px; text-align: center;">
        <div class="modal-icon-danger">
            <span class="material-symbols-outlined">warning</span>
        </div>
        <h3 class="modal-title" style="margin-bottom: 8px;">Delete Account?</h3>
        <p class="modal-desc" style="font-size: 14px; color: var(--on-surface-variant); line-height: 1.5; margin-bottom: 24px;">
            Are you sure you want to delete your account? All your medical records, appointments, and personal details will be permanently removed from the database. This action cannot be undone.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="btn-cancel-delete" style="padding: 10px 18px;">No, Keep Account</button>
            <button type="button" class="danger-btn" id="btn-confirm-delete" style="padding: 10px 20px;">Yes, Delete Account</button>
        </div>
    </div>
</div>

<!-- Toast Feedback Notification -->
<div class="settings-toast" id="settings-toast">
    <span class="material-symbols-outlined" id="settings-toast-icon">check_circle</span>
    <span id="settings-toast-message">Settings saved successfully</span>
</div>
