<div class="container">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 24px;">
        <div class="page-title">
            <h2 style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Settings</h2>
            <p style="font-size: 14px; color: var(--on-surface-variant); margin: 0;">Manage your personal profile, clinic availability, security credentials, and preferences.</p>
        </div>
    </div>

    <!-- Settings Layout -->
    <div class="settings-layout">
        <!-- Settings Sidebar Menu -->
        <div class="settings-sidebar">
            <ul class="settings-menu" id="doc-settings-nav-menu">
                <li class="settings-menu-item active" data-tab="profile">
                    <span class="material-symbols-outlined">person</span>
                    Profile
                </li>
                <li class="settings-menu-item" data-tab="availability">
                    <span class="material-symbols-outlined">schedule</span>
                    Availability
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

        <!-- Settings Content Panes -->
        <div class="settings-content">
            <!-- 1. PROFILE SECTION -->
            <div class="settings-tab-pane active" id="doc-pane-profile">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Profile Information</h3>
                        <p class="settings-section-desc">Update your personal details, credentials, and clinic information</p>
                    </div>
                    <div class="settings-section-body">
                        <!-- Profile Avatar Section -->
                        <div class="profile-section" style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-subtle);">
                            <div id="doc-profile-avatar-container" style="width: 76px; height: 76px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; overflow: hidden; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                <img id="doc-profile-image" alt="Profile Picture" class="profile-image" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;" />
                                <span id="doc-profile-initials">DR</span>
                            </div>
                            <div class="profile-actions">
                                <input type="file" id="doc-photo-input" accept="image/*" style="display: none;" />
                                <button type="button" class="btn btn-outline" id="doc-btn-change-photo" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
                                    <span class="material-symbols-outlined" style="font-size: 18px;">photo_camera</span>
                                    Change Picture
                                </button>
                                <p style="font-size: 12px; color: var(--on-surface-variant); margin-top: 6px;">JPG, PNG, GIF or WebP. Max size of 2MB.</p>
                            </div>
                        </div>

                        <!-- Profile Form -->
                        <form id="doc-profile-form">
                            <div class="form-group">
                                <label class="form-label" for="doc-name">Full Name</label>
                                <input class="form-input" type="text" id="doc-name" name="name" placeholder="Dr. Full Name" required />
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="doc-email">Email Address</label>
                                <input class="form-input" type="email" id="doc-email" name="email" placeholder="Your registered email" readonly style="opacity: 0.8; cursor: not-allowed; background-color: var(--surface-container-low);" title="Email cannot be changed directly" />
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="doc-phone">Phone Number</label>
                                    <input class="form-input" type="tel" id="doc-phone" name="phone" placeholder="+91 XXXXX XXXXX" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doc-dob">Date of Birth</label>
                                    <input class="form-input" type="date" id="doc-dob" name="dob" />
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="doc-gender">Gender</label>
                                    <select class="form-select" id="doc-gender" name="gender">
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doc-specialization">Medical Specialization</label>
                                    <input class="form-input" type="text" id="doc-specialization" name="specialization" placeholder="e.g. Senior Cardiologist, Neurologist" />
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="doc-reg-no">Medical Registration Number</label>
                                    <input class="form-input" type="text" id="doc-reg-no" name="medical_reg_no" placeholder="e.g. MCI-54891" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doc-qualification">Highest Qualification</label>
                                    <input class="form-input" type="text" id="doc-qualification" name="qualification" placeholder="e.g. MBBS, MD - Internal Medicine" />
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="doc-experience">Experience (Years)</label>
                                    <input class="form-input" type="number" min="0" max="60" id="doc-experience" name="experience" placeholder="e.g. 10" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doc-address">Clinic / Practice Address</label>
                                    <input class="form-input" type="text" id="doc-address" name="address" placeholder="e.g. Suite 302, MediCentral Hospital, Park Avenue" />
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                                <div class="form-group">
                                    <label class="form-label" for="doc-city">City</label>
                                    <input class="form-input" type="text" id="doc-city" name="city" placeholder="City" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doc-state">State</label>
                                    <input class="form-input" type="text" id="doc-state" name="state" placeholder="State" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="doc-pin">PIN Code</label>
                                    <input class="form-input" type="text" id="doc-pin" name="pin" placeholder="PIN Code" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="doc-about">About Doctor / Biography</label>
                                <textarea class="form-input" id="doc-about" name="about" rows="3" placeholder="Brief summary of your clinical background and patient care philosophy..." style="resize: vertical;"></textarea>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="btn btn-primary" id="btn-save-doc-profile">Save Changes</button>
                                <button type="button" class="btn btn-outline" id="btn-cancel-doc-profile">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- 2. AVAILABILITY SECTION -->
            <div class="settings-tab-pane" id="doc-pane-availability" style="display: none;">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Clinic Availability &amp; Schedule</h3>
                        <p class="settings-section-desc">Configure your working shift, appointment duration, and active booking status</p>
                    </div>
                    <div class="settings-section-body">
                        <form id="doc-avail-form">
                            <!-- Availability Toggle -->
                            <div class="settings-item">
                                <div class="settings-item-label">
                                    <div class="settings-item-title">Clinic Availability Status</div>
                                    <div class="settings-item-desc" id="doc-avail-status-desc">Active - Patients can view and book appointments with you</div>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" id="doc-availability-toggle" checked />
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>

                            <!-- Default Slot Duration -->
                            <div class="form-group" style="margin-top: 20px;">
                                <label class="form-label" for="doc-slot-duration">Default Consultation Duration</label>
                                <select class="form-select" id="doc-slot-duration" name="slot_duration" style="max-width: 240px;">
                                    <option value="15">15 Minutes</option>
                                    <option value="30" selected>30 Minutes</option>
                                    <option value="45">45 Minutes</option>
                                    <option value="60">60 Minutes</option>
                                </select>
                            </div>

                            <!-- Available Consultation Modes Section -->
                            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-subtle);">
                                <label class="form-label" style="font-size: 15px; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Available Consultation Modes</label>
                                <p style="font-size: 13px; color: var(--on-surface-variant); margin-bottom: 14px;">Select the consultation modes you offer to patients</p>
                                
                                <div style="display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 16px;" id="doc-avail-modes-container">
                                    <label class="doc-mode-option-label" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s ease;">
                                        <input type="checkbox" name="doc_available_mode" value="video_call" class="doc-mode-checkbox" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--trust-blue);" checked />
                                        <span class="material-symbols-outlined" style="font-size: 20px; color: var(--trust-blue);">videocam</span>
                                        <span>Video Call</span>
                                    </label>
                                    <label class="doc-mode-option-label" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s ease;">
                                        <input type="checkbox" name="doc_available_mode" value="chat" class="doc-mode-checkbox" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--trust-blue);" checked />
                                        <span class="material-symbols-outlined" style="font-size: 20px; color: var(--trust-blue);">chat</span>
                                        <span>Chat</span>
                                    </label>
                                    <label class="doc-mode-option-label" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s ease;">
                                        <input type="checkbox" name="doc_available_mode" value="offline" class="doc-mode-checkbox" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--trust-blue);" checked />
                                        <span class="material-symbols-outlined" style="font-size: 20px; color: var(--trust-blue);">apartment</span>
                                        <span>Offline (In-Person Clinic)</span>
                                    </label>
                                    <label class="doc-mode-option-label" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.2s ease;">
                                        <input type="checkbox" name="doc_available_mode" value="audio_call" class="doc-mode-checkbox" style="width: 16px; height: 16px; cursor: pointer; accent-color: var(--trust-blue);" checked />
                                        <span class="material-symbols-outlined" style="font-size: 20px; color: var(--trust-blue);">call</span>
                                        <span>Audio Call</span>
                                    </label>
                                </div>

                                <div style="display: flex; gap: 12px;">
                                    <button type="button" class="btn btn-primary" id="btn-save-doc-modes" style="padding: 8px 18px; font-size: 13px;">Save Modes</button>
                                </div>
                            </div>

                            <!-- Working Hours Schedule Header & Interactive Component -->
                            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-subtle);">
                                <h4 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0 0 4px 0;">Working hours</h4>
                                <p style="font-size: 13px; color: var(--on-surface-variant); margin: 0 0 16px 0;">Select when you are typically available to work</p>

                                <!-- Weekly Schedule Container -->
                                <div id="doc-schedule-container" class="sched-container"></div>
                            </div>

                            <div class="button-group">
                                <button type="submit" class="btn btn-primary" id="btn-save-doc-avail">Save Schedule</button>
                                <button type="button" class="btn btn-outline" id="btn-cancel-doc-avail">Reset</button>
                            </div>
                        </form>

                        <!-- Leave and Schedule Exceptions Section -->
                        <div style="margin-top: 36px; padding-top: 24px; border-top: 1px solid var(--border-subtle);">
                            <div style="margin-bottom: 16px;">
                                <h4 style="font-size: 16px; font-weight: 700; color: var(--text-main); margin: 0 0 4px 0;">Leave &amp; Schedule Exceptions</h4>
                                <p style="font-size: 13px; color: var(--on-surface-variant); margin: 0;">Set specific date overrides, holidays, or custom working hours for upcoming days</p>
                            </div>

                            <!-- Add New Exception Card -->
                            <div class="card" style="padding: 20px; background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 12px; margin-bottom: 24px;">
                                <h5 style="font-size: 14px; font-weight: 600; margin: 0 0 16px 0; color: var(--text-main);">Add Leave or Custom Hours</h5>
                                
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                                    <div class="form-group" style="margin: 0;">
                                        <label class="form-label" for="doc-exception-date" style="font-weight: 600;">Select Date (Next 60 Days)</label>
                                        <input type="date" id="doc-exception-date" class="form-input" />
                                    </div>
                                    <div class="form-group" style="margin: 0;">
                                        <label class="form-label" style="font-weight: 600;">Holiday / Exception Type</label>
                                        <div style="display: flex; gap: 16px; height: 42px; align-items: center;">
                                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                                <input type="radio" name="exception_type" value="full" checked id="radio-exc-full" />
                                                Full Holiday (Day Off)
                                            </label>
                                            <label style="display: flex; align-items: center; gap: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                                <input type="radio" name="exception_type" value="half" id="radio-exc-half" />
                                                Half Holiday / Custom Slots
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reason input -->
                                <div class="form-group" style="margin-bottom: 16px;">
                                    <label class="form-label" for="doc-exception-reason" style="font-weight: 600;">Reason / Note (Optional)</label>
                                    <input type="text" id="doc-exception-reason" class="form-input" placeholder="e.g. Personal Leave, Medical Conference, Morning Clinic Only" />
                                </div>

                                <!-- Half Holiday Custom Slots Builder (hidden by default) -->
                                <div id="doc-exc-slots-wrapper" style="display: none; padding: 16px; background: #ffffff; border: 1px solid var(--border-subtle); border-radius: 8px; margin-bottom: 16px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                        <span style="font-size: 13px; font-weight: 600; color: var(--text-main);">Custom Working Hours for this date</span>
                                        <button type="button" class="btn btn-outline" id="btn-add-exc-slot" style="padding: 4px 10px; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">add</span> Add Slot
                                        </button>
                                    </div>
                                    <div id="doc-exc-slots-list" style="display: flex; flex-direction: column; gap: 10px;"></div>
                                </div>

                                <div style="display: flex; gap: 12px;">
                                    <button type="button" class="btn btn-primary" id="btn-save-exception">Save Exception</button>
                                </div>
                            </div>

                            <!-- Active Exceptions List -->
                            <div>
                                <h5 style="font-size: 14px; font-weight: 600; margin: 0 0 12px 0; color: var(--text-main);">Upcoming Leaves &amp; Schedule Exceptions</h5>
                                <div id="doc-exceptions-list" style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="color: var(--on-surface-variant); font-size: 13px; padding: 12px; background: var(--surface-container-low); border-radius: 8px;">Loading exceptions...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. SECURITY SECTION -->
            <div class="settings-tab-pane" id="doc-pane-security" style="display: none;">
                <!-- Password Change -->
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Change Password</h3>
                        <p class="settings-section-desc">Ensure your doctor account uses a secure password to protect patient confidentiality</p>
                    </div>
                    <div class="settings-section-body">
                        <form id="doc-password-form">
                            <div class="form-group">
                                <label class="form-label" for="doc-current-password">Current Password</label>
                                <input class="form-input" type="password" id="doc-current-password" placeholder="Enter your current password" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="doc-new-password">New Password</label>
                                <input class="form-input" type="password" id="doc-new-password" placeholder="Enter new password (min. 6 characters)" required />
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="doc-confirm-password">Confirm New Password</label>
                                <input class="form-input" type="password" id="doc-confirm-password" placeholder="Confirm your new password" required />
                            </div>
                            <div class="button-group">
                                <button type="submit" class="btn btn-primary" id="btn-save-doc-password">Update Password</button>
                                <button type="button" class="btn btn-outline" id="btn-cancel-doc-password">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- 2FA Section -->
                <div class="settings-section" style="margin-top: 24px;">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Two-Factor Authentication (2FA)</h3>
                        <p class="settings-section-desc">Protect access to your clinical dashboard and medical records</p>
                    </div>
                    <div class="settings-section-body">
                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Two-Factor Authentication</div>
                                <div class="settings-item-desc" id="doc-2fa-desc">Require OTP verification when logging in from new devices</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-toggle-2fa" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Session Audit Details -->
                <div class="settings-section" style="margin-top: 24px;">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Recent Session Details</h3>
                        <p class="settings-section-desc">Doctor account security audit logs</p>
                    </div>
                    <div class="settings-section-body">
                        <div style="font-size: 13px; color: var(--on-surface-variant); display: flex; flex-direction: column; gap: 10px;">
                            <div><strong>Last Login Time:</strong> <span id="doc-last-login-at" style="color: var(--on-surface); font-weight: 500;">--</span></div>
                            <div><strong>Last Login IP Address:</strong> <span id="doc-last-login-ip" style="color: var(--on-surface); font-weight: 500;">--</span></div>
                            <div><strong>Account Created:</strong> <span id="doc-created-at" style="color: var(--on-surface); font-weight: 500;">--</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. NOTIFICATIONS SECTION -->
            <div class="settings-tab-pane" id="doc-pane-notifications" style="display: none;">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Notification Preferences</h3>
                        <p class="settings-section-desc">Manage clinical alerts, appointment bookings, and patient communication notices</p>
                    </div>
                    <div class="settings-section-body">
                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Appointment Booking Alerts</div>
                                <div class="settings-item-desc">Get real-time alerts when a patient schedules, reschedules, or cancels an appointment</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-notif-appointments" checked />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Patient Messages &amp; Consultation Inquiries</div>
                                <div class="settings-item-desc">Receive immediate notifications for new patient chat messages and follow-up queries</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-notif-messages" checked />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Critical Patient Alerts &amp; Lab Results</div>
                                <div class="settings-item-desc">Immediate notifications for urgent patient status changes and uploaded lab reports</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-notif-alerts" checked />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Email Notifications &amp; Summaries</div>
                                <div class="settings-item-desc">Receive daily clinic summaries and appointment rosters via email</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-notif-email" checked />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">SMS Urgent Notifications</div>
                                <div class="settings-item-desc">Receive critical appointment alerts via SMS on your registered phone number</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-notif-sms" />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="button-group">
                            <button type="button" class="btn btn-primary" id="btn-save-doc-notifications">Save Notification Settings</button>
                            <button type="button" class="btn btn-outline" id="btn-cancel-doc-notifications">Reset to Defaults</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. PRIVACY & DANGER ZONE SECTION -->
            <div class="settings-tab-pane" id="doc-pane-privacy" style="display: none;">
                <div class="settings-section">
                    <div class="settings-section-header">
                        <h3 class="settings-section-title">Privacy Controls</h3>
                        <p class="settings-section-desc">Manage doctor directory visibility and patient data sharing</p>
                    </div>
                    <div class="settings-section-body">
                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Doctor Directory Visibility</div>
                                <div class="settings-item-desc">Display your doctor profile in public patient search and recommended specialists directory</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-privacy-directory" checked />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Share Clinic Contact with Booked Patients</div>
                                <div class="settings-item-desc">Permit patients with confirmed appointments to see your clinic contact number</div>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="doc-privacy-contact" checked />
                                <span class="toggle-slider"></span>
                            </label>
                        </div>

                        <div class="settings-item">
                            <div class="settings-item-label">
                                <div class="settings-item-title">Account Activity &amp; Audit Logs</div>
                                <div class="settings-item-desc">Check your recent login records and security session timestamps</div>
                            </div>
                            <button class="btn btn-outline" type="button" id="btn-view-doc-activity-logs" style="padding: 8px 16px; font-size: 12px;">View Activity</button>
                        </div>

                        <div class="button-group">
                            <button type="button" class="btn btn-primary" id="btn-save-doc-privacy">Save Privacy Settings</button>
                            <button type="button" class="btn btn-outline" id="btn-cancel-doc-privacy">Reset</button>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone -->
                <div class="danger-zone" style="margin-top: 24px;">
                    <div class="danger-title">Danger Zone</div>
                    <div class="danger-desc">Once you delete your doctor account, there is no going back. All your consultations, appointment logs, prescriptions, and profile information will be permanently removed from our servers.</div>
                    <button type="button" class="danger-btn" id="btn-open-doc-delete-modal">Delete Account</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal (Mid Popup Box) -->
<div class="modal-overlay" id="delete-account-modal">
    <div class="modal-content" style="max-width: 440px; text-align: center;">
        <div class="modal-icon-danger">
            <span class="material-symbols-outlined">warning</span>
        </div>
        <h3 class="modal-title" style="margin-bottom: 8px;">Delete Account?</h3>
        <p class="modal-desc" style="font-size: 14px; color: var(--on-surface-variant); line-height: 1.5; margin-bottom: 24px;">
            Are you sure you want to delete your doctor account? All your medical appointments, patient consultations, and credentials will be permanently removed from the database. This action cannot be undone.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="btn-cancel-doc-delete" style="padding: 10px 18px;">No, Keep Account</button>
            <button type="button" class="danger-btn" id="btn-confirm-doc-delete" style="padding: 10px 20px;">Yes, Delete Account</button>
        </div>
    </div>
</div>

<!-- Toast Feedback Notification -->
<div class="settings-toast" id="doctor-toast">
    <span class="material-symbols-outlined" id="doctor-toast-icon">check_circle</span>
    <span id="doctor-toast-message">Settings saved successfully</span>
</div>
