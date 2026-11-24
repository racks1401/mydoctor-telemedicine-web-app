"use strict";

const APP = {
  content: document.getElementById("spa-content"),
  links: document.querySelectorAll("[data-page]"),
  currentPage: "dashboard",
  cache: {
    dashboard: null,
    appointments: {
      data: null,
      page: 1,
      limit: 5,
      search: "",
      filters: {
        status: "all"
      },
    },
    doctors: {
      data: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        availability: "all"
      },
    },
    calls: {
      data: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        status: "all"
      },
    },
    chats: {
      data: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        verification_status: "pending"
      },
    },
    settings: {
      data: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
          user_type: "all"
      },
    },
  },
};

const PAGE_CONFIG = {

    "appointments": {
        cache: APP.cache.appointments,
        loader: fetchAppointmentsTable,
        searchable: true,
        filterKey: "status",
        placeholder: "Search Appointments with ..."
    },

    "doctors": {
        cache: APP.cache.doctors,
        loader: fetchDoctorsList,
        searchable: true,
        filterKey: "availability",
        placeholder: "Search Doctors with Name or Email..."
    }

};

const PAGE_ACTIONS = {
    dashboard: handleDashboardActions,
    doctors: handleDoctorActions,
    appointments: handleAppointmentActions,
    settings: handleSettingsActions
};

const FORM_ACTIONS={
      // "change-password": handlePasswordForm,

    // "doctor-form":
    //     handleDoctorForm,

    // "patient-form":
    //     handlePatientForm

};

//resuable fetch helper

async function fetchAPI(url, options = {}) {

    const response = await fetch(url, options);

    if (!response.ok) {
        throw new Error(`HTTP Error ${response.status}`);
    }

    const result = await response.json();
    if (!result.status) {
        throw new Error(result.message);
    }

    return result.data;
}

function getInitials(name = "") {
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
}
function formatLabel(text) {
    return text
        .replace(/_/g, " ")
        .replace(/\b\w/g, char => char.toUpperCase());
}



// APP INITIALIZATION

document.addEventListener("DOMContentLoaded", init);

function init() {
  checkDOMElements();
  setupStaticEventListeners();
  setupGlobalDelegation();
  initializePage();
}

function setupGlobalDelegation() {
  document.addEventListener("click", handleClicks);
  document.addEventListener("submit", handleForms);
}

function handleClicks(event) {
  handlePagination(event);

  const btn = event.target.closest("[data-page][data-action]");
  if (!btn) return;

  const page = btn.dataset.page;
  const action = btn.dataset.action;

  PAGE_ACTIONS[page]?.(action, btn);

}
function handleForms(event){

  const form=event.target;

  if(!form.matches("[data-form]"))
  return;

  event.preventDefault();

  FORM_ACTIONS[form.dataset.form]?.(form);

}

// INITIAL SETUP

function initializePage() {
  const params = new URLSearchParams(window.location.search);

  const page = params.get("page") || "dashboard";

  loadPage(page);
}

// DOM CHECKS

function checkDOMElements() {
  if (!APP.content) {
    console.error("spa-content element not found");
  }
}

// EVENT LISTENERS
function setupStaticEventListeners() {
  fetchAndSetupUserProfile();
  setupSidebarNavigation();
  setupSearchEffect();
}

async function fetchAndSetupUserProfile(){
  try{
     const response = await fetchAPI("/my_doctor/public/patient/api/profile-data.php");
    
     setupUserProfile(response);
  }catch(error){
    console.error(error);
  }
}

function setupUserProfile(data){
  const avatarContainer = document.getElementById("header-profile-img-container");
  if (!avatarContainer) return;

  if (data && data.profile_url) {
    avatarContainer.innerHTML = `
        <img src="${data.profile_url}" alt="${data.name || 'Patient'}" class="profile-img">
    `;
  } else if (data && data.name) {
      avatarContainer.textContent = getInitials(data.name);
      avatarContainer.classList.add("user-avatar");
  } else {
      avatarContainer.textContent = "PT";
      avatarContainer.classList.add("user-avatar");
  }

  avatarContainer.style.cursor = "pointer";
  avatarContainer.addEventListener("click", ()=>{
    loadPage("settings");
  });
}


// SIDEBAR NAVIGATION
function setupSidebarNavigation() {
  APP.links.forEach((link) => {
    link.addEventListener("click", () => {
      const page = link.dataset.page;

      console.log("Navigating to:", page);
      
      loadPage(page);
    });
  });
}


// SEARCH INPUT EFFECT
let searchTimeout;

function setupSearchEffect() {
  const searchInput = document.querySelector(".topbar-search input, .search-container input");

  if (!searchInput) return;

  searchInput.addEventListener("input", () => {
    clearTimeout(searchTimeout);

  searchTimeout = setTimeout(() => {

    handleSearch(searchInput.value.trim());

  }, 100);

  });
}

function handleSearch(search) {
  
  const page = PAGE_CONFIG[APP.currentPage];

  if (!page) return;

  page.cache.search = search;
  page.cache.page = 1;

  page.loader();
}


// PAGINATION

async function handlePagination(event) {

  const button = event.target.closest(".page-nav-btn");
  
  if (!button) return;

  if (button.disabled) return;

  const page = Number(button.dataset.page);
  const type = button.dataset.type;

  if (Number.isNaN(page) || !type) return;

  const cached = APP.cache[type];
  cached.page = page;

  switch (type) {
    case "doctors":
      await fetchDoctorsList();
      break;

    case "patients":
      await fetchPatientsTable();
      break;

    case "appointments":
      await fetchAppointmentsTable();
      break;

    case "userVerification":
      await fetchUserVerificationTable();
      break;

    case "blockedUsers":
      await fetchBlockedUsersTable();
      break;

    default:
      console.warn(`Unknown pagination type: ${type}`);
  }
}


// ACTION BUTTONS

async function handleDashboardActions(action, btn) {

    console.log(`Action triggered: ${action}`);

    switch (action) {

        case "join-appointment":
            joinAppointment("profile");
            break;

        case "reschedule-appointment":
            rescheduleAppointment("profile");
            break;

        default:
            console.warn("Unknown action");
    }

}

function handleSettingsActions(action, btn) {

    alert(`Settings action triggered: ${action}`);
}




// ================= COMMON FUNCTIONS ====================

function handleFilter(tab) {
    if (tab.classList.contains("active")) return;

    const page = PAGE_CONFIG[APP.currentPage];
    if (!page) return;

    const tabsContainer = tab.closest(".filter-tabs") || tab.closest(".filter-group");
    tabsContainer?.querySelectorAll(".active")?.forEach(el => el.classList.remove("active"));

    tab.classList.add("active");

    page.cache.filters[page.filterKey] = tab.dataset.filter;
    page.cache.page = 1;

    page.loader();
}

function viewUserDetails(userId) {
  alert(`View User Details for ID: ${userId}`);
  // Implementation for viewing user details
}


// PAGE LOADING

async function loadPage(page) {
  console.log("Loading page:", page);
  APP.currentPage = page;

  try {
    const response = await fetch(
      `/my_doctor/public/patient/load-page.php?page=${page}`,
    );

    if (!response.ok) {
      throw new Error(`Fetch failed: ${response.status}`);
    }

    const html = await response.text();

    if (!APP.content) {
      console.error("Cannot set content, element missing");
      return;
    }

        APP.content.innerHTML = html;

    updateActiveSidebar(page);
    initializeDynamicPageFeatures(page);
  } catch (error) {
    console.error("Error loading page:", page, error);
  }
}

// ======================================================
// ACTIVE SIDEBAR STATE
// ======================================================

function updateActiveSidebar(page) {
  APP.links.forEach((link) => {
    link.classList.remove("active");
  });

  const activeLink = document.querySelector(`[data-page="${page}"]`);

  if (activeLink) {
    activeLink.classList.add("active");
  }
}

// ======================================================
// PAGE SPECIFIC FEATURES
// ======================================================

function initializeDynamicPageFeatures(page) {
  switch (page) {
    case "dashboard":
      initializeDashboard();
      break;

    case "doctors":
      initializeDoctors();

      break;

    case "patients":
      initializePatients();

      break;

    case "appointments":
      initializeAppointments();
      break;

    case "user-verification":
      initializeUserVerification();
      break;

    case "blocked-users":
      initializeBlockedUsers();
      break;
    
    case "profile":
      initializeProfile();
      break;

    case "chats":
      initializePatientChat();
      break;

    case "calls":
      initPatientCalls();
      break;

    case "prescriptions":
      initPatientPrescriptions();
      break;

    case "settings":
      initializeSettings();
      break;
  }
}



// =========================== Chat Functions ============================
function formatDoctorDisplayName(name) {
    if (!name) return "Doctor";
    const str = String(name).trim();
    if (/^Dr\.?\s+/i.test(str)) {
        return str;
    }
    return `Dr. ${str}`;
}

let activePatientConvId = null;
let lastPatientMessageId = 0;
let oldestPatientMessageId = null;
let isLoadingOlderPatientMessages = false;
let renderedPatientMessageIds = new Set();
let patientConversationsCache = [];

function initializePatientChat() {
    const itemsContainer = document.querySelector(".chat-items");
    const headerName = document.querySelector(".chat-header-name");
    const headerAvatar = document.querySelector(".chat-header-avatar");
    const headerStatus = document.querySelector(".chat-header-status");
    const headerActions = document.querySelector(".chat-header-actions");
    const messagesContainer = document.querySelector(".chat-messages");
    const chatInput = document.querySelector(".chat-input textarea") || document.querySelector(".chat-input");
    const sendBtn = document.querySelector(".chat-send-btn");
    const searchInput = document.querySelector(".chat-search input");

    if (!itemsContainer) return;

    const mobileBackBtn = document.getElementById("btn-chat-mobile-back");
    if (mobileBackBtn) {
        mobileBackBtn.onclick = (e) => {
            e.preventDefault();
            const container = document.querySelector(".chats-container");
            if (container) {
                container.classList.remove("chat-view-active");
            }
        };
    }

    const safeHtml = (str) => {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
    };

    const loadConversations = async (targetConvOrApptId = null) => {
        try {
            const res = await fetch("/my_doctor/public/patient/api/chat.php?action=list");
            const data = await res.json();
            if (!data.status) {
                itemsContainer.innerHTML = `<div style="padding: 20px; text-align: center; color: #dc2626; font-size: 13px;">${safeHtml(data.message || "Error loading chats")}</div>`;
                return;
            }

            patientConversationsCache = data.data.conversations || [];
            renderConversationItems(patientConversationsCache);

            if (patientConversationsCache.length === 0) {
                itemsContainer.innerHTML = '<div style="padding: 24px 16px; text-align: center; color: var(--on-surface-variant, #64748b); font-size: 13px;">No online consultations yet</div>';
                if (headerName) headerName.textContent = 'No active chat';
                if (headerStatus) headerStatus.textContent = '';
                if (messagesContainer) messagesContainer.innerHTML = '';
                return;
            }

            let toSelect = null;
            const reqTarget = targetConvOrApptId || window.targetChatConvId || window.targetChatAppointmentId;
            if (reqTarget) {
                toSelect = patientConversationsCache.find(c => c.conversation_id === reqTarget || c.appointment_id === reqTarget);
                window.targetChatAppointmentId = null;
                window.targetChatConvId = null;
            } else if (activePatientConvId) {
                toSelect = patientConversationsCache.find(c => c.conversation_id === activePatientConvId);
            }

            if (!toSelect && patientConversationsCache.length > 0) {
                toSelect = patientConversationsCache[0];
            }

            if (toSelect) {
                const isMobile = window.innerWidth <= 768;
                const openOnMobile = Boolean(reqTarget) || !isMobile;
                selectConversation(toSelect.conversation_id, openOnMobile);
            }
        } catch (err) {
            console.error("Error loading patient conversations:", err);
            itemsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #dc2626; font-size: 13px;">Network error loading chats</div>';
        }
    };

    const renderConversationItems = (conversations) => {
        itemsContainer.innerHTML = "";
        conversations.forEach(conv => {
            const item = document.createElement("div");
            item.className = "chat-item" + (conv.conversation_id === activePatientConvId ? " active" : "");
            item.dataset.chatId = conv.conversation_id;

            const doctorName = formatDoctorDisplayName(conv.other_user_name);
            const initials = conv.other_user_initials || (doctorName ? doctorName.split(" ").map(n=>n[0]).join("").substring(0,2).toUpperCase() : "DR");
            let avatarHtml = "";
            if (conv.other_user_avatar) {
                avatarHtml = `<img alt="${safeHtml(doctorName)}" class="chat-avatar-item" src="${conv.other_user_avatar}" onerror="this.onerror=null; this.outerHTML='<div class=\\'chat-avatar-initials\\'>${initials}</div>';">`;
            } else {
                avatarHtml = `<div class="chat-avatar-initials">${initials}</div>`;
            }

            item.innerHTML = `
                ${avatarHtml}
                <div class="chat-preview">
                    <p class="chat-name">${safeHtml(doctorName)}</p>
                    <p class="chat-last-msg">${safeHtml(conv.last_message || "No messages yet")}</p>
                </div>
                ${conv.unread_count > 0 ? `<div class="chat-unread">${conv.unread_count}</div>` : ""}
            `;

            item.addEventListener("click", () => {
                selectConversation(conv.conversation_id, true);
            });

            itemsContainer.appendChild(item);
        });
    };

    const selectConversation = async (convId, openViewOnMobile = true) => {
        activePatientConvId = convId;
        lastPatientMessageId = 0;
        oldestPatientMessageId = null;
        renderedPatientMessageIds.clear();

        const chatsContainer = document.querySelector(".chats-container");
        if (chatsContainer && openViewOnMobile && window.innerWidth <= 768) {
            chatsContainer.classList.add("chat-view-active");
        }

        itemsContainer.querySelectorAll(".chat-item").forEach(el => {
            if (parseInt(el.dataset.chatId, 10) === convId) {
                el.classList.add("active");
                const unreadEl = el.querySelector(".chat-unread");
                if (unreadEl) unreadEl.remove();
            } else {
                el.classList.remove("active");
            }
        });

        const conv = patientConversationsCache.find(c => c.conversation_id === convId);
        if (!conv) return;

        const doctorName = formatDoctorDisplayName(conv.other_user_name);
        const headerAvatarBox = document.getElementById("chat-header-avatar-box") || (headerAvatar ? headerAvatar.parentElement : null);
        const initials = conv.other_user_initials || (doctorName ? doctorName.split(" ").map(n=>n[0]).join("").substring(0,2).toUpperCase() : "DR");
        if (headerAvatarBox) {
            if (conv.other_user_avatar) {
                headerAvatarBox.innerHTML = `<img alt="${safeHtml(doctorName)}" class="chat-header-avatar" src="${conv.other_user_avatar}" onerror="this.onerror=null; this.outerHTML='<div class=\\'chat-header-initials\\'>${initials}</div>';">`;
            } else {
                headerAvatarBox.innerHTML = `<div class="chat-header-initials">${initials}</div>`;
            }
        } else if (headerAvatar) {
            if (conv.other_user_avatar) {
                headerAvatar.src = conv.other_user_avatar;
                headerAvatar.style.display = "block";
            }
        }
        if (headerName) headerName.textContent = doctorName;

        // Notify Chat Call UI of active conversation
        if (window.DoctorChatCallUI) {
            window.DoctorChatCallUI.onConversationSelected(conv);
        }

        // Step 4: Fire markMessagesAsRead non-blocking in background without awaiting it
        markMessagesAsRead(convId);

        // Step 4: Parallel fetch & Firebase init (render history without waiting for Firebase)
        const tOpen0 = performance.now();
        const loadPromise = loadMessages(convId);
        const fbPromise = window.DoctorFirebase ? window.DoctorFirebase.init() : Promise.resolve();

        await loadPromise;
        console.log(`[Perf] Patient chat history rendered in: ${(performance.now() - tOpen0).toFixed(2)}ms`);

        // Attach Firebase listener after history render
        fbPromise.then(() => {
            if (window.DoctorFirebase && activePatientConvId === convId) {
                window.DoctorFirebase.subscribeChat(convId, (signal) => {
                    // Sender must ignore own signal
                    if (signal.sender_id && parseInt(signal.sender_id, 10) === window.DoctorFirebase.currentUserId) {
                        return;
                    }
                    if (activePatientConvId === convId) {
                        fetchNewMessages(convId);
                    }
                });
            }
        });
    };

    const loadMessages = async (convId) => {
        if (!messagesContainer) return;
        messagesContainer.innerHTML = "";

        try {
            const t0 = performance.now();
            const res = await fetch(`/my_doctor/public/patient/api/chat.php?action=fetch_messages&conversation_id=${convId}&limit=30`);
            const data = await res.json();
            console.log(`[Perf] History fetch API took: ${(performance.now() - t0).toFixed(2)}ms`);

            if (!data.status) {
                messagesContainer.innerHTML = `<div style="margin: auto; color: #dc2626; font-size: 13px;">${safeHtml(data.message || "Error fetching messages")}</div>`;
                return;
            }

            const windowState = data.data.window_state;
            const canSend = data.data.can_send;
            const canFollowUp = data.data.can_follow_up;

            if (headerStatus) {
                if (windowState === 'live') {
                    headerStatus.textContent = 'Active now';
                    headerStatus.style.color = 'var(--primary, #0052cc)';
                } else if (windowState === 'not_started') {
                    headerStatus.textContent = `Opens at ${data.data.conversation?.appointment_time ? data.data.conversation.appointment_time.substring(0,5) : 'scheduled slot'}`;
                    headerStatus.style.color = 'var(--on-surface-variant, #64748b)';
                } else {
                    headerStatus.textContent = 'Consultation ended';
                    headerStatus.style.color = 'var(--on-surface-variant, #64748b)';
                }
            }

            // Remove any old follow-up button from header actions
            const existingFollowUp = headerActions?.querySelector('#btn-chat-followup');
            if (existingFollowUp) existingFollowUp.remove();

            if (canFollowUp && headerActions) {
                const followUpBtn = document.createElement("button");
                followUpBtn.id = "btn-chat-followup";
                followUpBtn.type = "button";
                followUpBtn.textContent = "+ Follow-up";
                followUpBtn.style.cssText = "padding: 5px 12px; font-size: 12px; border-radius: 6px; background: #2563eb; color: white; border: none; cursor: pointer; font-weight: 600; margin-right: 6px;";
                followUpBtn.onclick = () => {
                    if (typeof window.openFollowUpModal === "function") {
                        window.openFollowUpModal(convId);
                    } else {
                        alert("Follow-up booking will open in Phase 4.");
                    }
                };
                headerActions.prepend(followUpBtn);
            }

            if (chatInput) {
                chatInput.disabled = !canSend;
                chatInput.placeholder = canSend ? "Type a message..." : (windowState === 'not_started' ? "Chat opens 5m before slot" : "Consultation ended");
            }
            if (sendBtn) {
                sendBtn.disabled = !canSend;
                sendBtn.style.opacity = canSend ? "1" : "0.5";
            }

            messagesContainer.innerHTML = "";
            const msgs = data.data.messages || [];
            const conv = data.data.conversation;

            msgs.forEach(m => {
                appendMessageToDom(m, conv);
                if (m.message_id > lastPatientMessageId) {
                    lastPatientMessageId = m.message_id;
                }
                if (oldestPatientMessageId === null || m.message_id < oldestPatientMessageId) {
                    oldestPatientMessageId = m.message_id;
                }
            });
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

        } catch (err) {
            console.error("Error fetching patient messages:", err);
        }
    };

    const fetchNewMessages = async (convId) => {
        if (!messagesContainer || lastPatientMessageId === null || lastPatientMessageId === undefined) return;
        try {
            const afterParam = lastPatientMessageId > 0 ? `&after_id=${lastPatientMessageId}` : '';
            const t0 = performance.now();
            const res = await fetch(`/my_doctor/public/patient/api/chat.php?action=fetch_messages&conversation_id=${convId}${afterParam}`);
            const data = await res.json();
            console.log(`[Perf] Receiver fetchNewMessages took: ${(performance.now() - t0).toFixed(2)}ms`);

            if (data.status && data.data.messages && data.data.messages.length > 0) {
                const conv = data.data.conversation;
                let hasNew = false;
                data.data.messages.forEach(m => {
                    if (!renderedPatientMessageIds.has(m.message_id)) {
                        appendMessageToDom(m, conv);
                        if (m.message_id > lastPatientMessageId) {
                            lastPatientMessageId = m.message_id;
                        }
                        hasNew = true;
                    }
                });
                if (hasNew) {
                    messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    markMessagesAsRead(convId);

                    // Step 3: Fast receiver update - update sidebar snippet locally without reloading list
                    const activeItem = itemsContainer.querySelector(`.chat-item[data-chat-id="${convId}"]`);
                    if (activeItem) {
                        const lastMsgEl = activeItem.querySelector('.chat-last-msg');
                        if (lastMsgEl) {
                            lastMsgEl.textContent = data.data.messages[data.data.messages.length - 1].body;
                        }
                        itemsContainer.prepend(activeItem);
                    }
                }
            }
        } catch (err) {
            console.error("Error fetching patient messages:", err);
        }
    };

    // Phase 5: Firebase Real-time user inbox listener
    if (window.DoctorFirebase && typeof window.DoctorFirebase.subscribeUserChatUpdates === 'function') {
        window.DoctorFirebase.subscribeUserChatUpdates(() => {
            if (activePatientConvId) {
                fetchNewMessages(activePatientConvId);
            } else {
                loadConversations();
            }
        });
    }

    // Lightweight backup interval: only fires if Firebase not ready
    if (window.patientChatPollInterval) clearInterval(window.patientChatPollInterval);
    window.patientChatPollInterval = setInterval(() => {
        if (activePatientConvId && document.querySelector('.chats-container') && (!window.DoctorFirebase || !window.DoctorFirebase.isInitialized)) {
            fetchNewMessages(activePatientConvId);
        }
    }, 10000);

    // Window online and tab visibility handlers for instant resync
    window.addEventListener('online', () => {
        if (activePatientConvId) fetchNewMessages(activePatientConvId);
    });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && activePatientConvId) fetchNewMessages(activePatientConvId);
    });

    // Step 4: Scroll-up pagination for older messages
    messagesContainer.addEventListener('scroll', async () => {
        if (messagesContainer.scrollTop <= 10 && oldestPatientMessageId && !isLoadingOlderPatientMessages && activePatientConvId) {
            isLoadingOlderPatientMessages = true;
            const prevHeight = messagesContainer.scrollHeight;
            try {
                const res = await fetch(`/my_doctor/public/patient/api/chat.php?action=fetch_messages&conversation_id=${activePatientConvId}&before_id=${oldestPatientMessageId}&limit=30`);
                const data = await res.json();
                if (data.status && data.data.messages && data.data.messages.length > 0) {
                    const conv = data.data.conversation;
                    const reversed = [...data.data.messages].reverse();
                    reversed.forEach(m => {
                        if (!renderedPatientMessageIds.has(m.message_id)) {
                            appendMessageToDom(m, conv, true);
                            if (m.message_id < oldestPatientMessageId) {
                                oldestPatientMessageId = m.message_id;
                            }
                        }
                    });
                    messagesContainer.scrollTop = messagesContainer.scrollHeight - prevHeight;
                }
            } catch (e) {
                console.error("Error loading older messages:", e);
            } finally {
                isLoadingOlderPatientMessages = false;
            }
        }
    });

    const appendMessageToDom = (m, conv = null, prepend = false) => {
        if (!messagesContainer) return null;

        // Deduplicate by message_id
        if (!m.is_temp && renderedPatientMessageIds.has(m.message_id)) {
            return null;
        }
        if (!m.is_temp && m.message_id) {
            renderedPatientMessageIds.add(m.message_id);
        }

        const msgDiv = document.createElement("div");
        msgDiv.className = "message " + (m.is_me ? "sent" : "received");
        msgDiv.dataset.msgId = m.message_id;

        if (m.is_temp) {
            msgDiv.classList.add("message-pending");
            msgDiv.style.opacity = "0.7";
        }

        if (!m.is_me) {
            const doctorName = formatDoctorDisplayName(conv?.other_user_name);
            const otherInitials = conv?.other_user_initials || (doctorName ? doctorName.split(" ").map(n=>n[0]).join("").substring(0,2).toUpperCase() : "DR");
            if (conv?.other_user_avatar) {
                const avatarImg = document.createElement("img");
                avatarImg.className = "message-avatar";
                avatarImg.alt = doctorName;
                avatarImg.src = conv.other_user_avatar;
                avatarImg.onerror = function() {
                    const initDiv = document.createElement("div");
                    initDiv.className = "message-avatar-initials";
                    initDiv.textContent = otherInitials;
                    avatarImg.replaceWith(initDiv);
                };
                msgDiv.appendChild(avatarImg);
            } else {
                const initDiv = document.createElement("div");
                initDiv.className = "message-avatar-initials";
                initDiv.textContent = otherInitials;
                msgDiv.appendChild(initDiv);
            }
        }

        const innerWrap = document.createElement("div");

        const bubble = document.createElement("div");
        bubble.className = "message-bubble";
        bubble.textContent = m.body;

        const timeDiv = document.createElement("div");
        timeDiv.className = "message-time";
        timeDiv.textContent = m.time_formatted || (m.is_temp ? "Sending..." : m.created_at);

        innerWrap.appendChild(bubble);
        innerWrap.appendChild(timeDiv);
        msgDiv.appendChild(innerWrap);

        if (prepend) {
            messagesContainer.prepend(msgDiv);
        } else {
            messagesContainer.appendChild(msgDiv);
        }

        return msgDiv;
    };

    const markMessagesAsRead = async (convId) => {
        try {
            const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || "";
            fetch("/my_doctor/public/patient/api/chat.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token
                },
                body: JSON.stringify({
                    action: "mark_read",
                    conversation_id: convId,
                    csrf_token: token
                })
            });
        } catch (e) {}
    };

    // Step 1: Optimistic UI Send Flow
    const sendMessage = async () => {
        if (!chatInput || !activePatientConvId) return;
        const text = chatInput.value.trim();
        if (!text) return;

        // 1. Immediately clear the input & append sending bubble (do not wait for server)
        chatInput.value = "";
        const tempId = "temp_" + Date.now() + "_" + Math.floor(Math.random() * 1000);

        const optimisticMsg = {
            message_id: tempId,
            is_temp: true,
            body: text,
            time_formatted: "Sending...",
            is_me: true
        };

        const tempEl = appendMessageToDom(optimisticMsg);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // 2. Immediately update sidebar locally (last message & move to top)
        const activeItem = itemsContainer.querySelector(`.chat-item[data-chat-id="${activePatientConvId}"]`);
        if (activeItem) {
            const lastMsgEl = activeItem.querySelector('.chat-last-msg');
            if (lastMsgEl) lastMsgEl.textContent = text;
            itemsContainer.prepend(activeItem);
        }

        const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || "";
        const tSendReq0 = performance.now();

        try {
            const res = await fetch("/my_doctor/public/patient/api/chat.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token
                },
                body: JSON.stringify({
                    action: "send_message",
                    conversation_id: activePatientConvId,
                    body: text,
                    csrf_token: token
                })
            });

            const data = await res.json();
            console.log(`[Perf] Send request completed in: ${(performance.now() - tSendReq0).toFixed(2)}ms`);

            if (!data.status || !data.data) {
                markMessageFailed(tempEl, text, data.message || "Could not send message.");
                return;
            }

            // Replace temp bubble with real message_id & mark sent
            const realMsg = data.data;
            if (tempEl) {
                tempEl.dataset.msgId = realMsg.message_id;
                tempEl.classList.remove("message-pending");
                tempEl.style.opacity = "1";
                const timeEl = tempEl.querySelector(".message-time");
                if (timeEl) timeEl.textContent = realMsg.time_formatted || "Just now";
            }
            renderedPatientMessageIds.add(realMsg.message_id);
            if (realMsg.message_id > lastPatientMessageId) {
                lastPatientMessageId = realMsg.message_id;
            }

        } catch (err) {
            console.error("Patient send message error:", err);
            markMessageFailed(tempEl, text, "Network error. Failed to send.");
        }
    };

    const markMessageFailed = (el, originalText, errorMsg) => {
        if (!el) return;
        el.classList.remove("message-pending");
        el.classList.add("message-failed");
        el.style.opacity = "1";
        const timeEl = el.querySelector(".message-time");
        if (timeEl) {
            timeEl.innerHTML = `<span style="color:#ef4444; font-size:11px;">${safeHtml(errorMsg)}</span> <button type="button" class="btn-retry-chat" style="background:none; border:none; color:var(--primary, #0052cc); font-weight:600; cursor:pointer; font-size:11px; text-decoration:underline; margin-left:4px;">Retry</button>`;
            timeEl.querySelector('.btn-retry-chat')?.addEventListener('click', (e) => {
                e.stopPropagation();
                el.remove();
                chatInput.value = originalText;
                sendMessage();
            });
        }
    };

    if (sendBtn && chatInput) {
        sendBtn.onclick = (e) => {
            e.preventDefault();
            sendMessage();
        };

        chatInput.onkeydown = (e) => {
            if (e.key === "Enter" && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        };
    }

    if (searchInput) {
        searchInput.oninput = () => {
            const q = searchInput.value.toLowerCase().trim();
            itemsContainer.querySelectorAll(".chat-item").forEach(item => {
                const name = item.querySelector(".chat-name")?.textContent?.toLowerCase() || "";
                const msg = item.querySelector(".chat-last-msg")?.textContent?.toLowerCase() || "";
                if (!q || name.includes(q) || msg.includes(q)) {
                    item.style.display = "flex";
                } else {
                    item.style.display = "none";
                }
            });
        };
    }

    window.openConsultationChat = (appointmentId) => {
        window.targetChatAppointmentId = parseInt(appointmentId, 10);
        loadPage("chats");
    };

    loadConversations();
}

// =========================== Dashboard Functions ========================


async function initializeDashboard() {
  const cached = APP.cache.dashboard;
  if (cached) {
    renderDashboard(cached);
  }
  await fetchLatestDashboard();
}

async function fetchLatestDashboard() {
  try {
    const response = await fetchAPI(
      "/my_doctor/public/patient/api/dashboard.php",
    );

    APP.cache.dashboard = response;
    renderDashboard(response);
    console.log(response);
  } catch (error) {
    console.error(error);
  }
}

function renderDashboard(data) {
  const welcomeEl = document.getElementById("patient-dashboard-welcome") || document.querySelector(".page-title h2");
  if (welcomeEl && (data.patient_name || data.greeting)) {
    welcomeEl.textContent = data.greeting || `Welcome back, ${data.patient_name}`;
  }
  renderStats(data.stats);
  renderRecentActivity(data.recentAppointment);
  renderUpcomingAppointment(data.upcomingAppointment);
}
function renderStats(stats) {
  const totalAppointments = document.getElementById("total-appointments-count");
  const pendingAppointments = document.getElementById("pending-appointments-count");
  const completedAppointments = document.getElementById("completed-appointments-count");
  const totalDoctorsConsulted = document.getElementById("total-doctors-consulted");

  if (totalAppointments) totalAppointments.textContent = stats.totalAppointments || 0;
  if (pendingAppointments) pendingAppointments.textContent = stats.pendingAppointments || 0;
  if (completedAppointments) completedAppointments.textContent = stats.completedAppointments || 0;
  if (totalDoctorsConsulted) totalDoctorsConsulted.textContent = stats.totalDoctorsConsulted || 0;
}

function renderRecentActivity(appointments) {

  const recentAppointmentBody = document.getElementById("recent-appointments-body");

  if (!recentAppointmentBody) return;

  recentAppointmentBody.innerHTML = "";

  appointments.forEach((appointment) => {
    const avatar = appointment.profile_url
    ? `
            <div class="user-avatar">
              <img src="${appointment.profile_url}" alt="Doctor"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(appointment.doctor_name)}
            </div>
          `;
    recentAppointmentBody.innerHTML += `
            <tr>
                <td>
                    <div class="user-cell">
                    <div class="user-avatar">
                      ${avatar}
                    </div>
                    <div><p class="user-name">${formatDoctorDisplayName(appointment.doctor_name)}</p>
                  </div>
                </td>

                <td>
                    ${appointment.appointment_mode}
                </td>

                <td>
                    ${formatAppointmentDate(appointment.appointment_date, appointment.appointment_time)}
                </td>

                <td>
                    ${appointment.status}
                </td>


            </tr>
        `;
  });
}

function renderUpcomingAppointment(appointment) {
  const cardContainer = document.querySelector(".appointment-card");
  if (!cardContainer) return;

  const hasUpcoming = appointment && !Array.isArray(appointment) && appointment.appointment_date && appointment.doctor_name;

  if (!hasUpcoming) {
    window.upcomingAppointmentData = null;
    cardContainer.innerHTML = `
      <div class="appointment-header" style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: white; padding: 22px 24px;">
          <p class="appointment-header-label" style="margin: 0 0 6px 0; font-size: 11px; letter-spacing: 0.06em; opacity: 0.9; text-transform: uppercase;">Next Appointment</p>
          <h4 class="appointment-time" style="margin: 0; font-size: 17px; font-weight: 700;">No Scheduled Session</h4>
      </div>
      <div class="appointment-body" style="padding: 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 16px;">
          <div style="width: 54px; height: 54px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37,99,235,0.12); margin-top: 4px;">
              <span class="material-symbols-outlined" style="font-size: 28px;">event_available</span>
          </div>
          <div style="text-align: center;">
              <h4 style="margin: 0 0 6px 0; font-size: 15px; font-weight: 700; color: var(--on-surface, #1e293b);">No Upcoming Appointments</h4>
              <p style="margin: 0 auto; font-size: 12.5px; color: var(--text-muted, #64748b); line-height: 1.5; max-width: 250px;">
                  You have no pending consultations scheduled right now. Need medical consultation?
              </p>
          </div>
          <button class="btn btn-primary" type="button" id="btn-book-from-dashboard" style="width: 100%; justify-content: center; margin-top: 4px; padding: 10px 18px; font-weight: 600; font-size: 13.5px; border-radius: 8px; display: inline-flex; align-items: center; gap: 8px;">
              <span class="material-symbols-outlined" style="font-size: 18px;">add_circle</span>
              Book New Consultation
          </button>
      </div>
    `;

    const bookBtn = document.getElementById("btn-book-from-dashboard");
    if (bookBtn) {
      bookBtn.onclick = () => {
        if (typeof loadPage === "function") {
          loadPage("doctors");
        }
      };
    }
    return;
  }

  // Populate active appointment details
  cardContainer.innerHTML = `
    <div class="appointment-header">
        <p class="appointment-header-label">Next Appointment</p>
        <h4 class="appointment-time" id="upcoming-appointment-date"></h4>
    </div>
    <div class="appointment-body">
        <div class="appointment-doctor">
            <div class="user-avatar" id="doctor-profile-photo"></div>
            <div class="appointment-doctor-info">
                <h4 id="upcoming-appointment-doctor"></h4>
                <p id="upcoming-appointment-specialization"></p>
            </div>
        </div>
        <div class="appointment-details">
            <div class="appointment-detail-item">
                <span id="appointment-mode-icon" class="material-symbols-outlined appointment-detail-icon">videocam</span>
                <span class="appointment-detail-text" id="upcoming-appointment-mode"></span>
            </div>
            <div class="appointment-detail-item">
                <span class="material-symbols-outlined appointment-detail-icon">timer</span>
                <span class="appointment-detail-text" id="upcoming-appointment-duration"></span>
            </div>
        </div>
        <div class="appointment-actions">
            <button class="btn btn-primary" data-page="common" data-action="join-appointment" id="upcoming-appointment-join">Join Video Call</button>
            <button class="btn btn-outline" data-page="common" data-action="reschedule-appointment" id="upcoming-appointment-reschedule">Reschedule</button>
        </div>
    </div>
  `;

  const photo = document.getElementById("doctor-profile-photo");
  const modeIcon = document.getElementById("appointment-mode-icon");
  if (modeIcon) modeIcon.textContent = ICONS[appointment.appointment_mode] || "videocam";

  const dateEl = document.getElementById("upcoming-appointment-date");
  if (dateEl) dateEl.textContent = formatAppointmentDate(appointment.appointment_date, appointment.appointment_time);

  const docEl = document.getElementById("upcoming-appointment-doctor");
  if (docEl) docEl.textContent = formatDoctorDisplayName(appointment.doctor_name);

  const specEl = document.getElementById("upcoming-appointment-specialization");
  if (specEl) specEl.textContent = appointment.doctor_specialization || "General Medicine";

  const modeEl = document.getElementById("upcoming-appointment-mode");
  if (modeEl) modeEl.textContent = formatLabel(appointment.appointment_mode || "online");

  const durEl = document.getElementById("upcoming-appointment-duration");
  if (durEl) durEl.textContent = `${appointment.appointment_duration || 30} mins`;

  if (photo) {
    if (appointment.doctor_profile_picture) {
      photo.innerHTML = `<img src="${appointment.doctor_profile_picture}" alt="${appointment.doctor_name || 'Doctor'}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;"/>`;
    } else {
      photo.innerHTML = `<div class="whatsapp-call-avatar-initial" style="width:100%;height:100%;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#dbeafe;color:#1d4ed8;font-weight:700;">${getInitials(appointment.doctor_name || "DR")}</div>`;
    }
  }

  window.upcomingAppointmentData = appointment;

  const joinBtn = document.getElementById("upcoming-appointment-join");
  if (joinBtn) {
    if (appointment.appointment_mode === "offline") {
      joinBtn.textContent = "View Details";
    } else if (appointment.appointment_mode === "video_call") {
      joinBtn.textContent = "Join Video Call";
    } else {
      joinBtn.textContent = "Join Consultation";
    }

    joinBtn.onclick = () => {
      if (appointment.appointment_mode === "offline") {
        alert("Offline appointment: Please visit the clinic at your scheduled appointment time.");
      } else {
        if (appointment.conversation_id) window.targetChatConvId = appointment.conversation_id;
        if (appointment.appointment_id) window.targetChatAppointmentId = appointment.appointment_id;
        if (typeof window.openConsultationChat === "function") {
          window.openConsultationChat(appointment.appointment_id);
        } else if (typeof loadPage === "function") {
          loadPage("chats");
        }
      }
    };
  }

  const reschedBtn = document.getElementById("upcoming-appointment-reschedule");
  if (reschedBtn) {
    reschedBtn.onclick = () => {
      if (typeof loadPage === "function") {
        loadPage("appointments");
      }
    };
  }
}
const ICONS = {
    offline: "person_pin_circle",
    chat: "chat",
    audio_call: "call",
    video_call: "videocam"
};

function formatAppointmentDate(date, time) {
    if (!date || !time) return "No date scheduled";
    const appointment = new Date(`${date}T${time}`);
    if (isNaN(appointment.getTime())) return "No date scheduled";

    const today = new Date();
    const tomorrow = new Date();
    const yesterday = new Date();

    today.setHours(0, 0, 0, 0);

    tomorrow.setDate(today.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);

    yesterday.setDate(today.getDate() - 1);
    yesterday.setHours(0, 0, 0, 0);

    const appointmentDate = new Date(appointment);
    appointmentDate.setHours(0, 0, 0, 0);

    let dayText;

    if (appointmentDate.getTime() === today.getTime()) {
        dayText = "Today";
    } else if (appointmentDate.getTime() === tomorrow.getTime()) {
        dayText = "Tomorrow";
    } else if (appointmentDate.getTime() === yesterday.getTime()) {
        dayText = "Yesterday";
    } else {
        dayText = appointment.toLocaleDateString("en-GB", {
            day: "numeric",
            month: "short",
            year: "numeric"
        });
    }

    const timeText = appointment.toLocaleTimeString("en-US", {
        hour: "numeric",
        minute: "2-digit",
        hour12: true
    });

    return `${dayText} at ${timeText}`;
}

function joinAppointment(){
  if (window.upcomingAppointmentData && window.upcomingAppointmentData.appointment_id) {
    if (window.upcomingAppointmentData.appointment_mode === 'offline') {
      alert("Offline appointment: Please visit the clinic at your scheduled appointment time.");
    } else {
      window.openConsultationChat(window.upcomingAppointmentData.appointment_id);
    }
  } else {
    loadPage("chats");
  }
}

function rescheduleAppointment(){
  alert("Reschedule Appointment Feture will be implemented");
}

// =========================== appointments Functions ========================



async function initializeAppointments() {
  if (typeof initPhase4Modals === 'function') initPhase4Modals();
  const cached = APP.cache.appointments;
  console.log(cached);
  if (cached.data) {
    renderAppointments(cached.data);
  }
  await fetchAppointmentsTable();

  // New Appointment button handler
  const btnNewAppt = document.getElementById("btn-new-appointment");
  if (btnNewAppt) {
    btnNewAppt.onclick = (e) => {
      e.preventDefault();
      openBookingModal(null);
    };
  }

  // Phase 5: Firebase Real-time Signal for Patient Dashboard Appointments
  if (window.DoctorFirebase) {
    window.DoctorFirebase.subscribeDashboardAppointments(() => {
      fetchAppointmentsTable();
    });
  }
}

async function fetchAppointmentsTable(){
  const cached = APP.cache.appointments;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });
  try{
    const response = await fetchAPI(
      `/my_doctor/public/patient/api/appointments.php?${params}`,
    );
    cached.data = response;
    console.log(response);
    renderAppointments(response);
  }catch(error){
    console.log(error)
  }
}

function renderAppointments(data){
  window.cachedAppointmentsData = data;
  renderAppointmentsTable(data.appointments);
  renderPagination(data.pagination, "appointments");
}

function renderAppointmentsTable(appointments){
  const tbody = document.getElementById("appointments-tbody");
  tbody.innerHTML = "";
  appointments.forEach((appointment) =>{
    const row = createAppointmentRow(appointment);
    tbody.appendChild(row);
  })
}

function createAppointmentRow(appointment) {
  const row = document.createElement("tr");

  const avatar = appointment.profile_url
    ? `
            <div class="user-avatar">
              <img src="${appointment.profile_url}" alt="Doctor"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(appointment.doctor_name || "Doctor")}
            </div>
          `;

  const isRescheduled = (appointment.status === "resheduled");
  let statusBadgeHtml = `<span class="badge badge-success" style="border-radius: 999px; padding: 4px 12px; font-weight: 600;">${capitalize(appointment.status)}</span>`;

  if (isRescheduled) {
    statusBadgeHtml = `
      <div>
        <span class="badge badge-warning" style="background: #fef9c3; color: #854d0e; border: 1px solid #fde047; padding: 4px 12px; border-radius: 999px; font-weight: 600;">Rescheduled</span>
        ${appointment.proposed_date ? `<div style="font-size: 11px; color: #854d0e; margin-top: 4px; font-weight: 600;">Dr. proposed: ${formatDate(appointment.proposed_date)} at ${formatTime(appointment.proposed_start_time)}</div>` : ''}
      </div>
    `;
  } else if (appointment.status === "completed") {
    statusBadgeHtml = `<span class="badge" style="background: #e0e7ff; color: #3730a3; padding: 4px 12px; border-radius: 999px; font-weight: 600;">Completed</span>`;
  } else if (appointment.status === "cancelled") {
    statusBadgeHtml = `<span class="badge" style="background: #fee2e2; color: #b91c1c; padding: 4px 12px; border-radius: 999px; font-weight: 600;">Cancelled</span>`;
  } else if (appointment.status === "pending") {
    statusBadgeHtml = `<span class="badge" style="background: #fef3c7; color: #b45309; padding: 4px 12px; border-radius: 999px; font-weight: 600;">Pending</span>`;
  }

  const followUpTag = (appointment.appointment_type === "follow-up")
    ? `<span style="background: #e0e7ff; color: #3730a3; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">Follow-up</span>`
    : "";

  let actionBtnsHtml = "";

  if (isRescheduled) {
    actionBtnsHtml += `
      <button type="button" class="action-btn" title="Accept Proposed Slot" onclick="window.acceptProposedSlot(${appointment.appointment_id}, '${appointment.proposed_date} ${appointment.proposed_start_time}')" style="color: #16a34a; background: #dcfce7; border-radius: 6px; padding: 4px 8px; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; border: none; cursor: pointer;">
        <span class="material-symbols-outlined" style="font-size: 15px;">check_circle</span>Accept
      </button>
      <button type="button" class="action-btn" title="Pick Alternate Slot" onclick="window.openAlternateSlotModal(${appointment.appointment_id})" style="color: #b45309; background: #fef3c7; border-radius: 6px; padding: 4px 8px; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; border: none; cursor: pointer;">
        <span class="material-symbols-outlined" style="font-size: 15px;">calendar_month</span>Alternate
      </button>
    `;
  }

  if (appointment.can_follow_up === true) {
    actionBtnsHtml += `
      <button type="button" class="action-btn" title="Book Free Follow-up" onclick="window.openFollowUpModal(${appointment.appointment_id})" style="color: white; background: #2563eb; border-radius: 6px; padding: 4px 10px; font-size: 11.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; border: none; cursor: pointer;">
        <span class="material-symbols-outlined" style="font-size: 15px;">add</span>Follow-up
      </button>
    `;
  }

  if (["video_call", "chat", "audio_call"].includes(appointment.appointment_mode) && appointment.status === "confirmed") {
    actionBtnsHtml += `
      <button type="button" class="action-btn" title="Open Consultation Chat" onclick="window.openConsultationChat(${appointment.appointment_id})" style="color: #2563eb;">
        <span class="material-symbols-outlined">chat</span>
      </button>
    `;
  }

  if (["pending", "confirmed"].includes(appointment.status)) {
    actionBtnsHtml += `
      <button type="button" class="action-btn cancel" title="Cancel Appointment" onclick="window.cancelAppointmentPrompt(${appointment.appointment_id})">
        <span class="material-symbols-outlined">cancel</span>
      </button>
    `;
  }

  row.innerHTML = `
    <td>
        <div class="doctor-cell">
            ${avatar}
            <div>
                <p class="cell-main">${appointment.doctor_name || "Doctor"}</p>
                <p class="cell-sub">ID: #DOC-${appointment.doctor_id}</p>
            </div>
        </div>
    </td>
    <td><span class="cell-main">${appointment.specialization || "General Physician"}</span></td>
    <td>
        <p class="cell-main">${formatDate(appointment.appointment_date)}</p>
        <p class="cell-sub">${formatTime(appointment.appointment_time)} (${appointment.appointment_duration || 30} min)</p>
    </td>
    <td>
        <div class="type-indicator">
            <span class="dot dot-indigo"></span>
            <span>${capitalize(appointment.appointment_type || "consultation")}</span>
            ${followUpTag}
        </div>
    </td>
    <td>${statusBadgeHtml}</td>
    <td>
        <div class="action-btns">
            ${actionBtnsHtml}
        </div>
    </td>
  `;

  return row;
}

function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}
function formatTime(time) {
    const [hours, minutes] = time.split(":");

    const date = new Date();
    date.setHours(hours, minutes);

    return date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: true
    });
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function handleAppointmentActions(action, btn) {
  switch (action) {
    case "new-appointment":
      openBookingModal(null);
      break;

    case "edit":
      editAppointment(btn.dataset.id);
      break;

    case "view":
      viewAppointment(btn.dataset.id);
      break;

    case "appointments-filter":
      handleFilter(btn);
      break;

    default:
      console.warn(`Unknown appointment action: ${action}`);
  }
}

function editAppointment(id) {
  alert(`Edit Appointment ID: ${id}`);
}

function viewAppointment(id) {
  alert(`View Appointment ID: ${id}`);
}

function renderPagination(pagination, type) {
  if (!pagination) return;
  const header = document.getElementById(`${type}-pagination-header`) || document.getElementById("pagination-header");

  const start = pagination.total === 0
    ? 0
    : (pagination.page - 1) * pagination.limit + 1;

  const end = pagination.total === 0
    ? 0
    : Math.min(start + pagination.limit - 1, pagination.total);

  if (header) {
    header.textContent = `Showing ${start} to ${end} of ${pagination.total} ${type === "doctors" ? "doctors" : "appointments"}`;
  }

  const container = document.getElementById(`${type}-pagination-controls`) || document.getElementById("pagination-controls");
  if (!container) return;

  container.innerHTML = `
          <button class="page-nav-btn btn-prev" 
                data-page="${pagination.page - 1}"
                data-type="${type}"
                data-action="pagination"
                ${pagination.page <= 1 ? "disabled" : ""}>
              <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <button class="page-nav-btn btn-next"
                data-page="${pagination.page + 1}"
                data-type="${type}"
                data-action="pagination"
                ${pagination.page >= pagination.totalPages ? "disabled" : ""}>
              <span class="material-symbols-outlined">chevron_right</span>
          </button>
  `;
}

// ================================== doctors Functions ========================

async function initializeDoctors() {
  const cached = APP.cache.doctors;
  if (cached.data && cached.data.doctors) {
    renderDoctorsList(cached.data.doctors);
    renderPagination(cached.data.pagination, "doctors");
  }
  await fetchDoctorsList();
}

async function fetchDoctorsList() {
  const cached = APP.cache.doctors;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });

  try {
    const response = await fetchAPI(
      `/my_doctor/public/patient/api/doctors.php?${params}`
    );
    cached.data = response;
    renderDoctorsList(response.doctors || []);
    renderPagination(response.pagination, "doctors");

    const resultsInfo = document.getElementById("doctors-results-info") || document.querySelector(".results-info");
    if (resultsInfo && response.pagination) {
      resultsInfo.textContent = `Showing ${response.doctors?.length || 0} of ${response.pagination.total} doctors matching your criteria`;
    }
  } catch (error) {
    console.error("Error fetching doctors:", error);
  }
}

function renderDoctorsList(doctors) {
  const container = document.getElementById("doctor-grid");
  if (!container) return;
  
  container.innerHTML = "";

  if (!doctors || doctors.length === 0) {
    container.innerHTML = `
      <div style="grid-column: 1 / -1; text-align: center; padding: 48px; background: var(--surface-container-lowest); border-radius: var(--radius-xl); border: 1px dashed var(--border-subtle); color: var(--on-surface-variant);">
        <span class="material-symbols-outlined" style="font-size: 48px; color: var(--outline); margin-bottom: 8px;">person_search</span>
        <p style="font-size: 16px; font-weight: 500;">No doctors found matching your criteria</p>
      </div>
    `;
    return;
  }

  doctors.forEach((doctor) => {
    const card = createDoctorCard(doctor);
    container.appendChild(card);
  });
}

function createDoctorCard(doctor) {
  const row = document.createElement("div");
  row.classList.add("doctor-card");

  const static_profile_url = `/my_doctor/public/assets/images/doctor-profile-${doctor.gender || 'male'}.png`;
  const profileUrl = doctor.profile_url || static_profile_url;
  
  const avatar = `
    <div class="doctor-header">
        <img
            class="doctor-card-img"
            src="${profileUrl}"
            alt="${doctor.name}"
            onerror="this.src='/my_doctor/public/assets/images/doctor-profile-male.png'">
    </div>
  `;

  const availabilityText = doctor.availability_text || (doctor.slot_date ? `Available ${doctor.slot_date} ${doctor.start_time || ''}` : 'Available by Appointment');

  row.innerHTML = `
    ${avatar}
    <div class="doctor-content">
        <p class="doctor-name">${doctor.name}</p>
        <p class="doctor-specialty">${doctor.specialization || doctor.department || 'Healthcare Professional'}</p>
        <div class="doctor-rating">
            <div class="stars">
                <span class="star">★</span>
                <span class="star">★</span>
                <span class="star">★</span>
                <span class="star">★</span>
                <span class="star">★</span>
            </div>
            <span class="rating-text">${doctor.rating || '4.5'} (${doctor.total_reviews || 0})</span>
        </div>
        <div class="doctor-info">
            <div class="doctor-info-item">
                <span class="material-symbols-outlined">location_on</span>
                <span>${doctor.hospital_name || 'City Care Hospital'}</span>
            </div>
            <div class="doctor-info-item">
                <span class="material-symbols-outlined">schedule</span>
                <span>${availabilityText}</span>
            </div>
            <div class="doctor-info-item">
                <span class="material-symbols-outlined">payments</span>
                <span>₹${doctor.online_fee || doctor.offline_fee || doctor.min_fee || 100} per consultation</span>
            </div>
        </div>
        <div class="doctor-actions">
            <button class="btn btn-primary" data-page="doctors" data-action="book-appointment" data-id="${doctor.user_id}">Book Appointment</button>
            <button class="btn btn-outline" data-page="doctors" data-action="view-doctor" data-id="${doctor.user_id}">View Profile</button>
        </div>
    </div>
  `;

  return row;
}

function handleDoctorActions(action, btn) {

    switch (action) {

        case "book-appointment":
            openBookingModal(btn.dataset.id);
            break;

        case "view-doctor":
            openDoctorProfileModal(btn.dataset.id);
            break;

        case "edit":
            editDoctor(btn.dataset.id);
            break;

        case "view":
            openDoctorProfileModal(btn.dataset.id);
            break;

        case "delete":
            removeDoctor(btn.dataset.id);
            break;
          
        case "doctors-filter":
          handleFilter(btn);
          break;

        default:
          console.warn(`Unknown doctor action: ${action}`);

    }
}





// =====================================================================
// SETTINGS FUNCTIONS & HANDLERS
// =====================================================================

let cachedSettingsData = null;

function initializeSettings() {
    setupSettingsTabs();
    fetchSettingsData();
    setupSettingsForms();
    setupSettingsDeleteModal();
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString) return "N/A";
    const date = new Date(dateTimeString);
    if (isNaN(date.getTime())) return dateTimeString;
    return date.toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric"
    }) + " at " + date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit"
    });
}

// Inner side nav sub-tab switching
function setupSettingsTabs() {
    const menuItems = document.querySelectorAll("#settings-nav-menu .settings-menu-item");
    const panes = {
        profile: document.getElementById("pane-profile"),
        security: document.getElementById("pane-security"),
        notifications: document.getElementById("pane-notifications"),
        privacy: document.getElementById("pane-privacy")
    };

    menuItems.forEach(item => {
        item.addEventListener("click", () => {
            const tab = item.dataset.tab;
            if (!tab) return;

            menuItems.forEach(i => i.classList.remove("active"));
            item.classList.add("active");

            Object.keys(panes).forEach(key => {
                if (panes[key]) {
                    panes[key].style.display = (key === tab) ? "block" : "none";
                }
            });
        });
    });
}

// Fetch all settings data dynamically from DB
async function fetchSettingsData() {
    try {
        const response = await fetchAPI("/my_doctor/public/patient/api/settings.php?action=getSettings");
        cachedSettingsData = response;
        renderSettingsData(response);
    } catch (error) {
        console.error("Failed to load settings:", error);
        showSettingsToast(error.message || "Failed to load settings", false);
    }
}

// Render dynamic fields into inputs
function renderSettingsData(data) {
    if (!data) return;

    // Profile fields
    const nameEl = document.getElementById("setting-name");
    const emailEl = document.getElementById("setting-email");
    const phoneEl = document.getElementById("setting-phone");
    const dobEl = document.getElementById("setting-dob");
    const genderEl = document.getElementById("setting-gender");
    const bloodTypeEl = document.getElementById("setting-blood-type");
    const addressEl = document.getElementById("setting-address");
    const cityEl = document.getElementById("setting-city");
    const stateEl = document.getElementById("setting-state");
    const pinEl = document.getElementById("setting-pin");

    if (nameEl) nameEl.value = data.name || "";
    if (emailEl) emailEl.value = data.email || "";
    if (phoneEl) phoneEl.value = data.phone || "";
    if (dobEl) dobEl.value = data.dob || "";
    if (genderEl && data.gender) genderEl.value = data.gender;
    if (bloodTypeEl && data.blood_type) bloodTypeEl.value = data.blood_type;
    if (addressEl) addressEl.value = data.address || "";
    if (cityEl) cityEl.value = data.city || "";
    if (stateEl) stateEl.value = data.state || "";
    if (pinEl) pinEl.value = data.pin || "";

    // Security & Session fields
    const lastLoginAtEl = document.getElementById("setting-last-login-at");
    const lastLoginIpEl = document.getElementById("setting-last-login-ip");
    const createdAtEl = document.getElementById("setting-created-at");
    const twoFactorToggle = document.getElementById("setting-toggle-2fa");
    const twoFactorDesc = document.getElementById("setting-2fa-desc");

    if (lastLoginAtEl) lastLoginAtEl.textContent = data.last_login_at ? formatDateTime(data.last_login_at) : "Active Now";
    if (lastLoginIpEl) lastLoginIpEl.textContent = data.last_login_ip || "127.0.0.1";
    if (createdAtEl) createdAtEl.textContent = data.created_at ? formatDate(data.created_at) : "Recently";
    if (twoFactorToggle) {
        twoFactorToggle.checked = Boolean(Number(data.two_factor_enabled));
        if (twoFactorDesc) {
            twoFactorDesc.textContent = twoFactorToggle.checked 
                ? "2FA is enabled. Verification required when signing in." 
                : "Protect your health information by requiring an extra verification step";
        }
    }

    // Notification toggles
    const notifAppointments = document.getElementById("setting-notif-appointments");
    const notifMessages = document.getElementById("setting-notif-messages");
    const notifAlerts = document.getElementById("setting-notif-alerts");
    const notifEmail = document.getElementById("setting-notif-email");
    const notifSms = document.getElementById("setting-notif-sms");
    const notifNewsletter = document.getElementById("setting-notif-newsletter");

    if (notifAppointments) notifAppointments.checked = Boolean(Number(data.appointment_reminders ?? 1));
    if (notifMessages) notifMessages.checked = Boolean(Number(data.message_notifications ?? 1));
    if (notifAlerts) notifAlerts.checked = Boolean(Number(data.health_alerts ?? 1));
    if (notifEmail) notifEmail.checked = Boolean(Number(data.email_notifications ?? 1));
    if (notifSms) notifSms.checked = Boolean(Number(data.sms_notifications ?? 0));
    if (notifNewsletter) notifNewsletter.checked = Boolean(Number(data.newsletter ?? 0));

    // Privacy toggles
    const privacyShare = document.getElementById("setting-privacy-share");
    if (privacyShare) privacyShare.checked = Boolean(Number(data.share_health_data ?? 1));
}

// Setup form events & button clicks
function setupSettingsForms() {
    // 1. Profile Form Save & Cancel
    const profileForm = document.getElementById("settings-profile-form");
    const cancelProfileBtn = document.getElementById("btn-cancel-profile");

    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const saveBtn = document.getElementById("btn-save-profile");
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = "Saving...";
            }

            const payload = {
                name: document.getElementById("setting-name")?.value.trim(),
                phone: document.getElementById("setting-phone")?.value.trim(),
                dob: document.getElementById("setting-dob")?.value,
                gender: document.getElementById("setting-gender")?.value,
                blood_type: document.getElementById("setting-blood-type")?.value,
                address: document.getElementById("setting-address")?.value.trim(),
                city: document.getElementById("setting-city")?.value.trim(),
                state: document.getElementById("setting-state")?.value.trim(),
                pin: document.getElementById("setting-pin")?.value.trim()
            };

            try {
                const res = await fetchAPI("/my_doctor/public/patient/api/settings.php?action=updateProfile", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });

                cachedSettingsData = Object.assign({}, cachedSettingsData, payload);
                showSettingsToast("Profile updated successfully!");

                // Refresh topbar avatar and name dynamically
                fetchAndSetupUserProfile();
            } catch (err) {
                console.error("Profile save error:", err);
                showSettingsToast(err.message || "Failed to update profile", false);
            } finally {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = "Save Changes";
                }
            }
        });
    }

    if (cancelProfileBtn) {
        cancelProfileBtn.addEventListener("click", () => {
            if (cachedSettingsData) {
                renderSettingsData(cachedSettingsData);
                showSettingsToast("Changes reverted.", true);
            }
        });
    }

    // 2. Password Form Update & Cancel
    const passwordForm = document.getElementById("settings-password-form");
    const cancelPasswordBtn = document.getElementById("btn-cancel-password");

    if (passwordForm) {
        passwordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById("setting-current-password")?.value;
            const newPassword = document.getElementById("setting-new-password")?.value;
            const confirmPassword = document.getElementById("setting-confirm-password")?.value;

            if (newPassword !== confirmPassword) {
                showSettingsToast("New passwords do not match!", false);
                return;
            }

            if (newPassword.length < 6) {
                showSettingsToast("New password must be at least 6 characters long.", false);
                return;
            }

            const saveBtn = document.getElementById("btn-save-password");
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = "Updating...";
            }

            try {
                await fetchAPI("/my_doctor/public/patient/api/settings.php?action=updatePassword", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ currentPassword, newPassword })
                });

                showSettingsToast("Password updated successfully!");
                passwordForm.reset();
            } catch (err) {
                console.error("Password change error:", err);
                showSettingsToast(err.message || "Failed to change password", false);
            } finally {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = "Update Password";
                }
            }
        });
    }

    if (cancelPasswordBtn) {
        cancelPasswordBtn.addEventListener("click", () => {
            if (passwordForm) passwordForm.reset();
        });
    }

    // 3. 2FA Toggle Interactive handler
    const twoFactorToggle = document.getElementById("setting-toggle-2fa");
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener("change", async () => {
            const enabled = twoFactorToggle.checked ? 1 : 0;
            const desc = document.getElementById("setting-2fa-desc");
            if (desc) {
                desc.textContent = enabled 
                    ? "2FA is enabled. Verification required when signing in." 
                    : "Protect your health information by requiring an extra verification step";
            }

            try {
                await fetchAPI("/my_doctor/public/patient/api/settings.php?action=updatePreferences", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ two_factor_enabled: enabled })
                });
                showSettingsToast(enabled ? "Two-Factor Authentication enabled." : "Two-Factor Authentication disabled.");
            } catch (err) {
                console.error("2FA toggle error:", err);
            }
        });
    }

    // 4. Notifications Save & Cancel
    const saveNotifBtn = document.getElementById("btn-save-notifications");
    const cancelNotifBtn = document.getElementById("btn-cancel-notifications");

    if (saveNotifBtn) {
        saveNotifBtn.addEventListener("click", async () => {
            saveNotifBtn.disabled = true;
            saveNotifBtn.textContent = "Saving...";

            const payload = {
                appointment_reminders: document.getElementById("setting-notif-appointments")?.checked ? 1 : 0,
                message_notifications: document.getElementById("setting-notif-messages")?.checked ? 1 : 0,
                health_alerts: document.getElementById("setting-notif-alerts")?.checked ? 1 : 0,
                email_notifications: document.getElementById("setting-notif-email")?.checked ? 1 : 0,
                sms_notifications: document.getElementById("setting-notif-sms")?.checked ? 1 : 0,
                newsletter: document.getElementById("setting-notif-newsletter")?.checked ? 1 : 0
            };

            try {
                await fetchAPI("/my_doctor/public/patient/api/settings.php?action=updatePreferences", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                cachedSettingsData = Object.assign({}, cachedSettingsData, payload);
                showSettingsToast("Notification preferences saved!");
            } catch (err) {
                console.error("Save notifications error:", err);
                showSettingsToast(err.message || "Failed to save notifications", false);
            } finally {
                saveNotifBtn.disabled = false;
                saveNotifBtn.textContent = "Save Notification Settings";
            }
        });
    }

    if (cancelNotifBtn) {
        cancelNotifBtn.addEventListener("click", () => {
            if (cachedSettingsData) {
                renderSettingsData(cachedSettingsData);
                showSettingsToast("Notification preferences reset.", true);
            }
        });
    }

    // 5. Privacy Save & Cancel
    const savePrivacyBtn = document.getElementById("btn-save-privacy");
    const cancelPrivacyBtn = document.getElementById("btn-cancel-privacy");
    const viewActivityLogsBtn = document.getElementById("btn-view-activity-logs");

    if (savePrivacyBtn) {
        savePrivacyBtn.addEventListener("click", async () => {
            savePrivacyBtn.disabled = true;
            savePrivacyBtn.textContent = "Saving...";

            const payload = {
                share_health_data: document.getElementById("setting-privacy-share")?.checked ? 1 : 0
            };

            try {
                await fetchAPI("/my_doctor/public/patient/api/settings.php?action=updatePreferences", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                cachedSettingsData = Object.assign({}, cachedSettingsData, payload);
                showSettingsToast("Privacy preferences saved!");
            } catch (err) {
                console.error("Save privacy error:", err);
                showSettingsToast(err.message || "Failed to save privacy settings", false);
            } finally {
                savePrivacyBtn.disabled = false;
                savePrivacyBtn.textContent = "Save Privacy Settings";
            }
        });
    }

    if (cancelPrivacyBtn) {
        cancelPrivacyBtn.addEventListener("click", () => {
            if (cachedSettingsData) {
                renderSettingsData(cachedSettingsData);
                showSettingsToast("Privacy settings reset.", true);
            }
        });
    }

    if (viewActivityLogsBtn) {
        viewActivityLogsBtn.addEventListener("click", () => {
            const lastLogin = cachedSettingsData?.last_login_at ? formatDateTime(cachedSettingsData.last_login_at) : "Active now";
            const lastIp = cachedSettingsData?.last_login_ip || "127.0.0.1";
            showSettingsToast(`Last Session: ${lastLogin} from IP ${lastIp}`);
        });
    }
}

// Setup Delete Account Modal & Actions
function setupSettingsDeleteModal() {
    const modal = document.getElementById("delete-account-modal");
    const openBtn = document.getElementById("btn-open-delete-modal");
    const cancelBtn = document.getElementById("btn-cancel-delete");
    const confirmBtn = document.getElementById("btn-confirm-delete");

    if (!modal) return;

    if (openBtn) {
        openBtn.addEventListener("click", () => {
            modal.classList.add("active");
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
            modal.classList.remove("active");
        });
    }

    // Close on overlay backdrop click
    modal.addEventListener("click", (e) => {
        if (e.target === modal) {
            modal.classList.remove("active");
        }
    });

    if (confirmBtn) {
        confirmBtn.addEventListener("click", async () => {
            confirmBtn.disabled = true;
            confirmBtn.textContent = "Deleting Account...";

            try {
                const res = await fetchAPI("/my_doctor/public/patient/api/settings.php?action=deleteAccount", {
                    method: "POST"
                });

                showSettingsToast("Account deleted successfully. Logging out...", true);

                setTimeout(() => {
                    window.location.href = "/my_doctor/public/login_register.php";
                }, 1200);
            } catch (err) {
                console.error("Account delete error:", err);
                showSettingsToast(err.message || "Failed to delete account. Please try again.", false);
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Yes, Delete Account";
            }
        });
    }
}

// Toast helper
let settingsToastTimer = null;
function showSettingsToast(message, isSuccess = true) {
    const toast = document.getElementById("settings-toast");
    const msgEl = document.getElementById("settings-toast-message");
    const iconEl = document.getElementById("settings-toast-icon");

    if (!toast || !msgEl) {
        alert(message);
        return;
    }

    msgEl.textContent = message;
    if (iconEl) {
        iconEl.textContent = isSuccess ? "check_circle" : "error";
    }

    toast.classList.remove("success", "error");
    toast.classList.add(isSuccess ? "success" : "error");
    toast.classList.add("show");

    if (settingsToastTimer) clearTimeout(settingsToastTimer);
    settingsToastTimer = setTimeout(() => {
        toast.classList.remove("show");
    }, 3500);
}

// =====================================================================
// DOCTOR PROFILE MODAL CONTROLLER
// =====================================================================

function openDoctorProfileModal(doctorId) {
    const modal = document.getElementById("modal-doctor-profile");
    const content = document.getElementById("doctor-profile-content");
    const bookBtn = document.getElementById("btn-profile-modal-book");
    if (!modal) return;

    modal.style.display = "flex";
    modal.classList.add("active");
    document.body.classList.add("modal-open");
    document.documentElement.classList.add("modal-open");

    if (bookBtn) {
        bookBtn.dataset.id = doctorId;
    }

    if (content) {
        content.innerHTML = `
            <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                <span class="material-symbols-outlined" style="font-size: 36px; animation: spin 1s linear infinite; color: var(--primary);">sync</span>
                <p style="margin-top: 12px; font-size: 14px; font-weight: 500;">Loading doctor details...</p>
            </div>
        `;
    }

    setupDoctorProfileModalEventListeners();

    fetch(`/my_doctor/public/patient/api/doctors.php?action=getDoctorProfile&doctor_id=${doctorId}`)
        .then(res => res.json())
        .then(json => {
            if (!json.status || !json.data || !json.data.doctor) {
                if (content) {
                    content.innerHTML = `
                        <div style="text-align: center; padding: 40px 20px; color: #dc2626;">
                            <span class="material-symbols-outlined" style="font-size: 40px;">error</span>
                            <p style="margin-top: 8px; font-weight: 600;">${escapeDocHtml(json.message || "Failed to load doctor profile.")}</p>
                        </div>
                    `;
                }
                return;
            }
            renderDoctorProfileContent(json.data.doctor);
        })
        .catch(err => {
            console.error("Doctor profile load error:", err);
            if (content) {
                content.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; color: #dc2626;">
                        <span class="material-symbols-outlined" style="font-size: 40px;">wifi_off</span>
                        <p style="margin-top: 8px; font-weight: 600;">Unable to connect to server. Please try again.</p>
                    </div>
                `;
            }
        });
}

function closeDoctorProfileModal() {
    const modal = document.getElementById("modal-doctor-profile");
    if (modal) {
        modal.style.display = "none";
        modal.classList.remove("active");
    }
    document.body.classList.remove("modal-open");
    document.documentElement.classList.remove("modal-open");
}

function setupDoctorProfileModalEventListeners() {
    const modal = document.getElementById("modal-doctor-profile");
    if (!modal || modal.dataset.listenersAttached) return;
    modal.dataset.listenersAttached = "true";

    document.getElementById("btn-close-doctor-profile")?.addEventListener("click", closeDoctorProfileModal);
    document.getElementById("btn-profile-modal-close")?.addEventListener("click", closeDoctorProfileModal);

    const bookBtn = document.getElementById("btn-profile-modal-book");
    if (bookBtn) {
        bookBtn.addEventListener("click", () => {
            const docId = bookBtn.dataset.id;
            closeDoctorProfileModal();
            if (docId) {
                openBookingModal(docId);
            }
        });
    }
}

function renderDoctorProfileContent(doc) {
    const content = document.getElementById("doctor-profile-content");
    if (!content) return;

    const defaultImg = `/my_doctor/public/assets/images/doctor-profile-${doc.gender || 'male'}.png`;
    const avatarUrl = doc.profile_url || defaultImg;
    const isVerified = (doc.verification_status === 'approved' || doc.verification_status === 'verified');
    const onlineFee = parseFloat(doc.online_fee || 100).toFixed(2);
    const offlineFee = parseFloat(doc.offline_fee || 150).toFixed(2);
    const rating = doc.rating || '4.8';
    const reviews = doc.total_reviews || 0;
    const exp = doc.experience ? `${doc.experience} Years` : '5+ Years';

    // Parse active modes
    const rawModes = (doc.available_mode || "video_call,chat,offline,audio_call").toLowerCase().split(",").map(m => m.trim());
    const modeConfigs = [
        { key: "video_call", label: "Video Call", icon: "videocam", bg: "#eff6ff", border: "#bfdbfe", text: "#1d4ed8" },
        { key: "audio_call", label: "Audio Call", icon: "call", bg: "#eff6ff", border: "#bfdbfe", text: "#1d4ed8" },
        { key: "chat", label: "Instant Chat", icon: "chat", bg: "#eff6ff", border: "#bfdbfe", text: "#1d4ed8" },
        { key: "offline", label: "Clinic Visit", icon: "apartment", bg: "#f0fdf4", border: "#bbf7d0", text: "#166534" }
    ];

    const activeModesHtml = modeConfigs
        .filter(m => rawModes.includes(m.key))
        .map(m => `
            <div style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: ${m.bg}; border: 1px solid ${m.border}; border-radius: 8px; font-size: 12.5px; font-weight: 600; color: ${m.text};">
                <span class="material-symbols-outlined" style="font-size: 17px;">${m.icon}</span>
                <span>${m.label}</span>
            </div>
        `).join("");

    const fullAddress = [doc.address, doc.city, doc.state, doc.pin].filter(Boolean).map(escapeDocHtml).join(", ") || "City Medical Center & Online Care";

    content.innerHTML = `
        <!-- Doctor Top Banner -->
        <div style="display: flex; gap: 20px; align-items: flex-start; padding-bottom: 20px; border-bottom: 1px solid var(--border-subtle); flex-wrap: wrap;">
            <div style="position: relative;">
                <img src="${avatarUrl}" alt="${escapeDocHtml(doc.name)}" onerror="this.src='${defaultImg}'" style="width: 88px; height: 88px; border-radius: 16px; object-fit: cover; border: 2.5px solid #e2e8f0; box-shadow: 0 4px 12px rgba(0,0,0,0.06);">
                ${isVerified ? `
                    <div style="position: absolute; bottom: -4px; right: -4px; background: #2563eb; color: white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; border: 2px solid white;" title="Verified Practitioner">
                        <span class="material-symbols-outlined" style="font-size: 15px;">check</span>
                    </div>
                ` : ''}
            </div>

            <div style="flex: 1; min-width: 260px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;">
                    <h2 style="margin: 0; font-size: 20px; font-weight: 700; color: var(--text-heading);">Dr. ${escapeDocHtml(doc.name)}</h2>
                    ${isVerified ? `
                        <span style="display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #059669; font-size: 11.5px; font-weight: 700; padding: 2.5px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                            <span class="material-symbols-outlined" style="font-size: 14px;">verified</span> Verified Doctor
                        </span>
                    ` : ''}
                </div>

                <div style="font-size: 14px; font-weight: 600; color: #2563eb; margin-bottom: 4px;">
                    ${escapeDocHtml(doc.specialization || 'Specialist')} • ${escapeDocHtml(doc.department || 'Healthcare')}
                </div>

                <div style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 8px;">
                    <span style="font-weight: 600; color: var(--text-heading);">${escapeDocHtml(doc.qualification || 'MBBS')}</span>
                    ${doc.medical_reg_no ? ` • Reg. No: <span style="font-family: monospace; font-weight: 600; color: #475569;">${escapeDocHtml(doc.medical_reg_no)}</span>` : ''}
                </div>

                <div style="display: flex; align-items: center; gap: 12px; font-size: 13px;">
                    <div style="display: flex; align-items: center; gap: 3px; color: #f59e0b; font-weight: 700;">
                        <span>★</span> <span>${rating}</span>
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">(${reviews} reviews)</span>
                    </div>
                    <span style="color: var(--border-subtle);">|</span>
                    <div style="color: #475569; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                        <span class="material-symbols-outlined" style="font-size: 16px; color: #64748b;">work_history</span>
                        ${exp} Experience
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Quick Stats Badges -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin: 20px 0;">
            <div style="padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                <div style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Experience</div>
                <div style="font-size: 18px; font-weight: 800; color: #1e3a8a;">${exp}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Clinical practice</div>
            </div>
            <div style="padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                <div style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Patient Rating</div>
                <div style="font-size: 18px; font-weight: 800; color: #d97706;">★ ${rating}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">${reviews} Verified Reviews</div>
            </div>
            <div style="padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                <div style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Online Fee</div>
                <div style="font-size: 18px; font-weight: 800; color: #16a34a;">₹${onlineFee}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Video / Chat / Audio</div>
            </div>
            <div style="padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px;">
                <div style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">In-Clinic Fee</div>
                <div style="font-size: 18px; font-weight: 800; color: #0284c7;">₹${offlineFee}</div>
                <div style="font-size: 11px; color: #64748b; margin-top: 2px;">Offline Appointment</div>
            </div>
        </div>

        <!-- About Doctor -->
        <div style="margin-bottom: 22px;">
            <h4 style="margin: 0 0 8px 0; font-size: 15px; font-weight: 700; color: var(--text-heading); display: flex; align-items: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px; color: #2563eb;">description</span>
                About Doctor
            </h4>
            <div style="font-size: 13.5px; line-height: 1.6; color: #334155; background: #f8fafc; padding: 14px 18px; border-radius: 10px; border: 1px solid #e2e8f0;">
                ${doc.about ? escapeDocHtml(doc.about) : `Dr. ${escapeDocHtml(doc.name)} is an accomplished ${escapeDocHtml(doc.specialization || 'medical professional')} with ${exp} of dedicated experience in diagnosing, consulting, and delivering patient-focused medical care.`}
            </div>
        </div>

        <!-- Consultation Modes Offered -->
        <div style="margin-bottom: 22px;">
            <h4 style="margin: 0 0 8px 0; font-size: 15px; font-weight: 700; color: var(--text-heading); display: flex; align-items: center; gap: 6px;">
                <span class="material-symbols-outlined" style="font-size: 18px; color: #2563eb;">medical_services</span>
                Consultation Modes Offered
            </h4>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                ${activeModesHtml || '<div style="font-size: 13px; color: var(--text-muted);">Consultation options configured per appointment.</div>'}
            </div>
        </div>

        <!-- Practice & Working Schedule -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 12px;">
            <div style="padding: 14px 18px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-outlined" style="font-size: 16px; color: #2563eb;">schedule</span> Working Hours
                </div>
                <div style="font-size: 13.5px; font-weight: 600; color: var(--text-heading);">
                    ${escapeDocHtml(doc.working_time || 'Mon - Sat (09:00 AM - 05:00 PM)')}
                </div>
            </div>

            <div style="padding: 14px 18px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                <div style="font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 4px;">
                    <span class="material-symbols-outlined" style="font-size: 16px; color: #2563eb;">location_on</span> Clinic / Location
                </div>
                <div style="font-size: 13.5px; font-weight: 600; color: var(--text-heading);">
                    ${fullAddress}
                </div>
            </div>
        </div>
    `;
}

// =========================================================================
// PATIENT BOOK APPOINTMENT MODAL & SLOT CALCULATION CONTROLLER
// =========================================================================

let currentBookingDoctor = null;
let currentDoctorScheduleStatus = {};
let selectedSlotData = null;

function closeBookingModal() {
    const modal = document.getElementById("book-appointment-modal");
    if (modal) {
        modal.style.display = "none";
        modal.classList.remove("active");
        modal.style.opacity = "";
        modal.style.pointerEvents = "";
    }
    document.body.classList.remove("modal-open");
    document.documentElement.classList.remove("modal-open");
    switchBookingStep(1);
    currentBookingDoctor = null;
    selectedSlotData = null;
}

function switchBookingStep(step) {
    const pane1 = document.getElementById("booking-step-1");
    const pane2 = document.getElementById("booking-step-2");
    const badge1 = document.getElementById("booking-stepper-1");
    const badge2 = document.getElementById("booking-stepper-2");
    const circle2 = document.getElementById("step-badge-circle-2");

    if (step === 1) {
        if (pane1) pane1.style.display = "block";
        if (pane2) pane2.style.display = "none";
        if (badge1) badge1.style.color = "var(--primary)";
        if (badge2 && circle2) {
            badge2.style.color = "var(--on-surface-variant)";
            circle2.style.background = "var(--surface-container-high)";
            circle2.style.color = "var(--on-surface-variant)";
        }
    } else {
        if (pane1) pane1.style.display = "none";
        if (pane2) pane2.style.display = "block";
        if (badge2 && circle2) {
            badge2.style.color = "var(--primary)";
            circle2.style.background = "var(--primary)";
            circle2.style.color = "white";
        }
        updateBookingSummary();
    }
}

async function openBookingModal(doctorId = null) {
    const modal = document.getElementById("book-appointment-modal");
    if (!modal) return;

    modal.style.display = "flex";
    modal.classList.add("active");
    modal.style.opacity = "1";
    modal.style.pointerEvents = "auto";
    document.body.classList.add("modal-open");
    document.documentElement.classList.add("modal-open");
    switchBookingStep(1);
    setupBookingModalEventListeners();

    // Reset initial values
    document.getElementById("selected-slot-time").value = "";
    document.getElementById("booking-appointment-date").value = "";
    document.getElementById("booking-slots-container").innerHTML = `<div style="grid-column: 1 / -1; padding: 16px; color: var(--on-surface-variant); font-size: 13px;">Loading doctor availability & schedule...</div>`;
    document.getElementById("slots-count-badge").textContent = "";
    document.getElementById("booking-date-status-badge").style.display = "none";
    document.getElementById("booking-date-reason").textContent = "Select a date to check doctor availability and schedule exceptions";

    const selectorContainer = document.getElementById("modal-doc-selector-container");
    const docSelect = document.getElementById("modal-doctor-select");

    if (!doctorId) {
        if (selectorContainer) selectorContainer.style.display = "block";
        try {
            const listRes = await fetch("/my_doctor/public/patient/api/doctors.php?action=table&limit=100");
            const listJson = await listRes.json();
            const docs = listJson?.data?.doctors || listJson?.data?.table || [];
            if (docs.length > 0 && docSelect) {
                docSelect.innerHTML = docs.map(d => `<option value="${d.user_id}">Dr. ${d.name} (${d.specialization || d.department || 'Doctor'})</option>`).join("");
                doctorId = docs[0].user_id;
                docSelect.value = doctorId;
            } else {
                alert("No active doctors found at this time.");
                closeBookingModal();
                return;
            }
        } catch (e) {
            console.error("Failed to load doctor list for selector:", e);
        }
    } else {
        if (selectorContainer) selectorContainer.style.display = "none";
    }

    if (!doctorId) {
        alert("Please select a doctor to book an appointment.");
        closeBookingModal();
        return;
    }

    await loadDoctorBookingData(doctorId);
}

async function loadDoctorBookingData(doctorId) {
    document.getElementById("selected-slot-time").value = "";
    document.getElementById("booking-appointment-date").value = "";
    document.getElementById("booking-slots-container").innerHTML = `<div style="grid-column: 1 / -1; padding: 16px; color: var(--on-surface-variant); font-size: 13px;">Loading doctor availability & schedule...</div>`;
    document.getElementById("slots-count-badge").textContent = "";
    document.getElementById("booking-date-status-badge").style.display = "none";
    document.getElementById("booking-date-reason").textContent = "Select a date to check doctor availability and schedule exceptions";

    try {
        const res = await fetch(`/my_doctor/public/patient/api/doctors.php?action=getDoctorBookingInfo&doctor_id=${doctorId}`);
        const json = await res.json();
        if (!json.status || !json.data) {
            alert(json.message || "Failed to load doctor booking info.");
            closeBookingModal();
            return;
        }

        const doc = json.data.doctor;
        const pat = json.data.patient || {};
        currentBookingDoctor = doc;
        currentDoctorScheduleStatus = doc.schedule_status || {};

        // Banner info
        document.getElementById("modal-doc-header-name").textContent = `Book Appointment with Dr. ${doc.name}`;
        document.getElementById("modal-doc-header-specialty").textContent = `${doc.specialization} • ${doc.hospital_name || doc.department || 'Hospital'}`;
        document.getElementById("modal-doc-card-name").textContent = `Dr. ${doc.name}`;
        document.getElementById("modal-doc-card-info").textContent = `${doc.specialization} • ${doc.hospital_name || doc.department || 'Hospital'}`;
        document.getElementById("modal-doc-rating").textContent = doc.rating;
        document.getElementById("modal-doc-reviews").textContent = `(${doc.total_reviews} reviews)`;

        const staticProfile = `/my_doctor/public/assets/images/doctor-profile-${doc.gender || 'male'}.png`;
        const avatarEl = document.getElementById("modal-doc-avatar");
        if (avatarEl) {
            avatarEl.src = doc.profile_url || staticProfile;
            avatarEl.onerror = () => { avatarEl.src = staticProfile; };
        }

        // Render Mode Selection cards with mode-specific prices
        renderModeOptions(doc);

        // Date Picker initialization: min today, max today + 60 days
        const dateInput = document.getElementById("booking-appointment-date");
        const today = new Date();
        const yyyy = today.getFullYear();
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const dd = String(today.getDate()).padStart(2, '0');
        const minStr = `${yyyy}-${mm}-${dd}`;

        const maxD = new Date();
        maxD.setDate(today.getDate() + 60);
        const maxY = maxD.getFullYear();
        const maxM = String(maxD.getMonth() + 1).padStart(2, '0');
        const maxDd = String(maxD.getDate()).padStart(2, '0');
        const maxStr = `${maxY}-${maxM}-${maxDd}`;

        dateInput.min = minStr;
        dateInput.max = maxStr;

        // Auto-select first available date in next 14 days
        let firstAvailDate = null;
        for (let i = 0; i <= 14; i++) {
            const d = new Date();
            d.setDate(today.getDate() + i);
            const dStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
            if (currentDoctorScheduleStatus[dStr]?.status === 'available') {
                firstAvailDate = dStr;
                break;
            }
        }

        if (firstAvailDate) {
            dateInput.value = firstAvailDate;
            handleBookingDateChange(firstAvailDate);
        } else {
            dateInput.value = minStr;
            handleBookingDateChange(minStr);
        }

        // Pre-fill patient details for Step 2
        if (pat) {
            const pName = document.getElementById("booking-patient-name");
            const pEmail = document.getElementById("booking-patient-email");
            const pPhone = document.getElementById("booking-patient-phone");
            const pGender = document.getElementById("booking-patient-gender");
            const pDob = document.getElementById("booking-patient-dob");

            if (pName && pat.name) pName.value = pat.name;
            if (pEmail && pat.email) pEmail.value = pat.email;
            if (pPhone && pat.phone) pPhone.value = pat.phone;
            if (pGender && pat.gender) pGender.value = pat.gender;
            if (pDob && pat.dob) pDob.value = pat.dob;
        }

    } catch (e) {
        console.error("Booking modal init error:", e);
        closeBookingModal();
    }
}

function renderModeOptions(doc) {
    const modesGrid = document.getElementById("booking-modes-grid");
    if (!modesGrid) return;

    modesGrid.style.display = "block";

    // Doctor's active available modes from database
    const rawModes = (doc.available_mode || "video_call,chat,offline,audio_call").toLowerCase();
    const docActiveModes = rawModes.split(",").map(m => m.trim());

    // Single unified online fee and offline fee
    const onlineFeeVal = parseFloat(doc.online_fee || 100).toFixed(2);
    const offlineFeeVal = parseFloat(doc.offline_fee || 150).toFixed(2);

    // Online sub-modes definition (all share the same online_fee)
    const onlineModes = [
        { key: "video_call", label: "Video Call", icon: "videocam", fee: doc.online_fee || 100 },
        { key: "chat", label: "Chat Session", icon: "chat", fee: doc.online_fee || 100 },
        { key: "audio_call", label: "Audio Call", icon: "call", fee: doc.online_fee || 100 }
    ];

    const hasOffline = docActiveModes.includes("offline");
    const hasAnyOnline = onlineModes.some(m => docActiveModes.includes(m.key));

    // Initial state: Start with Offline selected (or online if offline not offered)
    const initialType = hasOffline ? "offline" : (hasAnyOnline ? "online" : "offline");

    // First offered online mode (to select when online is picked)
    const firstOnlineMode = onlineModes.find(m => docActiveModes.includes(m.key)) || onlineModes[0];
    let selectedOnlineKey = firstOnlineMode.key;

    // Build the HTML with 2 compact radio buttons + online subgrid
    modesGrid.innerHTML = `
        <!-- Two Radio Options: Offline & Online -->
        <div style="display: flex; gap: 20px; align-items: center; margin-bottom: 12px; padding: 10px 14px; background: var(--surface-container-low, #f8f9fa); border-radius: 10px; border: 1px solid var(--border-subtle, #e5e7eb); flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: ${hasOffline ? 'pointer' : 'not-allowed'}; font-size: 13.5px; font-weight: 600; color: var(--text-heading); margin: 0; user-select: none; opacity: ${hasOffline ? '1' : '0.5'};">
                <input type="radio" name="booking_consultation_type" value="offline" ${initialType === 'offline' ? 'checked' : ''} ${!hasOffline ? 'disabled' : ''} style="width: 17px; height: 17px; accent-color: var(--primary); cursor: pointer;" />
                <span class="material-symbols-outlined" style="font-size: 18px; color: var(--on-surface-variant); vertical-align: middle;">apartment</span>
                <span>Clinic Visit (Offline)</span>
                <span style="font-size: 12px; font-weight: 700; color: var(--primary); background: white; padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border-subtle); margin-left: 2px;">
                    ${hasOffline ? '₹' + offlineFeeVal : 'Not Offered'}
                </span>
            </label>

            <label style="display: flex; align-items: center; gap: 8px; cursor: ${hasAnyOnline ? 'pointer' : 'not-allowed'}; font-size: 13.5px; font-weight: 600; color: var(--text-heading); margin: 0; user-select: none; opacity: ${hasAnyOnline ? '1' : '0.5'};">
                <input type="radio" name="booking_consultation_type" value="online" ${initialType === 'online' ? 'checked' : ''} ${!hasAnyOnline ? 'disabled' : ''} style="width: 17px; height: 17px; accent-color: var(--primary); cursor: pointer;" />
                <span class="material-symbols-outlined" style="font-size: 18px; color: var(--on-surface-variant); vertical-align: middle;">language</span>
                <span>Online Consultation</span>
                <span style="font-size: 12px; font-weight: 700; color: var(--primary); background: white; padding: 2px 8px; border-radius: 6px; border: 1px solid var(--border-subtle); margin-left: 2px;">
                    ${hasAnyOnline ? '₹' + onlineFeeVal : 'Not Offered'}
                </span>
            </label>
        </div>

        <!-- Online Sub-Options (Video, Chat, Audio) - All with same online fee, Hidden when offline, Shown when online -->
        <div id="online-modes-subgrid" style="display: ${initialType === 'online' ? 'grid' : 'none'}; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-top: 10px;">
            ${onlineModes.map(m => {
                const isOffered = docActiveModes.includes(m.key);
                const isSelected = (m.key === selectedOnlineKey && isOffered);
                return `
                    <div class="booking-online-subcard ${isSelected ? 'active' : ''} ${!isOffered ? 'disabled' : ''}" 
                         data-mode="${m.key}" data-fee="${m.fee}" style="
                        border: 2px solid ${isSelected ? 'var(--primary)' : 'var(--border-subtle)'};
                        background: ${isSelected ? 'rgba(0, 82, 204, 0.04)' : 'white'};
                        border-radius: 10px;
                        padding: 12px 10px;
                        cursor: ${isOffered ? 'pointer' : 'not-allowed'};
                        text-align: center;
                        transition: all 0.2s ease;
                        opacity: ${isOffered ? '1' : '0.45'};
                        user-select: ${isOffered ? 'auto' : 'none'};
                    ">
                        <span class="material-symbols-outlined" style="font-size: 24px; color: ${isSelected ? 'var(--primary)' : 'var(--on-surface-variant)'};">${m.icon}</span>
                        <div style="font-weight: 700; font-size: 13px; margin: 3px 0 2px 0; color: var(--text-heading);">${m.label}</div>
                        <div style="font-size: 13px; font-weight: 800; color: var(--primary);">₹${onlineFeeVal}</div>
                        ${!isOffered ? '<div style="font-size: 10px; font-weight: 700; color: #e74c3c; margin-top: 2px;">Not Offered</div>' : ''}
                    </div>
                `;
            }).join("")}
        </div>
    `;

    const subGrid = document.getElementById("online-modes-subgrid");

    // Apply mode selection
    function applySelection(mode, fee) {
        document.getElementById("selected-booking-mode").value = mode;
        document.getElementById("selected-booking-fee").value = fee;
        updateBookingSummary();
    }

    // Initialize inputs based on initialType
    if (initialType === "offline") {
        applySelection("offline", doc.offline_fee || 150);
    } else {
        applySelection(firstOnlineMode.key, doc.online_fee || 100);
    }

    // Radio change events
    const radios = modesGrid.querySelectorAll('input[name="booking_consultation_type"]');
    radios.forEach(radio => {
        radio.addEventListener("change", (e) => {
            if (e.target.value === "offline") {
                if (subGrid) subGrid.style.display = "none";
                applySelection("offline", doc.offline_fee || 150);
            } else if (e.target.value === "online") {
                if (subGrid) subGrid.style.display = "grid";
                // Select currently active online card or first offered
                const activeCard = subGrid ? subGrid.querySelector(".booking-online-subcard.active:not(.disabled)") : null;
                if (activeCard) {
                    applySelection(activeCard.dataset.mode, doc.online_fee || 100);
                } else {
                    const firstOfferedCard = subGrid ? subGrid.querySelector(".booking-online-subcard:not(.disabled)") : null;
                    if (firstOfferedCard) {
                        selectOnlineCard(firstOfferedCard);
                    }
                }
            }
        });
    });

    // Subcard click events
    function selectOnlineCard(card) {
        if (!subGrid) return;
        subGrid.querySelectorAll(".booking-online-subcard:not(.disabled)").forEach(c => {
            c.classList.remove("active");
            c.style.borderColor = "var(--border-subtle)";
            c.style.background = "white";
            const icon = c.querySelector(".material-symbols-outlined");
            if (icon) icon.style.color = "var(--on-surface-variant)";
        });
        card.classList.add("active");
        card.style.borderColor = "var(--primary)";
        card.style.background = "rgba(0, 82, 204, 0.04)";
        const icon = card.querySelector(".material-symbols-outlined");
        if (icon) icon.style.color = "var(--primary)";

        selectedOnlineKey = card.dataset.mode;
        applySelection(card.dataset.mode, doc.online_fee || 100);
    }

    if (subGrid) {
        subGrid.querySelectorAll(".booking-online-subcard:not(.disabled)").forEach(card => {
            card.addEventListener("click", () => selectOnlineCard(card));
        });
    }
}

async function handleBookingDateChange(selectedDate) {
    if (!currentBookingDoctor || !selectedDate) return;

    const slotsContainer = document.getElementById("booking-slots-container");
    const countBadge = document.getElementById("slots-count-badge");
    const statusBadge = document.getElementById("booking-date-status-badge");
    const reasonText = document.getElementById("booking-date-reason");

    document.getElementById("selected-slot-time").value = "";
    selectedSlotData = null;
    updateBookingSummary();

    const dayStatus = currentDoctorScheduleStatus[selectedDate];

    if (dayStatus && dayStatus.status === 'blocked') {
        statusBadge.style.display = "inline-block";
        statusBadge.style.background = "rgba(231, 76, 60, 0.12)";
        statusBadge.style.color = "#e74c3c";
        statusBadge.textContent = "Unavailable";

        reasonText.textContent = dayStatus.reason ? `Notice: ${dayStatus.reason}` : "Doctor is not taking appointments on this date.";
        reasonText.style.color = "#e74c3c";

        slotsContainer.innerHTML = `
            <div style="grid-column: 1 / -1; padding: 20px; background: rgba(231, 76, 60, 0.06); border-radius: 8px; color: #c0392b; font-size: 13px; text-align: center;">
                <span class="material-symbols-outlined" style="font-size: 28px; vertical-align: middle; margin-bottom: 4px;">event_busy</span>
                <div><strong>No slots available.</strong> ${dayStatus.reason || 'Doctor on leave.'}</div>
                <div style="font-size: 12px; margin-top: 4px; color: var(--on-surface-variant);">Please choose another active calendar date.</div>
            </div>
        `;
        countBadge.textContent = "0 slots";
        return;
    }

    // Date is marked available: fetch dynamic slots from backend
    statusBadge.style.display = "inline-block";
    statusBadge.style.background = "rgba(40, 167, 69, 0.12)";
    statusBadge.style.color = "#28a745";
    statusBadge.textContent = "Available";

    reasonText.textContent = dayStatus?.reason ? dayStatus.reason : "Slots calculated with exceptions & baseline schedule";
    reasonText.style.color = "var(--on-surface-variant)";

    slotsContainer.innerHTML = `<div style="grid-column: 1 / -1; padding: 16px; color: var(--on-surface-variant); font-size: 13px;">Loading time slots...</div>`;

    try {
        const res = await fetch(`/my_doctor/public/patient/api/doctors.php?action=getDoctorDateSlots&doctor_id=${currentBookingDoctor.user_id}&date=${selectedDate}`);
        const json = await res.json();

        if (!json.status || !json.data || json.data.is_blocked || (json.data.slots || []).length === 0) {
            const blockReason = json.data?.reason || "No available slots remaining on this day.";
            statusBadge.style.background = "rgba(231, 76, 60, 0.12)";
            statusBadge.style.color = "#e74c3c";
            statusBadge.textContent = "Fully Booked / Blocked";
            reasonText.textContent = blockReason;

            slotsContainer.innerHTML = `
                <div style="grid-column: 1 / -1; padding: 20px; background: var(--surface-container-low); border-radius: 8px; color: var(--on-surface-variant); font-size: 13px; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 28px; margin-bottom: 4px;">schedule</span>
                    <div>${blockReason}</div>
                </div>
            `;
            countBadge.textContent = "0 slots";
            return;
        }

        let slots = json.data.slots || [];

        // Filter past time slots if selectedDate is today on user's local clock
        const clientNow = new Date();
        const clientTodayStr = `${clientNow.getFullYear()}-${String(clientNow.getMonth() + 1).padStart(2, '0')}-${String(clientNow.getDate()).padStart(2, '0')}`;
        if (selectedDate === clientTodayStr) {
            const currentTotalMin = clientNow.getHours() * 60 + clientNow.getMinutes();
            slots = slots.filter(s => {
                const parts = (s.start_time || '').split(':').map(Number);
                const slotTotalMin = (parts[0] || 0) * 60 + (parts[1] || 0);
                return slotTotalMin > currentTotalMin;
            });
        }

        if (slots.length === 0) {
            statusBadge.style.background = "rgba(231, 76, 60, 0.12)";
            statusBadge.style.color = "#e74c3c";
            statusBadge.textContent = "All Slots Passed";
            reasonText.textContent = "No upcoming time slots remaining for today. Please select a future date.";

            slotsContainer.innerHTML = `
                <div style="grid-column: 1 / -1; padding: 20px; background: var(--surface-container-low); border-radius: 8px; color: var(--on-surface-variant); font-size: 13px; text-align: center;">
                    <span class="material-symbols-outlined" style="font-size: 28px; margin-bottom: 4px;">schedule</span>
                    <div>No upcoming time slots remaining for today.</div>
                    <div style="font-size: 12px; margin-top: 4px; color: var(--on-surface-variant);">All consultation hours for today have already passed. Please select an upcoming calendar date.</div>
                </div>
            `;
            countBadge.textContent = "0 slots";
            return;
        }

        countBadge.textContent = `${slots.length} slots available`;

        slotsContainer.innerHTML = slots.map(s => {
            return `
                <button type="button" class="booking-slot-chip" data-time="${s.start_time}" data-display="${s.display_slot}" style="
                    padding: 8px 12px;
                    border: 1px solid var(--border-subtle);
                    background: white;
                    border-radius: 8px;
                    font-size: 13px;
                    font-weight: 500;
                    cursor: pointer;
                    color: var(--text-heading);
                    transition: all 0.15s ease;
                    text-align: center;
                ">
                    ${s.display_time}
                </button>
            `;
        }).join("");

        slotsContainer.querySelectorAll(".booking-slot-chip").forEach(chip => {
            chip.addEventListener("click", () => {
                slotsContainer.querySelectorAll(".booking-slot-chip").forEach(c => {
                    c.classList.remove("active");
                    c.style.background = "white";
                    c.style.color = "var(--text-heading)";
                    c.style.borderColor = "var(--border-subtle)";
                });
                chip.classList.add("active");
                chip.style.background = "var(--primary)";
                chip.style.color = "white";
                chip.style.borderColor = "var(--primary)";

                document.getElementById("selected-slot-time").value = chip.dataset.time;
                selectedSlotData = {
                    time: chip.dataset.time,
                    display: chip.dataset.display,
                    date: selectedDate
                };
                updateBookingSummary();
            });
        });

    } catch (err) {
        console.error("Error fetching slots:", err);
        slotsContainer.innerHTML = `<div style="color: #e74c3c; font-size: 13px;">Error loading slots. Please try again.</div>`;
    }
}

function updateBookingSummary() {
    const modeVal = document.getElementById("selected-booking-mode")?.value || "video_call";
    const feeVal = document.getElementById("selected-booking-fee")?.value || "100.00";
    const slotTime = document.getElementById("selected-slot-time")?.value;
    const dateVal = document.getElementById("booking-appointment-date")?.value;

    const modeLabels = {
        video_call: "Video Call Consultation",
        chat: "Chat Session",
        offline: "In-Person Clinic Visit",
        audio_call: "Audio Call Consultation"
    };

    const modeTextEl = document.getElementById("summary-mode-text");
    const dtTextEl = document.getElementById("summary-datetime-text");
    const feeTextEl = document.getElementById("summary-fee-text");

    if (modeTextEl) modeTextEl.textContent = modeLabels[modeVal] || modeVal;
    if (feeTextEl) feeTextEl.textContent = `Fee: ₹${parseFloat(feeVal).toFixed(2)}`;

    if (dtTextEl) {
        if (dateVal && slotTime) {
            const dateObj = new Date(dateVal + "T00:00:00");
            const dateFmt = dateObj.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
            const timeParts = slotTime.split(":");
            let h = parseInt(timeParts[0], 10);
            const m = timeParts[1] || "00";
            const ampm = h >= 12 ? "PM" : "AM";
            h = h % 12;
            if (h === 0) h = 12;
            dtTextEl.textContent = `${dateFmt} at ${h}:${m} ${ampm}`;
        } else if (dateVal) {
            dtTextEl.textContent = `${dateVal} (Select a time slot)`;
        } else {
            dtTextEl.textContent = "Please select date & slot";
        }
    }
}

function setupBookingModalEventListeners() {
    const modal = document.getElementById("book-appointment-modal");
    if (!modal || modal.dataset.listenersAttached) return;
    modal.dataset.listenersAttached = "true";

    // Modal outside click disabled - dismiss only via top-right close (x) button and cancel buttons

    // Date change
    const dateInput = document.getElementById("booking-appointment-date");
    if (dateInput) {
        dateInput.addEventListener("change", (e) => {
            handleBookingDateChange(e.target.value);
        });
    }

    // Step 1 -> Step 2
    const btnGotoStep2 = document.getElementById("btn-goto-step-2");
    if (btnGotoStep2) {
        btnGotoStep2.addEventListener("click", () => {
            const dateVal = document.getElementById("booking-appointment-date")?.value;
            const slotVal = document.getElementById("selected-slot-time")?.value;

            if (!dateVal) {
                alert("Please select an appointment date first.");
                document.getElementById("booking-appointment-date")?.focus();
                return;
            }

            if (!slotVal) {
                alert("Please click and select an available time slot before continuing.");
                return;
            }

            switchBookingStep(2);
        });
    }

    // Step 2 -> Step 1
    const btnBacktoStep1 = document.getElementById("btn-backto-step-1");
    if (btnBacktoStep1) {
        btnBacktoStep1.addEventListener("click", () => {
            switchBookingStep(1);
        });
    }

    // Close buttons
    document.getElementById("btn-close-booking-modal")?.addEventListener("click", closeBookingModal);
    document.getElementById("btn-cancel-booking-step1")?.addEventListener("click", closeBookingModal);
    document.getElementById("btn-cancel-booking-all")?.addEventListener("click", closeBookingModal);

    // Doctor selector change
    const docSelect = document.getElementById("modal-doctor-select");
    if (docSelect) {
        docSelect.addEventListener("change", (e) => {
            const chosenDocId = e.target.value;
            if (chosenDocId) {
                loadDoctorBookingData(chosenDocId);
            }
        });
    }

    // Stepper tabs direct click
    document.getElementById("booking-stepper-1")?.addEventListener("click", () => {
        switchBookingStep(1);
    });

    
    // Payment Method cards selection
    const pMethodCards = document.querySelectorAll("#booking-payment-methods-grid .payment-method-card");
    pMethodCards.forEach(card => {
        card.addEventListener("click", () => {
            pMethodCards.forEach(c => {
                c.classList.remove("active");
                c.style.borderColor = "var(--border-subtle)";
                c.style.background = "white";
            });
            card.classList.add("active");
            card.style.borderColor = "var(--primary)";
            card.style.background = "rgba(0, 82, 204, 0.04)";
            const radio = card.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;
        });
    });

    // Final Submit Booking Button
    const btnSubmit = document.getElementById("btn-submit-booking") || document.getElementById("btn-review-booking");
    if (btnSubmit) {
        btnSubmit.addEventListener("click", async () => {
            if (!currentBookingDoctor) {
                alert("Doctor details are missing. Please re-open the booking window.");
                return;
            }

            const pName = document.getElementById("booking-patient-name")?.value.trim();
            const pPhone = document.getElementById("booking-patient-phone")?.value.trim();
            const pEmail = document.getElementById("booking-patient-email")?.value.trim();
            const pReason = document.getElementById("booking-patient-reason")?.value.trim();
            const pNotes = document.getElementById("booking-patient-notes")?.value.trim();
            const pGender = document.getElementById("booking-patient-gender")?.value || 'male';
            const pDob = document.getElementById("booking-patient-dob")?.value || '';
            const apptType = document.getElementById("booking-appointment-type")?.value || 'consultation';

            const mode = document.getElementById("selected-booking-mode")?.value || 'video_call';
            const fee = document.getElementById("selected-booking-fee")?.value || '0.00';
            const slot = document.getElementById("selected-slot-time")?.value;
            const date = document.getElementById("booking-appointment-date")?.value;

            // Selected payment method radio
            const selectedPayRadio = document.querySelector('input[name="booking_payment_method"]:checked');
            const payMethod = selectedPayRadio ? selectedPayRadio.value : 'upi';

            if (!date) {
                alert("Please select an appointment date.");
                switchBookingStep(1);
                return;
            }
            if (!slot) {
                alert("Please click and select an available time slot before submitting.");
                switchBookingStep(1);
                return;
            }
            if (!pName) {
                alert("Please enter the patient's full name.");
                document.getElementById("booking-patient-name")?.focus();
                return;
            }
            if (!pPhone) {
                alert("Please enter a contact phone number.");
                document.getElementById("booking-patient-phone")?.focus();
                return;
            }
            if (!pEmail) {
                alert("Please enter a valid email address.");
                document.getElementById("booking-patient-email")?.focus();
                return;
            }
            if (!pReason) {
                alert("Please enter the reason for visit or primary symptoms.");
                document.getElementById("booking-patient-reason")?.focus();
                return;
            }

            // Button loading state
            const origHtml = btnSubmit.innerHTML;
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = `<span class="material-symbols-outlined" style="font-size: 18px; vertical-align: middle; animation: spin 1s linear infinite;">sync</span> Booking Appointment...`;

            try {
                console.log("Booking payload:", { doctor_id: currentBookingDoctor.user_id, date, slot, mode, fee, payMethod, apptType });
                const response = await fetch("/my_doctor/public/patient/api/appointments.php?action=bookAppointment", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        action: "bookAppointment",
                        doctor_id: currentBookingDoctor.user_id,
                        appointment_date: date,
                        appointment_time: slot,
                        appointment_duration: 30,
                        appointment_type: apptType,
                        appointment_mode: mode,
                        appointment_charge: fee,
                        payment_method: payMethod,
                        problem_description: pReason,
                        notes: pNotes,
                        patient_name: pName,
                        patient_phone: pPhone,
                        patient_email: pEmail,
                        patient_gender: pGender,
                        patient_dob: pDob
                    })
                });

                const result = await response.json();

                if (!result.status) {
                    alert(result.message || "Failed to book appointment. Please check availability and try again.");
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = origHtml;
                    return;
                }

                // Booking successful!
                const data = result.data || {};
                const txnInfo = data.transaction_id ? `\nTransaction ID: ${data.transaction_id}` : '';
                alert(`🎉 Appointment Booked Successfully!\n\n` +
                      `Appointment ID: #APPT-${data.appointment_id}\n` +
                      `Doctor: Dr. ${data.doctor_name || currentBookingDoctor.name}\n` +
                      `Date & Time: ${data.appointment_date} at ${data.appointment_time}\n` +
                      `Status: ${data.status ? data.status.toUpperCase() : 'CONFIRMED'}\n` +
                      `Payment: ${data.payment_status ? data.payment_status.toUpperCase() : 'PAID'} (${(data.payment_method || payMethod).toUpperCase()})${txnInfo}\n\n` +
                      `Your consultation has been confirmed. Thank you!`);

                // Reset and close modal
                closeBookingModal();

                // Refresh appointments list if appointments tab is initialized/cached
                if (window.APP && window.APP.cache && window.APP.cache.appointments) {
                    window.APP.cache.appointments.data = null;
                }
                if (typeof fetchAppointmentsTable === "function") {
                    fetchAppointmentsTable();
                }

            } catch (err) {
                console.error("Booking error:", err);
                alert("A network error occurred while booking the appointment. Please try again.");
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = origHtml;
            }
        });
    }
}


function escapeDocHtml(str) {
    if (!str) return "";
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// ============== NOTIFICATIONS SYSTEM (BLUE DOT + 70% MODAL) ==============
function initNotificationSystem(role) {
    const roleName = role || "doctor";
    const notifBtn = document.getElementById("topbar-notif-btn");
    const blueDot = document.getElementById("notif-badge-dot");
    const modal = document.getElementById("modal-notifications");
    const closeBtn = document.getElementById("btn-close-notif-modal");
    const markAllBtn = document.getElementById("btn-mark-all-read");
    const notifList = document.getElementById("modal-notif-list");
    const subtitle = document.getElementById("modal-notif-subtitle");

    if (!notifBtn || !modal) return;

    const apiBase = (roleName === "doctor") 
        ? "/my_doctor/public/doctor/api/notifications.php" 
        : "/my_doctor/public/patient/api/notifications.php";

    // 1. Check unread count (updates blue dot)
    async function checkUnreadCount() {
        try {
            const res = await fetch(apiBase + "?action=unread_count");
            const json = await res.json();
            if (json.status && json.data) {
                const unread = json.data.unread_count || 0;
                if (blueDot) {
                    blueDot.style.display = (unread > 0) ? "block" : "none";
                }
            }
        } catch (e) {
            // silent check
        }
    }

    // 2. Open notification modal & fetch list
    async function openNotifications() {
        modal.style.display = "flex";
        modal.classList.add("active");
        modal.style.opacity = "1";
        modal.style.pointerEvents = "auto";
        if (notifList) {
            notifList.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);"><span class="material-symbols-outlined" style="font-size: 32px; animation: spin 1s linear infinite;">sync</span><p style="margin-top: 8px;">Loading notifications...</p></div>';
        }

        try {
            const res = await fetch(apiBase + "?action=list");
            const json = await res.json();

            if (!json.status || !json.data) {
                if (notifList) notifList.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc2626;">Failed to load notifications.</div>';
                return;
            }

            const notifications = json.data.notifications || [];
            const unread = json.data.unread_count || 0;

            if (subtitle) {
                subtitle.textContent = (unread > 0) ? (unread + " unread notifications") : "All notifications are read";
            }

            if (notifications.length === 0) {
                if (notifList) {
                    notifList.innerHTML = '<div style="text-align: center; padding: 60px 20px; color: var(--text-muted);">' +
                        '<span class="material-symbols-outlined" style="font-size: 48px; color: var(--border-subtle); margin-bottom: 8px;">notifications_off</span>' +
                        '<h4 style="margin: 0; font-size: 16px; color: var(--text-heading);">No notifications yet</h4>' +
                        '<p style="margin: 4px 0 0 0; font-size: 13px;">You will receive alerts here when appointments are booked, confirmed, or updated.</p>' +
                        '</div>';
                }
                return;
            }

            const iconMap = {
                new_appointment_request: { icon: "event", bg: "#dbeafe", color: "#1d4ed8" },
                appointment_confirmed: { icon: "check_circle", bg: "#dcfce7", color: "#15803d" },
                appointment_cancelled: { icon: "cancel", bg: "#fee2e2", color: "#b91c1c" },
                appointment_rescheduled: { icon: "update", bg: "#fef9c3", color: "#a16207" },
                reschedule_accepted: { icon: "event_available", bg: "#dcfce7", color: "#15803d" },
                alternate_slot_proposed: { icon: "schedule", bg: "#fef3c7", color: "#b45309" },
                appointment_completed: { icon: "task_alt", bg: "#e0e7ff", color: "#4338ca" },
                default: { icon: "notifications", bg: "#f3f4f6", color: "#4b5563" }
            };

            if (notifList) {
                notifList.innerHTML = notifications.map(function(n) {
                    const isUnread = (n.is_read === 0);
                    const ic = iconMap[n.type] || iconMap.default;
                    const safeMsg = escapeDocHtml(n.message);

                    return '<div class="notification-item" data-id="' + n.notification_id + '" style="' +
                        'display: flex; align-items: flex-start; gap: 14px; padding: 14px 18px; border-radius: 10px; margin-bottom: 10px;' +
                        'background: ' + (isUnread ? "#f0f7ff" : "white") + ';' +
                        'border: 1px solid ' + (isUnread ? "#bfdbfe" : "var(--border-subtle, #e5e7eb)") + ';' +
                        'transition: all 0.2s ease;">' +
                        '<div style="width: 36px; height: 36px; border-radius: 50%; background: ' + ic.bg + '; color: ' + ic.color + '; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">' +
                        '<span class="material-symbols-outlined" style="font-size: 20px;">' + ic.icon + '</span>' +
                        '</div>' +
                        '<div style="flex: 1;">' +
                        '<p style="margin: 0; font-size: 13.5px; font-weight: ' + (isUnread ? "600" : "400") + '; color: var(--text-heading); line-height: 1.45;">' +
                        safeMsg +
                        '</p>' +
                        '<span style="font-size: 11.5px; color: var(--text-muted); margin-top: 4px; display: inline-block;" title="' + (n.formatted_datetime || '') + '">' +
                        (n.time_ago || n.formatted_datetime || '') +
                        '</span>' +
                        '</div>' +
                        (isUnread ? '<span style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb; flex-shrink: 0; margin-top: 6px;" title="Unread"></span>' : '') +
                        '</div>';
                }).join("");
            }

        } catch (err) {
            console.error("Notifications fetch error:", err);
            if (notifList) notifList.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc2626;">Network error loading notifications.</div>';
        }
    }

    // 3. Mark all as read
    async function markAllRead() {
        try {
            await fetch(apiBase + "?action=mark_all_read", { method: "POST" });
            if (blueDot) blueDot.style.display = "none";
            if (subtitle) subtitle.textContent = "All notifications marked as read";
            if (notifList) {
                notifList.querySelectorAll(".notification-item").forEach(function(item) {
                    item.style.background = "white";
                    item.style.borderColor = "var(--border-subtle, #e5e7eb)";
                    const dot = item.querySelector("span[title='Unread']");
                    if (dot) dot.remove();
                });
            }
        } catch (e) {
            console.error("Error marking all read:", e);
        }
    }

    // Event listeners
    const closeNotifModal = function() {
        modal.style.display = "none";
        modal.classList.remove("active");
        modal.style.opacity = "";
        modal.style.pointerEvents = "";
        checkUnreadCount();
    };

    notifBtn.onclick = function(e) {
        if (e) e.preventDefault();
        openNotifications();
    };
    window.openPatientNotifications = openNotifications;

    if (closeBtn) closeBtn.onclick = closeNotifModal;
    if (markAllBtn) markAllBtn.onclick = markAllRead;

    modal.onclick = function(e) {
        if (e.target === modal) {
            closeNotifModal();
        }
    };

    // Initial check + periodic polling every 30s
    checkUnreadCount();
    setInterval(checkUnreadCount, 30000);
}

// Auto-initialize notification system on document load
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", function() { initNotificationSystem("patient"); });
} else {
    initNotificationSystem("patient");
}


// =========================================================================
// PHASE 4: FOLLOW-UP & PATIENT RESCHEDULE FLOW
// =========================================================================

// Accept doctor's proposed slot
window.acceptProposedSlot = async (appointmentId, slotInfo) => {
    if (!confirm(`Accept doctor's proposed slot (${slotInfo}) for appointment #${appointmentId}?`)) {
        return;
    }

    try {
        const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || "";
        const res = await fetch("/my_doctor/public/patient/api/appointments.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token
            },
            body: JSON.stringify({
                action: "acceptReschedule",
                appointment_id: appointmentId,
                csrf_token: token
            })
        });

        const json = await res.json();
        if (!json.status) {
            alert(json.message || "Failed to accept proposed slot.");
            return;
        }

        alert("Proposed slot accepted! Appointment is now confirmed.");
        if (typeof fetchAppointments === "function") {
            fetchAppointments();
        } else {
            loadPage("appointments");
        }
    } catch (err) {
        console.error("Accept reschedule error:", err);
        alert("Network error accepting reschedule.");
    }
};

// Patient cancel appointment
window.cancelAppointmentPrompt = async (appointmentId) => {
    const reason = prompt("Please enter the reason for cancellation:");
    if (reason === null) return;

    try {
        const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || "";
        const res = await fetch("/my_doctor/public/patient/api/appointments.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token
            },
            body: JSON.stringify({
                action: "cancelAppointment",
                appointment_id: appointmentId,
                reason: reason.trim(),
                csrf_token: token
            })
        });

        const json = await res.json();
        if (!json.status) {
            alert(json.message || "Failed to cancel appointment.");
            return;
        }

        alert("Appointment cancelled successfully.");
        if (typeof fetchAppointments === "function") {
            fetchAppointments();
        } else {
            loadPage("appointments");
        }
    } catch (err) {
        console.error("Cancel appointment error:", err);
        alert("Network error cancelling appointment.");
    }
};

// Alternate Slot Modal logic
let currentAltApptId = null;
let currentAltDoctorId = null;
let selectedAltTime = null;

window.closeAlternateSlotModal = () => {
    const modal = document.getElementById("modal-alternate-slot");
    if (modal) {
        modal.style.display = "none";
        modal.classList.remove("active");
        modal.style.opacity = "";
        modal.style.pointerEvents = "";
    }
};

window.openAlternateSlotModal = (appointmentId, passedDoctorId, passedDoctorName) => {
    let appt = null;
    if (window.cachedAppointmentsData && Array.isArray(window.cachedAppointmentsData.appointments)) {
        appt = window.cachedAppointmentsData.appointments.find(a => a.appointment_id == appointmentId);
    }

    const doctorId = passedDoctorId || (appt ? appt.doctor_id : null);
    const doctorName = passedDoctorName || (appt ? appt.doctor_name : "your doctor");

    currentAltApptId = appointmentId;
    currentAltDoctorId = doctorId;
    selectedAltTime = null;

    const modal = document.getElementById("modal-alternate-slot");
    const sub = document.getElementById("alt-slot-doctor-subtitle");
    const dateInput = document.getElementById("alt-date-input");
    const apptInput = document.getElementById("alt-appointment-id");
    const docInput = document.getElementById("alt-doctor-id");
    const timeInput = document.getElementById("alt-selected-time");
    const displaySpan = document.getElementById("alt-selected-time-display");

    if (!modal) return;

    if (sub) sub.textContent = "Choose another date & time for Dr. " + (doctorName || "your doctor");
    if (apptInput) apptInput.value = appointmentId;
    if (docInput) docInput.value = doctorId || "";
    if (timeInput) timeInput.value = "";
    if (displaySpan) displaySpan.textContent = "";

    const todayStr = new Date().toISOString().split("T")[0];
    if (dateInput) {
        dateInput.min = todayStr;
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.value = tomorrow.toISOString().split("T")[0];
        if (doctorId) {
            loadAlternateSlots(doctorId, dateInput.value);
        }
    }

    modal.style.display = "flex";
    modal.classList.add("active");
    modal.style.opacity = "1";
    modal.style.pointerEvents = "auto";
};

async function loadAlternateSlots(doctorId, date) {
    const grid = document.getElementById("alt-slots-grid");
    const timeInput = document.getElementById("alt-selected-time");
    const displaySpan = document.getElementById("alt-selected-time-display");
    if (!grid) return;

    if (timeInput) timeInput.value = "";
    if (displaySpan) displaySpan.textContent = "";

    if (!doctorId) {
        grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 13px; text-align: center; padding: 12px;">Doctor information not available.</div>';
        return;
    }

    grid.innerHTML = '<div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px; text-align: center; padding: 12px;">Loading available slots...</div>';

    try {
        const res = await fetch("/my_doctor/public/patient/api/doctors.php?action=getDoctorDateSlots&doctor_id=" + encodeURIComponent(doctorId) + "&date=" + encodeURIComponent(date));
        const json = await res.json();

        if (!json.status || !json.data || json.data.is_blocked || (json.data.slots || []).length === 0) {
            const reason = json.data?.reason || "No available slots on this date. Please pick another date.";
            grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 12.5px; text-align: center; padding: 12px; font-weight: 500;">' + reason + '</div>';
            return;
        }

        const slots = json.data.slots || [];
        grid.innerHTML = "";
        slots.forEach(slot => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "slot-time-btn";
            btn.textContent = slot.display_time || slot.start_time;
            btn.style.cssText = "padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-subtle, #e2e8f0); background: white; cursor: pointer; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; color: var(--text-heading, #1e293b); transition: all 0.15s ease; text-align: center;";

            btn.onclick = () => {
                grid.querySelectorAll(".slot-time-btn").forEach(b => {
                    b.style.borderColor = "var(--border-subtle, #e2e8f0)";
                    b.style.background = "white";
                    b.style.color = "var(--text-heading, #1e293b)"; b.style.fontWeight = "500";
                });
                btn.style.borderColor = "#2563eb";
                btn.style.background = "#eff6ff";
                btn.style.color = "#2563eb"; btn.style.fontWeight = "600";
                selectedAltTime = slot.start_time;
                if (timeInput) timeInput.value = slot.start_time;
                if (displaySpan) displaySpan.textContent = "Selected: " + (slot.display_time || slot.start_time);
            };

            grid.appendChild(btn);
        });

    } catch (err) {
        console.error("Error loading alternate slots:", err);
        grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 12.5px; text-align: center; padding: 12px;">Error loading slots. Please try again.</div>';
    }
}

// Follow-up Modal logic
let currentFollowUpParentId = null;
let currentFollowUpDoctorId = null;
let selectedFollowUpTime = null;

window.closeFollowUpModal = () => {
    const modal = document.getElementById("modal-followup");
    if (modal) {
        modal.style.display = "none";
        modal.classList.remove("active");
        modal.style.opacity = "";
        modal.style.pointerEvents = "";
    }
};

window.openFollowUpModal = (parentAppointmentId) => {
    currentFollowUpParentId = parentAppointmentId;
    selectedFollowUpTime = null;

    const modal = document.getElementById("modal-followup");
    if (!modal) return;

    let parentAppt = null;
    if (window.cachedAppointmentsData && Array.isArray(window.cachedAppointmentsData.appointments)) {
        parentAppt = window.cachedAppointmentsData.appointments.find(a => a.appointment_id == parentAppointmentId);
    }

    const docName = document.getElementById("followup-doc-name");
    const docSpec = document.getElementById("followup-doc-spec");
    const docAvatar = document.getElementById("followup-doc-avatar");
    const modeBadge = document.getElementById("followup-mode-badge");
    const parentIdInput = document.getElementById("followup-parent-id");
    const docIdInput = document.getElementById("followup-doctor-id");
    const dateInput = document.getElementById("followup-date-input");
    const timeInput = document.getElementById("followup-selected-time");

    if (parentIdInput) parentIdInput.value = parentAppointmentId;
    if (timeInput) timeInput.value = "";

    if (parentAppt) {
        currentFollowUpDoctorId = parentAppt.doctor_id;
        if (docIdInput) docIdInput.value = parentAppt.doctor_id;
        if (docName) docName.textContent = parentAppt.doctor_name || "Doctor";
        if (docSpec) docSpec.textContent = parentAppt.specialization || "General Physician";
        if (docAvatar && parentAppt.profile_url) docAvatar.src = parentAppt.profile_url;
        if (modeBadge) modeBadge.textContent = (parentAppt.appointment_mode || "consultation").replace("_", " ");
    }

    const todayStr = new Date().toISOString().split("T")[0];
    if (dateInput) {
        dateInput.min = todayStr;
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateInput.value = tomorrow.toISOString().split("T")[0];
        if (currentFollowUpDoctorId) {
            loadFollowUpSlots(currentFollowUpDoctorId, dateInput.value);
        }
    }

    modal.style.display = "flex";
    modal.classList.add("active");
    modal.style.opacity = "1";
    modal.style.pointerEvents = "auto";
};

async function loadFollowUpSlots(doctorId, date) {
    const grid = document.getElementById("followup-slots-grid");
    if (!grid) return;
    grid.innerHTML = '<div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px; text-align: center; padding: 12px;">Loading available slots...</div>';

    try {
        const res = await fetch("/my_doctor/public/patient/api/doctors.php?action=getDoctorDateSlots&doctor_id=" + encodeURIComponent(doctorId) + "&date=" + encodeURIComponent(date));
        const json = await res.json();

        if (!json.status || !json.data || json.data.is_blocked || (json.data.slots || []).length === 0) {
            const reason = json.data?.reason || "No available slots on this date. Please pick another date.";
            grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 12.5px; text-align: center; padding: 12px; font-weight: 500;">' + reason + '</div>';
            return;
        }

        const slots = json.data.slots || [];
        grid.innerHTML = "";
        slots.forEach(slot => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "slot-time-btn";
            btn.textContent = slot.display_time || slot.start_time;
            btn.style.cssText = "padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border-subtle, #e2e8f0); background: white; cursor: pointer; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 500; color: var(--text-heading, #1e293b); transition: all 0.15s ease; text-align: center;";

            btn.onclick = () => {
                grid.querySelectorAll(".slot-time-btn").forEach(b => {
                    b.style.borderColor = "var(--border-subtle, #e2e8f0)";
                    b.style.background = "white";
                    b.style.color = "var(--text-heading, #1e293b)"; b.style.fontWeight = "500";
                });
                btn.style.borderColor = "#2563eb";
                btn.style.background = "#eff6ff";
                btn.style.color = "#2563eb"; btn.style.fontWeight = "600";
                selectedFollowUpTime = slot.start_time;
                document.getElementById("followup-selected-time").value = slot.start_time;
            };

            grid.appendChild(btn);
        });

    } catch (err) {
        console.error("Error loading follow-up slots:", err);
        grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 12.5px; text-align: center; padding: 12px;">Error loading slots.</div>';
    }
}

// Function to initialize modal events
function initPhase4Modals() {
    // Follow-up modal close handlers
    const closeFollowUpBtn = document.getElementById("btn-close-followup-modal");
    const cancelFollowUpBtn = document.getElementById("btn-cancel-followup");
    if (closeFollowUpBtn) closeFollowUpBtn.onclick = window.closeFollowUpModal;
    if (cancelFollowUpBtn) cancelFollowUpBtn.onclick = window.closeFollowUpModal;

    // Alternate slot modal close handlers
    const closeAltBtn = document.getElementById("btn-close-alt-slot-modal");
    const cancelAltBtn = document.getElementById("btn-cancel-alt-slot");
    if (closeAltBtn) closeAltBtn.onclick = window.closeAlternateSlotModal;
    if (cancelAltBtn) cancelAltBtn.onclick = window.closeAlternateSlotModal;

    // Date inputs change -> reload slots
    const followUpDateInput = document.getElementById("followup-date-input");
    if (followUpDateInput && !followUpDateInput.dataset.bound) {
        followUpDateInput.dataset.bound = "1";
        followUpDateInput.addEventListener("change", (e) => {
            const docId = document.getElementById("followup-doctor-id")?.value;
            if (docId) loadFollowUpSlots(docId, e.target.value);
        });
    }

    const altDateInput = document.getElementById("alt-date-input");
    if (altDateInput && !altDateInput.dataset.bound) {
        altDateInput.dataset.bound = "1";
        altDateInput.addEventListener("change", (e) => {
            const docId = document.getElementById("alt-doctor-id")?.value;
            if (docId) loadAlternateSlots(docId, e.target.value);
        });
    }

    // Submit Follow-up Form
    const formFollowUp = document.getElementById("form-book-followup");
    if (formFollowUp && !formFollowUp.dataset.bound) {
        formFollowUp.dataset.bound = "1";
        formFollowUp.addEventListener("submit", async (e) => {
            e.preventDefault();
            const parentId = document.getElementById("followup-parent-id").value;
            const date = document.getElementById("followup-date-input").value;
            const time = document.getElementById("followup-selected-time").value;
            const problem = document.getElementById("followup-problem").value.trim();

            if (!time) {
                alert("Please select an available time slot from the list.");
                return;
            }

            const btn = document.getElementById("btn-submit-followup");
            if (btn) {
                btn.disabled = true;
                btn.textContent = "Submitting...";
            }

            try {
                const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || "";
                const res = await fetch("/my_doctor/public/patient/api/appointments.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token
                    },
                    body: JSON.stringify({
                        action: "bookFollowUp",
                        parent_appointment_id: parentId,
                        appointment_date: date,
                        appointment_time: time,
                        problem_description: problem,
                        csrf_token: token
                    })
                });

                const json = await res.json();
                if (!json.status) {
                    alert(json.message || "Failed to book follow-up.");
                    return;
                }

                alert("Free follow-up booked successfully! Awaiting doctor's confirmation.");
                window.closeFollowUpModal();
                if (typeof fetchAppointmentsTable === "function") {
                    fetchAppointmentsTable();
                } else if (typeof loadPage === "function") {
                    loadPage("appointments");
                }
            } catch (err) {
                console.error("Book follow-up error:", err);
                alert("Network error booking follow-up.");
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = "Confirm Free Follow-up";
                }
            }
        });
    }

    // Submit Alternate Slot Form
    const formAlt = document.getElementById("form-alternate-slot");
    if (formAlt && !formAlt.dataset.bound) {
        formAlt.dataset.bound = "1";
        formAlt.addEventListener("submit", async (e) => {
            e.preventDefault();
            const apptId = document.getElementById("alt-appointment-id").value;
            const date = document.getElementById("alt-date-input").value;
            const time = document.getElementById("alt-selected-time").value;

            if (!time) {
                alert("Please select an available time slot from the list.");
                return;
            }

            const btn = document.getElementById("btn-submit-alt-slot");
            if (btn) {
                btn.disabled = true;
                btn.textContent = "Submitting...";
            }

            try {
                const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || "";
                const res = await fetch("/my_doctor/public/patient/api/appointments.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token
                    },
                    body: JSON.stringify({
                        action: "proposeAlternateSlot",
                        appointment_id: apptId,
                        appointment_date: date,
                        appointment_time: time,
                        csrf_token: token
                    })
                });

                const json = await res.json();
                if (!json.status) {
                    alert(json.message || "Failed to propose alternate slot.");
                    return;
                }

                alert("Alternate slot proposed! Appointment status is now pending doctor review.");
                window.closeAlternateSlotModal();
                if (typeof fetchAppointmentsTable === "function") {
                    fetchAppointmentsTable();
                } else if (typeof loadPage === "function") {
                    loadPage("appointments");
                }
            } catch (err) {
                console.error("Propose alternate slot error:", err);
                alert("Network error proposing alternate slot.");
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = "Propose Slot";
                }
            }
        });
    }
}

// Attach listeners immediately and on DOMContentLoaded
initPhase4Modals();
setupDoctorProfileModalEventListeners();
if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        initPhase4Modals();
        setupDoctorProfileModalEventListeners();
    });
}

// Global modal helpers
window.openDoctorProfileModal = openDoctorProfileModal;
window.closeDoctorProfileModal = closeDoctorProfileModal;
window.openBookingModal = openBookingModal;
window.closeBookingModal = closeBookingModal;

// ==========================================================================
// PATIENT CALLS TAB (WhatsApp Style Call Log & Real-Time Metrics)
// ==========================================================================

window.loadPatientPage = loadPage;

function initPatientCalls() {
    let currentFilter = "all";
    let searchQuery = "";
    let searchDebounceTimer = null;

    const totalMinsEl = document.getElementById("calls-metric-total-minutes");
    const totalSubtitleEl = document.getElementById("calls-metric-total-subtitle");
    const qualityEl = document.getElementById("calls-metric-quality");
    const completionRateEl = document.getElementById("calls-metric-completion-rate");
    const videoPctEl = document.getElementById("calls-metric-video-pct");
    const videoCountEl = document.getElementById("calls-metric-video-count");
    const audioPctEl = document.getElementById("calls-metric-audio-pct");
    const audioCountEl = document.getElementById("calls-metric-audio-count");
    const countBadgeEl = document.getElementById("calls-count-badge");
    const container = document.getElementById("whatsapp-calls-container");
    const searchInput = document.getElementById("calls-search-input");
    const filterPills = document.querySelectorAll("#calls-filter-bar .calls-filter-pill");
    const waitingRoomBtn = document.getElementById("btn-calls-waiting-room");

    if (waitingRoomBtn) {
        waitingRoomBtn.onclick = () => {
            if (window.loadPatientPage) window.loadPatientPage("chats");
            else if (typeof loadPage === "function") loadPage("chats");
        };
    }

    const escapeHtml = (str) => {
        if (!str) return "";
        return String(str).replace(/[&<>"']/g, m => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[m]);
    };

    filterPills.forEach(pill => {
        pill.onclick = (e) => {
            e.preventDefault();
            filterPills.forEach(p => p.classList.remove("active"));
            pill.classList.add("active");
            currentFilter = pill.dataset.callFilter || "all";
            fetchAndRenderCalls();
        };
    });

    if (searchInput) {
        searchInput.oninput = () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                searchQuery = searchInput.value.trim();
                fetchAndRenderCalls();
            }, 300);
        };
    }

    const colorClasses = ["", "teal", "amber", "rose", "purple"];

    const fetchAndRenderCalls = () => {
        if (!container) return;

        container.innerHTML = `
            <div class="whatsapp-empty-state">
                <span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">progress_activity</span>
                <p>Loading consultation calls...</p>
            </div>
        `;

        const url = `/my_doctor/public/patient/api/calls.php?type=${encodeURIComponent(currentFilter)}&search=${encodeURIComponent(searchQuery)}`;
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(res => {
                if (!res.status || !res.data) {
                    container.innerHTML = `<div class="whatsapp-empty-state"><span class="material-symbols-outlined">error</span><p>${escapeHtml(res.message || "Error loading calls")}</p></div>`;
                    return;
                }

                const data = res.data;
                const metrics = data.metrics || {};
                const calls = data.calls || [];

                // 1. Update Metrics Cards
                if (totalMinsEl) totalMinsEl.textContent = metrics.total_minutes || "0";
                if (totalSubtitleEl) totalSubtitleEl.textContent = metrics.subtitle_consultations || "Total across consultations";
                if (qualityEl) qualityEl.textContent = metrics.avg_quality || "4.9";
                if (completionRateEl) completionRateEl.textContent = `${metrics.completion_rate || 100}% Completion rate`;
                if (videoPctEl) videoPctEl.textContent = `${metrics.video_percentage || 0}%`;
                if (videoCountEl) videoCountEl.textContent = `${metrics.video_calls || 0} Video`;
                if (audioPctEl) audioPctEl.textContent = `${metrics.audio_percentage || 0}%`;
                if (audioCountEl) audioCountEl.textContent = `${metrics.audio_calls || 0} Audio`;
                if (countBadgeEl) countBadgeEl.textContent = `${data.total_count || 0} Calls`;

                // 2. Render WhatsApp Call List
                if (calls.length === 0) {
                    container.innerHTML = `
                        <div class="whatsapp-empty-state">
                            <span class="material-symbols-outlined">phone_missed</span>
                            <p>No call logs found matching your filter</p>
                        </div>
                    `;
                    return;
                }

                container.innerHTML = calls.map((call, idx) => {
                    let avatarHtml = "";
                    if (call.doctor_avatar) {
                        avatarHtml = `<img class="whatsapp-call-avatar" src="${escapeHtml(call.doctor_avatar)}" alt="Doctor" onerror="this.outerHTML='<div class=\\'whatsapp-call-avatar-initial\\'>${call.initials}</div>'">`;
                    } else {
                        const colorClass = colorClasses[idx % colorClasses.length];
                        avatarHtml = `<div class="whatsapp-call-avatar-initial ${colorClass}">${call.initials}</div>`;
                    }

                    let dirIcon = "call_made";
                    let dirClass = "call-dir-outgoing";
                    let statusText = "Outgoing";

                    if (call.is_missed) {
                        dirIcon = "call_missed";
                        dirClass = "call-dir-missed";
                        statusText = "Missed";
                    } else if (call.status === "cancelled") {
                        dirIcon = "cancel";
                        dirClass = "call-dir-cancelled";
                        statusText = "Cancelled";
                    } else if (call.call_direction === "incoming") {
                        dirIcon = "call_received";
                        dirClass = "call-dir-incoming";
                        statusText = "Incoming";
                    }

                    const callTypeIcon = (call.call_type === "video_call") ? "videocam" : "call";
                    const callBtnTitle = (call.call_type === "video_call") ? "Start Video Call" : "Start Audio Call";

                    return `
                        <div class="whatsapp-call-item" data-call-id="${call.call_id}">
                            <div class="whatsapp-call-left">
                                ${avatarHtml}
                                <div class="whatsapp-call-info">
                                    <div class="whatsapp-call-name-row">
                                        <span class="whatsapp-call-name">${escapeHtml(call.doctor_name)}</span>
                                        <span class="badge ${call.quality_class}" style="font-size: 11px;">${call.quality_label}</span>
                                    </div>
                                    <div class="whatsapp-call-meta">
                                        <span class="material-symbols-outlined call-dir-icon ${dirClass}">${dirIcon}</span>
                                        <span style="font-weight: 600; color: ${call.is_missed ? '#ef4444' : 'inherit'};">${statusText}</span>
                                        <span>•</span>
                                        <span class="material-symbols-outlined icon-xs" style="font-size: 14px;">${callTypeIcon}</span>
                                        <span>${call.call_type_label}</span>
                                        <span>•</span>
                                        <span>${call.date_formatted}, ${call.time_formatted}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="whatsapp-call-actions">
                                <span class="badge ${call.is_missed ? 'badge-danger' : 'badge-success'}" style="font-size: 12px; font-weight: 600;">
                                    ${call.duration_formatted}
                                </span>
                                <button class="whatsapp-call-btn" data-action="quick-call" data-appt-id="${call.appointment_id}" data-conv-id="${call.conversation_id}" title="${callBtnTitle}">
                                    <span class="material-symbols-outlined">${callTypeIcon}</span>
                                </button>
                                <button class="whatsapp-chat-btn" data-action="quick-chat" data-appt-id="${call.appointment_id}" data-conv-id="${call.conversation_id}" title="Open Consultation Chat">
                                    <span class="material-symbols-outlined">chat</span>
                                </button>
                            </div>
                        </div>
                    `;
                }).join("");

                container.querySelectorAll('[data-action="quick-call"], [data-action="quick-chat"]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        const apptId = parseInt(btn.dataset.apptId || "0", 10);
                        const convId = parseInt(btn.dataset.convId || "0", 10);
                        if (apptId > 0) {
                            window.targetChatAppointmentId = apptId;
                        }
                        if (convId > 0) {
                            window.targetChatConvId = convId;
                        }
                        if (window.loadPatientPage) {
                            window.loadPatientPage("chats");
                        } else if (typeof loadPage === "function") {
                            loadPage("chats");
                        }
                    };
                });
            })
            .catch(err => {
                console.error("Error loading calls:", err);
                container.innerHTML = `<div class="whatsapp-empty-state"><span class="material-symbols-outlined">wifi_off</span><p>Network error loading calls. Please try again.</p></div>`;
            });
    };

    fetchAndRenderCalls();
}

// ==========================================================================
// PATIENT PRESCRIPTIONS TAB (Digital Prescriptions & Printable Slip)
// ==========================================================================

function initPatientPrescriptions() {
    let searchQuery = "";
    let searchDebounceTimer = null;
    let prescriptionsCache = [];

    const totalEl = document.getElementById("prescriptions-metric-total");
    const medsEl = document.getElementById("prescriptions-metric-medicines");
    const docsEl = document.getElementById("prescriptions-metric-doctors");
    const countBadge = document.getElementById("prescriptions-count-badge");
    const container = document.getElementById("patient-prescriptions-container");
    const searchInput = document.getElementById("prescriptions-search-input");
    const refreshBtn = document.getElementById("btn-refresh-prescriptions");
    const slipModal = document.getElementById("modal-prescription-slip");
    const slipPrintableArea = document.getElementById("prescription-slip-printable-area");
    const btnCloseSlip = document.getElementById("btn-close-slip");
    const btnPrintSlip = document.getElementById("btn-print-slip");

    const escapeHtml = (str) => {
        if (!str) return "";
        return String(str).replace(/[&<>"']/g, m => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[m]);
    };

    if (refreshBtn) {
        refreshBtn.onclick = () => fetchPrescriptions();
    }

    if (searchInput) {
        searchInput.oninput = () => {
            clearTimeout(searchDebounceTimer);
            searchDebounceTimer = setTimeout(() => {
                searchQuery = searchInput.value.trim();
                fetchPrescriptions();
            }, 300);
        };
    }

    if (btnCloseSlip && slipModal) {
        btnCloseSlip.onclick = () => {
            slipModal.style.display = "none";
        };
    }

    if (btnPrintSlip && slipPrintableArea) {
        btnPrintSlip.onclick = () => {
            const printContent = slipPrintableArea.innerHTML;
            const printWindow = window.open("", "_blank", "width=850,height=900");
            printWindow.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Medical Prescription Slip</title>
                    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
                    <style>
                        body { font-family: 'Inter', sans-serif; padding: 40px; color: #1e293b; background: #fff; }
                        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                        th, td { border: 1px solid #cbd5e1; padding: 10px 14px; text-align: left; }
                        th { background: #f1f5f9; font-weight: 700; }
                        @media print {
                            button { display: none; }
                        }
                    </style>
                </head>
                <body>
                    ${printContent}
                    <script>
                        window.onload = function() { window.print(); window.close(); };
                    </script>
                </body>
                </html>
            `);
            printWindow.document.close();
        };
    }

    const openPrescriptionSlip = (prescriptionId) => {
        const item = prescriptionsCache.find(p => p.prescription_id === prescriptionId);
        if (!item || !slipModal || !slipPrintableArea) return;

        let medsRows = "";
        (item.medicines || []).forEach((m, idx) => {
            medsRows += `
                <tr>
                    <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; font-weight: 600;">${idx + 1}. ${escapeHtml(m.name)}</td>
                    <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;">${escapeHtml(m.dosage || "-")}</td>
                    <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;"><span style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 12px;">${escapeHtml(m.frequency || "-")}</span></td>
                    <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0;">${escapeHtml(m.duration || "-")}</td>
                    <td style="padding: 10px 14px; border-bottom: 1px solid #e2e8f0; color: #64748b;">${escapeHtml(m.instruction || "-")}</td>
                </tr>
            `;
        });

        slipPrintableArea.innerHTML = `
            <!-- Clinic & Doctor Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-start; padding-bottom: 20px; border-bottom: 2px solid #2563eb;">
                <div>
                    <h2 style="margin: 0 0 4px 0; font-size: 22px; color: #2563eb; font-weight: 800;">MyDoctor Healthcare</h2>
                    <p style="margin: 0; font-size: 13px; color: #64748b;">Certified Digital Telehealth &amp; Medical Clinic</p>
                </div>
                <div style="text-align: right;">
                    <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">${escapeHtml(item.doctor_name)}</h3>
                    <p style="margin: 2px 0; font-size: 13px; color: #475569;">${escapeHtml(item.doctor_specialization)} • ${escapeHtml(item.doctor_qualification)}</p>
                    <p style="margin: 0; font-size: 12px; color: #64748b;">Reg. No: <strong>${escapeHtml(item.doctor_reg_no)}</strong></p>
                </div>
            </div>

            <!-- Patient & Prescription Metadata -->
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 16px 0; border-bottom: 1px dashed #cbd5e1; font-size: 13px;">
                <div>
                    <span style="color: #64748b;">Consultation Date:</span> <strong>${escapeHtml(item.date_formatted)}</strong><br>
                    <span style="color: #64748b;">Appointment Mode:</span> <strong>${escapeHtml(item.appointment_mode || "Online")}</strong>
                </div>
                <div style="text-align: right;">
                    <span style="color: #64748b;">Prescription ID:</span> <strong>#RX-${item.prescription_id}</strong><br>
                    <span style="color: #64748b;">Follow-up Date:</span> <strong>${escapeHtml(item.follow_up_date || "As needed")}</strong>
                </div>
            </div>

            <!-- Diagnosis -->
            <div style="margin: 20px 0; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 18px;">
                <span style="font-size: 12px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 0.04em;">Primary Diagnosis</span>
                <div style="font-size: 16px; font-weight: 700; color: #15803d; margin-top: 2px;">${escapeHtml(item.diagnosis)}</div>
            </div>

            <!-- Prescribed Medications (Rx) -->
            <div style="margin: 24px 0;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <span style="font-size: 20px; font-weight: 800; color: #2563eb;">℞</span>
                    <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: #0f172a;">Prescribed Medications</h4>
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8fafc; text-align: left; font-size: 12px; color: #475569;">
                            <th style="padding: 10px 14px; border-bottom: 2px solid #e2e8f0;">Medicine Name</th>
                            <th style="padding: 10px 14px; border-bottom: 2px solid #e2e8f0;">Dosage</th>
                            <th style="padding: 10px 14px; border-bottom: 2px solid #e2e8f0;">Frequency</th>
                            <th style="padding: 10px 14px; border-bottom: 2px solid #e2e8f0;">Duration</th>
                            <th style="padding: 10px 14px; border-bottom: 2px solid #e2e8f0;">Instructions</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${medsRows}
                    </tbody>
                </table>
            </div>

            <!-- Lab Tests & Advice -->
            ${item.lab_tests ? `
                <div style="margin-bottom: 18px; padding: 12px 16px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px;">
                    <strong style="color: #854d0e; font-size: 13px;">Recommended Lab Tests:</strong>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #713f12;">${escapeHtml(item.lab_tests)}</p>
                </div>
            ` : ""}

            <div style="margin-bottom: 24px; padding: 14px 18px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                <strong style="color: #334155; font-size: 13px;">Physician Advice &amp; Notes:</strong>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #475569; line-height: 1.5;">${escapeHtml(item.advice)}</p>
            </div>

            <!-- Signature footer -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: 36px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <div style="font-size: 11.5px; color: #94a3b8;">
                    Electronically verified digital prescription.<br>
                    Valid for pharmacy dispensing across licensed healthcare providers.
                </div>
                <div style="text-align: right;">
                    <div style="font-family: cursive; font-size: 18px; color: #1e40af; font-weight: 700;">${escapeHtml(item.doctor_name)}</div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">Authorized Signature</div>
                </div>
            </div>
        `;

        slipModal.style.display = "flex";
    };

    const fetchPrescriptions = () => {
        if (!container) return;

        container.innerHTML = `
            <div class="whatsapp-empty-state">
                <span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">progress_activity</span>
                <p>Loading digital prescriptions...</p>
            </div>
        `;

        const url = `/my_doctor/public/patient/api/prescriptions.php?search=${encodeURIComponent(searchQuery)}`;
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error("HTTP " + res.status);
                return res.json();
            })
            .then(res => {
                if (!res.status || !res.data) {
                    container.innerHTML = `<div class="whatsapp-empty-state"><span class="material-symbols-outlined">error</span><p>${escapeHtml(res.message || "Error loading prescriptions")}</p></div>`;
                    return;
                }

                const data = res.data;
                const metrics = data.metrics || {};
                prescriptionsCache = data.prescriptions || [];

                // 1. Update Metrics
                if (totalEl) totalEl.textContent = metrics.total_prescriptions || "0";
                if (medsEl) medsEl.textContent = metrics.total_medicines || "0";
                if (docsEl) docsEl.textContent = metrics.attending_doctors || "0";
                if (countBadge) countBadge.textContent = `${data.total_count || 0} Records`;

                // 2. Render Cards
                if (prescriptionsCache.length === 0) {
                    container.innerHTML = `
                        <div class="whatsapp-empty-state">
                            <span class="material-symbols-outlined">receipt_long</span>
                            <p>${searchQuery ? "No prescriptions match your search query." : "No prescriptions found in your medical history."}</p>
                        </div>
                    `;
                    return;
                }

                const colorClasses = ["", "teal", "amber", "rose", "purple"];

                container.innerHTML = prescriptionsCache.map((item, idx) => {
                    const colorClass = colorClasses[idx % colorClasses.length];

                    let avatarHtml = "";
                    if (item.doctor_avatar) {
                        avatarHtml = `<img class="prescription-doc-avatar" src="${escapeHtml(item.doctor_avatar)}" alt="Doctor" onerror="this.outerHTML='<div class=\\'whatsapp-call-avatar-initial ${colorClass}\\'>${item.doctor_initials}</div>'">`;
                    } else {
                        avatarHtml = `<div class="whatsapp-call-avatar-initial ${colorClass}">${item.doctor_initials}</div>`;
                    }

                    let medicinesTableHtml = "";
                    if (item.medicines && item.medicines.length > 0) {
                        const medRows = item.medicines.map((m, mIdx) => `
                            <tr>
                                <td><span class="prescription-med-name">${mIdx + 1}. ${escapeHtml(m.name)}</span></td>
                                <td>${escapeHtml(m.dosage || "-")}</td>
                                <td><span class="badge badge-blue" style="font-weight: 700;">${escapeHtml(m.frequency || "-")}</span></td>
                                <td>${escapeHtml(m.duration || "-")}</td>
                                <td style="color: var(--text-muted, #64748b);">${escapeHtml(m.instruction || "-")}</td>
                            </tr>
                        `).join("");

                        medicinesTableHtml = `
                            <table class="prescription-table">
                                <thead>
                                    <tr>
                                        <th>Medicine</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Instructions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${medRows}
                                </tbody>
                            </table>
                        `;
                    }

                    return `
                        <div class="prescription-card" data-prescription-id="${item.prescription_id}">
                            <!-- Header -->
                            <div class="prescription-card-header">
                                <div class="prescription-doc-info">
                                    ${avatarHtml}
                                    <div>
                                        <h4 class="prescription-doc-name">${escapeHtml(item.doctor_name)}</h4>
                                        <p class="prescription-doc-sub">${escapeHtml(item.doctor_specialization)} • ${escapeHtml(item.doctor_qualification)}</p>
                                        <p class="prescription-doc-sub" style="font-size: 11.5px;">Reg: <strong>${escapeHtml(item.doctor_reg_no)}</strong></p>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge badge-blue" style="font-size: 12px; margin-bottom: 4px;">Prescribed: ${escapeHtml(item.date_formatted)}</span>
                                    <div style="font-size: 11.5px; color: var(--text-muted, #64748b);">Appt #${item.appointment_id} • ${escapeHtml(item.appointment_mode || "Online")}</div>
                                </div>
                            </div>

                            <!-- Diagnosis Banner -->
                            <div class="prescription-diag-banner">
                                <span class="material-symbols-outlined" style="color: #2563eb; font-size: 20px;">stethoscope</span>
                                <span class="prescription-diag-text">Diagnosis: ${escapeHtml(item.diagnosis)}</span>
                            </div>

                            <!-- Medications Table -->
                            ${medicinesTableHtml}

                            <!-- Lab Tests Required (if any) -->
                            ${item.lab_tests ? `
                                <div style="margin: 12px 0; padding: 8px 14px; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; font-size: 13px;">
                                    <strong style="color: #854d0e;">Recommended Lab Tests:</strong> <span style="color: #713f12;">${escapeHtml(item.lab_tests)}</span>
                                </div>
                            ` : ""}

                            <!-- Advice & Actions Footer -->
                            <div class="prescription-footer-box">
                                <div style="flex: 1; min-width: 260px;">
                                    <div class="prescription-advice-text">
                                        <strong>Doctor's Advice:</strong> ${escapeHtml(item.advice)}
                                    </div>
                                    ${item.follow_up_date ? `
                                        <div style="margin-top: 6px; font-size: 12px; color: #166534; font-weight: 600;">
                                            <span class="material-symbols-outlined" style="font-size: 14px; vertical-align: -2px;">event_repeat</span>
                                            Recommended Follow-up: ${escapeHtml(item.follow_up_date)}
                                        </div>
                                    ` : ""}
                                </div>

                                <div class="prescription-actions-row">
                                    <button class="btn btn-primary" data-action="print-slip" data-id="${item.prescription_id}" style="padding: 8px 16px; font-size: 13px;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">receipt</span>
                                        View &amp; Print Slip
                                    </button>
                                    <button class="btn btn-outline" data-action="consult-doc" data-conv-id="${item.conversation_id}" data-appt-id="${item.appointment_id}" style="padding: 8px 16px; font-size: 13px; border: 1px solid #cbd5e1; background: #ffffff; color: #1e293b; border-radius: 8px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">chat</span>
                                        Consult Again
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }).join("");

                // Attach button listeners
                container.querySelectorAll('[data-action="print-slip"]').forEach(btn => {
                    btn.onclick = () => {
                        const id = parseInt(btn.dataset.id || "0", 10);
                        if (id > 0) openPrescriptionSlip(id);
                    };
                });

                container.querySelectorAll('[data-action="consult-doc"]').forEach(btn => {
                    btn.onclick = () => {
                        const apptId = parseInt(btn.dataset.apptId || "0", 10);
                        const convId = parseInt(btn.dataset.convId || "0", 10);
                        if (apptId > 0) window.targetChatAppointmentId = apptId;
                        if (convId > 0) window.targetChatConvId = convId;
                        if (window.loadPatientPage) window.loadPatientPage("chats");
                        else if (typeof loadPage === "function") loadPage("chats");
                    };
                });
            })
            .catch(err => {
                console.error("Error loading prescriptions:", err);
                container.innerHTML = `<div class="whatsapp-empty-state"><span class="material-symbols-outlined">wifi_off</span><p>Network error loading prescriptions. Please try again.</p></div>`;
            });
    };

    fetchPrescriptions();
}
