        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h2>Doctors Directory</h2>
                    <p>Browse and connect with our network of qualified healthcare professionals.</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-title">Availability</div>
                <div class="filter-group">
                    <button class="filter-option active" data-page="doctors" data-action="doctors-filter" data-filter="all">All</button>
                    <button class="filter-option" data-page="doctors" data-action="doctors-filter" data-filter="today">Available Today</button>
                    <button class="filter-option" data-page="doctors" data-action="doctors-filter" data-filter="week">This Week</button>
                    <button class="filter-option" data-page="doctors" data-action="doctors-filter" data-filter="month">This Month</button>
                </div>

            </div>

            <!-- Results Info -->
            <div class="results-info" id="doctors-results-info">
                Showing doctors matching your criteria
            </div>

            <!-- Doctors Grid -->
            <div class="doctor-grid" id="doctor-grid">
            </div>

            <!-- Pagination Section -->
            <div class="card" id="doctors-pagination-card" style="margin-top: 24px;">
                <div class="pagination" id="doctors-pagination" style="border-top: none;">
                    <p id="doctors-pagination-header">Showing 0 of 0 doctors</p>
                    <div class="pagination-controls" id="doctors-pagination-controls"></div>
                </div>
            </div>
        </div>
