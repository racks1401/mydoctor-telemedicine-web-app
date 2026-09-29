
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h2>Settings</h2>
                    <p>Manage your account preferences and privacy settings.</p>
                </div>
            </div>

            <!-- Settings Layout -->
            <div class="settings-layout">
                <!-- Settings Sidebar Menu -->
                <div class="settings-sidebar">
                    <ul class="settings-menu">
                        <li class="settings-menu-item active">
                            <span class="material-symbols-outlined">person</span>
                            Profile
                        </li>
                        <li class="settings-menu-item">
                            <span class="material-symbols-outlined">lock</span>
                            Security
                        </li>
                        <li class="settings-menu-item">
                            <span class="material-symbols-outlined">notifications</span>
                            Notifications
                        </li>
                        <li class="settings-menu-item">
                            <span class="material-symbols-outlined">privacy_tip</span>
                            Privacy
                        </li>
                    </ul>
                </div>

                <!-- Settings Content -->
                <div class="settings-content">
                    <!-- Profile Section -->
                    <div class="settings-section">
                        <div class="settings-section-header">
                            <h3 class="settings-section-title">Profile Information</h3>
                            <p class="settings-section-desc">Update your personal details and contact information</p>
                        </div>
                        <div class="settings-section-body">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input class="form-input" type="text" value="Alexander Root">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input class="form-input" type="email" value="alexander.root@email.com">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone Number</label>
                                <input class="form-input" type="tel" value="+1 (555) 123-4567">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input class="form-input" type="date" value="1990-05-15">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Blood Type</label>
                                <select class="form-select">
                                    <option>O+</option>
                                    <option>A+</option>
                                    <option>B+</option>
                                    <option>AB+</option>
                                </select>
                            </div>
                            <div class="button-group">
                                <button class="btn btn-primary">Save Changes</button>
                                <button class="btn btn-outline">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Section -->
                    <div class="settings-section">
                        <div class="settings-section-header">
                            <h3 class="settings-section-title">Notification Preferences</h3>
                            <p class="settings-section-desc">Choose how you want to be notified</p>
                        </div>
                        <div class="settings-section-body">
                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Appointment Reminders</div>
                                    <div class="settings-item-desc">Get notified about upcoming appointments</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Message Notifications</div>
                                    <div class="settings-item-desc">Get notified when doctors send messages</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Health Alerts</div>
                                    <div class="settings-item-desc">Get alerts for critical health metrics</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Newsletter</div>
                                    <div class="settings-item-desc">Receive weekly health tips and updates</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Section -->
                    <div class="settings-section">
                        <div class="settings-section-header">
                            <h3 class="settings-section-title">Privacy & Security</h3>
                            <p class="settings-section-desc">Manage your data and account security</p>
                        </div>
                        <div class="settings-section-body">
                            <div class="form-group">
                                <label class="form-label">Two-Factor Authentication</label>
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <span style="font-size: 13px; color: var(--on-surface-variant);">Not enabled</span>
                                    <button class="btn btn-outline" style="padding: 8px 16px; font-size: 12px;">Enable</button>
                                </div>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Share Health Data</div>
                                    <div class="settings-item-desc">Allow doctors to access your health records</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Activity Logs</div>
                                    <div class="settings-item-desc">View your account activity history</div>
                                </div>
                                <button class="btn btn-outline" style="padding: 8px 16px; font-size: 12px;">View Logs</button>
                            </div>
                        </div>
                    </div>

                    <!-- Danger Zone -->
                    <div class="danger-zone">
                        <div class="danger-title">Danger Zone</div>
                        <div class="danger-desc">These actions are permanent and cannot be undone.</div>
                        <button class="danger-btn">Delete Account</button>
                    </div>
                </div>
            </div>
        </div>
