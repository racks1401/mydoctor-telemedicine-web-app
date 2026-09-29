
// update pofile section

// let currentTab = "edit-form";

// function showTab(tabName, direction) {
//   if (currentTab === tabName + "-form") return;

//   const current = document.getElementById(currentTab);
//   const next = document.getElementById(tabName + "-form");

//   // Remove active tab class
//   document.querySelectorAll(".tab").forEach(t => t.classList.remove("active"));
//   document.querySelector(`[data-tab='${tabName}']`).classList.add("active");


//   // Prepare animation
//   next.classList.remove("active", "show-left", "show-right");
//   next.classList.add(direction === "left" ? "show-left" : "show-right");

//   // Delay to allow animation class to apply
//   setTimeout(() => {
//     current.classList.remove("active");
//     next.classList.remove("show-left", "show-right");
//     next.classList.add("active");
//     current.style.display = "none";
//     next.style.display = "block";
//     currentTab = tabName + "-form";
//   }, 50);
// };

// // dynamic chart implementation
// document.addEventListener("DOMContentLoaded", () => {
//   const ctx = document.getElementById('chart').getContext('2d');
//   let chartInstance;

//   const fetchAndUpdateChart = (range = '10days') => {
//     fetch(`fetch_appointments_chart.php?range=${range}`)
//       .then(res => res.json())
//       .then(data => {
//         const newData = {
//           labels: data.labels,
//           datasets: [{
//             label: `Appointments (${range.replace(/(\d+days|month|year)/g, match => {
//               return {
//                 '10days': 'Last 10 Days',
//                 'month': 'Last Month',
//                 'year': 'Last Year'
//               }[match];
//             })})`,
//             data: data.appointments,
//             borderColor: '#4caf50',
//             backgroundColor: 'rgba(76, 175, 80, 0.1)',
//             fill: true,
//             tension: 0.4
//           }]
//         };

//         if (chartInstance) {
//           chartInstance.data = newData;
//           chartInstance.update();
//         } else {
//           chartInstance = new Chart(ctx, {
//             type: 'line',
//             data: newData,
//             options: {
//               responsive: true,
//               plugins: {
//                 legend: { position: 'top' }
//               }
//             }
//           });
//         }
//       })
//       .catch(err => console.error("Error fetching chart data:", err));
//   };

//   // Initial load
//   fetchAndUpdateChart('10days');

//   // Dropdown handler
//   document.querySelector('.chart-header select').addEventListener('change', (e) => {
//     const value = e.target.value;
//     if (value.includes('10')) fetchAndUpdateChart('10days');
//     else if (value.includes('month')) fetchAndUpdateChart('month');
//     else if (value.includes('year')) fetchAndUpdateChart('year');
//   });
// });

// // function to manage tab for updating profile
// document.addEventListener("DOMContentLoaded", () => {
//   const tabBtns = document.querySelectorAll(".tab-btn");
//   const tabContents = document.querySelectorAll(".tab-content");

//   tabBtns.forEach(btn => {
//     btn.addEventListener("click", () => {
//       // Remove active from all
//       tabBtns.forEach(b => b.classList.remove("active"));
//       tabContents.forEach(tc => tc.classList.remove("active"));

//       // Activate clicked tab
//       btn.classList.add("active");
//       const tabId = btn.getAttribute("data-tab");
//       document.getElementById(tabId).classList.add("active");
//     });
//   });
// });

// // add slot
// function addSlot(day) {
//   const container = document.querySelector(`#${day}Slots .slot-group`);
//   const newInput = document.createElement('input');
//   newInput.type = 'text';
//   newInput.name = `slots[${day}][]`;
//   newInput.placeholder = "e.g., 04:00 PM - 06:00 PM";
//   container.appendChild(newInput);
// }

// document.querySelectorAll("input[type='checkbox']").forEach(checkbox => {
//   checkbox.addEventListener("change", () => {
//     const slotsDiv = document.getElementById(`${checkbox.value}Slots`);
//     slotsDiv.style.display = checkbox.checked ? "block" : "none";
//   });
// });

// document.querySelectorAll("input[type='checkbox'][name='days[]']").forEach(checkbox => {
//   checkbox.addEventListener('change', function () {
//     const day = this.value;
//     const slotDiv = document.getElementById(day + 'Slots');
//     if (this.checked) {
//       slotDiv.style.display = 'block';
//     } else {
//       slotDiv.style.display = 'none';
//     }
//   });
// });

// function addSlot(day) {
//   const container = document.getElementById(day + 'Slots').querySelector('.slot-group');
//   const newInput = document.createElement("input");
//   newInput.setAttribute("type", "text");
//   newInput.setAttribute("name", `slots[${day}][]`);
//   newInput.setAttribute("placeholder", "e.g., 03:00 PM - 05:00 PM");
//   container.appendChild(newInput);
// }

// function toggleChargeInput(checkbox) {
//   const input = checkbox.nextElementSibling;
//   if (checkbox.checked) {
//     input.style.display = 'inline-block';
//   } else {
//     input.style.display = 'none';
//     input.value = ''; // clear charge if unchecked
//   }
// }


// // upload profile pic
// const profileUpload = document.getElementById('profile-upload');
// if (profileUpload) {
//   profileUpload.addEventListener('change', function () {
//     document.getElementById('profile-upload').addEventListener('change', function () {
//       const fileInput = this;
//       const file = fileInput.files[0];
//       const formData = new FormData();
//       formData.append('profile_pic', file);
    
//       const preview = document.getElementById('preview-image');
//       const progressBar = document.getElementById('upload-progress');
//       const progressContainer = document.getElementById('progress-container');
    
//       // Show progress bar
//       progressContainer.style.display = 'block';
//       progressBar.style.width = '0%';
    
//       // Instant preview (before upload completes)
//       const reader = new FileReader();
//       reader.onload = function (e) {
//         preview.src = e.target.result;
//       };
//       reader.readAsDataURL(file);
    
//       // AJAX Upload with progress
//       const xhr = new XMLHttpRequest();
//       xhr.open('POST', 'upload-profile-pic.php', true);
    
//       xhr.upload.onprogress = function (e) {
//         if (e.lengthComputable) {
//           const percentComplete = (e.loaded / e.total) * 100;
//           progressBar.style.width = percentComplete + '%';
//         }
//       };
    
//       xhr.onload = function () {
//         if (xhr.status === 200) {
//           const response = JSON.parse(xhr.responseText);
//           if (response.success) {
//             preview.src = response.imageUrl + '?t=' + new Date().getTime(); // bust cache
//           } else {
//             alert('Upload failed: ' + response.error);
//           }
//         } else {
//           alert('Upload failed. Server error.');
//         }
      
//         // hide progress bar after upload
//         setTimeout(() => {
//           progressContainer.style.display = 'none';
//           progressBar.style.width = '0%';
//         }, 1500);
//       };  
    
//       xhr.onerror = function () {
//         alert('Upload error.');
//         progressContainer.style.display = 'none';
//       };
    
//       xhr.send(formData);
//     });
//   });
// }

// // floating model

// function openEditModal(data) {
//   document.getElementById('editAppointmentModal').style.display = 'flex';
//   document.getElementById('appointment_id').innerText = data.ap_id;
//   document.getElementById('doctor_name').innerText = data.doctor_name;
//   document.getElementById('doctor_degree').innerText = data.doctor_degree;
//   document.getElementById('doctor_speciality').innerText = data.doctor_speciality;
//   document.getElementById('patient_name').innerText = data.patient_name;
//   document.getElementById('patient_age').innerText = data.patient_age;
//   document.getElementById('patient_gender').innerText = data.patient_gender;
//   document.getElementById('patient_blood_group').innerText = data.patient_blood_group || 'N/A';
//   document.getElementById('appointment_mode').innerText = data.appointment_mode;
//   document.getElementById('appointment_status').innerText = data.status;
//   document.getElementById('appointment_date').innerText = data.appointment_date;
//   document.getElementById('appointment_time').innerText = data.appointment_time;
//   document.getElementById('problem_desc').innerText = data.problem_desc || 'N/A';

//   // Prescription Image Viewer
//   const prescriptionImg = document.getElementById('prescription_image');
//   if (data.prescription_url) {
//     prescriptionImg.src = data.prescription_url;
//     prescriptionImg.style.display = 'block';
//   } else {
//     prescriptionImg.style.display = 'none';
//   }

//   // Set current status in dropdown
//   document.getElementById('status_select').value = data.status || 'Pending';
// }


// function updateStatus() {
//   const newStatus = document.getElementById('status_select').value;
//   const apId = document.getElementById('appointment_id').innerText;

//   // AJAX request to update the status
//   const xhr = new XMLHttpRequest();
//   xhr.open("POST", "update-status.php", true);
//   xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
//   xhr.onreadystatechange = function () {
//     if (xhr.readyState === 4) {
//       if (xhr.status === 200) {
//         alert(xhr.responseText); // Display success or error message
//         closeModal();
//         setTimeout(() => {
//           location.reload(); // Refresh the whole page
//         }, 300);
//       } else {
//         alert("Failed to update status. Try again.");
//       }
//     }
//   };
//   xhr.send("appointment_id=" + encodeURIComponent(apId) + "&status=" + encodeURIComponent(newStatus));
// }

// function cancelBooking() {
//   alert("Cancel function can be implemented here.");
//   closeModal();
// }
// function closeModal() {
//   document.getElementById("editAppointmentModal").style.display = "none"; // Hide the modal
// }

// // update current time

// document.addEventListener('DOMContentLoaded', function () {
//   function updateDateTime() {
//       const now = new Date();
//       const formatted = now.toLocaleString('en-IN', {
//           weekday: 'short',
//           day: 'numeric',
//           month: 'short',
//           year: 'numeric',
//           hour: '2-digit',
//           minute: '2-digit',
//           hour12: true
//       });
//       const datetimeEl = document.getElementById('datetime');
//       if (datetimeEl) {
//           datetimeEl.textContent = formatted;
//       }
//   }

//   // Initial call
//   updateDateTime();

//   // Update every 60 seconds
//   setInterval(updateDateTime, 60000);
// });

// =========================================================================

/* MediCentral Doctor Dashboard - Complete Shared JavaScript */
document.addEventListener('DOMContentLoaded', () => {
    console.log('MediCentral Doctor Portal Initialized');


    // ============== NAVIGATION MANAGEMENT ==============
    const initNavigation = () => {
        const navLinks = document.querySelectorAll('.nav-link');
        const currentPath = window.location.pathname;
        const currentFile = currentPath.split('/').pop() || 'dashboard.html';

        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            const linkFile = href.split('/').pop();

            const isSettingsContext = (currentFile === 'settings.html' || currentFile === 'availability.html') && linkFile === 'settings.html';
            if (linkFile === currentFile || isSettingsContext) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }

            link.addEventListener('click', (e) => {
                const href = link.getAttribute('href');
                if (href && (href.startsWith('#') || href === '')) {
                    e.preventDefault();
                }
                
                navLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });
    };

    // ============== SEARCH FUNCTIONALITY ==============
    const initSearch = () => {
        const searchInput = document.querySelector('.search-bar input');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    const query = searchInput.value.trim();
                    if (query) {
                        console.log('Searching for:', query);
                        performSearch(query);
                    }
                }
            });
        }
    };

    const performSearch = (query) => {
        // Search functionality can be extended here
        console.log('Search query:', query);
    };

    // ============== LIVE CLOCK ==============
    const initClock = () => {
        const timeDisplay = document.getElementById('current-time');
        if (timeDisplay) {
            const updateTime = () => {
                const now = new Date();
                timeDisplay.textContent = now.toLocaleTimeString([], { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    second: '2-digit'
                });
            };
            updateTime();
            setInterval(updateTime, 1000);
        }
    };

    // ============== CHAT FUNCTIONALITY ==============
    const initChat = () => {
        const chatItems = document.querySelectorAll('.chat-item');
        
        chatItems.forEach(item => {
            item.addEventListener('click', (e) => {
                chatItems.forEach(c => c.classList.remove('active'));
                item.classList.add('active');
            });
        });

        const sendBtn = document.querySelector('.chat-send-btn');
        const chatInput = document.querySelector('.chat-input');
        
        if (sendBtn && chatInput) {
            sendBtn.addEventListener('click', () => {
                const message = chatInput.value.trim();
                if (message) {
                    console.log('Sending message:', message);
                    chatInput.value = '';
                }
            });

            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    const message = chatInput.value.trim();
                    if (message) {
                        console.log('Sending message:', message);
                        chatInput.value = '';
                    }
                }
            });
        }
    };

    // ============== TABLE INTERACTIONS ==============
    const initTableInteractions = () => {
        const tableRows = document.querySelectorAll('tbody tr');
        
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.backgroundColor = '#f8f9fb';
            });
            
            row.addEventListener('mouseleave', () => {
                row.style.backgroundColor = '';
            });
        });
    };

    // ============== BUTTON INTERACTIONS ==============
    const initButtonInteractions = () => {
        const buttons = document.querySelectorAll('.btn');
        
        buttons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (btn.getAttribute('href') === '#' || btn.getAttribute('href') === '') {
                    e.preventDefault();
                }
            });
        });
    };

    // ============== NOTIFICATION HANDLER ==============
    const initNotifications = () => {
        const notificationBtns = document.querySelectorAll('[data-notification]');
        
        notificationBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                console.log('Notification clicked');
            });
        });
    };

    // ============== FORM HANDLING ==============
    const initFormHandling = () => {
        const forms = document.querySelectorAll('form');
        
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                console.log('Form submitted');
                
                // Get form data
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                console.log('Form data:', data);
            });
        });
    };

    // ============== MODAL/DROPDOWN HELPERS ==============
    const initDropdowns = () => {
        const dropdownTriggers = document.querySelectorAll('[data-dropdown]');
        
        dropdownTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = trigger.getAttribute('data-dropdown');
                const target = document.getElementById(targetId);
                
                if (target) {
                    target.classList.toggle('hidden');
                }
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            const dropdowns = document.querySelectorAll('[data-dropdown-target]');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            });
        });
    };

    // ============== TOOLTIP SUPPORT ==============
    const initTooltips = () => {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        
        tooltipElements.forEach(el => {
            el.addEventListener('mouseenter', (e) => {
                const tooltipText = el.getAttribute('data-tooltip');
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = tooltipText;
                document.body.appendChild(tooltip);
                
                const rect = el.getBoundingClientRect();
                tooltip.style.position = 'fixed';
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
                tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
            });

            el.addEventListener('mouseleave', (e) => {
                const tooltips = document.querySelectorAll('.tooltip');
                tooltips.forEach(t => t.remove());
            });
        });
    };

    // ============== RESPONSIVE MENU ==============
    const initMobileMenu = () => {
        const menuToggle = document.querySelector('[data-menu-toggle]');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            });
        }
    };

    // ============== ACTIVE TAB MANAGEMENT ==============
    const initTabs = () => {
        const tabLinks = document.querySelectorAll('[data-tab]');
        const tabContents = document.querySelectorAll('[data-tab-content]');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabId = link.getAttribute('data-tab');
                
                tabLinks.forEach(l => l.classList.remove('active'));
                tabContents.forEach(c => c.classList.add('hidden'));
                
                link.classList.add('active');
                const content = document.getElementById(tabId);
                if (content) {
                    content.classList.remove('hidden');
                }
            });
        });
    };

    // ============== ACCORDION SUPPORT ==============
    const initAccordions = () => {
        const accordionHeaders = document.querySelectorAll('[data-accordion-header]');
        
        accordionHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const accordionId = header.getAttribute('data-accordion-header');
                const content = document.getElementById(accordionId);
                
                if (content) {
                    content.classList.toggle('hidden');
                    header.classList.toggle('active');
                }
            });
        });
    };

    // ============== DATA ATTRIBUTES HANDLER ==============
    const initDataAttributes = () => {
        // Handle click actions
        document.querySelectorAll('[data-action]').forEach(el => {
            el.addEventListener('click', (e) => {
                const action = el.getAttribute('data-action');
                console.log('Action triggered:', action);
                handleAction(action);
            });
        });
    };

    const handleAction = (action) => {
        switch(action) {
            case 'start-call':
                console.log('Starting call...');
                break;
            case 'view-records':
                console.log('Viewing records...');
                break;
            case 'reschedule':
                console.log('Rescheduling appointment...');
                break;
            default:
                console.log('Action:', action);
        }
    };

    

    // ============== INITIALIZE ALL COMPONENTS ==============
    initNavigation();
    initSearch();
    initClock();
    initChat();
    initTableInteractions();
    initButtonInteractions();
    initNotifications();
    initFormHandling();
    initDropdowns();
    initTooltips();
    initMobileMenu();
    initTabs();
    initAccordions();
    initDataAttributes();

    console.log('All components initialized');

    function loadPage(page) {
    console.log('loading page', page);
    fetch(`/my_doctor/public/doctor/load-page.php?page=${page}`)
        .then(res => {
            if (!res.ok) throw new Error('Fetch failed ' + res.status);
            return res.text();
        })
        .then(html => {
            if (!content) {
                console.error('Cannot set content, element missing');
                return;
            }
            content.innerHTML = html;

            links.forEach(l => l.classList.remove("active"));
            const activeLink = document.querySelector(`[data-page="${page}"]`);
            if (activeLink) activeLink.classList.add("active");

            // if (page === "dashboard") {
            //     loadStats();
            //     loadChart();
                
            // }else if (page === "doctors") { 
            //     loadDoctors();
            // }
        })
        .catch(err => console.error('Error loading page', page, err));
    }

    const links = document.querySelectorAll("[data-page]");
    links.forEach(link => {
        link.addEventListener("click", () => {
            const page = link.dataset.page;
            loadPage(page);

        });

    });

    const content = document.getElementById('spa-content');
    if (!content) console.error('spa-content element not found');

    const params = new URLSearchParams(window.location.search);
    const page = params.get("page") || "dashboard";
    loadPage(page);

    renderSchedule();
    onStatusChange();

    
});

// ============== UTILITY FUNCTIONS ==============
window.showNotification = function(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // Can be extended with toast notifications
};

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
};

window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
    }
};

window.toggleElement = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.toggle('hidden');
    }
};


// Setup state persisting via LocalStorage with initial mock data
const PRESET_RULES = [
    { id: 1, type: 'weekly', value: 'Monday', start: '09:00', end: '17:00', status: 'On Duty', location: 'Clinic Suite 302' },
    { id: 2, type: 'weekly', value: 'Tuesday', start: '09:00', end: '17:00', status: 'On Duty', location: 'Clinic Suite 302' },
    { id: 3, type: 'weekly', value: 'Wednesday', start: '09:00', end: '12:00', status: 'On Call', location: 'Emergency Department' },
    { id: 4, type: 'weekly', value: 'Thursday', start: '09:00', end: '17:00', status: 'On Duty', location: 'Clinic Suite 302' },
    { id: 5, type: 'weekly', value: 'Friday', start: '13:00', end: '18:00', status: 'On Duty', location: 'Remote Consultation' },
    { id: 6, type: 'date', value: '2026-05-30', start: '00:00', end: '23:59', status: 'On Leave', location: 'Medical Conference' }
];

let currentType = 'weekly';

// Initialize schedule state
function getSchedule() {
    const data = localStorage.getItem('medicentral_schedule');
    if (data) {
        return JSON.parse(data);
    }
    localStorage.setItem('medicentral_schedule', JSON.stringify(PRESET_RULES));
    return PRESET_RULES;
}

function saveSchedule(schedule) {
    localStorage.setItem('medicentral_schedule', JSON.stringify(schedule));
}

// Toggle form input style
function toggleScheduleType(type) {
    currentType = type;
    const btnWeekly = document.getElementById('type-weekly');
    const btnDate = document.getElementById('type-date');
    const groupDay = document.getElementById('group-day');
    const groupDate = document.getElementById('group-date');

    if (type === 'weekly') {
        btnWeekly.classList.add('active');
        btnDate.classList.remove('active');
        groupDay.classList.remove('hidden');
        groupDate.classList.add('hidden');
    } else {
        btnWeekly.classList.remove('active');
        btnDate.classList.add('active');
        groupDay.classList.add('hidden');
        groupDate.classList.remove('hidden');
    }
}

// Disable location picker for 'On Leave' status
function onStatusChange() {
    const status = document.getElementById('avail-status').value;
    const locationGroup = document.getElementById('location-form-group');
    if (status === 'On Leave' || status === 'Out of Office') {
        locationGroup.style.opacity = '0.5';
        document.getElementById('avail-location').disabled = true;
    } else {
        locationGroup.style.opacity = '1';
        document.getElementById('avail-location').disabled = false;
    }
}

// Convert 24hr string to 12hr representation for gorgeous visual style
function formatTime12(timeStr) {
    if (!timeStr) return '';
    const parts = timeStr.split(':');
    let hours = parseInt(parts[0]);
    const minutes = parts[1];
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 becomes 12
    return `${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
}

function formatDateReadable(dateStr) {
    const date = new Date(dateStr);
    if (isNaN(date)) return dateStr;
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

// Render current status badge above
function updateMainDutyStatusHeader(schedule) {
    const badge = document.getElementById('current-status-badge');
    const desc = document.getElementById('duty-description');
    
    // Look if any "On Leave" is active today (May 23, 2026 as per user header context)
    const todayStr = '2026-05-23';
    const todayDay = 'Saturday'; // May 23, 2026 is Saturday
    
    const activeToday = schedule.find(r => {
        if (r.type === 'weekly' && r.value === todayDay) return true;
        if (r.type === 'date' && r.value === todayStr) return true;
        return false;
    });

    if (activeToday) {
        if (activeToday.status === 'On Leave') {
            badge.className = 'status-pill status-pill-red';
            badge.innerHTML = `<span class="material-symbols-outlined icon-xs">do_not_disturb_on</span>On Leave`;
            desc.textContent = `Dr. Sarah Jenkins is on leave today ("${activeToday.location}"). Urgent requests will be routed to Dr. Robert Henderson.`;
        } else if (activeToday.status === 'On Call') {
            badge.className = 'status-pill status-pill-blue';
            badge.innerHTML = `<span class="material-symbols-outlined icon-xs">notifications_active</span>On Call`;
            desc.textContent = `Dr. Sarah Jenkins is currently On Call for the and Emergency/Urgent Cardiology consultations.`;
        } else if (activeToday.status === 'Out of Office') {
            badge.className = 'status-pill status-pill-yellow';
            badge.innerHTML = `<span class="material-symbols-outlined icon-xs">logout</span>Out of Office`;
            desc.textContent = `Dr. Sarah Jenkins is out of clinical offices but remains reachable for critical medical emergencies.`;
        } else {
            badge.className = 'status-pill status-pill-green';
            badge.innerHTML = `<span class="material-symbols-outlined icon-xs">check_circle</span>On Duty`;
            desc.textContent = `Dr. Sarah Jenkins is fully On Duty and active inside clinic room "${activeToday.location || 'Suite 302'}".`;
        }
    } else {
        // Saturday naturally might be off duty
        badge.className = 'status-pill status-pill-yellow';
        badge.innerHTML = `<span class="material-symbols-outlined icon-xs">home</span>Off Duty`;
        desc.textContent = `Dr. Sarah Jenkins has no active schedules configure today. She will resume on Monday at 09:00 AM.`;
    }
}

// Re-render HTML table body
function renderSchedule() {
    const schedule = getSchedule();
    const tbody = document.getElementById('schedule-tbody');
    tbody.innerHTML = '';

    schedule.forEach(rule => {
        const tr = document.createElement('tr');
        tr.id = `rule-row-${rule.id}`;

        // Day / Date formatting
        const dayDateVal = rule.type === 'weekly' 
            ? `<div class="txt-medium">${rule.value}</div><div class="color-muted font-size-7">Weekly Recurring</div>`
            : `<div class="txt-medium">${formatDateReadable(rule.value)}</div><div class="color-muted font-size-7">Single Event</div>`;

        // Badge style
        let badgeClass = 'badge-success';
        let iconName = 'check_circle';
        if (rule.status === 'On Call') {
            badgeClass = 'badge-blue';
            iconName = 'notifications';
        } else if (rule.status === 'On Leave') {
            badgeClass = 'badge-danger';
            iconName = 'block';
        } else if (rule.status === 'Out of Office') {
            badgeClass = 'badge-warning';
            iconName = 'home_pin';
        }

        const statusBadge = `<span class="badge ${badgeClass}"><span class="material-symbols-outlined icon-xs">${iconName}</span>${rule.status}</span>`;

        tr.innerHTML = `
            <td>${dayDateVal}</td>
            <td>
                <div class="txt-medium">${formatTime12(rule.start)} - ${formatTime12(rule.end)}</div>
            </td>
            <td>
                <div class="txt-medium text-ellipsis" style="max-width: 180px;">${rule.status === 'On Leave' || rule.status === 'Out of Office' ? rule.location : (rule.location || 'Suite 302')}</div>
            </td>
            <td>${statusBadge}</td>
            <td style="text-align: right;">
                <button class="avail-delete-btn" onclick="deleteRule(${rule.id})" title="Delete rule">
                    <span class="material-symbols-outlined icon-18">delete</span>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });

    updateMainDutyStatusHeader(schedule);
}

// Show toast helper
function triggerToast(text, isSuccess = true) {
    const toast = document.getElementById('action-toast');
    const icon = document.getElementById('toast-icon');
    const label = document.getElementById('toast-text');

    label.textContent = text;
    if (isSuccess) {
        icon.textContent = 'check_circle';
        icon.style.color = '#4ade80';
    } else {
        icon.textContent = 'error';
        icon.style.color = '#f87171';
    }

    toast.classList.add('show');
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Action: Add Schedule Rule
function handleAddRule() {
    const status = document.getElementById('avail-status').value;
    const start = document.getElementById('avail-start').value;
    const end = document.getElementById('avail-end').value;
    
    let value = '';
    if (currentType === 'weekly') {
        value = document.getElementById('avail-day').value;
    } else {
        value = document.getElementById('avail-date').value;
        if (!value) {
            triggerToast("Please select a calendar date.", false);
            return;
        }
    }

    if (!start || !end) {
        triggerToast("Please select start and end hours.", false);
        return;
    }

    if (start >= end) {
        triggerToast("Start hour must be before end hour.", false);
        return;
    }

    let location = 'Clinic Suite 302';
    if (status === 'On Leave' || status === 'Out of Office') {
        location = currentType === 'weekly' ? 'General Leave / Unavailable' : 'Personal Time Off / Leave';
    } else {
        location = document.getElementById('avail-location').value;
    }

    const schedule = getSchedule();
    const newId = schedule.length > 0 ? Math.max(...schedule.map(x => x.id)) + 1 : 1;
    
    const newRule = {
        id: newId,
        type: currentType,
        value: value,
        start: start,
        end: end,
        status: status,
        location: location
    };

    schedule.push(newRule);
    saveSchedule(schedule);
    renderSchedule();
    triggerToast(`Successfully added availability rule for ${value}!`);
}

// Action: Delete Specific Rule
function deleteRule(id) {
    let schedule = getSchedule();
    const ruleToDelete = schedule.find(x => x.id === id);
    if (!ruleToDelete) return;

    schedule = schedule.filter(x => x.id !== id);
    saveSchedule(schedule);
    renderSchedule();
    triggerToast(`Deleted availability rule for ${ruleToDelete.value}.`);
}

// Action: Reset Schedule to presets
function resetDefaultSchedule() {
    localStorage.setItem('medicentral_schedule', JSON.stringify(PRESET_RULES));
    renderSchedule();
    triggerToast(`Clinic scheduler reset to initial system template.`);
}

    




