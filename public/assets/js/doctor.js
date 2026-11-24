
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
        const searchInput = document.querySelector('.topbar-search input, .search-bar input');
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
    let activeChatConvId = null;
    let lastLoadedMessageId = 0;
    let oldestLoadedMessageId = null;
    let isLoadingOlderMessages = false;
    let renderedMessageIds = new Set();
    let chatConversationsCache = [];

    const initChat = () => {
        const itemsContainer = document.querySelector('.chat-items');
        const headerName = document.querySelector('.chat-header-name');
        const headerAvatar = document.querySelector('.chat-header-avatar');
        const headerStatus = document.querySelector('.chat-header-status');
        const messagesContainer = document.querySelector('.chat-messages');
        const chatInput = document.querySelector('.chat-input textarea') || document.querySelector('.chat-input');
        const sendBtn = document.querySelector('.chat-send-btn');
        const searchInput = document.getElementById('chat-search-input') || document.querySelector('.chat-search input');

        if (!itemsContainer) return;

        const mobileBackBtn = document.getElementById('btn-chat-mobile-back');
        if (mobileBackBtn) {
            mobileBackBtn.onclick = (e) => {
                e.preventDefault();
                const container = document.querySelector('.chats-container');
                if (container) {
                    container.classList.remove('chat-view-active');
                }
            };
        }

        const safeHtml = (str) => {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
        };

        const loadConversations = async (targetConvOrApptId = null) => {
            try {
                const res = await fetch('/my_doctor/public/doctor/api/chat.php?action=list');
                const data = await res.json();
                if (!data.status) {
                    itemsContainer.innerHTML = `<div style="padding: 20px; text-align: center; color: #dc2626; font-size: 13px;">${safeHtml(data.message || 'Error loading chats')}</div>`;
                    return;
                }

                chatConversationsCache = data.data.conversations || [];
                renderConversationItems(chatConversationsCache);

                if (chatConversationsCache.length === 0) {
                    itemsContainer.innerHTML = '<div style="padding: 24px 16px; text-align: center; color: var(--on-surface-variant, #64748b); font-size: 13px;">No online consultations yet</div>';
                    if (headerName) headerName.textContent = 'No active chat';
                    if (headerStatus) headerStatus.textContent = '';
                    if (messagesContainer) messagesContainer.innerHTML = '';
                    return;
                }

                let toSelect = null;
                const reqTarget = targetConvOrApptId || window.targetChatConvId || window.targetChatAppointmentId;
                if (reqTarget) {
                    toSelect = chatConversationsCache.find(c => c.conversation_id === reqTarget || c.appointment_id === reqTarget);
                    window.targetChatAppointmentId = null;
                    window.targetChatConvId = null;
                } else if (activeChatConvId) {
                    toSelect = chatConversationsCache.find(c => c.conversation_id === activeChatConvId);
                }

                if (!toSelect && chatConversationsCache.length > 0) {
                    toSelect = chatConversationsCache[0];
                }

                if (toSelect) {
                    const isMobile = window.innerWidth <= 768;
                    const openOnMobile = Boolean(reqTarget) || !isMobile;
                    selectConversation(toSelect.conversation_id, openOnMobile);
                }
            } catch (err) {
                console.error("Error loading conversations:", err);
                itemsContainer.innerHTML = '<div style="padding: 20px; text-align: center; color: #dc2626; font-size: 13px;">Network error loading chats</div>';
            }
        };

        const renderConversationItems = (conversations) => {
            itemsContainer.innerHTML = '';
            conversations.forEach(conv => {
                const item = document.createElement('div');
                item.className = 'chat-item' + (conv.conversation_id === activeChatConvId ? ' active' : '');
                item.dataset.chatId = conv.conversation_id;

                const initials = conv.other_user_initials || (conv.other_user_name ? conv.other_user_name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : 'PT');
                let avatarHtml = '';
                if (conv.other_user_avatar) {
                    avatarHtml = `<img alt="${safeHtml(conv.other_user_name)}" class="chat-avatar-item" src="${conv.other_user_avatar}" onerror="this.onerror=null; this.outerHTML='<div class=\\'chat-avatar-initials\\'>${initials}</div>';">`;
                } else {
                    avatarHtml = `<div class="chat-avatar-initials">${initials}</div>`;
                }

                item.innerHTML = `
                    ${avatarHtml}
                    <div class="chat-preview">
                        <p class="chat-name">${safeHtml(conv.other_user_name)}</p>
                        <p class="chat-last-msg">${safeHtml(conv.last_message || 'No messages yet')}</p>
                    </div>
                    ${conv.unread_count > 0 ? `<div class="chat-unread">${conv.unread_count}</div>` : ''}
                `;

                item.addEventListener('click', () => {
                    selectConversation(conv.conversation_id, true);
                });

                itemsContainer.appendChild(item);
            });
        };

        const selectConversation = async (convId, openViewOnMobile = true) => {
            activeChatConvId = convId;
            lastLoadedMessageId = 0;
            oldestLoadedMessageId = null;
            renderedMessageIds.clear();

            const chatsContainer = document.querySelector('.chats-container');
            if (chatsContainer && openViewOnMobile && window.innerWidth <= 768) {
                chatsContainer.classList.add('chat-view-active');
            }

            itemsContainer.querySelectorAll('.chat-item').forEach(el => {
                if (parseInt(el.dataset.chatId, 10) === convId) {
                    el.classList.add('active');
                    const unreadEl = el.querySelector('.chat-unread');
                    if (unreadEl) unreadEl.remove();
                } else {
                    el.classList.remove('active');
                }
            });

            const conv = chatConversationsCache.find(c => c.conversation_id === convId);
            if (!conv) return;

            const headerAvatarBox = document.getElementById('chat-header-avatar-box') || (headerAvatar ? headerAvatar.parentElement : null);
            const initials = conv.other_user_initials || (conv.other_user_name ? conv.other_user_name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : 'PT');
            if (headerAvatarBox) {
                if (conv.other_user_avatar) {
                    headerAvatarBox.innerHTML = `<img alt="${safeHtml(conv.other_user_name)}" class="chat-header-avatar" src="${conv.other_user_avatar}" onerror="this.onerror=null; this.outerHTML='<div class=\\'chat-header-initials\\'>${initials}</div>';">`;
                } else {
                    headerAvatarBox.innerHTML = `<div class="chat-header-initials">${initials}</div>`;
                }
            } else if (headerAvatar) {
                if (conv.other_user_avatar) {
                    headerAvatar.src = conv.other_user_avatar;
                    headerAvatar.style.display = 'block';
                }
            }
            if (headerName) headerName.textContent = conv.other_user_name;

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
            console.log(`[Perf] Chat history rendered in: ${(performance.now() - tOpen0).toFixed(2)}ms`);

            // Attach Firebase listener after history render
            fbPromise.then(() => {
                if (window.DoctorFirebase && activeChatConvId === convId) {
                    window.DoctorFirebase.subscribeChat(convId, (signal) => {
                        // Sender must ignore own signal
                        if (signal.sender_id && parseInt(signal.sender_id, 10) === window.DoctorFirebase.currentUserId) {
                            return;
                        }
                        if (activeChatConvId === convId) {
                            fetchNewMessages(convId);
                        }
                    });
                }
            });
        };

        const loadMessages = async (convId) => {
            if (!messagesContainer) return;
            messagesContainer.innerHTML = '';

            try {
                const t0 = performance.now();
                const res = await fetch(`/my_doctor/public/doctor/api/chat.php?action=fetch_messages&conversation_id=${convId}&limit=30`);
                const data = await res.json();
                console.log(`[Perf] History fetch API took: ${(performance.now() - t0).toFixed(2)}ms`);

                if (!data.status) {
                    messagesContainer.innerHTML = `<div style="margin: auto; color: #dc2626; font-size: 13px;">${safeHtml(data.message || 'Error fetching messages')}</div>`;
                    return;
                }

                const windowState = data.data.window_state;
                const canSend = data.data.can_send;

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

                if (chatInput) {
                    chatInput.disabled = !canSend;
                    chatInput.placeholder = canSend ? 'Type a message...' : (windowState === 'not_started' ? 'Chat opens 5m before slot' : 'Consultation ended');
                }
                if (sendBtn) {
                    sendBtn.disabled = !canSend;
                    sendBtn.style.opacity = canSend ? '1' : '0.5';
                }

                messagesContainer.innerHTML = '';
                const msgs = data.data.messages || [];
                const conv = data.data.conversation;

                msgs.forEach(m => {
                    appendMessageToDom(m, conv);
                    if (m.message_id > lastLoadedMessageId) {
                        lastLoadedMessageId = m.message_id;
                    }
                    if (oldestLoadedMessageId === null || m.message_id < oldestLoadedMessageId) {
                        oldestLoadedMessageId = m.message_id;
                    }
                });
                messagesContainer.scrollTop = messagesContainer.scrollHeight;

            } catch (err) {
                console.error("Error fetching messages:", err);
            }
        };

        const fetchNewMessages = async (convId) => {
            if (!messagesContainer || lastLoadedMessageId === null || lastLoadedMessageId === undefined) return;
            try {
                const afterParam = lastLoadedMessageId > 0 ? `&after_id=${lastLoadedMessageId}` : '';
                const t0 = performance.now();
                const res = await fetch(`/my_doctor/public/doctor/api/chat.php?action=fetch_messages&conversation_id=${convId}${afterParam}`);
                const data = await res.json();
                console.log(`[Perf] Receiver fetchNewMessages took: ${(performance.now() - t0).toFixed(2)}ms`);

                if (data.status && data.data.messages && data.data.messages.length > 0) {
                    const conv = data.data.conversation;
                    let hasNew = false;
                    data.data.messages.forEach(m => {
                        if (!renderedMessageIds.has(m.message_id)) {
                            appendMessageToDom(m, conv);
                            if (m.message_id > lastLoadedMessageId) {
                                lastLoadedMessageId = m.message_id;
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
                console.error("Error fetching new messages:", err);
            }
        };

        // Phase 5: Firebase Real-time user inbox listener
        if (window.DoctorFirebase && typeof window.DoctorFirebase.subscribeUserChatUpdates === 'function') {
            window.DoctorFirebase.subscribeUserChatUpdates(() => {
                if (activeChatConvId) {
                    fetchNewMessages(activeChatConvId);
                } else {
                    loadConversations();
                }
            });
        }

        // Lightweight backup interval: only fires if Firebase not ready
        if (window.doctorChatPollInterval) clearInterval(window.doctorChatPollInterval);
        window.doctorChatPollInterval = setInterval(() => {
            if (activeChatConvId && document.querySelector('.chats-container') && (!window.DoctorFirebase || !window.DoctorFirebase.isInitialized)) {
                fetchNewMessages(activeChatConvId);
            }
        }, 10000);

        // Window online and tab visibility handlers for instant resync
        window.addEventListener('online', () => {
            if (activeChatConvId) fetchNewMessages(activeChatConvId);
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && activeChatConvId) fetchNewMessages(activeChatConvId);
        });

        // Step 4: Scroll-up pagination for older messages
        messagesContainer.addEventListener('scroll', async () => {
            if (messagesContainer.scrollTop <= 10 && oldestLoadedMessageId && !isLoadingOlderMessages && activeChatConvId) {
                isLoadingOlderMessages = true;
                const prevHeight = messagesContainer.scrollHeight;
                try {
                    const res = await fetch(`/my_doctor/public/doctor/api/chat.php?action=fetch_messages&conversation_id=${activeChatConvId}&before_id=${oldestLoadedMessageId}&limit=30`);
                    const data = await res.json();
                    if (data.status && data.data.messages && data.data.messages.length > 0) {
                        const conv = data.data.conversation;
                        const reversed = [...data.data.messages].reverse();
                        reversed.forEach(m => {
                            if (!renderedMessageIds.has(m.message_id)) {
                                appendMessageToDom(m, conv, true);
                                if (m.message_id < oldestLoadedMessageId) {
                                    oldestLoadedMessageId = m.message_id;
                                }
                            }
                        });
                        messagesContainer.scrollTop = messagesContainer.scrollHeight - prevHeight;
                    }
                } catch (e) {
                    console.error("Error loading older messages:", e);
                } finally {
                    isLoadingOlderMessages = false;
                }
            }
        });

        const appendMessageToDom = (m, conv = null, prepend = false) => {
            if (!messagesContainer) return null;

            // Deduplicate by message_id
            if (!m.is_temp && renderedMessageIds.has(m.message_id)) {
                return null;
            }
            if (!m.is_temp && m.message_id) {
                renderedMessageIds.add(m.message_id);
            }

            const msgDiv = document.createElement('div');
            msgDiv.className = 'message ' + (m.is_me ? 'sent' : 'received');
            msgDiv.dataset.msgId = m.message_id;

            if (m.is_temp) {
                msgDiv.classList.add('message-pending');
                msgDiv.style.opacity = '0.7';
            }

            if (!m.is_me) {
                const otherInitials = conv?.other_user_initials || (conv?.other_user_name ? conv.other_user_name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase() : 'PT');
                if (conv?.other_user_avatar) {
                    const avatarImg = document.createElement('img');
                    avatarImg.className = 'message-avatar';
                    avatarImg.alt = conv?.other_user_name || 'Patient';
                    avatarImg.src = conv.other_user_avatar;
                    avatarImg.onerror = function() {
                        const initDiv = document.createElement('div');
                        initDiv.className = 'message-avatar-initials';
                        initDiv.textContent = otherInitials;
                        avatarImg.replaceWith(initDiv);
                    };
                    msgDiv.appendChild(avatarImg);
                } else {
                    const initDiv = document.createElement('div');
                    initDiv.className = 'message-avatar-initials';
                    initDiv.textContent = otherInitials;
                    msgDiv.appendChild(initDiv);
                }
            }

            const innerWrap = document.createElement('div');

            const bubble = document.createElement('div');
            bubble.className = 'message-bubble';
            bubble.textContent = m.body;

            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = m.time_formatted || (m.is_temp ? 'Sending...' : m.created_at);

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
                const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
                fetch('/my_doctor/public/doctor/api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        action: 'mark_read',
                        conversation_id: convId,
                        csrf_token: token
                    })
                });
            } catch (e) {}
        };

        // Step 1: Optimistic UI Send Flow
        const sendMessage = async () => {
            if (!chatInput || !activeChatConvId) return;
            const text = chatInput.value.trim();
            if (!text) return;

            // 1. Immediately clear the input & append sending bubble (do not wait for server)
            chatInput.value = '';
            const tempId = 'temp_' + Date.now() + '_' + Math.floor(Math.random() * 1000);

            const optimisticMsg = {
                message_id: tempId,
                is_temp: true,
                body: text,
                time_formatted: 'Sending...',
                is_me: true
            };

            const tempEl = appendMessageToDom(optimisticMsg);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;

            // 2. Immediately update sidebar locally (last message & move to top)
            const activeItem = itemsContainer.querySelector(`.chat-item[data-chat-id="${activeChatConvId}"]`);
            if (activeItem) {
                const lastMsgEl = activeItem.querySelector('.chat-last-msg');
                if (lastMsgEl) lastMsgEl.textContent = text;
                itemsContainer.prepend(activeItem);
            }

            const token = window.csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
            const tSendReq0 = performance.now();

            try {
                const res = await fetch('/my_doctor/public/doctor/api/chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        action: 'send_message',
                        conversation_id: activeChatConvId,
                        body: text,
                        csrf_token: token
                    })
                });

                const data = await res.json();
                console.log(`[Perf] Send request completed in: ${(performance.now() - tSendReq0).toFixed(2)}ms`);

                if (!data.status || !data.data) {
                    markMessageFailed(tempEl, text, data.message || 'Could not send message.');
                    return;
                }

                // Replace temp bubble with real message_id & mark sent
                const realMsg = data.data;
                if (tempEl) {
                    tempEl.dataset.msgId = realMsg.message_id;
                    tempEl.classList.remove('message-pending');
                    tempEl.style.opacity = '1';
                    const timeEl = tempEl.querySelector('.message-time');
                    if (timeEl) timeEl.textContent = realMsg.time_formatted || 'Just now';
                }
                renderedMessageIds.add(realMsg.message_id);
                if (realMsg.message_id > lastLoadedMessageId) {
                    lastLoadedMessageId = realMsg.message_id;
                }

            } catch (err) {
                console.error("Send message error:", err);
                markMessageFailed(tempEl, text, "Network error. Failed to send.");
            }
        };

        const markMessageFailed = (el, originalText, errorMsg) => {
            if (!el) return;
            el.classList.remove('message-pending');
            el.classList.add('message-failed');
            el.style.opacity = '1';
            const timeEl = el.querySelector('.message-time');
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
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            };
        }

        if (searchInput) {
            searchInput.oninput = () => {
                const q = searchInput.value.toLowerCase().trim();
                itemsContainer.querySelectorAll('.chat-item').forEach(item => {
                    const name = item.querySelector('.chat-name')?.textContent?.toLowerCase() || '';
                    const msg = item.querySelector('.chat-last-msg')?.textContent?.toLowerCase() || '';
                    if (!q || name.includes(q) || msg.includes(q)) {
                        item.style.display = 'flex';
                    } else {
                        item.style.display = 'none';
                    }
                });
            };
        }

        window.openConsultationChat = (appointmentId) => {
            window.targetChatAppointmentId = parseInt(appointmentId, 10);
            loadPage('chats');
        };

        loadConversations();
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

    

    // ============== USER PROFILE & AVATAR ==============
    window.getInitials = function getInitials(name = "") {
        return (name || "")
            .split(" ")
            .filter(Boolean)
            .map(word => word[0])
            .join("")
            .substring(0, 2)
            .toUpperCase() || "DR";
    };

    window.fetchAndSetupUserProfile = async function fetchAndSetupUserProfile() {
        try {
            const response = await fetch("/my_doctor/public/doctor/api/profile-data.php");
            const result = await response.json();
            if (result.status && result.data) {
                window.setupUserProfile(result.data);
            }
        } catch (error) {
            console.error("Error fetching doctor profile:", error);
        }
    };

    window.setupUserProfile = function setupUserProfile(data) {
        const avatarContainer = document.getElementById("header-profile-img-container");
        if (!avatarContainer) return;

        if (data && data.profile_url) {
            avatarContainer.innerHTML = `<img src="${data.profile_url}" alt="${data.name || 'Doctor'}" class="profile-img">`;
        } else if (data && data.name) {
            avatarContainer.textContent = window.getInitials(data.name);
            avatarContainer.classList.add("user-avatar");
        } else {
            avatarContainer.textContent = "DR";
            avatarContainer.classList.add("user-avatar");
        }

        avatarContainer.style.cursor = "pointer";
        avatarContainer.addEventListener("click", () => {
            loadPage("settings");
        });
    };

    // ============== INITIALIZE ALL COMPONENTS ==============
    fetchAndSetupUserProfile();
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

    if (document.querySelector('.chats-container')) { initChat(); }
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

            if (page === "chats") {
                initChat();
            } else if (page === "settings") {
                initializeDoctorSettings();
            } else if (page === "appointments") {
                initDoctorAppointments();
            } else if (page === "dashboard") {
                initDoctorDashboard();
            } else if (page === "calls") {
                initDoctorCalls();
            }
        })
        .catch(err => console.error('Error loading page', page, err));
    }

    window.loadDoctorPage = loadPage;

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

    






// =====================================================================
// DOCTOR SETTINGS & PROFILE MANAGEMENT
// =====================================================================

// Default weekly schedule matching user reference image exactly
const DEFAULT_WEEKLY_SCHEDULE = {
    sun: [
        { start: "09:00", end: "12:30" },
        { start: "15:00", end: "23:00" }
    ],
    mon: [
        { start: "09:00", end: "12:00" },
        { start: "15:00", end: "23:00" }
    ],
    tue: [
        { start: "09:00", end: "12:00" },
        { start: "15:00", end: "23:00" }
    ],
    wed: [], // Unavailable
    thu: [
        { start: "09:00", end: "12:00" },
        { start: "15:00", end: "23:00" }
    ],
    fri: [
        { start: "09:00", end: "12:00" },
        { start: "15:00", end: "23:00" }
    ],
    sat: [
        { start: "09:00", end: "12:00" },
        { start: "15:00", end: "23:00" }
    ]
};

const SCHEDULE_DAYS = [
    { key: "sun", label: "Sunday", badge: "S" },
    { key: "mon", label: "Monday", badge: "M" },
    { key: "tue", label: "Tuesday", badge: "T" },
    { key: "wed", label: "Wednesday", badge: "W" },
    { key: "thu", label: "Thursday", badge: "T" },
    { key: "fri", label: "Friday", badge: "F" },
    { key: "sat", label: "Saturday", badge: "S" }
];

let cachedDoctorSettings = null;
let currentWeeklySchedule = JSON.parse(JSON.stringify(DEFAULT_WEEKLY_SCHEDULE));

function initializeDoctorSettings() {
    setupDoctorSettingsTabs();
    fetchDoctorSettingsData();
    setupDoctorSettingsForms();
    setupDoctorSettingsDeleteModal();
    setupScheduleEventListeners();
    setupLeaveExceptions();
}

function setupDoctorSettingsTabs() {
    const tabMenuItems = document.querySelectorAll("#doc-settings-nav-menu [data-tab]");
    const panes = {
        profile: document.getElementById("doc-pane-profile"),
        availability: document.getElementById("doc-pane-availability"),
        security: document.getElementById("doc-pane-security"),
        notifications: document.getElementById("doc-pane-notifications"),
        privacy: document.getElementById("doc-pane-privacy")
    };

    tabMenuItems.forEach(item => {
        item.addEventListener("click", () => {
            const targetTab = item.dataset.tab;
            if (!targetTab) return;

            tabMenuItems.forEach(b => b.classList.remove("active"));
            item.classList.add("active");

            Object.keys(panes).forEach(key => {
                if (panes[key]) {
                    panes[key].style.display = (key === targetTab) ? "block" : "none";
                }
            });
        });
    });
}

async function fetchDoctorSettingsData() {
    try {
        const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=getSettings");
        if (!res.ok) {
            console.error("HTTP error fetching doctor settings:", res.status);
            return;
        }
        const json = await res.json();
        if (json.status && json.data) {
            cachedDoctorSettings = json.data;
            renderDoctorSettingsData(json.data);
        } else if (json.message) {
            console.warn("Doctor settings notice:", json.message);
        }
    } catch (err) {
        console.error("Doctor settings load error:", err);
    }
}

function renderDoctorSettingsData(data) {
    if (!data) return;

    // Profile fields
    const nameEl = document.getElementById("doc-name");
    const emailEl = document.getElementById("doc-email");
    const phoneEl = document.getElementById("doc-phone");
    const dobEl = document.getElementById("doc-dob");
    const genderEl = document.getElementById("doc-gender");
    const specEl = document.getElementById("doc-specialization");
    const regNoEl = document.getElementById("doc-reg-no");
    const qualEl = document.getElementById("doc-qualification");
    const expEl = document.getElementById("doc-experience");
    const addrEl = document.getElementById("doc-address");
    const cityEl = document.getElementById("doc-city");
    const stateEl = document.getElementById("doc-state");
    const pinEl = document.getElementById("doc-pin");
    const aboutEl = document.getElementById("doc-about");

    if (nameEl) nameEl.value = data.name || "";
    if (emailEl) emailEl.value = data.email || "";
    if (phoneEl) phoneEl.value = data.phone || "";
    if (dobEl) dobEl.value = data.dob || "";
    if (genderEl && data.gender) genderEl.value = data.gender;
    if (specEl) specEl.value = data.specialization || "";
    if (regNoEl) regNoEl.value = data.medical_reg_no || "";
    if (qualEl) qualEl.value = data.qualification || "";
    if (expEl) expEl.value = data.experience || "";
    if (addrEl) addrEl.value = data.address || "";
    if (cityEl) cityEl.value = data.city || "";
    if (stateEl) stateEl.value = data.state || "";
    if (pinEl) pinEl.value = data.pin || "";
    if (aboutEl) aboutEl.value = data.about || "";

    // Avatar image or initials
    const imgEl = document.getElementById("doc-profile-image");
    const initialsEl = document.getElementById("doc-profile-initials");
    if (data.profile_url && imgEl && initialsEl) {
        imgEl.src = data.profile_url;
        imgEl.style.display = "block";
        initialsEl.style.display = "none";
    } else if (initialsEl && imgEl) {
        imgEl.style.display = "none";
        initialsEl.style.display = "block";
        const initialsFunc = window.getInitials || (typeof getInitials === 'function' ? getInitials : null);
        initialsEl.textContent = initialsFunc ? initialsFunc(data.name || "Doctor") : ((data.name || "Doctor").substring(0, 2).toUpperCase());
    }

    // Availability fields & Working Hours Schedule
    const availToggle = document.getElementById("doc-availability-toggle");
    const availDesc = document.getElementById("doc-avail-status-desc");
    const slotDurationEl = document.getElementById("doc-slot-duration");

    if (availToggle) {
        availToggle.checked = Boolean(Number(data.availability ?? 1));
        if (availDesc) {
            availDesc.textContent = availToggle.checked 
                ? "Active - Patients can view and book appointments with you" 
                : "Inactive - You are currently marked as not taking appointments";
        }
    }
    if (slotDurationEl && data.slot_duration) {
        slotDurationEl.value = data.slot_duration;
    }

    // Populate Available Consultation Modes
    const rawModes = data.available_mode || "video_call,chat,offline,audio_call";
    const activeModesList = rawModes.split(",").map(m => m.trim().toLowerCase());
    document.querySelectorAll(".doc-mode-checkbox").forEach(cb => {
        cb.checked = activeModesList.includes(cb.value.toLowerCase());
    });

    // Load and render Weekly Schedule
    if (data.weekly_schedule && typeof data.weekly_schedule === "object") {
        currentWeeklySchedule = JSON.parse(JSON.stringify(data.weekly_schedule));
    } else if (typeof data.weekly_schedule === "string" && data.weekly_schedule.trim()) {
        try {
            currentWeeklySchedule = JSON.parse(data.weekly_schedule);
        } catch (e) {
            currentWeeklySchedule = JSON.parse(JSON.stringify(DEFAULT_WEEKLY_SCHEDULE));
        }
    } else {
        currentWeeklySchedule = JSON.parse(JSON.stringify(DEFAULT_WEEKLY_SCHEDULE));
    }
    renderWeeklySchedule(currentWeeklySchedule);

    // Security & Session details
    const lastLoginAtEl = document.getElementById("doc-last-login-at");
    const lastLoginIpEl = document.getElementById("doc-last-login-ip");
    const createdAtEl = document.getElementById("doc-created-at");
    const twoFactorToggle = document.getElementById("doc-toggle-2fa");
    const twoFactorDesc = document.getElementById("doc-2fa-desc");

    if (lastLoginAtEl) lastLoginAtEl.textContent = data.last_login_at ? formatDoctorDateTime(data.last_login_at) : "Active now";
    if (lastLoginIpEl) lastLoginIpEl.textContent = data.last_login_ip || "127.0.0.1";
    if (createdAtEl) createdAtEl.textContent = data.created_at ? formatDoctorDate(data.created_at) : "Recently";
    if (twoFactorToggle) {
        twoFactorToggle.checked = Boolean(Number(data.two_factor_enabled ?? 0));
        if (twoFactorDesc) {
            twoFactorDesc.textContent = twoFactorToggle.checked 
                ? "2FA is enabled. OTP verification required on login." 
                : "Require OTP verification when logging in from new devices";
        }
    }

    // Notification toggles
    const notifAppointments = document.getElementById("doc-notif-appointments");
    const notifMessages = document.getElementById("doc-notif-messages");
    const notifAlerts = document.getElementById("doc-notif-alerts");
    const notifEmail = document.getElementById("doc-notif-email");
    const notifSms = document.getElementById("doc-notif-sms");

    if (notifAppointments) notifAppointments.checked = Boolean(Number(data.appointment_reminders ?? 1));
    if (notifMessages) notifMessages.checked = Boolean(Number(data.message_notifications ?? 1));
    if (notifAlerts) notifAlerts.checked = Boolean(Number(data.health_alerts ?? 1));
    if (notifEmail) notifEmail.checked = Boolean(Number(data.email_notifications ?? 1));
    if (notifSms) notifSms.checked = Boolean(Number(data.sms_notifications ?? 0));

    // Privacy toggles
    const privDirectory = document.getElementById("doc-privacy-directory");
    const privContact = document.getElementById("doc-privacy-contact");

    if (privDirectory) privDirectory.checked = Boolean(Number(data.directory_visibility ?? 1));
    if (privContact) privContact.checked = Boolean(Number(data.share_contact_data ?? 1));
}

function renderWeeklySchedule(schedule) {
    const container = document.getElementById("doc-schedule-container");
    if (!container) return;

    container.innerHTML = "";

    SCHEDULE_DAYS.forEach(day => {
        const slots = Array.isArray(schedule[day.key]) ? schedule[day.key] : [];
        const isAvailable = slots.length > 0;

        const row = document.createElement("div");
        row.className = "sched-day-row";
        row.dataset.day = day.key;

        // Day circle badge
        const badge = document.createElement("div");
        badge.className = "sched-day-badge" + (isAvailable ? "" : " unavailable");
        badge.title = day.label;
        badge.textContent = day.badge;
        row.appendChild(badge);

        // Slots column
        const slotsCol = document.createElement("div");
        slotsCol.className = "sched-day-slots";

        if (!isAvailable) {
            // Unavailable day row
            const unavailRow = document.createElement("div");
            unavailRow.className = "sched-slot-row";
            unavailRow.innerHTML = `
                <span class="sched-unavailable-text">Unavailable</span>
                <button type="button" class="sched-btn-action sched-btn-add" data-day="${day.key}" title="Add working hours">
                    <span class="material-symbols-outlined">add_circle</span>
                </button>
            `;
            slotsCol.appendChild(unavailRow);
        } else {
            // Available day with time slot rows
            slots.forEach((slot, idx) => {
                const slotRow = document.createElement("div");
                slotRow.className = "sched-slot-row";
                slotRow.dataset.slotIndex = String(idx);

                const isFirst = (idx === 0);
                slotRow.innerHTML = `
                    <input type="time" class="sched-time-input sched-start" value="${slot.start || '09:00'}" data-day="${day.key}" data-slot-index="${idx}" />
                    <span class="sched-arrow">→</span>
                    <input type="time" class="sched-time-input sched-end" value="${slot.end || '17:00'}" data-day="${day.key}" data-slot-index="${idx}" />
                    <button type="button" class="sched-btn-action sched-btn-remove" data-day="${day.key}" data-slot-index="${idx}" title="Remove slot">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                    ${isFirst ? `
                        <button type="button" class="sched-btn-action sched-btn-add" data-day="${day.key}" title="Add another slot">
                            <span class="material-symbols-outlined">add_circle</span>
                        </button>
                    ` : ''}
                `;
                slotsCol.appendChild(slotRow);
            });
        }

        row.appendChild(slotsCol);
        container.appendChild(row);
    });
}

function syncScheduleFromDOM() {
    SCHEDULE_DAYS.forEach(day => {
        const row = document.querySelector(`.sched-day-row[data-day="${day.key}"]`);
        if (!row) return;
        const slotRows = row.querySelectorAll(".sched-slot-row[data-slot-index]");
        if (slotRows.length === 0) {
            currentWeeklySchedule[day.key] = [];
        } else {
            const list = [];
            slotRows.forEach(sr => {
                const s = sr.querySelector(".sched-start")?.value;
                const e = sr.querySelector(".sched-end")?.value;
                if (s && e) {
                    list.push({ start: s, end: e });
                }
            });
            currentWeeklySchedule[day.key] = list;
        }
    });
}

function validateScheduleBeforeSave() {
    syncScheduleFromDOM();

    for (const day of SCHEDULE_DAYS) {
        const dayKey = day.key;
        const dayLabel = day.label;
        const slots = Array.isArray(currentWeeklySchedule[dayKey]) ? currentWeeklySchedule[dayKey] : [];
        if (slots.length === 0) continue; // Unavailable day is fine

        const dayRow = document.querySelector(`.sched-day-row[data-day="${dayKey}"]`);
        const slotRows = dayRow ? dayRow.querySelectorAll(".sched-slot-row[data-slot-index]") : [];

        // Reset error styling on this day
        slotRows.forEach(sr => {
            sr.querySelector(".sched-start")?.classList.remove("is-invalid");
            sr.querySelector(".sched-end")?.classList.remove("is-invalid");
        });

        // 1. Check for unconfigured 00:00 -> 00:00 or empty or start >= end
        for (let i = 0; i < slots.length; i++) {
            const s = slots[i];
            const sr = slotRows[i];
            const sInput = sr?.querySelector(".sched-start");
            const eInput = sr?.querySelector(".sched-end");

            if (!s.start || !s.end || (s.start === "00:00" && s.end === "00:00")) {
                sInput?.classList.add("is-invalid");
                eInput?.classList.add("is-invalid");
                showDoctorToast(`Please set valid working hours for ${dayLabel}.`, false);
                return false;
            }

            if (s.start >= s.end) {
                sInput?.classList.add("is-invalid");
                eInput?.classList.add("is-invalid");
                showDoctorToast(`Invalid time on ${dayLabel}: Start time (${format12Hour(s.start)}) must be earlier than end time (${format12Hour(s.end)}).`, false);
                return false;
            }
        }

        // 2. Check for exact duplicate slots on the same day
        for (let i = 0; i < slots.length; i++) {
            for (let j = i + 1; j < slots.length; j++) {
                if (slots[i].start === slots[j].start && slots[i].end === slots[j].end) {
                    const srA = slotRows[i];
                    const srB = slotRows[j];
                    srA?.querySelector(".sched-start")?.classList.add("is-invalid");
                    srA?.querySelector(".sched-end")?.classList.add("is-invalid");
                    srB?.querySelector(".sched-start")?.classList.add("is-invalid");
                    srB?.querySelector(".sched-end")?.classList.add("is-invalid");

                    showDoctorToast(`Time slot (${format12Hour(slots[i].start)} - ${format12Hour(slots[i].end)}) is already present for ${dayLabel}.`, false);
                    return false;
                }
            }
        }

        // 3. Check for overlapping slots on the same day
        for (let i = 0; i < slots.length; i++) {
            for (let j = i + 1; j < slots.length; j++) {
                if (slots[i].start < slots[j].end && slots[i].end > slots[j].start) {
                    const srA = slotRows[i];
                    const srB = slotRows[j];
                    srA?.querySelector(".sched-start")?.classList.add("is-invalid");
                    srA?.querySelector(".sched-end")?.classList.add("is-invalid");
                    srB?.querySelector(".sched-start")?.classList.add("is-invalid");
                    srB?.querySelector(".sched-end")?.classList.add("is-invalid");

                    showDoctorToast(`Time slots on ${dayLabel} overlap (${format12Hour(slots[i].start)} - ${format12Hour(slots[i].end)} and ${format12Hour(slots[j].start)} - ${format12Hour(slots[j].end)}). Please adjust them.`, false);
                    return false;
                }
            }
        }
    }

    return true;
}

function setupScheduleEventListeners() {
    const container = document.getElementById("doc-schedule-container");
    if (!container || container.dataset.listenerAttached) return;
    container.dataset.listenerAttached = "true";

    container.addEventListener("click", (e) => {
        const addBtn = e.target.closest(".sched-btn-add");
        if (addBtn) {
            const dayKey = addBtn.dataset.day;
            if (!dayKey) return;
            syncScheduleFromDOM();

            if (!Array.isArray(currentWeeklySchedule[dayKey])) {
                currentWeeklySchedule[dayKey] = [];
            }

            // Always add a new entry initialized to 00:00 -> 00:00 as requested
            currentWeeklySchedule[dayKey].push({ start: "00:00", end: "00:00" });
            renderWeeklySchedule(currentWeeklySchedule);

            setTimeout(() => {
                const dayRow = document.querySelector(`.sched-day-row[data-day="${dayKey}"]`);
                if (dayRow) {
                    const inputs = dayRow.querySelectorAll(".sched-start");
                    const lastInput = inputs[inputs.length - 1];
                    if (lastInput) {
                        lastInput.focus();
                    }
                }
            }, 50);
            return;
        }

        const removeBtn = e.target.closest(".sched-btn-remove");
        if (removeBtn) {
            const dayKey = removeBtn.dataset.day;
            const slotIdx = parseInt(removeBtn.dataset.slotIndex, 10);
            if (!dayKey || isNaN(slotIdx)) return;
            syncScheduleFromDOM();
            if (Array.isArray(currentWeeklySchedule[dayKey])) {
                currentWeeklySchedule[dayKey].splice(slotIdx, 1);
            }
            renderWeeklySchedule(currentWeeklySchedule);
            return;
        }
    });

    container.addEventListener("change", (e) => {
        if (e.target.classList.contains("sched-time-input")) {
            e.target.classList.remove("is-invalid");
            const row = e.target.closest(".sched-slot-row");
            if (row) {
                row.querySelectorAll(".sched-time-input").forEach(i => i.classList.remove("is-invalid"));
            }
            syncScheduleFromDOM();
        }
    });
}

function generateWorkingTimeSummary(schedule) {
    const activeDays = SCHEDULE_DAYS.filter(d => (schedule[d.key] || []).length > 0);
    if (activeDays.length === 0) return "Unavailable";
    const dayLabels = activeDays.map(d => d.badge).join(", ");
    const firstSlot = (schedule[activeDays[0].key] || [])[0];
    if (firstSlot) {
        return `${dayLabels}: ${format12Hour(firstSlot.start)} - ${format12Hour(firstSlot.end)}`;
    }
    return "Custom Schedule";
}

function format12Hour(timeStr) {
    if (!timeStr) return "";
    const parts = timeStr.split(":");
    let h = parseInt(parts[0], 10);
    const m = parts[1] || "00";
    const ampm = h >= 12 ? "PM" : "AM";
    h = h % 12;
    if (h === 0) h = 12;
    return `${h}:${m} ${ampm}`;
}


// =========================================================================
// LEAVE & SCHEDULE EXCEPTIONS MANAGEMENT
// =========================================================================

function setupLeaveExceptions() {
    const dateInput = document.getElementById("doc-exception-date");
    if (!dateInput) return;

    // Date range: today to today + 60 days
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const minDateStr = `${yyyy}-${mm}-${dd}`;

    const maxDate = new Date();
    maxDate.setDate(today.getDate() + 60);
    const maxY = maxDate.getFullYear();
    const maxM = String(maxDate.getMonth() + 1).padStart(2, '0');
    const maxD = String(maxDate.getDate()).padStart(2, '0');
    const maxDateStr = `${maxY}-${maxM}-${maxD}`;

    dateInput.min = minDateStr;
    dateInput.max = maxDateStr;

    const radioFull = document.getElementById("radio-exc-full");
    const radioHalf = document.getElementById("radio-exc-half");
    const slotsWrapper = document.getElementById("doc-exc-slots-wrapper");
    const slotsList = document.getElementById("doc-exc-slots-list");
    const addSlotBtn = document.getElementById("btn-add-exc-slot");
    const saveBtn = document.getElementById("btn-save-exception");

    function updateTypeVisibility() {
        if (radioHalf && radioHalf.checked) {
            if (slotsWrapper) slotsWrapper.style.display = "block";
            if (slotsList && slotsList.children.length === 0) {
                addCustomSlotRow("09:00", "12:00");
            }
        } else {
            if (slotsWrapper) slotsWrapper.style.display = "none";
        }
    }

    if (radioFull) radioFull.addEventListener("change", updateTypeVisibility);
    if (radioHalf) radioHalf.addEventListener("change", updateTypeVisibility);

    function addCustomSlotRow(defaultStart = "09:00", defaultEnd = "12:00") {
        if (!slotsList) return;
        const row = document.createElement("div");
        row.className = "exc-slot-row";
        row.style.cssText = "display: flex; align-items: center; gap: 8px;";
        row.innerHTML = `
            <input type="time" class="form-input exc-start" value="${defaultStart}" style="max-width: 130px; padding: 6px 10px;" required />
            <span style="color: var(--on-surface-variant);">&rarr;</span>
            <input type="time" class="form-input exc-end" value="${defaultEnd}" style="max-width: 130px; padding: 6px 10px;" required />
            <button type="button" class="btn btn-outline btn-remove-exc-slot" style="padding: 6px 10px; color: #e74c3c; border-color: #ffdad6;" title="Remove Slot">
                <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
            </button>
        `;
        row.querySelector(".btn-remove-exc-slot")?.addEventListener("click", () => {
            row.remove();
        });
        slotsList.appendChild(row);
    }

    if (addSlotBtn) {
        addSlotBtn.addEventListener("click", () => {
            addCustomSlotRow("14:00", "17:00");
        });
    }

    // Save exception
    if (saveBtn) {
        saveBtn.addEventListener("click", async () => {
            const dateVal = dateInput.value;
            if (!dateVal) {
                showDoctorToast("Please select a date for the exception/leave.", false);
                dateInput.focus();
                return;
            }

            const isHalf = radioHalf && radioHalf.checked;
            const isAvailable = isHalf ? 1 : 0;
            const reason = document.getElementById("doc-exception-reason")?.value.trim() || "";

            let slots = [];
            if (isAvailable === 1) {
                const rows = slotsList?.querySelectorAll(".exc-slot-row") || [];
                rows.forEach(r => {
                    const st = r.querySelector(".exc-start")?.value;
                    const et = r.querySelector(".exc-end")?.value;
                    if (st && et) {
                        slots.push({ start: st, end: et });
                    }
                });

                if (slots.length === 0) {
                    showDoctorToast("Please add at least one time slot for half holiday / custom hours.", false);
                    return;
                }
            }

            saveExceptionRequest({
                exception_date: dateVal,
                is_available: isAvailable,
                reason: reason,
                slots: slots,
                force: false
            });
        });
    }

    async function saveExceptionRequest(payload) {
        try {
            saveBtn.disabled = true;
            saveBtn.textContent = "Saving...";

            const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=saveException", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload)
            });
            const json = await res.json();

            if (json.status) {
                showDoctorToast(json.message || "Schedule exception saved successfully!", true);
                dateInput.value = "";
                const reasonInput = document.getElementById("doc-exception-reason");
                if (reasonInput) reasonInput.value = "";
                if (slotsList) slotsList.innerHTML = "";
                if (radioFull) radioFull.checked = true;
                updateTypeVisibility();
                await fetchDoctorExceptions();
            } else {
                if (json.data && json.data.has_conflict) {
                    const conf = confirm(json.message + "\n\nDo you want to confirm and force save this leave? (Patients will need to be notified or rescheduled).");
                    if (conf) {
                        payload.force = true;
                        saveExceptionRequest(payload);
                        return;
                    }
                } else {
                    showDoctorToast(json.message || "Failed to save exception", false);
                }
            }
        } catch (err) {
            console.error("Exception save error:", err);
            showDoctorToast("Network error saving exception", false);
        } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = "Save Exception";
        }
    }

    fetchDoctorExceptions();
}

async function fetchDoctorExceptions() {
    try {
        const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=getExceptions");
        const json = await res.json();
        if (json.status && json.data) {
            renderDoctorExceptionsList(json.data.exceptions || []);
        }
    } catch (e) {
        console.error("Error loading doctor exceptions:", e);
    }
}

function renderDoctorExceptionsList(exceptions) {
    const listEl = document.getElementById("doc-exceptions-list");
    if (!listEl) return;

    if (!exceptions || exceptions.length === 0) {
        listEl.innerHTML = `<div style="color: var(--on-surface-variant); font-size: 13px; padding: 14px; background: var(--surface-container-low); border-radius: 8px;">No upcoming leaves or schedule exceptions set.</div>`;
        return;
    }

    // Group by date
    const byDate = {};
    exceptions.forEach(ex => {
        if (!byDate[ex.exception_date]) byDate[ex.exception_date] = [];
        byDate[ex.exception_date].push(ex);
    });

    listEl.innerHTML = "";
    Object.keys(byDate).sort().forEach(dateStr => {
        const items = byDate[dateStr];
        const first = items[0];
        const isLeave = (parseInt(first.is_available, 10) === 0);

        const card = document.createElement("div");
        card.style.cssText = "display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: white; border: 1px solid var(--border-subtle); border-radius: 10px;";

        const dateObj = new Date(dateStr + "T00:00:00");
        const dateFormatted = dateObj.toLocaleDateString("en-US", { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });

        let badgeHtml = isLeave 
            ? `<span style="background: rgba(231, 76, 60, 0.12); color: #e74c3c; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">Full Holiday (Day Off)</span>`
            : `<span style="background: rgba(0, 82, 204, 0.12); color: #0052cc; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">Custom Working Hours</span>`;

        let slotsHtml = "";
        if (!isLeave) {
            const slotsText = items.map(it => `${format12Hour(it.start_time)} - ${format12Hour(it.end_time)}`).join(", ");
            slotsHtml = `<div style="font-size: 12px; color: var(--on-surface-variant); margin-top: 4px;"><span class="material-symbols-outlined" style="font-size: 14px; vertical-align: middle;">schedule</span> ${slotsText}</div>`;
        }

        const reasonHtml = first.reason 
            ? `<div style="font-size: 12px; color: var(--on-surface-variant); margin-top: 2px;"><em>Note: ${first.reason}</em></div>`
            : "";

        card.innerHTML = `
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 2px;">
                    <span style="font-weight: 700; font-size: 14px; color: var(--text-main);">${dateFormatted}</span>
                    ${badgeHtml}
                </div>
                ${slotsHtml}
                ${reasonHtml}
            </div>
            <button type="button" class="btn btn-outline btn-del-exc" data-id="${first.exception_id}" style="padding: 6px 12px; color: #e74c3c; border-color: #ffdad6; font-size: 12px;" title="Delete Exception">
                Remove
            </button>
        `;

        card.querySelector(".btn-del-exc")?.addEventListener("click", async () => {
            if (confirm(`Remove exception for ${dateFormatted}?`)) {
                try {
                    const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=deleteException", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ exception_id: first.exception_id })
                    });
                    const json = await res.json();
                    if (json.status) {
                        showDoctorToast("Exception removed.", true);
                        await fetchDoctorExceptions();
                    } else {
                        showDoctorToast(json.message || "Failed to remove exception", false);
                    }
                } catch (e) {
                    console.error("Delete exception error:", e);
                }
            }
        });

        listEl.appendChild(card);
    });
}

function formatDoctorDateTime(str) {
    if (!str) return "N/A";
    const d = new Date(str);
    if (isNaN(d.getTime())) return str;
    return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" }) + " at " + d.toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" });
}

function formatDoctorDate(str) {
    if (!str) return "N/A";
    const d = new Date(str);
    if (isNaN(d.getTime())) return str;
    return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
}

function setupDoctorSettingsForms() {
    // 1. Profile Form Save & Cancel
    const profileForm = document.getElementById("doc-profile-form");
    const cancelProfileBtn = document.getElementById("btn-cancel-doc-profile");

    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const saveBtn = document.getElementById("btn-save-doc-profile");
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = "Saving...";
            }

            const payload = {
                name: document.getElementById("doc-name")?.value.trim(),
                phone: document.getElementById("doc-phone")?.value.trim(),
                dob: document.getElementById("doc-dob")?.value,
                gender: document.getElementById("doc-gender")?.value,
                specialization: document.getElementById("doc-specialization")?.value.trim(),
                medical_reg_no: document.getElementById("doc-reg-no")?.value.trim(),
                qualification: document.getElementById("doc-qualification")?.value.trim(),
                experience: document.getElementById("doc-experience")?.value.trim(),
                address: document.getElementById("doc-address")?.value.trim(),
                city: document.getElementById("doc-city")?.value.trim(),
                state: document.getElementById("doc-state")?.value.trim(),
                pin: document.getElementById("doc-pin")?.value.trim(),
                about: document.getElementById("doc-about")?.value.trim()
            };

            try {
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=updateProfile", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.status) {
                    cachedDoctorSettings = Object.assign({}, cachedDoctorSettings, payload);
                    showDoctorToast("Doctor profile updated successfully!");
                    if (typeof window.fetchAndSetupUserProfile === 'function') {
                        window.fetchAndSetupUserProfile();
                    }
                } else {
                    showDoctorToast(json.message || "Failed to update profile", false);
                }
            } catch (err) {
                console.error("Profile save error:", err);
                showDoctorToast("Failed to save changes", false);
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
            if (cachedDoctorSettings) {
                renderDoctorSettingsData(cachedDoctorSettings);
                showDoctorToast("Changes reverted.", true);
            }
        });
    }

    // 2. Picture Upload Trigger
    const changePhotoBtn = document.getElementById("doc-btn-change-photo");
    const photoInput = document.getElementById("doc-photo-input");

    if (changePhotoBtn && photoInput) {
        changePhotoBtn.addEventListener("click", () => {
            photoInput.click();
        });

        photoInput.addEventListener("change", async () => {
            if (!photoInput.files || !photoInput.files[0]) return;
            const file = photoInput.files[0];

            const formData = new FormData();
            formData.append("profile_pic", file);

            changePhotoBtn.disabled = true;
            changePhotoBtn.textContent = "Uploading...";

            try {
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=uploadPhoto", {
                    method: "POST",
                    body: formData
                });
                const json = await res.json();
                if (json.status && json.data && json.data.profile_url) {
                    const newUrl = json.data.profile_url;
                    if (cachedDoctorSettings) cachedDoctorSettings.profile_url = newUrl;

                    const imgEl = document.getElementById("doc-profile-image");
                    const initialsEl = document.getElementById("doc-profile-initials");
                    if (imgEl) {
                        imgEl.src = newUrl;
                        imgEl.style.display = "block";
                    }
                    if (initialsEl) initialsEl.style.display = "none";

                    showDoctorToast("Profile photo updated successfully!");
                    if (typeof window.fetchAndSetupUserProfile === 'function') {
                        window.fetchAndSetupUserProfile();
                    }
                } else {
                    showDoctorToast(json.message || "Failed to upload photo", false);
                }
            } catch (err) {
                console.error("Photo upload error:", err);
                showDoctorToast("Upload error. Try another image.", false);
            } finally {
                changePhotoBtn.disabled = false;
                changePhotoBtn.innerHTML = '<span class="material-symbols-outlined" style="font-size: 18px;">photo_camera</span> Change Picture';
                photoInput.value = "";
            }
        });
    }

    // 3. Availability Form Save & Cancel (Weekly Schedule)
    const availForm = document.getElementById("doc-avail-form");
    const cancelAvailBtn = document.getElementById("btn-cancel-doc-avail");
    const availToggle = document.getElementById("doc-availability-toggle");

    if (availToggle) {
        availToggle.addEventListener("change", () => {
            const desc = document.getElementById("doc-avail-status-desc");
            if (desc) {
                desc.textContent = availToggle.checked 
                    ? "Active - Patients can view and book appointments with you" 
                    : "Inactive - You are currently marked as not taking appointments";
            }
        });
    }

    // Dedicated Save Modes Button Handler
    const saveModesBtn = document.getElementById("btn-save-doc-modes");
    if (saveModesBtn) {
        saveModesBtn.addEventListener("click", async () => {
            const selectedModes = [];
            document.querySelectorAll(".doc-mode-checkbox:checked").forEach(cb => {
                selectedModes.push(cb.value);
            });

            if (selectedModes.length === 0) {
                showDoctorToast("Please select at least one available consultation mode.", false);
                return;
            }

            try {
                saveModesBtn.disabled = true;
                saveModesBtn.textContent = "Saving...";

                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=updateAvailableModes", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ available_mode: selectedModes })
                });
                const json = await res.json();

                if (json.status) {
                    if (cachedDoctorSettings) {
                        cachedDoctorSettings.available_mode = selectedModes.join(",");
                    }
                    showDoctorToast("Available consultation modes saved successfully!", true);
                } else {
                    showDoctorToast(json.message || "Failed to save modes.", false);
                }
            } catch (err) {
                console.error("Save modes error:", err);
                showDoctorToast("Network error saving modes.", false);
            } finally {
                saveModesBtn.disabled = false;
                saveModesBtn.textContent = "Save Modes";
            }
        });
    }

    if (availForm) {
        availForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const saveBtn = document.getElementById("btn-save-doc-avail");
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = "Saving...";
            }

            syncScheduleFromDOM();

            // Validate all days for unconfigured (00:00), duplicates, overlaps and invalid times before saving
            if (!validateScheduleBeforeSave()) {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = "Save Schedule";
                }
                return;
            }

            const summaryWorkingTime = generateWorkingTimeSummary(currentWeeklySchedule);

            const selectedModes = [];
            document.querySelectorAll(".doc-mode-checkbox:checked").forEach(cb => {
                selectedModes.push(cb.value);
            });

            if (selectedModes.length === 0) {
                showDoctorToast("Please select at least one available consultation mode.", false);
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = "Save Schedule";
                }
                return;
            }

            const payload = {
                slot_duration: document.getElementById("doc-slot-duration")?.value || "30",
                availability: document.getElementById("doc-availability-toggle")?.checked ? 1 : 0,
                available_mode: selectedModes,
                weekly_schedule: currentWeeklySchedule,
                working_time: summaryWorkingTime
            };

            try {
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=updateAvailability", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.status) {
                    cachedDoctorSettings = Object.assign({}, cachedDoctorSettings, payload);
                    showDoctorToast("Clinic schedule and available modes saved!");
                } else {
                    showDoctorToast(json.message || "Failed to update availability", false);
                }
            } catch (err) {
                console.error("Availability save error:", err);
                showDoctorToast("Failed to save availability", false);
            } finally {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = "Save Schedule";
                }
            }
        });
    }

    if (cancelAvailBtn) {
        cancelAvailBtn.addEventListener("click", () => {
            if (cachedDoctorSettings) {
                currentWeeklySchedule = cachedDoctorSettings.weekly_schedule 
                    ? JSON.parse(JSON.stringify(cachedDoctorSettings.weekly_schedule)) 
                    : JSON.parse(JSON.stringify(DEFAULT_WEEKLY_SCHEDULE));
                renderWeeklySchedule(currentWeeklySchedule);

                const rawModes = cachedDoctorSettings.available_mode || "video_call,chat,offline,audio_call";
                const activeModesList = rawModes.split(",").map(m => m.trim().toLowerCase());
                document.querySelectorAll(".doc-mode-checkbox").forEach(cb => {
                    cb.checked = activeModesList.includes(cb.value.toLowerCase());
                });

                showDoctorToast("Availability reset to saved state.", true);
            }
        });
    }

    // 4. Password Form Update & Cancel
    const passwordForm = document.getElementById("doc-password-form");
    const cancelPasswordBtn = document.getElementById("btn-cancel-doc-password");

    if (passwordForm) {
        passwordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const currentPassword = document.getElementById("doc-current-password")?.value;
            const newPassword = document.getElementById("doc-new-password")?.value;
            const confirmPassword = document.getElementById("doc-confirm-password")?.value;

            if (newPassword !== confirmPassword) {
                showDoctorToast("New passwords do not match!", false);
                return;
            }

            if (newPassword.length < 6) {
                showDoctorToast("New password must be at least 6 characters long.", false);
                return;
            }

            const saveBtn = document.getElementById("btn-save-doc-password");
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.textContent = "Updating...";
            }

            try {
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=updatePassword", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ currentPassword, newPassword })
                });
                const json = await res.json();
                if (json.status) {
                    showDoctorToast("Password updated successfully!");
                    passwordForm.reset();
                } else {
                    showDoctorToast(json.message || "Failed to change password", false);
                }
            } catch (err) {
                console.error("Password update error:", err);
                showDoctorToast("Failed to update password", false);
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

    // 5. 2FA Toggle Interactive handler
    const twoFactorToggle = document.getElementById("doc-toggle-2fa");
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener("change", async () => {
            const enabled = twoFactorToggle.checked ? 1 : 0;
            const desc = document.getElementById("doc-2fa-desc");
            if (desc) {
                desc.textContent = enabled 
                    ? "2FA is enabled. OTP verification required on login." 
                    : "Require OTP verification when logging in from new devices";
            }

            try {
                await fetch("/my_doctor/public/doctor/api/settings.php?action=updatePreferences", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ two_factor_enabled: enabled })
                });
                showDoctorToast(enabled ? "Two-Factor Authentication enabled." : "Two-Factor Authentication disabled.");
            } catch (err) {
                console.error("2FA toggle error:", err);
            }
        });
    }

    // 6. Notifications Save & Cancel
    const saveNotifBtn = document.getElementById("btn-save-doc-notifications");
    const cancelNotifBtn = document.getElementById("btn-cancel-doc-notifications");

    if (saveNotifBtn) {
        saveNotifBtn.addEventListener("click", async () => {
            saveNotifBtn.disabled = true;
            saveNotifBtn.textContent = "Saving...";

            const payload = {
                appointment_reminders: document.getElementById("doc-notif-appointments")?.checked ? 1 : 0,
                message_notifications: document.getElementById("doc-notif-messages")?.checked ? 1 : 0,
                health_alerts: document.getElementById("doc-notif-alerts")?.checked ? 1 : 0,
                email_notifications: document.getElementById("doc-notif-email")?.checked ? 1 : 0,
                sms_notifications: document.getElementById("doc-notif-sms")?.checked ? 1 : 0
            };

            try {
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=updatePreferences", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.status) {
                    cachedDoctorSettings = Object.assign({}, cachedDoctorSettings, payload);
                    showDoctorToast("Notification preferences saved!");
                } else {
                    showDoctorToast(json.message || "Failed to save notifications", false);
                }
            } catch (err) {
                console.error("Save notifications error:", err);
                showDoctorToast("Failed to save notifications", false);
            } finally {
                saveNotifBtn.disabled = false;
                saveNotifBtn.textContent = "Save Notification Settings";
            }
        });
    }

    if (cancelNotifBtn) {
        cancelNotifBtn.addEventListener("click", () => {
            if (cachedDoctorSettings) {
                renderDoctorSettingsData(cachedDoctorSettings);
                showDoctorToast("Notification preferences reset.", true);
            }
        });
    }

    // 7. Privacy Save & Cancel & Activity logs
    const savePrivacyBtn = document.getElementById("btn-save-doc-privacy");
    const cancelPrivacyBtn = document.getElementById("btn-cancel-doc-privacy");
    const viewActivityLogsBtn = document.getElementById("btn-view-doc-activity-logs");

    if (savePrivacyBtn) {
        savePrivacyBtn.addEventListener("click", async () => {
            savePrivacyBtn.disabled = true;
            savePrivacyBtn.textContent = "Saving...";

            const payload = {
                directory_visibility: document.getElementById("doc-privacy-directory")?.checked ? 1 : 0,
                share_contact_data: document.getElementById("doc-privacy-contact")?.checked ? 1 : 0
            };

            try {
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=updatePreferences", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.status) {
                    cachedDoctorSettings = Object.assign({}, cachedDoctorSettings, payload);
                    showDoctorToast("Privacy preferences saved!");
                } else {
                    showDoctorToast(json.message || "Failed to save privacy", false);
                }
            } catch (err) {
                console.error("Save privacy error:", err);
                showDoctorToast("Failed to save privacy preferences", false);
            } finally {
                savePrivacyBtn.disabled = false;
                savePrivacyBtn.textContent = "Save Privacy Settings";
            }
        });
    }

    if (cancelPrivacyBtn) {
        cancelPrivacyBtn.addEventListener("click", () => {
            if (cachedDoctorSettings) {
                renderDoctorSettingsData(cachedDoctorSettings);
                showDoctorToast("Privacy preferences reset.", true);
            }
        });
    }

    if (viewActivityLogsBtn) {
        viewActivityLogsBtn.addEventListener("click", () => {
            const lastLogin = cachedDoctorSettings?.last_login_at ? formatDoctorDateTime(cachedDoctorSettings.last_login_at) : "Active now";
            const lastIp = cachedDoctorSettings?.last_login_ip || "127.0.0.1";
            showDoctorToast(`Last Session: ${lastLogin} from IP ${lastIp}`);
        });
    }
}

// 8. Delete Account Modal & Actions
function setupDoctorSettingsDeleteModal() {
    const modal = document.getElementById("delete-account-modal");
    const openBtn = document.getElementById("btn-open-doc-delete-modal");
    const cancelBtn = document.getElementById("btn-cancel-doc-delete");
    const confirmBtn = document.getElementById("btn-confirm-doc-delete");

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
                const res = await fetch("/my_doctor/public/doctor/api/settings.php?action=deleteAccount", {
                    method: "POST"
                });
                const json = await res.json();
                if (json.status) {
                    showDoctorToast("Doctor account permanently deleted. Redirecting...", true);
                    setTimeout(() => {
                        window.location.href = "/my_doctor/public/login_register.php";
                    }, 1200);
                } else {
                    showDoctorToast(json.message || "Failed to delete account. Please try again.", false);
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = "Yes, Delete Account";
                }
            } catch (err) {
                console.error("Account delete error:", err);
                showDoctorToast("Failed to delete account. Please try again.", false);
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Yes, Delete Account";
            }
        });
    }
}

// Doctor Toast feedback helper
let docToastTimer = null;
function showDoctorToast(message, isSuccess = true) {
    const toast = document.getElementById("doctor-toast");
    const msgEl = document.getElementById("doctor-toast-message");
    const iconEl = document.getElementById("doctor-toast-icon");

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

    if (docToastTimer) clearTimeout(docToastTimer);
    docToastTimer = setTimeout(() => {
        toast.classList.remove("show");
    }, 3500);
}

window.showDocNotification = function(msg, type) {
    showDoctorToast(msg, type === 'success');
};


// ============== DOCTOR APPOINTMENTS MANAGEMENT (PHASE 2) ==============
let currentDocApptFilter = "all";
let currentDocApptPage = 1;

function escapeDocHtml(str) {
    if (!str) return "";
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" };
    return String(str).replace(/[&<>"']/g, function(s) { return map[s]; });
}

async function fetchDoctorAppointments() {
    const tbody = document.getElementById("doctor-appointments-tbody");
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">Loading appointments...</td></tr>`;

    try {
        const res = await fetch(`/my_doctor/public/doctor/api/appointments.php?action=table&status=${currentDocApptFilter}&page=${currentDocApptPage}`);
        const json = await res.json();

        if (!json.status || !json.data) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 24px; color: #dc2626;">${json.message || "Failed to load appointments."}</td></tr>`;
            return;
        }

        const appointments = json.data.appointments || [];
        window.cachedDoctorAppointments = appointments;
        const pagination = json.data.pagination || {};

        const countEl = document.getElementById("doc-appointments-count");
        if (countEl) countEl.textContent = `Total: ${pagination.total || appointments.length}`;

        if (appointments.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 32px; color: var(--text-muted);">No appointments found in this status.</td></tr>`;
            return;
        }

        tbody.innerHTML = appointments.map(a => {
            const isOnline = ["video_call", "chat", "audio_call"].includes(a.appointment_mode);
            const modeIcons = {
                video_call: "videocam",
                chat: "chat",
                audio_call: "call",
                offline: "apartment"
            };
            const modeLabels = {
                video_call: "Video Call",
                chat: "Chat",
                audio_call: "Audio Call",
                offline: "In-Person Clinic"
            };

            const statusClassMap = {
                pending: "status-pending",
                confirmed: "status-confirmed",
                resheduled: "status-rescheduled",
                completed: "status-completed",
                cancelled: "status-cancelled"
            };
            const statusClass = statusClassMap[a.status] || "status-completed";

            let rescheduleNotice = "";
            if (a.status === "resheduled" && a.proposed_date) {
                rescheduleNotice = `<div style="font-size: 11px; color: #854d0e; margin-top: 4px; font-weight: 600;">Proposed: ${a.proposed_date} at ${a.proposed_start_time}</div>`;
            }

            // Status Selector Dropdown (Doctor can update appointment status directly)
            const isFinished = ["completed", "cancelled"].includes(a.status);
            const actionsHtml = `
                <div style="display: flex; align-items: center; gap: 8px;">
                    <select class="doc-status-select" 
                            data-id="${a.appointment_id}" 
                            data-current="${a.status}" 
                            data-name="${escapeDocHtml(a.patient_name || "Patient")}" 
                            style="padding: 6px 10px; border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--border-subtle); background: white; color: var(--text-heading); outline: none;">
                        <option value="pending" ${a.status === "pending" ? "selected" : ""} ${isFinished ? "disabled" : ""}>Pending</option>
                        <option value="confirmed" ${a.status === "confirmed" ? "selected" : ""} ${isFinished ? "disabled" : ""}>Confirmed</option>
                        <option value="resheduled" ${a.status === "resheduled" ? "selected" : ""} ${isFinished ? "disabled" : ""}>Reschedule</option>
                        <option value="completed" ${a.status === "completed" ? "selected" : ""} ${a.status === "cancelled" ? "disabled" : ""}>Completed</option>
                        <option value="cancelled" ${a.status === "cancelled" ? "selected" : ""}>Cancel</option>
                    </select>
                    ${(isOnline && a.status === "confirmed") ? `<button type="button" class="btn btn-primary btn-sm" onclick="openConsultationChat(${a.appointment_id})" title="Open Chat" style="padding: 6px 9px; display: inline-flex; align-items: center; border-radius: 6px; cursor: pointer; background: #2563eb; color: white; border: none;"><span class="material-symbols-outlined" style="font-size: 16px;">chat</span></button>` : ""}
                </div>
            `;

            const followUpTag = (a.appointment_type === "follow-up")
                ? `<span style="display: inline-block; background: #e0e7ff; color: #3730a3; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 6px;">Follow-up</span>`
                : "";

            return `
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text-heading);">${a.appointment_date}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">${a.appointment_time} (${a.appointment_duration} min)</div>
                    </td>
                    <td>
                        <div style="font-weight: 600; color: var(--text-heading);">${escapeDocHtml(a.patient_name || 'Patient')} ${followUpTag}</div>
                        <div style="font-size: 11px; color: var(--text-muted);">${escapeDocHtml(a.patient_phone || a.patient_email || '')}</div>
                    </td>
                    <td style="max-width: 220px;">
                        <div style="font-size: 13px; color: var(--text-heading); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeDocHtml(a.problem_description || 'No notes')}">
                            ${escapeDocHtml(a.problem_description || 'Consultation')}
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: var(--primary);">${modeIcons[a.appointment_mode] || 'medical_services'}</span>
                            <span>${modeLabels[a.appointment_mode] || a.appointment_mode}</span>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge ${statusClass}">${escapeDocHtml(a.status)}</span>
                        ${rescheduleNotice}
                    </td>
                    <td>${actionsHtml}</td>
                </tr>
            `;
        }).join("");

        // Wire Status Selector Event Listeners
        tbody.querySelectorAll(".doc-status-select").forEach(select => {
            select.addEventListener("change", async (e) => {
                const newStatus = e.target.value;
                const currentStatus = e.target.dataset.current;
                const apptId = e.target.dataset.id;
                const patientName = e.target.dataset.name;

                if (newStatus === currentStatus) return;

                if (newStatus === "confirmed") {
                    if (!confirm(`Confirm appointment #${apptId} for ${patientName}?`)) {
                        e.target.value = currentStatus;
                        return;
                    }
                    try {
                        const resp = await fetch("/my_doctor/public/doctor/api/appointments.php?action=confirm", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ appointment_id: apptId })
                        });
                        const res = await resp.json();
                        alert(res.message || "Confirmed successfully!");
                    } catch (err) {
                        alert("Network error confirming appointment.");
                    }
                    fetchDoctorAppointments();

                } else if (newStatus === "resheduled") {
                    // Open reschedule modal
                    const modal = document.getElementById("modal-doc-reschedule");
                    if (modal) {
                        const appt = (window.cachedDoctorAppointments || []).find(x => x.appointment_id == apptId);
                        document.getElementById("reschedule-appointment-id").value = apptId;
                        
                        const nameEl = document.getElementById("reschedule-patient-name");
                        if (nameEl) nameEl.textContent = patientName || "Patient";

                        const avatarEl = document.getElementById("reschedule-patient-avatar");
                        if (avatarEl) {
                            avatarEl.textContent = (patientName || "P").trim().charAt(0).toUpperCase();
                        }

                        const currentInfoEl = document.getElementById("reschedule-current-slot-info");
                        if (currentInfoEl) {
                            currentInfoEl.textContent = appt ? ("Current: " + appt.appointment_date + " at " + appt.appointment_time) : "Select date & time to reschedule";
                        }

                        const badgeEl = document.getElementById("reschedule-mode-badge");
                        if (badgeEl && appt) {
                            badgeEl.textContent = (appt.appointment_mode || "consultation").replace("_", " ");
                        }

                        const dateInput = document.getElementById("reschedule-proposed-date");
                        const timeInput = document.getElementById("reschedule-proposed-time");
                        const displaySpan = document.getElementById("doc-reschedule-selected-display");

                        if (timeInput) timeInput.value = "";
                        if (displaySpan) displaySpan.textContent = "";

                        const todayStr = new Date().toISOString().split("T")[0];
                        if (dateInput) {
                            dateInput.min = todayStr;
                            const tomorrow = new Date();
                            tomorrow.setDate(tomorrow.getDate() + 1);
                            dateInput.value = tomorrow.toISOString().split("T")[0];
                            loadDoctorRescheduleSlots(dateInput.value);
                        }

                        modal.style.display = "flex";
                        modal.classList.add("active");
                        modal.style.opacity = "1";
                        modal.style.pointerEvents = "auto";
                        modal.dataset.revertSelectId = apptId;
                        modal.dataset.revertStatus = currentStatus;
                    }

                } else if (newStatus === "cancelled") {
                    const reason = prompt(`Enter cancellation reason for ${patientName}:`);
                    if (reason === null) {
                        e.target.value = currentStatus;
                        return;
                    }
                    try {
                        const resp = await fetch("/my_doctor/public/doctor/api/appointments.php?action=cancel", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ appointment_id: apptId, reason })
                        });
                        const res = await resp.json();
                        alert(res.message || "Cancelled successfully!");
                    } catch (err) {
                        alert("Network error cancelling appointment.");
                    }
                    fetchDoctorAppointments();

                } else if (newStatus === "completed") {
                    if (!confirm(`Mark appointment #${apptId} as completed?`)) {
                        e.target.value = currentStatus;
                        return;
                    }
                    try {
                        const resp = await fetch("/my_doctor/public/doctor/api/appointments.php?action=complete", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ appointment_id: apptId })
                        });
                        const res = await resp.json();
                        alert(res.message || "Marked as completed!");
                    } catch (err) {
                        alert("Network error completing appointment.");
                    }
                    fetchDoctorAppointments();
                } else {
                    e.target.value = currentStatus;
                }
            });
        });

        // Pagination info
        const infoEl = document.getElementById("doc-pagination-info");
        if (infoEl) {
            infoEl.textContent = `Showing page ${pagination.page || 1} of ${pagination.totalPages || 1}`;
        }

    } catch (e) {
        console.error("Error fetching doctor appointments:", e);
        tbody.innerHTML = `<tr><td colspan="6" style="text-align: center; padding: 24px; color: #dc2626;">Network error loading appointments.</td></tr>`;
    }
}

async function loadDoctorRescheduleSlots(date) {
    const grid = document.getElementById("doc-reschedule-slots-grid");
    const timeInput = document.getElementById("reschedule-proposed-time");
    const displaySpan = document.getElementById("doc-reschedule-selected-display");
    if (!grid) return;

    if (timeInput) timeInput.value = "";
    if (displaySpan) displaySpan.textContent = "";

    if (!date) {
        grid.innerHTML = '<div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px; text-align: center; padding: 14px;">Please pick a date to view available slots.</div>';
        return;
    }

    grid.innerHTML = '<div style="grid-column: 1 / -1; color: var(--text-muted); font-size: 13px; text-align: center; padding: 14px;">Loading available slots...</div>';

    try {
        const resp = await fetch("/my_doctor/public/doctor/api/appointments.php?action=getAvailableSlots&date=" + encodeURIComponent(date));
        const resJson = await resp.json();

        if (!resJson.status || !resJson.data || resJson.data.is_blocked || (resJson.data.slots || []).length === 0) {
            const reason = resJson.data?.reason || "No available slots on this date. Please pick another date.";
            grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 12.5px; text-align: center; padding: 14px; font-weight: 500;">' + reason + '</div>';
            return;
        }

        const slots = resJson.data.slots || [];
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

                if (timeInput) timeInput.value = slot.start_time;
                if (displaySpan) displaySpan.textContent = "Selected: " + (slot.display_time || slot.start_time);
            };

            grid.appendChild(btn);
        });

    } catch (err) {
        console.error("Error loading doctor reschedule slots:", err);
        grid.innerHTML = '<div style="grid-column: 1 / -1; color: #dc2626; font-size: 12.5px; text-align: center; padding: 14px;">Error fetching slots. Please try again.</div>';
    }
}

function initDoctorAppointments() {
    currentDocApptFilter = "all";
    currentDocApptPage = 1;

    // Phase 5: Firebase Real-time Signal for Doctor Dashboard Appointments
    if (window.DoctorFirebase) {
        window.DoctorFirebase.subscribeDashboardAppointments(() => {
            fetchDoctorAppointments();
        });
    }

    // Filter buttons
    const filterContainer = document.getElementById("doctor-appointment-filters");
    if (filterContainer) {
        filterContainer.querySelectorAll(".doc-filter-btn").forEach(btn => {
            btn.addEventListener("click", () => {
                filterContainer.querySelectorAll(".doc-filter-btn").forEach(b => {
                    b.classList.remove("btn-primary");
                    b.classList.add("btn-outline");
                });
                btn.classList.remove("btn-outline");
                btn.classList.add("btn-primary");
                currentDocApptFilter = btn.dataset.status;
                currentDocApptPage = 1;
                fetchDoctorAppointments();
            });
        });
    }

    // Reschedule Modal Buttons
    const modal = document.getElementById("modal-doc-reschedule");
    const closeBtn = document.getElementById("btn-close-reschedule-modal");
    const cancelBtn = document.getElementById("btn-cancel-reschedule-modal");
    const submitBtn = document.getElementById("btn-submit-reschedule-modal");
    const dateInput = document.getElementById("reschedule-proposed-date");

    const closeRescheduleModal = () => {
        if (modal) {
            modal.style.display = "none";
            modal.classList.remove("active");
            modal.style.opacity = "";
            modal.style.pointerEvents = "";
            const revertApptId = modal.dataset.revertSelectId;
            const revertStatus = modal.dataset.revertStatus;
            if (revertApptId && revertStatus) {
                const sel = document.querySelector('.doc-status-select[data-id="' + revertApptId + '"]');
                if (sel) sel.value = revertStatus;
            }
        }
    };

    const form = document.getElementById("form-doc-reschedule");
    if (form) {
        form.onsubmit = async (e) => {
            e.preventDefault();
            if (submitBtn) submitBtn.click();
        };
    }

    if (closeBtn) closeBtn.onclick = closeRescheduleModal;
    if (cancelBtn) cancelBtn.onclick = closeRescheduleModal;

    if (dateInput) {
        dateInput.onchange = (e) => {
            loadDoctorRescheduleSlots(e.target.value);
        };
    }

    if (submitBtn) {
        submitBtn.onclick = async () => {
            const apptId = document.getElementById("reschedule-appointment-id").value;
            const propDate = document.getElementById("reschedule-proposed-date").value;
            const propTime = document.getElementById("reschedule-proposed-time").value;

            if (!propDate || !propTime) {
                alert("Please select both a date and an available time slot from the list.");
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = "Submitting...";

            try {
                const resp = await fetch("/my_doctor/public/doctor/api/appointments.php?action=reschedule", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        appointment_id: apptId,
                        proposed_date: propDate,
                        proposed_start_time: propTime
                    })
                });
                const resJson = await resp.json();
                if (resJson.status) {
                    alert(resJson.message || "Rescheduled successfully!");
                    closeRescheduleModal();
                    fetchDoctorAppointments();
                } else {
                    alert(resJson.message || "Failed to reschedule.");
                }
            } catch (err) {
                console.error("Reschedule error:", err);
                alert("Network error rescheduling appointment.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = "Propose New Slot";
            }
        };
    }

    fetchDoctorAppointments();
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
    window.openDoctorNotifications = openNotifications;

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
    document.addEventListener("DOMContentLoaded", function() { initNotificationSystem("doctor"); });
} else {
    initNotificationSystem("doctor");
}


/* ==========================================================================
   DOCTOR DASHBOARD (Phase 7 - Dynamic Integration)
   ========================================================================== */
function initDoctorDashboard() {
    const greetingEl = document.getElementById('doc-dash-greeting');
    const subgreetingEl = document.getElementById('doc-dash-subgreeting');
    const totalPatientsEl = document.getElementById('doc-dash-total-patients');
    const patientsTrendEl = document.getElementById('doc-dash-patients-trend-text');
    const apptsTodayEl = document.getElementById('doc-dash-appts-today');
    const apptsTodayBadgeEl = document.getElementById('doc-dash-appts-today-badge');
    const pendingApptsEl = document.getElementById('doc-dash-pending-appts');
    const pendingBadgeEl = document.getElementById('doc-dash-pending-badge');
    const ratingEl = document.getElementById('doc-dash-rating');
    const reviewsBadgeEl = document.getElementById('doc-dash-reviews-badge');
    const activitiesListEl = document.getElementById('doc-dash-activities-list');
    const completedTotalEl = document.getElementById('doc-dash-completed-total');
    const completedTodayEl = document.getElementById('doc-dash-completed-today');
    const followupsEl = document.getElementById('doc-dash-followups');
    const chartContainerEl = document.getElementById('doc-dash-chart-container');
    const chartLabelsEl = document.getElementById('doc-dash-chart-labels');
    const btnFlowWeekly = document.getElementById('btn-flow-weekly');
    const btnFlowMonthly = document.getElementById('btn-flow-monthly');
    const telehealthTextEl = document.getElementById('doc-dash-telehealth-text');
    const btnTelehealthAction = document.getElementById('btn-dash-telehealth-action');
    const btnViewAllAppts = document.getElementById('btn-dash-view-all-appts');

    if (btnViewAllAppts) {
        btnViewAllAppts.onclick = () => {
            if (window.loadDoctorPage) window.loadDoctorPage('appointments');
        };
    }

    const escapeHtml = (str) => {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
    };

    fetch('/my_doctor/public/doctor/api/dashboard.php')
        .then(res => {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(res => {
            if (!res.status || !res.data) {
                console.warn('Dashboard fetch returned error:', res.message);
                return;
            }
            const data = res.data;

            // 1. Header
            if (greetingEl && data.doctor_info) {
                greetingEl.textContent = data.doctor_info.greeting || 'Good Morning, Doctor';
            }
            if (subgreetingEl && data.doctor_info) {
                subgreetingEl.textContent = data.doctor_info.subgreeting || 'Here is a summary of your clinic\'s performance today.';
            }

            // 2. Stat Cards
            if (totalPatientsEl && data.stats) {
                totalPatientsEl.textContent = data.stats.total_patients;
            }
            if (patientsTrendEl && data.stats) {
                patientsTrendEl.textContent = data.stats.patients_trend;
            }
            if (apptsTodayEl && data.stats) {
                apptsTodayEl.textContent = data.stats.appointments_today;
            }
            if (apptsTodayBadgeEl && data.stats) {
                apptsTodayBadgeEl.textContent = `${data.stats.confirmed_today} Confirmed`;
            }
            if (pendingApptsEl && data.stats) {
                pendingApptsEl.textContent = data.stats.pending_appointments;
            }
            if (pendingBadgeEl && data.stats) {
                if (data.stats.pending_appointments > 0) {
                    pendingBadgeEl.className = 'badge badge-danger';
                    pendingBadgeEl.textContent = 'Action Needed';
                } else {
                    pendingBadgeEl.className = 'badge badge-success';
                    pendingBadgeEl.textContent = 'All Cleared';
                }
            }
            if (ratingEl && data.stats) {
                ratingEl.textContent = `${data.stats.rating} / 5.0`;
            }
            if (reviewsBadgeEl && data.stats) {
                reviewsBadgeEl.textContent = data.stats.rating_badge;
            }

            // 3. Quick Overview
            if (completedTotalEl && data.quick_overview) {
                completedTotalEl.textContent = data.quick_overview.completed_consultations;
            }
            if (completedTodayEl && data.quick_overview) {
                completedTodayEl.textContent = data.quick_overview.completed_today;
            }
            if (followupsEl && data.quick_overview) {
                followupsEl.textContent = data.quick_overview.followups_needed;
            }

            // 4. Recent Activities List
            if (activitiesListEl) {
                const activities = data.recent_activities || [];
                if (activities.length === 0) {
                    activitiesListEl.innerHTML = `
                        <div style="padding: 24px; text-align: center; color: var(--text-muted, #64748b);">
                            <span class="material-symbols-outlined" style="font-size: 32px; color: #94a3b8; display: block; margin-bottom: 8px;">calendar_today</span>
                            No recent consultations found
                        </div>
                    `;
                } else {
                    activitiesListEl.innerHTML = activities.map(act => {
                        let iconBg = '#dbeafe';
                        let iconColor = '#1d4ed8';
                        if (act.mode === 'video_call') {
                            iconBg = '#ecfdf5';
                            iconColor = '#059669';
                        } else if (act.mode === 'audio_call') {
                            iconBg = '#fef3c7';
                            iconColor = '#b45309';
                        }

                        let statusBadge = 'badge-success';
                        if (act.status === 'pending') statusBadge = 'badge-warning';
                        else if (act.status === 'cancelled') statusBadge = 'badge-danger';
                        else if (act.status === 'confirmed') statusBadge = 'badge-blue';

                        return `
                            <div class="alert-item" style="cursor: pointer; transition: background 0.15s; border-radius: 8px; padding: 10px;" onclick="if(window.loadDoctorPage) window.loadDoctorPage('appointments');">
                                <div class="alert-icon" style="background: ${iconBg}; color: ${iconColor};">
                                    <span class="material-symbols-outlined">${act.mode_icon}</span>
                                </div>
                                <div class="alert-content" style="flex: 1; min-width: 0;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                        <p class="alert-title" style="margin: 0; font-weight: 600;">${escapeHtml(act.patient_name)}</p>
                                        <span class="badge ${statusBadge}" style="font-size: 11px; text-transform: capitalize;">${act.status}</span>
                                    </div>
                                    <p class="alert-message" style="margin: 4px 0 2px 0; font-size: 13px; color: var(--text-muted, #64748b); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(act.problem)}</p>
                                    <p class="alert-time" style="margin: 0; font-size: 11.5px; color: #94a3b8;">${act.relative_time} • ${act.mode_label}</p>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
            }

            // 5. Patient Flow Chart
            if (chartContainerEl && data.patient_flow && data.patient_flow.days) {
                const days = data.patient_flow.days;
                chartContainerEl.innerHTML = `
                    ${days.map(d => `<div class="chart-bar h-pct-${d.pct} ${d.is_future ? 'chart-bar-light' : ''}" title="${d.day} (${d.date}): ${d.count} appointments"></div>`).join('')}
                    <div class="chart-grid-line bottom-pct-25"></div>
                    <div class="chart-grid-line bottom-pct-50"></div>
                    <div class="chart-grid-line bottom-pct-75"></div>
                `;
                if (chartLabelsEl) {
                    chartLabelsEl.innerHTML = days.map(d => `<span>${d.day}</span>`).join('');
                }
            }

            // Weekly / Monthly Toggle Buttons
            if (btnFlowWeekly && btnFlowMonthly) {
                btnFlowWeekly.onclick = () => {
                    btnFlowWeekly.className = 'badge badge-blue badge-btn-borderless';
                    btnFlowMonthly.className = 'badge badge-monthly';
                    if (data.patient_flow && data.patient_flow.days) {
                        chartContainerEl.innerHTML = `
                            ${data.patient_flow.days.map(d => `<div class="chart-bar h-pct-${d.pct} ${d.is_future ? 'chart-bar-light' : ''}" title="${d.day}: ${d.count} appointments"></div>`).join('')}
                            <div class="chart-grid-line bottom-pct-25"></div>
                            <div class="chart-grid-line bottom-pct-50"></div>
                            <div class="chart-grid-line bottom-pct-75"></div>
                        `;
                        if (chartLabelsEl) {
                            chartLabelsEl.innerHTML = data.patient_flow.days.map(d => `<span>${d.day}</span>`).join('');
                        }
                    }
                };

                btnFlowMonthly.onclick = () => {
                    btnFlowMonthly.className = 'badge badge-blue badge-btn-borderless';
                    btnFlowWeekly.className = 'badge badge-monthly';
                    const monthWeeks = [
                        { label: 'Week 1', pct: 45, count: Math.max(1, Math.round(data.stats.total_patients * 0.2)) },
                        { label: 'Week 2', pct: 70, count: Math.max(2, Math.round(data.stats.total_patients * 0.35)) },
                        { label: 'Week 3', pct: 85, count: Math.max(3, Math.round(data.stats.total_patients * 0.4)) },
                        { label: 'Week 4', pct: 55, count: Math.max(1, Math.round(data.stats.total_patients * 0.25)) }
                    ];
                    chartContainerEl.innerHTML = `
                        ${monthWeeks.map(w => `<div class="chart-bar h-pct-${w.pct}" title="${w.label}: ${w.count} consultations"></div>`).join('')}
                        <div class="chart-grid-line bottom-pct-25"></div>
                        <div class="chart-grid-line bottom-pct-50"></div>
                        <div class="chart-grid-line bottom-pct-75"></div>
                    `;
                    if (chartLabelsEl) {
                        chartLabelsEl.innerHTML = monthWeeks.map(w => `<span>${w.label}</span>`).join('');
                    }
                };
            }

            // 6. Telehealth Ready
            if (telehealthTextEl && data.telehealth) {
                const count = data.telehealth.scheduled_today;
                if (count > 0 && data.telehealth.next_session) {
                    telehealthTextEl.textContent = `You have ${count} virtual consultation(s) today. Next: ${escapeHtml(data.telehealth.next_session.patient_name)} at ${data.telehealth.next_session.time_formatted}.`;
                } else if (count > 0) {
                    telehealthTextEl.textContent = `You have ${count} virtual consultation(s) scheduled for today.`;
                } else {
                    telehealthTextEl.textContent = `No virtual consultations scheduled for today. You are all caught up!`;
                }
            }

            if (btnTelehealthAction) {
                btnTelehealthAction.onclick = () => {
                    if (data.telehealth && data.telehealth.next_session && data.telehealth.next_session.appointment_id) {
                        window.targetChatAppointmentId = data.telehealth.next_session.appointment_id;
                    }
                    if (window.loadDoctorPage) window.loadDoctorPage('chats');
                };
            }
        })
        .catch(err => {
            console.error('Error fetching doctor dashboard:', err);
        });
}

/* ==========================================================================
   DOCTOR CALLS TAB (Phase 7 - WhatsApp Style Calls Log)
   ========================================================================== */
function initDoctorCalls() {
    let currentFilter = 'all';
    let searchQuery = '';
    let searchDebounceTimer = null;

    const totalMinsEl = document.getElementById('calls-metric-total-minutes');
    const totalSubtitleEl = document.getElementById('calls-metric-total-subtitle');
    const qualityEl = document.getElementById('calls-metric-quality');
    const completionRateEl = document.getElementById('calls-metric-completion-rate');
    const videoPctEl = document.getElementById('calls-metric-video-pct');
    const videoCountEl = document.getElementById('calls-metric-video-count');
    const audioPctEl = document.getElementById('calls-metric-audio-pct');
    const audioCountEl = document.getElementById('calls-metric-audio-count');
    const countBadgeEl = document.getElementById('calls-count-badge');
    const container = document.getElementById('whatsapp-calls-container');
    const searchInput = document.getElementById('calls-search-input');
    const filterPills = document.querySelectorAll('#calls-filter-bar .calls-filter-pill');
    const waitingRoomBtn = document.getElementById('btn-calls-waiting-room');

    if (waitingRoomBtn) {
        waitingRoomBtn.onclick = () => {
            if (window.loadDoctorPage) window.loadDoctorPage('chats');
        };
    }

    const escapeHtml = (str) => {
        if (!str) return '';
        return String(str).replace(/[&<>"']/g, m => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[m]);
    };

    filterPills.forEach(pill => {
        pill.onclick = (e) => {
            e.preventDefault();
            filterPills.forEach(p => p.classList.remove('active'));
            pill.classList.add('active');
            currentFilter = pill.dataset.callFilter || 'all';
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

    const colorClasses = ['', 'teal', 'amber', 'rose', 'purple'];

    const fetchAndRenderCalls = () => {
        if (!container) return;

        container.innerHTML = `
            <div class="whatsapp-empty-state">
                <span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">progress_activity</span>
                <p>Loading consultation calls...</p>
            </div>
        `;

        const url = `/my_doctor/public/doctor/api/calls.php?type=${encodeURIComponent(currentFilter)}&search=${encodeURIComponent(searchQuery)}`;
        fetch(url)
            .then(res => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(res => {
                if (!res.status || !res.data) {
                    container.innerHTML = `<div class="whatsapp-empty-state"><span class="material-symbols-outlined">error</span><p>${escapeHtml(res.message || 'Error loading calls')}</p></div>`;
                    return;
                }

                const data = res.data;
                const metrics = data.metrics || {};
                const calls = data.calls || [];

                // 1. Update Metrics Cards
                if (totalMinsEl) totalMinsEl.textContent = metrics.total_minutes || '0';
                if (totalSubtitleEl) totalSubtitleEl.textContent = metrics.subtitle_consultations || 'Total across consultations';
                if (qualityEl) qualityEl.textContent = metrics.avg_quality || '4.9';
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
                    let avatarHtml = '';
                    if (call.patient_avatar) {
                        avatarHtml = `<img class="whatsapp-call-avatar" src="${escapeHtml(call.patient_avatar)}" alt="Patient" onerror="this.outerHTML='<div class=\\'whatsapp-call-avatar-initial\\'>${call.initials}</div>'">`;
                    } else {
                        const colorClass = colorClasses[idx % colorClasses.length];
                        avatarHtml = `<div class="whatsapp-call-avatar-initial ${colorClass}">${call.initials}</div>`;
                    }

                    let dirIcon = 'call_made';
                    let dirClass = 'call-dir-outgoing';
                    let statusText = 'Outgoing';

                    if (call.is_missed) {
                        dirIcon = 'call_missed';
                        dirClass = 'call-dir-missed';
                        statusText = 'Missed';
                    } else if (call.status === 'cancelled') {
                        dirIcon = 'cancel';
                        dirClass = 'call-dir-cancelled';
                        statusText = 'Cancelled';
                    } else if (call.call_direction === 'incoming') {
                        dirIcon = 'call_received';
                        dirClass = 'call-dir-incoming';
                        statusText = 'Incoming';
                    }

                    const callTypeIcon = (call.call_type === 'video_call') ? 'videocam' : 'call';
                    const callBtnTitle = (call.call_type === 'video_call') ? 'Start Video Call' : 'Start Audio Call';

                    return `
                        <div class="whatsapp-call-item" data-call-id="${call.call_id}">
                            <div class="whatsapp-call-left">
                                ${avatarHtml}
                                <div class="whatsapp-call-info">
                                    <div class="whatsapp-call-name-row">
                                        <span class="whatsapp-call-name">${escapeHtml(call.patient_name)}</span>
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
                }).join('');

                container.querySelectorAll('[data-action="quick-call"], [data-action="quick-chat"]').forEach(btn => {
                    btn.onclick = (e) => {
                        e.stopPropagation();
                        const apptId = parseInt(btn.dataset.apptId || '0', 10);
                        const convId = parseInt(btn.dataset.convId || '0', 10);
                        if (apptId > 0) {
                            window.targetChatAppointmentId = apptId;
                        }
                        if (convId > 0) {
                            window.targetChatConvId = convId;
                        }
                        if (window.loadDoctorPage) {
                            window.loadDoctorPage('chats');
                        }
                    };
                });
            })
            .catch(err => {
                console.error('Error fetching doctor calls:', err);
                if (container) {
                    container.innerHTML = `<div class="whatsapp-empty-state"><span class="material-symbols-outlined">error</span><p>Failed to load call history</p></div>`;
                }
            });
    };

    fetchAndRenderCalls();
}
