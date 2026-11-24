/**
 * Responsive Sidebar Navigation (Phase 6)
 * Handles smooth dynamic collapse, tablet icon-only mode, and mobile drawer for
 * Doctor, Patient, and Admin portals.
 */
(function() {
    function initResponsiveSidebar() {
        const toggleBtn = document.getElementById('sidebar-toggle-btn');
        const collapseBtn = document.getElementById('sidebar-collapse-btn');
        const closeBtn = document.getElementById('sidebar-close-btn');
        const sidebar = document.querySelector('.sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const navLinks = document.querySelectorAll('.sidebar-nav .nav-link');

        if (!sidebar) return;

        // Ensure backdrop exists in DOM
        let currentBackdrop = backdrop;
        if (!currentBackdrop) {
            currentBackdrop = document.createElement('div');
            currentBackdrop.id = 'sidebar-backdrop';
            currentBackdrop.className = 'sidebar-backdrop';
            document.body.appendChild(currentBackdrop);
        }

        // Add tooltip titles to nav links for icon-only collapsed mode
        navLinks.forEach(link => {
            if (!link.getAttribute('title')) {
                const labelSpan = link.querySelector('span:not(.material-symbols-outlined)');
                if (labelSpan && labelSpan.textContent.trim()) {
                    link.setAttribute('title', labelSpan.textContent.trim());
                }
            }
        });

        // Restore desktop collapsed preference
        const isDesktop = window.innerWidth > 991;
        const savedPref = localStorage.getItem('sidebar_collapsed');
        if (isDesktop && savedPref === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }

        function isMobile() {
            return window.innerWidth <= 768;
        }

        function isTablet() {
            return window.innerWidth > 768 && window.innerWidth <= 991;
        }

        function openMobileSidebar() {
            document.body.classList.add('sidebar-mobile-open');
            document.documentElement.classList.add('sidebar-mobile-open');
            sidebar.classList.add('active');
            if (currentBackdrop) currentBackdrop.classList.add('active');
        }

        function closeMobileSidebar() {
            document.body.classList.remove('sidebar-mobile-open');
            document.documentElement.classList.remove('sidebar-mobile-open');
            sidebar.classList.remove('active');
            if (currentBackdrop) currentBackdrop.classList.remove('active');
        }

        function toggleSidebar() {
            if (isMobile()) {
                if (document.body.classList.contains('sidebar-mobile-open') || sidebar.classList.contains('active')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else if (isTablet()) {
                // On tablet, toggle between icon-only mode and expanded mode
                if (document.body.classList.contains('sidebar-tablet-expanded')) {
                    document.body.classList.remove('sidebar-tablet-expanded');
                } else {
                    document.body.classList.add('sidebar-tablet-expanded');
                }
            } else {
                // On laptop/desktop, toggle collapsed mode
                const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
                localStorage.setItem('sidebar_collapsed', isCollapsed ? 'true' : 'false');
            }
        }

        // Mobile topbar hamburger toggle button
        if (toggleBtn) {
            toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        }

        // Inside sidebar collapse button (Laptop & Tablet)
        if (collapseBtn) {
            collapseBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleSidebar();
            });
        }

        // Mobile close button (X)
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                closeMobileSidebar();
            });
        }

        // Backdrop click & touch handler
        if (currentBackdrop) {
            currentBackdrop.addEventListener('click', () => {
                closeMobileSidebar();
                if (isTablet()) {
                    document.body.classList.remove('sidebar-tablet-expanded');
                }
            });

            // Prevent background touch scrolling through the backdrop
            currentBackdrop.addEventListener('touchmove', (e) => {
                e.preventDefault();
            }, { passive: false });
        }

        // Close mobile drawer when user taps any nav link to switch page
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (isMobile()) {
                    closeMobileSidebar();
                } else if (isTablet() && document.body.classList.contains('sidebar-tablet-expanded')) {
                    document.body.classList.remove('sidebar-tablet-expanded');
                }
            });
        });

        // Window resize handler: clean up states when resizing across breakpoints
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (!isMobile()) {
                    closeMobileSidebar();
                }
                if (!isTablet()) {
                    document.body.classList.remove('sidebar-tablet-expanded');
                }
            }, 100);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initResponsiveSidebar);
    } else {
        initResponsiveSidebar();
    }
})();
