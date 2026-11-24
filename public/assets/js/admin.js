// DOM GLOBAL ELEMENTS

const APP = {
  content: document.getElementById("spa-content"),
  links: document.querySelectorAll("[data-page]"),
  currentPage: "dashboard",
  cache: {
    dashboard: null,
    doctors: {
      table: null,
      pagination: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        status: "all"
      },
    },
    patients: {
      table: null,
      pagination: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        status: "all"
      },
    },
    appointments: {
      table: null,
      pagination: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        status: "all"
      },
    },
    userVerification: {
      table: null,
      pagination: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
        verification_status: "pending"
      },
    },
    blockedUsers: {
      table: null,
      pagination: null,
      page: 1,
      limit: 10,
      search: "",
      filters: {
          user_type: "all"
      },
    },
    profile: null,
  },
};

const PAGE_CONFIG = {

    "doctors": {
        cache: APP.cache.doctors,
        loader: fetchDoctorsTable,
        searchable: true,
        filterKey: "status",
        placeholder: "Search Doctors with Name or Email..."
    },

    "patients": {
        cache: APP.cache.patients,
        loader: fetchPatientsTable,
        searchable: true,
        filterKey: "status",
        placeholder: "Search Patients with Name or Email..."
    },

    "appointments": {
        cache: APP.cache.appointments,
        loader: fetchAppointmentsTable,
        searchable: true,
        filterKey: "status",
        placeholder: "Search Appointments with ..."
    },

    "user-verification": {
        cache: APP.cache.userVerification,
        loader: fetchUserVerificationTable,
        searchable: true,
        filterKey: "verification_status",
        placeholder: "Search Users with Name or Email..."
    },

    "blocked-users": {
        cache: APP.cache.blockedUsers,
        loader: fetchBlockedUsersTable,
        searchable: true,
        filterKey: "user_type",
        placeholder: "Search Blocked Users with Name or Email..."
    }

};

const PAGE_ACTIONS = {
    common: handleActionButtons,
    dashboard: handleDashboardActions,
    doctors: handleDoctorActions,
    patients: handlePatientActions,
    profile: handleProfileActions,
    settings: handleProfileActions,
    appointments: handleAppointmentActions,
    userVerification: handleUserVerificationActions,
    blockedUsers: handleBlockedUserActions
};

const FORM_ACTIONS={
      "change-password": handlePasswordForm,

    // "doctor-form":
    //     handleDoctorForm,

    // "patient-form":
    //     handlePatientForm

};

// APP INITIALIZATION

document.addEventListener("DOMContentLoaded", init);

function init() {
  const savedTheme = localStorage.getItem("theme") || "light";
  setTheme(savedTheme);
  checkDOMElements();
  setupStaticEventListeners();
  setupGlobalDelegation();
  initializePage();
}

function setupGlobalDelegation() {
  document.addEventListener("click", handleClicks);
  document.addEventListener("change", handleChanges);
  document.addEventListener("submit", handleForms);
}

function handleClicks(event) {
  handlePagination(event);

  const btn = event.target.closest("[data-action]");
  if (!btn) return;

  const page = btn.dataset.page || APP.currentPage;
  const action = btn.dataset.action;

  if (action === "export") {
    exportPageData(page);
    return;
  }

  if (action === "add-doctor") {
    openAddDoctorModal();
    return;
  }

  if (action === "add-patient") {
    openAddPatientModal();
    return;
  }

  if (action === "open-settings" || action === "open-profile") {
    loadPage("settings");
    return;
  }

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
  setupProfileNavigation();
  initAdminNotifications();
  setupAddDoctorModal();
  setupAddPatientModal();
}

async function fetchAndSetupUserProfile() {
  try {
    const response = await fetchAPI("/my_doctor/public/admin/api/profile-data.php");
    setupUserProfile(response);
    if (response && response.theme) {
      localStorage.setItem("theme", response.theme);
      setTheme(response.theme);
    }
  } catch (error) {
    console.error("Error fetching admin profile:", error);
  }
}

function setupUserProfile(data) {
  const avatarContainer = document.getElementById("header-profile-img-container");
  if (!avatarContainer) return;

  if (data && data.profile_url) {
    avatarContainer.innerHTML = `
        <img src="${data.profile_url}" alt="${data.name || 'Admin'}" class="profile-img">
    `;
  } else if (data && data.name) {
    avatarContainer.textContent = getInitials(data.name);
    avatarContainer.classList.add("user-avatar");
  } else {
    avatarContainer.textContent = "AD";
    avatarContainer.classList.add("user-avatar");
  }

  avatarContainer.style.cursor = "pointer";
  avatarContainer.addEventListener("click", () => {
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
  const searchInput = document.querySelector(".topbar-search input");

  if (!searchInput) return;

  searchInput.addEventListener("focus", () => {
    searchInput.parentElement.style.transform = "scale(1.01)";
  });

  searchInput.addEventListener("blur", () => {
    searchInput.parentElement.style.transform = "";
  });

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

  const button = event.target.closest(".page-btn, .btn-prev-next");
  
  if (!button) return;

  if (button.disabled) return;

  const page = Number(button.dataset.page);
  const type = button.dataset.type;

  if (Number.isNaN(page) || !type) return;

  const cached = APP.cache[type];
  cached.page = page;

  switch (type) {
    case "doctors":
      await fetchDoctorsTable();
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

async function handleActionButtons(action, btn) {

    console.log(`Action triggered: ${action}`);

    switch (action) {

        case "open-settings":
        case "open-profile":
            loadPage("settings");
            break;

        case "export":
            exportPageData("dashboard");
            break;

        default:
            console.warn("Unknown action");
    }

}

function handleDashboardActions(action, btn) {
    switch (action) {
        case "export":
            exportPageData("dashboard");
            break;

        default:
            console.warn(`Unknown dashboard action: ${action}`);
    }
}


// ================= COMMON FUNCTIONS ====================

function handleFilter(card) {

    if (card.classList.contains("active")) return;

    const page = PAGE_CONFIG[APP.currentPage];

    if (!page) return;

    const grid = card.closest(".stat-grid");

    grid.querySelector(".stat-card.active")
        ?.classList.remove("active");

    card.classList.add("active");

    page.cache.filters[page.filterKey] = card.dataset.filter;
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
      `/my_doctor/public/admin/load-page.php?page=${page}`,
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

  const targetPage = (page === "profile") ? "settings" : page;
  const activeLink = document.querySelector(`[data-page="${targetPage}"]`) || document.querySelector(`[data-page="${page}"]`);

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
    
    case "settings":
    case "profile":
      initializeProfile();
      break;
  }
}


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

// ==============================Initialize Dashboard=====================================

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
      "/my_doctor/public/admin/api/dashboard.php?",
    );

    APP.cache.dashboard = response;
    console.log(response);
    renderDashboard(response);
  } catch (error) {
    console.error(error);
  }
}

function renderDashboard(data) {
  renderCurrentUser(data.currentUser);
  renderDashboardStats(data.stats);
  renderRecentAppointments(data.recentAppointments);
  renderSystemAlerts(data.alerts);
}

function renderDashboardStats(stats) {
  console.log("render State", stats);

  document.getElementById("total-patients").textContent =
    stats.totalPatientsCount;

  document.getElementById("active-doctors").textContent =
    stats.activeDoctorsCount;

  document.getElementById("appointments-today").textContent =
    stats.appointmentsTodayCount;

  document.getElementById("blocked-users").textContent =
    stats.blockedUsersCount;

}

function renderCurrentUser(user) {
  const greetEl = document.getElementById("greet-with-user-name");
  if (greetEl && user && user.name) {
    greetEl.textContent =
      `Welcome back, ${user.name}. Here's what's happening today.`;
  }

  if (user) {
    setupUserProfile(user);
  }
}

function renderRecentAppointments(appointments) {
  const tbody = document.getElementById("recent-appointments-body");

  if (!tbody) return;

  tbody.innerHTML = "";

  appointments.forEach((appointment) => {
    const avatar = appointment.profile_url
    ? `
            <div class="user-avatar">
              <img src="${appointment.profile_url}" alt="Doctor"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(appointment.patient_name)}
            </div>
          `;
    tbody.innerHTML += `
            <tr>
                <td>
                    <div class="user-cell">
                    <div class="user-avatar">
                      ${avatar}
                    </div>
                    <div><p class="user-name">${appointment.patient_name}</p>
                    <p class="user-id">PAT-${appointment.patient_id}</p></div>
                  </div>
                </td>

                <td>
                    ${appointment.doctor_name}
                </td>

                <td>
                    ${formatDateTime(appointment.appointment_date, appointment.appointment_time)}
                </td>

                <td>
                    ${appointment.status}
                </td>


            </tr>
        `;
  });
}

function renderSystemAlerts(alerts) {
  const container = document.getElementById("system-alerts-timeline");
  if (!container) return;
  container.innerHTML = "";

  const displayAlerts = (alerts || []).slice(0, 4);

  if (displayAlerts.length === 0) {
    container.innerHTML = `<p style="padding: 16px; color: var(--on-surface-variant); font-size: 13px;">No active system alerts.</p>`;
    return;
  }

  displayAlerts.forEach((alert) => {
    let dotClass = "primary";
    if (alert.type === "error" || alert.type === "danger") dotClass = "error";
    else if (alert.type === "warning") dotClass = "warning";
    else if (alert.type === "success") dotClass = "success";

    const item = document.createElement("div");
    item.className = "timeline-item";
    item.innerHTML = `
      <div class="timeline-indicator">
        <div class="timeline-dot ${dotClass}"></div>
        <div class="timeline-line"></div>
      </div>
      <div>
        <p class="timeline-title">${alert.title}</p>
        <p class="timeline-body">${alert.body}</p>
        <p class="timeline-time">${formatRelativeTime(alert.time || alert.created_at)}</p>
      </div>
    `;
    container.appendChild(item);
  });
}

//================================= DOCTORS ===============================================

function initializeDoctors() {
  const cached = APP.cache.doctors;

  if (cached.table) {
    renderDoctorsTable(cached.table);
    if (cached.pagination) renderPagination(cached.pagination, "doctors");
  }

  fetchDoctorsStats();
  fetchDoctorsTable();
}

async function fetchDoctorsStats() {
  const stats = await fetchAPI("/my_doctor/public/admin/api/doctors.php?action=stats");
  renderDoctorsStats(stats);
}

async function fetchDoctorsTable() {
  const cached = APP.cache.doctors;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });
  const response = await fetchAPI(
    `/my_doctor/public/admin/api/doctors.php?${params}`,
  );

  cached.table = response.doctors;
  cached.pagination = response.pagination;
  
  renderDoctorsTable(response.doctors);
  console.log(response);
  renderPagination(response.pagination, "doctors");
}

function renderDoctorsStats(stats) {
  console.log(stats);
  document.getElementById("registered-doctors").textContent = stats.total.count;
  renderActiveTrend(stats.active, "doctors");
  document.getElementById("pending-verification").textContent =
    stats.pending_verification.count;
  renderSuspendedTrend(stats.blocked, "doctors");
}

function renderDoctorsTable(doctors) {
  let html = "";

  doctors.forEach((doc) => {
    const avatar = doc.profile_url
      ? `
            <div class="user-avatar">
              <img src="${doc.profile_url}" alt="Doctor"/>
            </div>
          `
      : `
            <div class="user-avatar">
              ${getInitials(doc.name)}
            </div>
          `;

    html += `

        <tr>
          <td>
            <div class="user-cell">
              ${avatar}
              <div><p class="user-name">${doc.name}</p><p class="user-id">DOC-${doc.user_id}</p></div>
            </div>
          </td>
          <td><span class="font-body-md text-on-surface">${doc.specialization}</span></td>
          <td><span class="font-body-md text-on-surface-variant">${doc.department}</span></td>
          <td><span class="badge badge-${doc.status}">${capitalize(doc.status)}</span></td>
          <td><span class="font-body-md text-on-surface-variant">${doc.created_at}</span></td>
          <td class="text-right">
            <div class="action-cell">
              <button data-page="doctors" data-action="edit" data-id="${doc.user_id}" class="btn-icon"><span class="material-symbols-outlined">edit</span></button>
              <button data-page="doctors" data-action="view" data-id="${doc.user_id}" class="btn-icon"><span class="material-symbols-outlined">visibility</span></button>
            </div>
          </td>
        </tr>
    
    `;
    document.getElementById("doctors-table-body").innerHTML = html;
  });
}

function getInitials(name = "") {
  return name
    .split(" ")
    .map((word) => word[0])
    .join("")
    .substring(0, 2)
    .toUpperCase();
}

function handleVerificationActions(event) {
  const verifyBtn = event.target.closest(".btn-approve");
  const rejectBtn = event.target.closest(".btn-reject");

  if (verifyBtn) {
    handleApprove(verifyBtn);
  }

  if (rejectBtn) {
    handleReject(rejectBtn);
  }
}

function handleDoctorActions(action, btn) {

    switch (action) {

        case "edit":
            editDoctor(btn.dataset.id);
            break;

        case "view":
            viewUserDetails(btn.dataset.id);
            break;

        case "delete":
            removeDoctor(btn.dataset.id);
            break;
          
        case "doctors-filter":
          handleFilter(btn);
          break;

        case "export":
          exportPageData("doctors");
          break;

        case "add-doctor":
          openAddDoctorModal();
          break;

        default:
          console.warn(`Unknown doctor action: ${action}`);

    }
}

// DOCTOR ACTIONS

function editDoctor(id) {
  alert(`Edit Doctor ID: ${id}`);
}

function viewDoctor(id) {
  alert(`View Doctor ID: ${id}`);
}

function removeDoctor(id) {
  const confirmed = confirm("Are you sure to delete this doctor?");

  if (confirmed) {
    alert(`Doctor removed: ${id}`);
  }
}



//================================= Patinents Tab ===============================================

async function initializePatients() {
  const cached = APP.cache.patients;
  if (cached.table) {
    renderPatientsTable(cached.table);
    if (cached.pagination) renderPagination(cached.pagination, "patients");
  }

  fetchPatientsStats();

  fetchPatientsTable();
}

async function fetchPatientsStats() {
  const stats = await fetchAPI(
    "/my_doctor/public/admin/api/patients.php?action=stats",
  );
  console.log("Patients Stats",stats);
  renderPatientsStats(stats);
}

function renderPatientsStats(stats) {
  document.getElementById("total-patients-count").textContent =
    stats.total.count;
  renderActiveTrend(stats.active, "patients");
  renderThisMonthTrend(stats.new_this_month);
  renderSuspendedTrend(stats.blocked, "patients");
}

function renderActiveTrend(active, type) {
  let trend = active.percentage + "% of Total";
  if (type === "patients") {
    document.getElementById("active-patients-count-patient").textContent =
      active.count;
    document.getElementById("active-patients-trend-patient").textContent =
      trend;
  } else if (type === "doctors") {
    document.getElementById("active-doctors-count-doctor").textContent =
      active.count;
    document.getElementById("active-doctors-trend-doctor").textContent = trend;
  } else if (type === "dashboard") {
    document.getElementById("active-doctors-count-patient").textContent =
      active.count;
    document.getElementById("active-doctors-trend-patient").textContent = trend;
  }
}

function renderThisMonthTrend(stats) {
  document.getElementById("new-this-month-patients-count").textContent =
    stats.count;

  const trendElement = document.getElementById("this-month-trend");

  trendElement.classList.remove("up", "down");

  if (stats.direction === "up") {
    trendElement.classList.add("up");

    trendElement.innerHTML = `
            +${stats.trend}%
            <span
                class="material-symbols-outlined"
                style="font-size:12px;"
            >
                trending_up
            </span>
        `;
  } else {
    trendElement.classList.add("down");

    trendElement.innerHTML = `
            ${stats.trend}%
            <span
                class="material-symbols-outlined"
                style="font-size:12px;"
            >
                trending_down
            </span>
        `;
  }
}

function renderSuspendedTrend(blocked, type) {
  if (type === "patients") {
    document.getElementById("blocked-patients-count").textContent = blocked.count;
    document.getElementById("blocked-trend-patient").textContent = blocked.percentage + "% of Total";
  } else if (type === "doctors") {
    document.getElementById("blocked-doctor").textContent = blocked.count;
    document.getElementById("blocked-trend-doctor").textContent = blocked.percentage + "% of Total";
  } else if (type == "dashboard") {
    document.getElementById("blocked-doctor").textContent = blocked.count;
    document.getElementById("blocked-trend-doctor").textContent = blocked.percentage + "% of Total";
  }
}

async function fetchPatientsTable() {
  const cached = APP.cache.patients;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });

  const response = await fetchAPI(
    `/my_doctor/public/admin/api/patients.php?${params}`,
  );

  console.log("Patients Table response ",response);

  cached.table = response.patients;
  cached.pagination = response.pagination;

  renderPatientsTable(response.patients);

  renderPagination(response.pagination, "patients");
}

function renderPagination(pagination, type) {
  const header = document.getElementById(`${type}-pagination-header`);

  const start = pagination.total === 0
    ? 0
    : (pagination.page - 1) * pagination.limit + 1;

  const end = pagination.total === 0
    ? 0
    : Math.min(start + pagination.limit - 1, pagination.total);

  header.textContent = `Showing ${start}-${end} of ${pagination.total} data`;

  const { page, totalPages } = pagination;
  

  const container = document.getElementById(`${type}-pagination`);

  if (!container) return;

  const pages = getVisiblePages(page, totalPages);

  let pagesHtml = "";

  pages.forEach((item) => {
    if (item === "...") {
      pagesHtml += `<span class="page-ellipsis">...</span>`;
    } else {
      pagesHtml += `
      <button class="page-btn ${item === page ? "active" : ""}" data-page="${item}" data-type="${type}" data-action="pagination">${item}</button>
      `;
    }
  });

  container.innerHTML = `
            <button
                class="btn-prev-next ${page === 1 ? "disabled" : ""}"
                ${page === 1 ? "disabled" : ""}
                data-page="${page - 1}"
                data-type="${type}"
                data-action="pagination">
                Previous
            </button>

            <div class="pagination-pages">
                ${pagesHtml}
            </div>

            <button
                class="btn-prev-next ${page === totalPages ? "disabled" : ""}"
                ${page === totalPages ? "disabled" : ""}
                data-page="${page + 1}"
                data-type="${type}"
                data-action="pagination">
                Next
            </button>
    `;
}

function getVisiblePages(currentPage, totalPages) {
  const pages = [];

  if (totalPages <= 5) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i);
    }
    return pages;
  }
  pages.push(1);

  if (currentPage > 3) {
    pages.push("...");
  }

  for (
    let i = Math.max(2, currentPage - 1);
    i <= Math.min(totalPages - 1, currentPage + 1);
    i++
  ) {
    pages.push(i);
  }

  if (currentPage < totalPages - 2) {
    pages.push("...");
  }

  pages.push(totalPages);

  return pages;
}

function formatDoctorDisplayName(name) {
  if (!name) return "Doctor";
  const str = String(name).trim();
  if (/^Dr\.?\s+/i.test(str)) {
    return str;
  }
  return `Dr. ${str}`;
}

function createPatientRow(patient) {
  const row = document.createElement("tr");
  const avatar = patient.profile_url
    ? `
            <div class="user-avatar">
              <img src="${patient.profile_url}" alt="Patient"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(patient.name)}
            </div>
          `;

  row.innerHTML = `
    <td>
      <div class="user-cell">
        <div class="user-avatar">
          ${avatar}
        </div>
        <div>
          <p class="user-name">${patient.name}</p>
          <p class="user-id">PAT-${patient.user_id}</p>
        </div>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface-variant">${formatDate(patient.dob)}</span></td>
    <td><span class="font-body-md text-on-surface-variant">${patient.last_login_at ? formatDateTime(patient.last_login_at) : 'Never'}</span></td>
    <td><span class="font-body-md text-on-surface">${patient.email}</span></td>
    <td><span class="badge badge-${patient.status}">${capitalize(patient.status)}</span></td>
    <td class="text-right">
      <div class="action-cell">
        <button class="btn-icon" data-page="patients" data-action="edit" data-id="${patient.user_id}" >
          <span class="material-symbols-outlined">edit</span>
        </button>
        <button class="btn-icon" data-page="patients" data-action="view" data-id="${patient.user_id}">
          <span class="material-symbols-outlined">visibility</span>
        </button>
        <button class="btn-icon" data-page="patients" data-action="delete" data-id="${patient.user_id}">
          <span class="material-symbols-outlined">delete</span>
        </button>
      </div>
    </td>
  `;

  return row;
}


function formatDate(dateString) {
  if (!dateString) return "N/A";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "N/A";
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

function renderPatientsTable(patients) {
  const tbody = document.getElementById("patients-table-body");
  tbody.innerHTML = "";

  patients.forEach((patient) => {
    const row = createPatientRow(patient);
    tbody.appendChild(row);
  });
}

function handlePatientActions(action, btn) {
   switch (action) {

      case "edit":
            editPatient(btn.dataset.id);
            break;

        case "view":
            viewUserDetails(btn.dataset.id);
            break;

        case "delete":
            removePatient(btn.dataset.id);
            break;

        case "patients-filter":
            handleFilter(btn);
            break;

        case "export":
            exportPageData("patients");
            break;

        case "add-patient":
            openAddPatientModal();
            break;
        
        default:
          console.warn(`Unknown patient action: ${action}`);
   }
}


function editPatient(userId) {
  alert(`Edit Patient ID: ${userId}`);
}
function removePatient(userId) {
  alert(`Remove Patient ID: ${userId}`);
}

//=========================================Appointments=======================================

function initializeAppointments() {
  const cached = APP.cache.appointments;

  if (cached.table) {
    renderAppointmentsTable(cached.table);
    if (cached.pagination) renderPagination(cached.pagination, "appointments");
  }

  fetchAppointmentsStats();
  fetchAppointmentsTable(cached.page, cached.search, cached.filters);
}

async function fetchAppointmentsStats() {
  const stats = await fetchAPI(
    "/my_doctor/public/admin/api/appointments.php?action=stats",
  );
  console.log("Appointments Stats: ", stats);
  renderAppointmentsStats(stats);
}

async function fetchAppointmentsTable(page = 1, limit=10, search = "", filters = {}) {
  const cached = APP.cache.appointments;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });
  const response = await fetchAPI(
    `/my_doctor/public/admin/api/appointments.php?${params}`,
  );

  console.log("Appointments Table: ", response);
  cached.table = response.appointments;
  cached.pagination = response.pagination;

  renderAppointmentsTable(response.appointments);
  renderPagination(response.pagination, "appointments");
}

function renderAppointmentsStats(stats) {
  document.getElementById("today-appointment-count").textContent =
    stats.total_today.count;

  document.getElementById("total-today-appointment-trend").textContent =
    `+${stats.total_today.trend}% from yesterday`;

  document.getElementById("confirmed-appointment-count").textContent =
    stats.confirmed_today.count;

  document.getElementById("confirmed-today-appointment-trend").textContent =
    `${stats.confirmed_today.percentage}% of today`;

  document.getElementById("pending-appointment-count").textContent =
    stats.pending.count;

  document.getElementById("pending-today-appointment-trend").textContent =
    `${stats.confirmed_today.percentage}% of today`;

  document.getElementById("cancelled-appointment-count").textContent = 
    stats.cancelled.count;

  document.getElementById("cancelled-today-appointment-trend").textContent =
    `${stats.cancelled.percentage}% of today`;
}

function renderAppointmentsTable(data) {
  const tbody = document.getElementById("appointments-table-body");
  tbody.innerHTML = "";
  data.forEach((appointment) => {
    const row = createAppointmentRow(appointment);
    tbody.appendChild(row);
  });
}

function goToAppointmentsPage(page) {
  fetchAppointmentsTable(
    page,
    APP.cache.appointments.search,
    APP.cache.appointments.filters,
  );
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
              ${getInitials(appointment.patient_name)}
            </div>
          `;

  row.innerHTML = `
    <td>
      <div class="user-cell">
      ${avatar}
        <div>
          <p class="user-name">${appointment.patient_name}</p>
          <p class="user-id">PAT-${appointment.patient_id}</p>
        </div>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface">${formatDoctorDisplayName(appointment.doctor_name)}</span></td>
    <td><span class="font-body-md text-on-surface-variant">${formatDateTime(appointment.appointment_date, appointment.appointment_time)}</span></td>
    <td><span class="font-body-md text-on-surface">${formatAppointmentType(appointment.appointment_mode)}</span></td>
    <td><span class="badge badge-${appointment.status}">${capitalize(appointment.status)}</span></td>
    <td class="text-right">
      <div class="action-cell">
        <button data-page="appointments" data-action="edit" data-id="${appointment.appointment_id}" class="btn-icon"><span class="material-symbols-outlined">edit</span></button>
        <button data-page="appointments" data-action="view" data-id="${appointment.appointment_id}" class="btn-icon"><span class="material-symbols-outlined">visibility</span></button>
      </div>
    </td>
  `;

  return row;
}

function formatDateTime(date, time) {
    if (!date) return "Never";
    let dateTime;

    try {
        if (time !== undefined && time !== null && time !== "") {
            dateTime = new Date(`${date}T${time}`);
        } else if (typeof date === 'string') {
            dateTime = new Date(date.replace(" ", "T"));
        } else {
            dateTime = new Date(date);
        }

        if (isNaN(dateTime.getTime())) return "Never";

        const datePart = new Intl.DateTimeFormat("en-US", {
            month: "short",
            day: "numeric",
            year: "numeric"
        }).format(dateTime);

        const timePart = new Intl.DateTimeFormat("en-US", {
            hour: "numeric",
            minute: "2-digit",
            hour12: true
        }).format(dateTime);

        return `${datePart} • ${timePart}`;
    } catch (e) {
        return "Never";
    }
}

function formatAppointmentType(type) {
    return type
        .replace(/_/g, " ")
        .replace(/\b\w/g, char => char.toUpperCase());
}

function handleAppointmentActions(action, btn) {
  switch (action) {
    case "edit":
      editAppointment(btn.dataset.id);
      break;

    case "view":
      viewAppointment(btn.dataset.id);
      break;

    case "appointments-filter":
      handleFilter(btn);
      break;

    case "export":
      exportPageData("appointments");
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

// =============================== User Verification ===================================

function initializeUserVerification() {
  const cached = APP.cache.userVerification;

  if (cached.table) {
    renderUserVerificationsTable(cached.table);
    if (cached.pagination)
      renderPagination(cached.pagination, "userVerification");
  }

  fetchUserVerificationsStats();
  fetchUserVerificationTable();
}

async function fetchUserVerificationsStats() {
  const stats = await fetchAPI(
    "/my_doctor/public/admin/api/user-verification.php?action=stats",
  );
  console.log("Verification Stats ", stats);
  renderUserVerificationStats(stats);
}

async function fetchUserVerificationTable() {
  const cached = APP.cache.userVerification;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });
  const response = await fetchAPI(
    `/my_doctor/public/admin/api/user-verification.php?${params}`,
  );
  console.log("Verification Response", response);
  cached.table = response.doctors;
  cached.pagination = response.pagination;

  renderUserVerificationsTable(response.doctors);

  renderPagination(response.pagination, "userVerification");
}

function renderUserVerificationStats(stats) {
  document.getElementById("total-pending-review-count").textContent =
    stats.totalPendingReview.count;
  document.getElementById("total-pending-review-trend").textContent =
    `+${stats.totalPendingReview.trend} Today`;
  document.getElementById("approved-review-count").textContent =
    stats.totalApprovedReview.count;
  document.getElementById("rejected-review-count").textContent =
    stats.rejected.count;
  document.getElementById("avg-review-time").textContent =
    `${stats.avgReviewTime} h`;
}

function renderUserVerificationsTable(data) {
  const tbody = document.getElementById("user-verification-table-body");
  tbody.innerHTML = "";
  data.forEach((user) => {
    const row = createUserVerificationRow(user);
    tbody.appendChild(row);
  });
}

function createUserVerificationRow(user) {
  const row = document.createElement("tr");

  const avatar = user.profile_url
    ? `
            <div class="user-avatar">
              <img src="${user.profile_url}" alt="Doctor"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(user.name)}
            </div>
          `;

  const docTypes = (user.document_types || "Medical License + Gov ID")
    .replace(/_/g, " ")
    .replace(/\b\w/g, c => c.toUpperCase());
  const submittedDate = user.submitted_at || user.sumbitted_at || user.created_at;

  row.innerHTML = `
    <td>
      <div class="user-cell">
      ${avatar}
        <div>
          <p class="user-name">${formatDoctorDisplayName(user.name)}</p>
          <p class="user-id">DOC-${user.user_id}</p>
        </div>
      </div>
    </td>
    <td>
      <div class="reason-cell">
        <span class="material-symbols-outlined" style="font-size:18px;color:var(--on-surface-variant);">description</span>
        <span class="font-body-md text-on-surface">${docTypes}</span>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface-variant">${formatDate(submittedDate)}</span></td>
    <td><span class="badge">${user.department || 'General Medicine'}</span></td>
    <td><span class="badge">${user.specialization || 'General Physician'}</span></td>
    <td class="text-right">
      <div class="action-cell">
        <button data-page="userVerification" data-id="${user.user_id}" class="btn-approve" data-action="approve">Approve</button>
        <button data-page="userVerification" data-id="${user.user_id}" class="btn-reject" data-action="reject">Reject</button>
        <button data-page="userVerification" data-id="${user.user_id}" class="btn-icon" data-action="view"><span class="material-symbols-outlined">visibility</span></button>
      </div>
    </td>
  `;

  return row;
}

function handleUserVerificationActions(action, btn) {
  switch (action) {
    case "approve":
      handleApprove(btn);
      break;

    case "reject":
      handleReject(btn);
      break;

    case "view":
      viewUserDetails(btn.dataset.id);
      break;

    case "export":
      exportPageData("userVerification");
      break;

    default:
      console.warn(`Unknown verification action: ${action}`);
  }
}

async function updateVerification(button, action) {
  if (!button) return;

  const id = button.dataset.id;
  const label = action === "approve" ? "Approve" : "Reject";

  if (!confirm(`${label} verification for doctor #${id}?`)) return;

  let reason = "";
  if (action === "reject") {
    reason = prompt("Reason for rejection (optional):") || "";
  }

  const formData = new FormData();
  formData.append("action", action);
  formData.append("id", id);
  formData.append("reason", reason);

  button.disabled = true;
  try {
    const response = await fetch("/my_doctor/public/admin/api/user-verification.php", {
      method: "POST",
      body: formData
    });
    const result = await response.json();

    if (!result.status) {
      alert(result.message || `Failed to ${action} verification.`);
      return;
    }

    button.closest("tr")?.remove();
    fetchUserVerificationTable();
    fetchUserVerificationsStats();
  } catch (error) {
    console.error(`Verification ${action} failed:`, error);
    alert("Something went wrong. Please try again.");
  } finally {
    button.disabled = false;
  }
}

function handleApprove(button) {
  return updateVerification(button, "approve");
}

function handleReject(button) {
  return updateVerification(button, "reject");
}

// =============================== Blocked Users ===================================

function initializeBlockedUsers() {
  const cached = APP.cache.blockedUsers;

  if (cached.table) {
    renderBlockedUsersTable(cached.table);
    if (cached.pagination) renderPagination(cached.pagination, "blockedUsers");
  }

  fetchBlockedUsersStats();
  fetchBlockedUsersTable();
}

async function fetchBlockedUsersStats() {
  const stats = await fetchAPI(
    "/my_doctor/public/admin/api/blocked-users.php?action=stats"
  );
  console.log(stats);
  renderBlockedUsersStats(stats);
}

async function fetchBlockedUsersTable() {
  const cached = APP.cache.blockedUsers;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });

  const data = await fetchAPI(
    `/my_doctor/public/admin/api/blocked-users.php?${params}`
  );

  console.log(data);

  cached.table = data.blockedUsers;
  cached.pagination = data.pagination;

  renderBlockedUsersTable(data.blockedUsers);
  renderPagination(data.pagination, "blockedUsers");
}

function renderBlockedUsersStats(stats) {
  if (stats.totalBlocked) {
    document.getElementById("total-blocked-count").textContent =
      stats.totalBlocked.count;
    document.getElementById("total-blocked-trend").textContent =
      `+${stats.totalBlocked.trend} Today`;
  }
  if (stats.doctorsBlocked) {
    document.getElementById("doctor-blocked-count").textContent =
      stats.doctorsBlocked.count;
    document.getElementById("doctor-blocked-trend").textContent =
      `${stats.doctorsBlocked.trend}% of Total`;
  }
  if (stats.patientsBlocked) {
    document.getElementById("patient-blocked-count").textContent =
      stats.patientsBlocked.count;
    document.getElementById("patient-blocked-trend").textContent =
      `${stats.patientsBlocked.trend}% of Total`;
  }
  if (stats.appealBlocked) {
    document.getElementById("blocked-appeal-count").textContent =
      stats.appealBlocked.count;
    document.getElementById("blocked-appeal-trend").textContent =
      `+${stats.appealBlocked.trend} Today`;
  }

  if (Array.isArray(stats.audit_logs)) {
    renderRestrictionAuditLogs(stats.audit_logs);
  }
  if (Array.isArray(stats.policies)) {
    renderRestrictionPolicies(stats.policies);
  }
}

function renderRestrictionAuditLogs(logs) {
  const container = document.getElementById("restriction-audit-timeline");
  if (!container) return;
  container.innerHTML = "";

  const displayLogs = (logs || []).slice(0, 4);

  if (displayLogs.length === 0) {
    container.innerHTML = `<p style="padding: 16px; color: var(--on-surface-variant); font-size: 13px;">No restriction audit events found.</p>`;
    return;
  }

  displayLogs.forEach((log) => {
    const isUnblock = log.action === 'unblocked';
    const dotClass = isUnblock ? 'success' : 'error';
    const rolePrefix = log.user_type === 'doctor' ? 'DOC' : 'PAT';
    const displayName = log.user_type === 'doctor' ? formatDoctorDisplayName(log.user_name) : log.user_name;
    const actionText = isUnblock ? 'was unblocked' : 'was blocked';
    const notesText = log.notes ? ` ${log.notes}` : '';
    const adminText = log.admin_name ? ` Action taken by ${log.admin_name}.` : '';

    const item = document.createElement("div");
    item.className = "timeline-item";
    item.innerHTML = `
      <div class="timeline-indicator">
        <div class="timeline-dot ${dotClass}"></div>
        <div class="timeline-line"></div>
      </div>
      <div>
        <p class="timeline-title">${displayName} (${rolePrefix}-${log.user_id}) ${actionText}</p>
        <p class="timeline-body">${log.reason}.${notesText}${adminText}</p>
        <p class="timeline-time">${formatRelativeTime(log.created_at)}</p>
      </div>
    `;
    container.appendChild(item);
  });
}

function renderRestrictionPolicies(policies) {
  const list = document.getElementById("restriction-policy-list");
  if (!list) return;
  list.innerHTML = "";

  if (!policies || policies.length === 0) {
    list.innerHTML = `<li><span class="material-symbols-outlined">info</span><span>No policies defined.</span></li>`;
    return;
  }

  policies.forEach((p) => {
    const li = document.createElement("li");
    li.innerHTML = `
      <span class="material-symbols-outlined">${p.icon || 'security'}</span>
      <span>${p.description}</span>
    `;
    list.appendChild(li);
  });
}

function formatRelativeTime(dateStr) {
  if (!dateStr) return "Recent";
  const date = new Date(typeof dateStr === 'string' ? dateStr.replace(" ", "T") : dateStr);
  if (isNaN(date.getTime())) return "Recent";
  const now = new Date();
  const diffSec = Math.floor((now - date) / 1000);
  if (diffSec < 60) return "Just now";
  if (diffSec < 3600) return `${Math.floor(diffSec / 60)} minutes ago`;
  if (diffSec < 86400) return `${Math.floor(diffSec / 3600)} hours ago`;
  if (diffSec < 172800) return "Yesterday, " + date.toLocaleTimeString("en-US", { hour: "numeric", minute: "2-digit", hour12: true });
  return date.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}

function renderBlockedUsersTable(blockedUsers) {
  console.log("RenderBlockedUsersTable is called");
  const tbody = document.getElementById("blocked-users-table-body");

  tbody.innerHTML = "";
  blockedUsers.forEach((user) => {
    const row = createBlockedUsersRow(user);
    tbody.appendChild(row);
  });
}

function createBlockedUsersRow(user) {
  const row = document.createElement("tr");

  const avatar = user.profile_url
    ? `
            <div class="user-avatar">
              <img src="${user.profile_url}" alt="${user.user_type === 'doctor' ? 'Doctor' : 'Patient'}"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(user.name)}
            </div>
          `;

  const displayName = user.user_type === 'doctor' ? formatDoctorDisplayName(user.name) : user.name;
  const userPrefix = user.user_type === 'doctor' ? 'DOC' : 'PAT';
  const reasonText = user.reason || "Terms of service violation";
  const blockedDate = user.blocked_at || user.created_at;

  row.innerHTML = `
    <td>
      <div class="user-cell">
        <div class="user-avatar">
        ${avatar}
        </div>
        <div>
          <p class="user-name">${displayName}</p>
          <p class="user-id">${userPrefix}-${user.user_id}</p>
        </div>
      </div>
    </td>
    <td><span class="badge badge-${user.user_type}">${capitalize(user.user_type)}</span></td>
    <td>
      <div class="reason-cell">
        <span class="reason-dot error"></span>
        <p class="font-body-md text-on-surface">${reasonText}</p>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface-variant">${formatDate(blockedDate)}</span></td>
    <td class="text-right">
      <div class="action-cell">
        <button class="btn-unblock" data-page="blockedUsers" data-action="unblock" data-id="${user.user_id}">Unblock</button>
        <button class="btn-icon" data-page="blockedUsers" data-action="view" data-id="${user.user_id}"><span class="material-symbols-outlined">visibility</span></button>
      </div>
    </td>
  `;

  return row;
}

async function handleBlockedUserActions(action, btn) {
  
    switch (action) {

        case "unblock":
            await unblockUser(btn);
            break;

        case "blocked-filter":
          handleFilter(btn);
          break;

        case "view":
            viewUserDetails(btn.dataset.id);
            break;

        case "export":
            exportPageData("blockedUsers");
            break;
        
          
        default:
          console.warn(`Unknown profile action: ${action}`);
    }
}

async function unblockUser(button){
  
    if(!button) return;

    const id = button.dataset.id;

    if(!confirm(`Unblock this user? ${id}`)) return;

    const formData = new FormData();

    formData.append("action", "unblock");
    formData.append("id", id);

    const response = await fetch(
        "/my_doctor/public/admin/api/blocked-users.php",
        {
            method: "POST",
            body: formData
        }
    );

    const result = await response.json();

    if(result.status){
        button.closest("tr")?.remove();
        fetchBlockedUsersTable();
        fetchBlockedUsersStats();
    } else {
        alert(result.message || "Failed to unblock user.");
    }
}

// ================================ Profile =========================================

function initializeProfile() {
  const cached = APP.cache.profile;
  if(cached) {
    renderProfile(cached);
  }
  fetchProfileData();
}

async function fetchProfileData() {
  const data = await fetchAPI("/my_doctor/public/admin/api/profile.php?action=getProfileData");

  console.log("Profile Data: ", data);
  APP.cache.profile = data;
  renderProfile(data);
  
}

function renderProfile(profile) {
  document.getElementById("profile-email").textContent = profile.email;
  document.getElementById("profile-phone").textContent = profile.phone;
  document.querySelectorAll(".profile-joined").forEach(el => {
      el.textContent = formatDate(profile.created_at);
  });
  document.querySelectorAll(".profile-last-login-value").forEach(el => {
    el.textContent = `${formatDateTime(profile.last_login_at)}`;
  });
  document.querySelectorAll(".profile-status").forEach(el => {
    el.textContent = `${profile.status === 'active' ? 'Active' : 'Blocked'}`;
  });
  document.querySelectorAll(".profile-role").forEach(el => {
    el.textContent = `${capitalize(profile.user_type)}`;
  });
  document.querySelectorAll(".profile-id").forEach(el => {
    el.textContent = `ADM-${profile.user_id}`;
  });
  document.querySelectorAll(".profile-name").forEach(el => {
    el.textContent = profile.name;
  });
  document.querySelectorAll(".profile-last-login-ip").forEach(el => {
    el.textContent = profile.last_login_ip;
  });

  setTheme(profile.theme || localStorage.getItem("theme") || "light");
  const emailNotif = document.getElementById("emailNotif");
  const smsNotif = document.getElementById("smsNotif");

  emailNotif.checked = profile.email_notifications;

  smsNotif.checked = profile.sms_notifications;
}

function setTheme(theme) {
    const isDark = (theme === "dark");

    if (document.body) {
        document.body.classList.toggle("dark-mode", isDark);
    }
    if (document.documentElement) {
        document.documentElement.classList.toggle("dark-mode", isDark);
    }

    const buttons = document.querySelectorAll("#themeButtons button");
    buttons.forEach(btn => {
        btn.classList.toggle("active", btn.dataset.value === theme);
    });
}

function handleChanges(e) {

    const input = e.target;

    if (!input.matches("[data-setting]")) return;

    updatePreference(
        input.dataset.setting,
        input.checked ? 1 : 0
    );
}

async function updatePreference(key, value) {

    if (!APP.cache.profile) APP.cache.profile = {};
    const previousValue = APP.cache.profile[key];

    APP.cache.profile[key] = value;

    if (key === "theme") {
        localStorage.setItem("theme", value);
        setTheme(value);
    }

    try {
        
        await fetchAPI("/my_doctor/public/admin/api/profile.php?action=updatePreference", {
            method: "POST",
            body: JSON.stringify({
                key,
                value
            })
        });

        console.log("Update successful");

    } catch (error) {

        console.error(error);

        APP.cache.profile[key] = previousValue;

        if (key === "theme") {
            const revertTheme = previousValue || "light";
            localStorage.setItem("theme", revertTheme);
            setTheme(revertTheme);

        } else {
            document.querySelector(
                `[data-setting="${key}"]`
            ).checked = previousValue;

        }

        alert(error.message);

    }

}

function handleProfileActions(action, btn) {

    switch (action) {

        case "togglePassword":
            togglePassword(btn);
            break;

        case "uploadPhoto":
            uploadProfilePhoto();
            break;
        
        case "handleThemePreference":
            handleThemePreference(btn);
            break;
          
        default:
          console.warn(`Unknown profile action: ${action}`);
    }

}

function togglePassword(button) {

    const targetId = button.dataset.target;

    const input = document.getElementById(targetId);

    if (!input) return;

    const hidden = input.type === "password";

    input.type = hidden ? "text" : "password";

    button.textContent = hidden
        ? "visibility_off"
        : "visibility";
}

function uploadProfilePhoto() {
  alert("Upload profile photo functionality is not implemented yet.");
}

function updatePreferences() {
  alert("Update preferences functionality is not implemented yet.");
}

function handleThemePreference(btn){
  console.log("Theme button clicked:", btn);

  if (!btn) return;

  const theme = btn.dataset.value;

  setTheme(theme);
  updatePreference("theme", theme);
}

async function handlePasswordForm(form){

    if(!form) return;

    const currentPassword =
        form.currentPassword.value;

    const newPassword =
        form.newPassword.value;

    const confirmPassword =
        form.confirmPassword.value;

    if (!currentPassword || !newPassword || !confirmPassword) {
      alert('Please fill in all password fields.');
      return;
    }

    if (newPassword !== confirmPassword) {
      alert('New password and confirm password do not match.');
      return;
    }

    if (newPassword.length < 6) {
      alert('New password must be at least 6 characters long.');
      return;
    }

    try{
      
      await fetchAPI("/my_doctor/public/admin/api/profile.php?action=changePassword",{
        method:"POST",

        body:JSON.stringify({

        currentPassword,

        newPassword

        })
      });

      alert("Password updated successfully.");
      form.reset();

    }catch(error){
        console.error(error);
        alert("An error occurred while updating the password.");
    }

}

// ================= UNIVERSAL EXPORT & EXCEL DOWNLOAD ====================

function downloadCSV(filename, headers, rows) {
  const escapeCSV = (val) => {
    if (val === null || val === undefined) return '""';
    let str = String(val).replace(/"/g, '""');
    return `"${str}"`;
  };

  const headerLine = headers.map(h => escapeCSV(typeof h === "object" ? h.label : h)).join(",");
  const dataLines = rows.map(row => {
    if (Array.isArray(row)) {
      return row.map(escapeCSV).join(",");
    }
    return headers.map(h => {
      const key = typeof h === "object" ? h.key : h;
      let val = row[key];
      if (typeof h === "object" && typeof h.formatter === "function") {
        val = h.formatter(val, row);
      }
      return escapeCSV(val);
    }).join(",");
  });

  const csvContent = "\uFEFF" + [headerLine, ...dataLines].join("\r\n");
  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  const nowStr = new Date().toISOString().slice(0, 10);
  const finalFilename = filename.includes(".csv") ? filename : `${filename}_${nowStr}.csv`;
  link.setAttribute("download", finalFilename);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

async function exportPageData(page) {
  const targetPage = page || APP.currentPage;
  try {
    switch (targetPage) {
      case "dashboard": {
        const res = await fetchAPI("/my_doctor/public/admin/api/dashboard.php");
        if (!res) return;
        const stats = res.stats || {};
        const appointments = res.recentAppointments || [];
        const rows = [
          ["--- METRICS SUMMARY ---", "", "", ""],
          ["Total Patients", stats.totalPatientsCount || 0, "", ""],
          ["Active Doctors", stats.activeDoctorsCount || 0, "", ""],
          ["Appointments Today", stats.appointmentsTodayCount || 0, "", ""],
          ["Blocked Users", stats.blockedUsersCount || 0, "", ""],
          ["", "", "", ""],
          ["--- RECENT APPOINTMENTS ---", "", "", ""]
        ];
        appointments.forEach(a => {
          rows.push([
            a.patient_name || "N/A",
            formatDoctorDisplayName(a.doctor_name || "Doctor"),
            a.appointment_date || "N/A",
            a.status || "N/A"
          ]);
        });
        downloadCSV("system_overview_report", ["Section / Metric", "Value / Detail", "Info", "Status"], rows);
        break;
      }

      case "doctors": {
        const res = await fetchAPI("/my_doctor/public/admin/api/doctors.php?action=table&limit=1000&page=1");
        const list = res?.doctors || [];
        if (!list.length) {
          alert("No doctor records found to export.");
          return;
        }
        const headers = [
          { label: "Doctor ID", key: "doctor_id" },
          { label: "Name", key: "doctor_name", formatter: (val) => formatDoctorDisplayName(val) },
          { label: "Email", key: "email" },
          { label: "Phone", key: "phone" },
          { label: "Specialization", key: "specialization" },
          { label: "Department", key: "department" },
          { label: "Experience (Years)", key: "experience" },
          { label: "Working Hours", key: "working_time" },
          { label: "Online Fee", key: "online_fee" },
          { label: "Offline Fee", key: "offline_fee" },
          { label: "Status", key: "status" },
          { label: "Verification", key: "verification_status" }
        ];
        downloadCSV("doctors_list_export", headers, list);
        break;
      }

      case "patients": {
        const res = await fetchAPI("/my_doctor/public/admin/api/patients.php?action=table&limit=1000&page=1");
        const list = res?.patients || [];
        if (!list.length) {
          alert("No patient records found to export.");
          return;
        }
        const headers = [
          { label: "Patient ID", key: "user_id" },
          { label: "Name", key: "name" },
          { label: "Email", key: "email" },
          { label: "Phone", key: "phone" },
          { label: "Gender", key: "gender" },
          { label: "Blood Group", key: "blood_type" },
          { label: "City", key: "city" },
          { label: "State", key: "state" },
          { label: "Status", key: "status" },
          { label: "Registered At", key: "created_at" }
        ];
        downloadCSV("patients_list_export", headers, list);
        break;
      }

      case "appointments": {
        const res = await fetchAPI("/my_doctor/public/admin/api/appointments.php?action=table&limit=1000&page=1");
        const list = res?.appointments || [];
        if (!list.length) {
          alert("No appointment records found to export.");
          return;
        }
        const headers = [
          { label: "Appointment ID", key: "appointment_id" },
          { label: "Patient Name", key: "patient_name" },
          { label: "Doctor Name", key: "doctor_name", formatter: (val) => formatDoctorDisplayName(val) },
          { label: "Date", key: "appointment_date" },
          { label: "Time Slot", key: "appointment_time" },
          { label: "Type", key: "appointment_type" },
          { label: "Status", key: "status" },
          { label: "Consultation Fee", key: "consultation_fee" }
        ];
        downloadCSV("appointments_export", headers, list);
        break;
      }

      case "userVerification":
      case "user-verification": {
        const res = await fetchAPI("/my_doctor/public/admin/api/user-verification.php?action=table&limit=1000&page=1");
        const list = res?.verifications || [];
        if (!list.length) {
          alert("No user verification records found to export.");
          return;
        }
        const headers = [
          { label: "User ID", key: "user_id" },
          { label: "Name", key: "user_name" },
          { label: "Role", key: "user_type" },
          { label: "Email", key: "email" },
          { label: "Medical Reg. No", key: "medical_reg_no" },
          { label: "Specialization", key: "specialization" },
          { label: "Verification Status", key: "verification_status" },
          { label: "Registered Date", key: "created_at" }
        ];
        downloadCSV("user_verification_export", headers, list);
        break;
      }

      case "blockedUsers":
      case "blocked-users": {
        const res = await fetchAPI("/my_doctor/public/admin/api/blocked-users.php?action=table&limit=1000&page=1");
        const list = res?.blockedUsers || [];
        if (!list.length) {
          alert("No blocked user records found to export.");
          return;
        }
        const headers = [
          { label: "User ID", key: "user_id" },
          { label: "Name", key: "user_name" },
          { label: "Role", key: "user_type" },
          { label: "Email", key: "email" },
          { label: "Status", key: "status" },
          { label: "Reason for Block", key: "reason" },
          { label: "Blocked At", key: "blocked_at" }
        ];
        downloadCSV("blocked_users_export", headers, list);
        break;
      }

      default:
        console.warn(`No export handler defined for page: ${targetPage}`);
    }
  } catch (err) {
    console.error("Export error:", err);
    alert("An error occurred while generating the export.");
  }
}

function checkAndRemoveModalOpenClass() {
  const anyActive = document.querySelectorAll(".modal-overlay.active");
  if (anyActive.length === 0) {
    document.body.classList.remove("modal-open");
  }
}

// ================= ADD DOCTOR MODAL ====================

function openAddDoctorModal() {
  const modal = document.getElementById("modal-add-doctor");
  if (!modal) return;
  document.body.classList.add("modal-open");
  modal.classList.add("active");
}

function closeAddDoctorModal() {
  const modal = document.getElementById("modal-add-doctor");
  if (!modal) return;
  modal.classList.remove("active");
  modal.style.display = "";
  checkAndRemoveModalOpenClass();
}

function setupAddDoctorModal() {
  const modal = document.getElementById("modal-add-doctor");
  const closeBtn = document.getElementById("btn-close-add-doctor-modal");
  const cancelBtn = document.getElementById("btn-cancel-add-doctor");
  const form = document.getElementById("form-add-doctor");

  if (closeBtn) closeBtn.onclick = closeAddDoctorModal;
  if (cancelBtn) cancelBtn.onclick = closeAddDoctorModal;
  if (modal) {
    modal.onclick = (e) => {
      if (e.target === modal) closeAddDoctorModal();
    };
  }

  if (form) {
    form.onsubmit = async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById("btn-submit-add-doctor");
      const origText = submitBtn ? submitBtn.textContent : "Save Doctor";
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Saving...";
      }

      const formData = new FormData(form);
      formData.append("action", "add");

      try {
        const res = await fetch("/my_doctor/public/admin/api/doctors.php", {
          method: "POST",
          body: formData
        });
        const json = await res.json();
        if (json.status) {
          alert("Doctor added successfully!");
          form.reset();
          closeAddDoctorModal();
          fetchDoctorsTable();
          fetchDoctorsStats();
        } else {
          alert(json.message || "Failed to add doctor.");
        }
      } catch (err) {
        console.error("Error adding doctor:", err);
        alert("Network error while adding doctor.");
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = origText;
        }
      }
    };
  }
}

// ================= ADD PATIENT MODAL ====================

function openAddPatientModal() {
  const modal = document.getElementById("modal-add-patient");
  if (!modal) return;
  document.body.classList.add("modal-open");
  modal.classList.add("active");
}

function closeAddPatientModal() {
  const modal = document.getElementById("modal-add-patient");
  if (!modal) return;
  modal.classList.remove("active");
  modal.style.display = "";
  checkAndRemoveModalOpenClass();
}

function setupAddPatientModal() {
  const modal = document.getElementById("modal-add-patient");
  const closeBtn = document.getElementById("btn-close-add-patient-modal");
  const cancelBtn = document.getElementById("btn-cancel-add-patient");
  const form = document.getElementById("form-add-patient");

  if (closeBtn) closeBtn.onclick = closeAddPatientModal;
  if (cancelBtn) cancelBtn.onclick = closeAddPatientModal;
  if (modal) {
    modal.onclick = (e) => {
      if (e.target === modal) closeAddPatientModal();
    };
  }

  if (form) {
    form.onsubmit = async (e) => {
      e.preventDefault();
      const submitBtn = document.getElementById("btn-submit-add-patient");
      const origText = submitBtn ? submitBtn.textContent : "Save Patient";
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Saving...";
      }

      const formData = new FormData(form);
      formData.append("action", "add");

      try {
        const res = await fetch("/my_doctor/public/admin/api/patients.php", {
          method: "POST",
          body: formData
        });
        const json = await res.json();
        if (json.status) {
          alert("Patient added successfully!");
          form.reset();
          closeAddPatientModal();
          fetchPatientsTable();
          fetchPatientsStats();
        } else {
          alert(json.message || "Failed to add patient.");
        }
      } catch (err) {
        console.error("Error adding patient:", err);
        alert("Network error while adding patient.");
      } finally {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = origText;
        }
      }
    };
  }
}

// ================= ADMIN NOTIFICATIONS ====================

function initAdminNotifications() {
  const notifBtn = document.getElementById("topbar-notif-btn");
  const modal = document.getElementById("modal-notifications");
  const closeBtn = document.getElementById("btn-close-notif-modal");
  const markAllBtn = document.getElementById("btn-mark-all-read");

  if (notifBtn) {
    notifBtn.onclick = (e) => {
      if (e) e.preventDefault();
      openAdminNotifications();
    };
  }

  if (closeBtn) {
    closeBtn.onclick = closeAdminNotifications;
  }

  if (markAllBtn) {
    markAllBtn.onclick = markAllAdminNotificationsRead;
  }

  if (modal) {
    modal.onclick = (e) => {
      if (e.target === modal) closeAdminNotifications();
    };
  }

  checkAdminUnreadCount();
  setInterval(checkAdminUnreadCount, 30000);
}

async function checkAdminUnreadCount() {
  try {
    const dot = document.getElementById("notif-badge-dot");
    if (!dot) return;
    const res = await fetch("/my_doctor/public/admin/api/notifications.php?action=unread_count");
    const json = await res.json();
    if (json.status && json.data) {
      const unread = json.data.unread_count || 0;
      dot.style.display = unread > 0 ? "block" : "none";
    }
  } catch (e) {
    // silent
  }
}

async function openAdminNotifications() {
  const modal = document.getElementById("modal-notifications");
  const list = document.getElementById("modal-notif-list");
  const subtitle = document.getElementById("modal-notif-subtitle");
  if (!modal) return;

  document.body.classList.add("modal-open");
  modal.classList.add("active");

  if (list) {
    list.innerHTML = `<div style="text-align: center; padding: 40px; color: var(--on-surface-variant);"><span class="material-symbols-outlined" style="font-size: 32px; animation: spin 1s linear infinite;">sync</span><p style="margin-top: 8px;">Loading notifications...</p></div>`;
  }

  try {
    const res = await fetch("/my_doctor/public/admin/api/notifications.php?action=list");
    const json = await res.json();
    if (!json.status || !json.data) {
      if (list) list.innerHTML = `<div style="text-align: center; padding: 40px; color: var(--error-red);">Failed to load notifications.</div>`;
      return;
    }

    const notifications = json.data.notifications || [];
    const unread = json.data.unread_count || 0;

    if (subtitle) {
      subtitle.textContent = unread > 0 ? `${unread} unread notifications` : "All notifications are read";
    }

    if (notifications.length === 0) {
      if (list) {
        list.innerHTML = `
          <div style="text-align: center; padding: 60px 20px; color: var(--on-surface-variant);">
            <span class="material-symbols-outlined" style="font-size: 48px; color: var(--border-subtle); margin-bottom: 8px;">notifications_off</span>
            <h4 style="margin: 0; font-size: 16px; color: var(--text-heading);">No notifications yet</h4>
            <p style="margin: 4px 0 0 0; font-size: 13px;">You will receive alerts here when important system events occur.</p>
          </div>
        `;
      }
      return;
    }

    const iconMap = {
      admin_alert: { icon: "verified_user", bg: "#e0f2fe", color: "#0284c7" },
      admin_security: { icon: "security", bg: "#fee2e2", color: "#b91c1c" },
      new_patient_registration: { icon: "person_add", bg: "#dcfce7", color: "#15803d" },
      system_update: { icon: "build_circle", bg: "#e0e7ff", color: "#4338ca" },
      default: { icon: "notifications", bg: "#f3f4f6", color: "#4b5563" }
    };

    if (list) {
      list.innerHTML = notifications.map(n => {
        const isUnread = (n.is_read === 0);
        const ic = iconMap[n.type] || iconMap.default;
        const safeMsg = escapeHtml(n.message);

        return `
          <div class="notification-item" data-id="${n.notification_id}" style="
            display: flex; align-items: flex-start; gap: 14px; padding: 14px 18px; border-radius: 10px; margin-bottom: 10px;
            background: ${isUnread ? "rgba(0, 102, 204, 0.06)" : "var(--surface, #ffffff)"};
            border: 1px solid ${isUnread ? "var(--primary)" : "var(--border-subtle)"};
            transition: all 0.2s ease;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: ${ic.bg}; color: ${ic.color}; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
              <span class="material-symbols-outlined" style="font-size: 20px;">${ic.icon}</span>
            </div>
            <div style="flex: 1;">
              <p style="margin: 0; font-size: 13.5px; font-weight: ${isUnread ? "600" : "400"}; color: var(--text-heading); line-height: 1.45;">
                ${safeMsg}
              </p>
              <span style="font-size: 11.5px; color: var(--on-surface-variant); margin-top: 4px; display: inline-block;">
                ${n.time_ago || n.formatted_datetime || ''}
              </span>
            </div>
            ${isUnread ? '<span style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb; flex-shrink: 0; margin-top: 6px;" title="Unread"></span>' : ''}
          </div>
        `;
      }).join("");
    }
  } catch (err) {
    console.error("Notifications fetch error:", err);
    if (list) list.innerHTML = `<div style="text-align: center; padding: 40px; color: var(--error-red);">Network error loading notifications.</div>`;
  }
}

function closeAdminNotifications() {
  const modal = document.getElementById("modal-notifications");
  if (!modal) return;
  modal.classList.remove("active");
  modal.style.display = "";
  checkAndRemoveModalOpenClass();
  checkAdminUnreadCount();
}

async function markAllAdminNotificationsRead() {
  try {
    await fetch("/my_doctor/public/admin/api/notifications.php?action=mark_all_read", { method: "POST" });
    const dot = document.getElementById("notif-badge-dot");
    if (dot) dot.style.display = "none";
    const subtitle = document.getElementById("modal-notif-subtitle");
    if (subtitle) subtitle.textContent = "All notifications marked as read";
    const list = document.getElementById("modal-notif-list");
    if (list) {
      list.querySelectorAll(".notification-item").forEach(item => {
        item.style.background = "var(--surface, #ffffff)";
        item.style.borderColor = "var(--border-subtle)";
        const unreadDot = item.querySelector("span[title='Unread']");
        if (unreadDot) unreadDot.remove();
      });
    }
  } catch (e) {
    console.error("Error marking all read:", e);
  }
}

function escapeHtml(str) {
  if (!str) return "";
  const div = document.createElement("div");
  div.textContent = str;
  return div.innerHTML;
}

// ================= PROFILE NAVIGATION ====================

function setupProfileNavigation() {
  const avatarContainer = document.getElementById("header-profile-img-container");
  if (avatarContainer) {
    avatarContainer.style.cursor = "pointer";
    avatarContainer.onclick = (e) => {
      if (e) e.preventDefault();
      loadPage("settings");
    };
  }
}
