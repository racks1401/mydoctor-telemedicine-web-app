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
      limit: 8,
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

  if (data.profile_url) {
    avatarContainer.innerHTML = `
        <img src="${data.profile_url}" alt="${data.name}" class="profile-img">
    `;
  } else {
      avatarContainer.textContent = getInitials(data.name);
      avatarContainer.classList.add("user-avatar");
  }

  avatarContainer.addEventListener("click", ()=>{
    loadPage("settings");
  })
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
  const searchInput = document.querySelector(".search-container input");

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

    const tabsContainer = tab.closest(".filter-tabs");

    tabsContainer.querySelector(".tab.active")
        ?.classList.remove("active");

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
  }
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
                    <div><p class="user-name">${appointment.doctor_name}</p>
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
  const photo = document.getElementById("doctor-profile-photo");
  const icon = document.getElementById("appointment-mode-icon").textContent = ICONS[appointment.appointment_mode];
  document.getElementById("upcoming-appointment-date").textContent = 
    `${formatAppointmentDate(appointment.appointment_date, appointment.appointment_time)}`;
  document.getElementById("upcoming-appointment-doctor").textContent = appointment.doctor_name;
  document.getElementById("upcoming-appointment-specialization").textContent = appointment.doctor_specialization;
  document.getElementById("upcoming-appointment-mode").textContent = `${formatLabel(appointment.appointment_mode)}`;
  document.getElementById("upcoming-appointment-duration").textContent = appointment.appointment_duration;

  if (appointment.doctor_profile_picture) {
      photo.innerHTML = `
          <div class="user-avatar">
              <img src="${appointment.doctor_profile_picture}" alt="${appointment.doctor_name}"/>
          </div>
      `;
  } else {
      photo.innerHTML = `
          <div class="user-avatar">
              ${getInitials(appointment.doctor_name)}
          </div>
      `;
  }

  if(appointment.appointment_mode === "offline"){
    document.getElementById("upcoming-appointment-join").textContent = "Mark As Visited";
  }
}
const ICONS = {
    offline: "person_pin_circle",
    chat: "chat",
    audio_call: "call",
    video_call: "videocam"
};

function formatAppointmentDate(date, time) {
    const appointment = new Date(`${date}T${time}`);

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
  alert("Join Appointment Feture will be implemented");
}

function rescheduleAppointment(){
  alert("Reschedule Appointment Feture will be implemented");
}

// =========================== appointments Functions ========================



async function initializeAppointments() {
  const cached = APP.cache.appointments;
  console.log(cached);
  if (cached.data) {
    renderAppointments(cached.data);
  }
  await fetchAppointmentsTable();
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
              ${getInitials(appointment.doctor_name)}
            </div>
          `;

  row.innerHTML = `
    <td>
        <div class="doctor-cell">
            ${avatar}
            <div>
                <p class="cell-main">${appointment.doctor_name}</p>
                <p class="cell-sub">ID: #DOC-${appointment.doctor_id}</p>
            </div>
        </div>
    </td>
    <td><span class="cell-main">${appointment.specialization}</span></td>
    <td>
        <p class="cell-main">${formatDate(appointment.appointment_date)}</p>
        <p class="cell-sub">${formatTime(appointment.appointment_time)} (${appointment.appointment_duration} min)</p>
    </td>
    <td>
        <div class="type-indicator">
            <span class="dot dot-indigo"></span>
            <span>${capitalize(appointment.appointment_type)}</span>
        </div>
    </td>
    <td><span class="badge badge-success">${capitalize(appointment.status)}</span></td>
    <td>
        <div class="action-btns">
            <button class="action-btn edit">
                <span class="material-symbols-outlined">edit</span>
            </button>
            <button class="action-btn cancel">
                <span class="material-symbols-outlined">cancel</span>
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
  const header = document.getElementById(`${type}-pagination-header`);

  const start = pagination.total === 0
    ? 0
    : (pagination.page - 1) * pagination.limit + 1;

  const end = pagination.total === 0
    ? 0
    : Math.min(start + pagination.limit - 1, pagination.total);

  header.textContent = `Showing ${start}-${end} of ${pagination.total} data`;

  const container = document.getElementById("pagination-controls");

  container.innerHTML = `
          <button class="page-nav-btn btn-prev" 
                data-page="${pagination.page - 1}"
                data-type="${type}"
                data-action="pagination"
                ${pagination.page === 1 ? "disabled" : ""}>
              <span class="material-symbols-outlined">chevron_left</span>
          </button>
          <button class="page-nav-btn btn-next"
                data-page="${pagination.page + 1}"
                data-type="${type}"
                ${pagination.page === pagination.totalPages ? "disabled" : ""}>
              <span class="material-symbols-outlined">chevron_right</span>
          </button>
  `;
}
// ================================== doctors Functions ========================

async function initializeDoctors() {
  const cached = APP.cache.appointments;
  console.log(cached);
  if (cached.data) {
    renderDoctorsList(cached.data);
  }
  await fetchDoctorsList();
}

async function fetchDoctorsList(){
  const cached = APP.cache.doctors;
  const params = new URLSearchParams({
    action: "table",
    page: cached.page,
    limit: cached.limit,
    search: cached.search,
    ...cached.filters
  });
  try{
    const response = await fetchAPI(
      `/my_doctor/public/patient/api/doctors.php?${params}`,
    );
    cached.data = response;
    console.log(response);
    renderDoctorsList(response.doctors);
    // renderPagination(response.pagination);
  }catch(error){
    console.log(error)
  }
}


function renderDoctorsList(doctors){
  const tbody = document.getElementById("doctor-grid");
  
  tbody.innerHTML = "";
  doctors.forEach((doctor)=>{
    const card = createDoctorCard(doctor);
    tbody.appendChild(card);
  })
}

function createDoctorCard(doctor){
  const row = document.createElement("div");
  row.classList.add("doctor-card");

  const static_profile_url = `/my_doctor/public/assets/images/doctor-profile-${doctor.gender}.png`
  const profileUrl = doctor.profile_url || static_profile_url;
  
  const avatar =`
    <div class="doctor-header">
        <img
            class="doctor-card-img"
            src="${profileUrl}"
            alt="${doctor.name}">
    </div>
  `;

  row.innerHTML = `
    
          ${avatar}
          <div class="doctor-content">
              <p class="doctor-name">${doctor.name}</p>
              <p class="doctor-specialty">${doctor.specialization}</p>
              <div class="doctor-rating">
                  <div class="stars">
                      <span class="star">★</span>
                      <span class="star">★</span>
                      <span class="star">★</span>
                      <span class="star">★</span>
                      <span class="star">★</span>
                  </div>
                  <span class="rating-text">${doctor.rating} (${doctor.total_reviews})</span>
              </div>
              <div class="doctor-info">
                  <div class="doctor-info-item">
                      <span class="material-symbols-outlined">location_on</span>
                      <span>${doctor.hospital_name}</span>
                  </div>
                  <div class="doctor-info-item">
                      <span class="material-symbols-outlined">schedule</span>
                      <span>Available ${doctor.slot_date} ${doctor.start_time}-${doctor.end_time}</span>
                  </div>
                  <div class="doctor-info-item">
                      <span class="material-symbols-outlined">attach_money</span>
                      <span>$${doctor.consultation_fee} per consultation</span>
                  </div>
              </div>
              <div class="doctor-actions">
                  <button class="btn btn-primary">Book Appointment</button>
                  <button class="btn btn-outline">View Profile</button>
              </div>
            </div>
          </div>
  `;

  return row;
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



