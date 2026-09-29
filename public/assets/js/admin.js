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
    doctors: handleDoctorActions,
    patients: handlePatientActions,
    profile: handleProfileActions,
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
  setupSidebarNavigation();
  setupSearchEffect();
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

        case "open-profile":
            loadPage("profile");
            break;

        default:
            console.warn("Unknown action");
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
  document.getElementById("greet-with-user-name").textContent =
    `Welcome back, ${user.name}. Here's what's happening today.`;

  document.getElementById("dashboard-user-image").src =
    user.profile_url ||
    "/my_doctor/public/assets/images/profile-placeholder.svg";
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
  console.log("Alerts:- ", alerts);
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

function createPatientRow(patient) {
  const row = document.createElement("tr");
  const avatar = patient.profile_url
    ? `
            <div class="user-avatar">
              <img src="${patient.profile_url}" alt="Doctor"/>
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
    <td><span class="font-body-md text-on-surface-variant">${formatDate(patient.created_at)}</span></td>
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
  const date = new Date(dateString);
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
          <p class="user-id">DOC-${appointment.patient_id}</p>
        </div>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface">${appointment.doctor_name}</span></td>
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
    let dateTime;

    if (time !== undefined) {
        dateTime = new Date(`${date}T${time}`);
    } else {
        dateTime = new Date(date.replace(" ", "T"));
    }

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

  row.innerHTML = `
    <td>
      <div class="user-cell">
      ${avatar}
        <div>
          <p class="user-name">${user.name}</p>
          <p class="user-id">DOC-${user.user_id}</p>
        </div>
      </div>
    </td>
    <td>
      <div class="reason-cell">
        <span class="material-symbols-outlined" style="font-size:18px;color:var(--on-surface-variant);">description</span>
        <span class="font-body-md text-on-surface">${user.document_types}</span>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface-variant">${formatDate(user.sumbitted_at)}</span></td>
    <td><span class="badge">${user.department}</span></td>
    <td><span class="badge">${user.specialization}</span></td>
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

    default:
      console.warn(`Unknown verification action: ${action}`);
  }
}

function handleApprove(button) {

  if (!button) return;

  const id = button.dataset.id;

  event.stopPropagation();

  const confirmed = confirm(`Approve verification for ${id}?`);

  if (confirmed) {
    row?.remove();
  }
}

function handleReject(button) {

  if (!button) return;

  const id = button.dataset.id;

  event.stopPropagation();

  const confirmed = confirm(`Reject verification for ${id}?`);

  if (confirmed) {
    row?.remove();
  }
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
  document.getElementById("total-blocked-count").textContent =
    stats.totalBlocked.count;
  document.getElementById("total-blocked-trend").textContent =
    `+${stats.totalBlocked.trend} Today`;
  document.getElementById("doctor-blocked-count").textContent =
    stats.doctorsBlocked.count;
  document.getElementById("doctor-blocked-trend").textContent =
    `${stats.doctorsBlocked.trend}% of Total`;
  document.getElementById("patient-blocked-count").textContent =
    stats.patientsBlocked.count;
  document.getElementById("patient-blocked-trend").textContent =
    `${stats.patientsBlocked.trend}% of Total`;
  document.getElementById("blocked-appeal-count").textContent =
    stats.appealBlocked.count;
  document.getElementById("blocked-appeal-trend").textContent =
    `+${stats.appealBlocked.trend} Today`;
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
              <img src="${user.profile_url}" alt="Doctor"/>
            </div>
          `
    : `
            <div class="user-avatar">
              ${getInitials(user.name)}
            </div>
          `;

  row.innerHTML = `
    <td>
      <div class="user-cell">
        <div class="user-avatar">
        ${avatar}
        </div>
        <div>
          <p class="user-name">${user.name}</p>
          <p class="user-id">ID: ${user.user_id}</p>
        </div>
      </div>
    </td>
    <td><span class="badge badge-${user.user_type}">${capitalize(user.user_type)}</span></td>
    <td>
      <div class="reason-cell">
        <span class="reason-dot error"></span>
        <p class="font-body-md text-on-surface">${user.reason}</p>
      </div>
    </td>
    <td><span class="font-body-md text-on-surface-variant">${formatDate(user.blocked_at)}</span></td>
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
        row.remove();
        fetchBlockedUsersTable()
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

  setTheme(profile.theme);
  const emailNotif = document.getElementById("emailNotif");
  const smsNotif = document.getElementById("smsNotif");

  emailNotif.checked = profile.email_notifications;

  smsNotif.checked = profile.sms_notifications;
}

function setTheme(theme) {

    document.body.classList.toggle(
        "dark-mode",
        theme === "dark"
    );

    const buttons = document.querySelectorAll("#themeButtons button");

    console.log("Buttons:", buttons.length);

    buttons.forEach(btn => {

        console.log(
            btn.dataset.value,
            btn.classList.contains("active")
        );

        btn.classList.toggle(
            "active",
            btn.dataset.value === theme
        );

        console.log(
            "After:",
            btn.dataset.value,
            btn.classList.contains("active")
        );
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

    const previousValue = APP.cache.profile[key];

    APP.cache.profile[key] = value;

    if (key === "theme") {
        localStorage.setItem("theme", value);
        // setTheme(value);
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
            localStorage.setItem("theme", previousValue);
            // setTheme(previousValue);

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
