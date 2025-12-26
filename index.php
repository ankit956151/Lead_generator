<?php
// Include configuration
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
}

// Include User model for authentication
require_once __DIR__ . '/models/User.php';

// Check if user is logged in
if (!User::isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user data
$currentUser = User::getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="LeadGen CMS - Powerful lead generation and management platform">
    <title>LeadGen CMS - Lead Generation Platform</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="css/themes.css">
</head>
<body>
    <!-- Preloader -->
    <div class="preloader" id="preloader">
        <div class="loader">
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <div class="loader-ring"></div>
            <span class="loader-text">LeadGen</span>
        </div>
    </div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <span class="logo-text">LeadGen<span class="accent">CMS</span></span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <span class="nav-section-title">Lead Generation</span>
                <ul class="nav-list">
                    <li class="nav-item active">
                        <a href="javascript:void(0)" class="nav-link" data-page="dashboard">
                            <i class="fas fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-page="leads">
                            <i class="fas fa-users"></i>
                            <span>All Leads</span>
                            <span class="nav-badge" id="leadCount">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-page="capture">
                            <i class="fas fa-magnet"></i>
                            <span>Lead Capture</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-page="sources">
                            <i class="fas fa-plug"></i>
                            <span>Lead Sources</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-page="get-leads">
                            <i class="fas fa-download" style="color: #10b981;"></i>
                            <span>Get Leads</span>
                            <span class="nav-badge" style="background: linear-gradient(135deg, #10b981, #059669);">Free</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <span class="nav-section-title">API Integrations</span>
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-page="api-settings">
                            <i class="fas fa-cog"></i>
                            <span>API Settings</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="javascript:void(0)" class="nav-link" data-page="scraper">
                            <i class="fas fa-spider"></i>
                            <span>Web Scraper</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['name']); ?>&background=6366f1&color=fff" alt="User Avatar">
                    <span class="status-indicator online"></span>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($currentUser['name']); ?></span>
                    <span class="user-role"><?php echo ucfirst($currentUser['role']); ?></span>
                </div>
                <a href="logout.php" class="user-menu-btn" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search leads..." id="globalSearch">
                </div>
            </div>
            <div class="header-right">
                <button class="header-btn theme-toggle" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="header-divider"></div>
                <button class="get-leads-btn" onclick="navigateTo('get-leads')" title="Get Free Leads">
                    <i class="fas fa-download"></i>
                    <span>Get Leads</span>
                    <span class="pulse-dot"></span>
                </button>
                <button class="add-new-btn" id="addLeadBtn">
                    <i class="fas fa-plus"></i>
                    <span>Add Lead</span>
                </button>
            </div>
        </header>

        <!-- Page Content Container -->
        <div class="page-container">
            
            <!-- Dashboard Page -->
            <section class="page-content active" id="page-dashboard">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">Lead Generation Dashboard</h1>
                        <p class="page-subtitle">Overview of your lead generation performance</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-outline" onclick="LeadGenerator.exportLeads()">
                            <i class="fas fa-download"></i>
                            Export CSV
                        </button>
                        <button class="btn btn-primary" onclick="LeadGenerator.openAddModal()">
                            <i class="fas fa-plus"></i>
                            Add Lead
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card stat-primary">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Total Leads</span>
                            <h2 class="stat-value" id="statTotalLeads">0</h2>
                            <div class="stat-change positive">
                                <i class="fas fa-layer-group"></i>
                                <span>All captured leads</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stat-success">
                        <div class="stat-icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Verified</span>
                            <h2 class="stat-value" id="statVerified">0</h2>
                            <div class="stat-change positive">
                                <i class="fas fa-check-circle"></i>
                                <span>Email verified</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stat-warning">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">New Today</span>
                            <h2 class="stat-value" id="statNewToday">0</h2>
                            <div class="stat-change">
                                <i class="fas fa-calendar-day"></i>
                                <span>Last 24 hours</span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stat-info">
                        <div class="stat-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Sources</span>
                            <h2 class="stat-value" id="statSources">0</h2>
                            <div class="stat-change">
                                <i class="fas fa-plug"></i>
                                <span>Active integrations</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Sources Overview -->
                <div class="charts-grid">
                    <div class="chart-card large">
                        <div class="chart-header">
                            <div class="chart-title-section">
                                <h3 class="chart-title">Recent Leads</h3>
                                <p class="chart-subtitle">Latest captured leads from all sources</p>
                            </div>
                            <a href="#leads" class="card-link" onclick="navigateTo('leads')">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="recent-leads-table">
                            <table class="leads-table">
                                <thead>
                                    <tr>
                                        <th>Lead</th>
                                        <th>Source</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="recentLeadsTable">
                                    <tr>
                                        <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">
                                            <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                                            No leads captured yet. Add your first lead!
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <div class="chart-title-section">
                                <h3 class="chart-title">Lead Sources</h3>
                                <p class="chart-subtitle">Where your leads come from</p>
                            </div>
                        </div>
                        <div class="sources-list" id="sourcesList">
                            <div class="source-item">
                                <div class="source-icon" style="background: rgba(99, 102, 241, 0.15); color: var(--color-primary-400);">
                                    <i class="fas fa-file-lines"></i>
                                </div>
                                <div class="source-info">
                                    <span class="source-name">Contact Form</span>
                                    <span class="source-count">0 leads</span>
                                </div>
                            </div>
                            <div class="source-item">
                                <div class="source-icon" style="background: rgba(6, 182, 212, 0.15); color: var(--color-secondary-400);">
                                    <i class="fab fa-hubspot"></i>
                                </div>
                                <div class="source-info">
                                    <span class="source-name">HubSpot</span>
                                    <span class="source-count">0 leads</span>
                                </div>
                            </div>
                            <div class="source-item">
                                <div class="source-icon" style="background: rgba(245, 158, 11, 0.15); color: var(--color-warning-400);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="source-info">
                                    <span class="source-name">Google Maps</span>
                                    <span class="source-count">0 leads</span>
                                </div>
                            </div>
                            <div class="source-item">
                                <div class="source-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--color-success-400);">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="source-info">
                                    <span class="source-name">Hunter.io</span>
                                    <span class="source-count">0 leads</span>
                                </div>
                            </div>
                            <div class="source-item">
                                <div class="source-icon" style="background: rgba(236, 72, 153, 0.15); color: #ec4899;">
                                    <i class="fas fa-spider"></i>
                                </div>
                                <div class="source-info">
                                    <span class="source-name">Apify Scraper</span>
                                    <span class="source-count">0 leads</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- All Leads Page -->
            <section class="page-content" id="page-leads">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">All Leads</h1>
                        <p class="page-subtitle">Manage and track all your captured leads</p>
                    </div>
                    <div class="page-actions">
                        <button class="btn btn-outline" onclick="LeadGenerator.exportLeads()">
                            <i class="fas fa-download"></i>
                            Export
                        </button>
                        <button class="btn btn-primary" onclick="LeadGenerator.openAddModal()">
                            <i class="fas fa-plus"></i>
                            Add Lead
                        </button>
                    </div>
                </div>

                <!-- Leads Filters -->
                <div class="leads-toolbar">
                    <div class="search-filter-group">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search by name, email, or company..." id="leadsSearch" oninput="LeadGenerator.filterLeads()">
                        </div>
                        <div class="filter-tabs" id="statusFilters">
                            <button class="filter-tab active" data-status="all" onclick="LeadGenerator.setFilter('all')">
                                All <span class="count" id="countAll">0</span>
                            </button>
                            <button class="filter-tab" data-status="new" onclick="LeadGenerator.setFilter('new')">
                                New <span class="count" id="countNew">0</span>
                            </button>
                            <button class="filter-tab" data-status="contacted" onclick="LeadGenerator.setFilter('contacted')">
                                Contacted <span class="count" id="countContacted">0</span>
                            </button>
                            <button class="filter-tab" data-status="qualified" onclick="LeadGenerator.setFilter('qualified')">
                                Qualified <span class="count" id="countQualified">0</span>
                            </button>
                            <button class="filter-tab" data-status="converted" onclick="LeadGenerator.setFilter('converted')">
                                Converted <span class="count" id="countConverted">0</span>
                            </button>
                        </div>
                    </div>
                    <select class="form-select" id="sourceFilter" style="width: auto;" onchange="LeadGenerator.filterLeads()">
                        <option value="">All Sources</option>
                        <option value="Contact Form">Contact Form</option>
                        <option value="HubSpot">HubSpot</option>
                        <option value="Google Maps">Google Maps</option>
                        <option value="Hunter.io">Hunter.io</option>
                        <option value="Apify">Apify</option>
                        <option value="Manual">Manual Entry</option>
                    </select>
                </div>

                <!-- Leads Table -->
                <div class="leads-container">
                    <div class="table-wrapper">
                        <table class="data-table" id="leadsTable">
                            <thead>
                                <tr>
                                    <th>
                                        <label class="custom-checkbox">
                                            <input type="checkbox" id="selectAllLeads" onchange="LeadGenerator.toggleSelectAll()">
                                            <span class="checkmark"></span>
                                        </label>
                                    </th>
                                    <th>Name <i class="fas fa-sort"></i></th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Company</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="leadsTableBody">
                                <!-- Leads will be rendered here -->
                            </tbody>
                        </table>
                    </div>
                    <div class="table-footer">
                        <div class="table-info" id="tableInfo">
                            Showing <strong>0</strong> of <strong>0</strong> leads
                        </div>
                        <div class="pagination-controls" id="paginationControls">
                            <button class="pagination-btn" id="prevPageBtn" onclick="LeadGenerator.prevPage()" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <span class="pagination-info" id="paginationInfo">Page 1 of 1</span>
                            <button class="pagination-btn" id="nextPageBtn" onclick="LeadGenerator.nextPage()" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="table-actions">
                            <button class="btn btn-outline btn-sm" id="bulkDeleteBtn" style="display: none;" onclick="LeadGenerator.bulkDelete()">
                                <i class="fas fa-trash"></i>
                                Delete Selected
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Lead Capture Page -->
            <section class="page-content" id="page-capture">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">Lead Capture</h1>
                        <p class="page-subtitle">Embed forms on your website to capture leads</p>
                    </div>
                </div>

                <div class="capture-grid">
                    <!-- Embed Code Card -->
                    <div class="capture-card">
                        <div class="capture-card-header">
                            <div class="capture-icon">
                                <i class="fas fa-code"></i>
                            </div>
                            <h3>Embed Code</h3>
                        </div>
                        <p class="capture-desc">Copy this HTML form code and paste it on your website to start capturing leads.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span>HTML Form Code</span>
                                <button class="btn btn-sm btn-ghost" onclick="copyEmbedCode()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <pre><code id="embedCode">&lt;form action="YOUR_API_ENDPOINT" method="POST"&gt;
  &lt;input type="text" name="name" placeholder="Your Name" required&gt;
  &lt;input type="email" name="email" placeholder="Email Address" required&gt;
  &lt;input type="tel" name="phone" placeholder="Phone Number"&gt;
  &lt;input type="text" name="company" placeholder="Company Name"&gt;
  &lt;input type="hidden" name="source" value="Contact Form"&gt;
  &lt;button type="submit"&gt;Submit&lt;/button&gt;
&lt;/form&gt;</code></pre>
                        </div>
                    </div>

                    <!-- Laravel Integration -->
                    <div class="capture-card">
                        <div class="capture-card-header">
                            <div class="capture-icon" style="background: linear-gradient(135deg, #ff2d20, #ff6b6b);">
                                <i class="fab fa-laravel"></i>
                            </div>
                            <h3>Laravel Integration</h3>
                        </div>
                        <p class="capture-desc">Backend code for storing leads in your Laravel application.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span>LeadController.php</span>
                                <button class="btn btn-sm btn-ghost" onclick="copyLaravelCode()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <pre><code id="laravelCode">// routes/web.php
Route::post('/api/leads', [LeadController::class, 'store']);

// app/Http/Controllers/LeadController.php
public function store(Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:leads',
        'phone' => 'nullable|string',
        'company' => 'nullable|string',
        'source' => 'required|string',
    ]);

    $lead = Lead::create($validated);
    
    // Broadcast for real-time updates
    broadcast(new NewLeadCaptured($lead));
    
    return response()->json([
        'success' => true,
        'lead' => $lead
    ]);
}</code></pre>
                        </div>
                    </div>

                    <!-- Migration Code -->
                    <div class="capture-card full-width">
                        <div class="capture-card-header">
                            <div class="capture-icon" style="background: linear-gradient(135deg, #4f46e5, #7c3aed);">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3>Database Migration</h3>
                        </div>
                        <p class="capture-desc">Create the leads table in your database with this migration.</p>
                        
                        <div class="code-block">
                            <div class="code-header">
                                <span>create_leads_table.php</span>
                                <button class="btn btn-sm btn-ghost" onclick="copyMigrationCode()">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <pre><code id="migrationCode">Schema::create('leads', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('phone')->nullable();
    $table->string('company')->nullable();
    $table->string('source')->default('Contact Form');
    $table->enum('status', ['new', 'contacted', 'qualified', 'converted', 'lost'])->default('new');
    $table->boolean('verified')->default(false);
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->index('email');
    $table->index('source');
    $table->index('status');
});</code></pre>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Lead Sources Page -->
            <section class="page-content" id="page-sources">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">Lead Sources</h1>
                        <p class="page-subtitle">Configure where your leads come from</p>
                    </div>
                </div>

                <div class="sources-grid">
                    <!-- Inbound Sources -->
                    <div class="source-category">
                        <h3 class="category-title">
                            <i class="fas fa-arrow-down"></i>
                            Inbound Sources
                        </h3>
                        <p class="category-desc">Visitors fill out your forms and become leads</p>
                        
                        <div class="source-cards">
                            <div class="source-card active">
                                <div class="source-card-icon">
                                    <i class="fas fa-file-lines"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>Contact Form</h4>
                                    <p>Capture leads from your website forms</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot active"></span>
                                    Active
                                </div>
                            </div>

                            <div class="source-card">
                                <div class="source-card-icon" style="background: linear-gradient(135deg, #ff7a45, #ff4d4f);">
                                    <i class="fab fa-hubspot"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>HubSpot Forms</h4>
                                    <p>Sync leads from HubSpot CRM</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot"></span>
                                    Not Connected
                                </div>
                            </div>

                            <div class="source-card">
                                <div class="source-card-icon" style="background: linear-gradient(135deg, #ffc107, #ff9800);">
                                    <i class="fab fa-wpforms"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>OptinMonster</h4>
                                    <p>Popup and slide-in form leads</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot"></span>
                                    Not Connected
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Outbound Sources -->
                    <div class="source-category">
                        <h3 class="category-title">
                            <i class="fas fa-arrow-up"></i>
                            Outbound Sources
                        </h3>
                        <p class="category-desc">Find and scrape leads from external sources</p>
                        
                        <div class="source-cards">
                            <div class="source-card">
                                <div class="source-card-icon" style="background: linear-gradient(135deg, #00c853, #69f0ae);">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>Google Maps Scraper</h4>
                                    <p>Extract business data from Google Maps</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot"></span>
                                    Not Connected
                                </div>
                            </div>

                            <div class="source-card">
                                <div class="source-card-icon" style="background: linear-gradient(135deg, #ff5722, #ff9800);">
                                    <i class="fas fa-envelope-open-text"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>Hunter.io</h4>
                                    <p>Find & verify professional emails</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot"></span>
                                    Not Connected
                                </div>
                            </div>

                            <div class="source-card">
                                <div class="source-card-icon" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                                    <i class="fas fa-spider"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>Apify</h4>
                                    <p>Scrape leads from any website</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot"></span>
                                    Not Connected
                                </div>
                            </div>

                            <div class="source-card">
                                <div class="source-card-icon" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                                    <i class="fab fa-linkedin"></i>
                                </div>
                                <div class="source-card-content">
                                    <h4>Apollo.io</h4>
                                    <p>B2B lead database access</p>
                                </div>
                                <div class="source-card-status">
                                    <span class="status-dot"></span>
                                    Not Connected
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- API Settings Page -->
            <section class="page-content" id="page-api-settings">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">API Settings</h1>
                        <p class="page-subtitle">Configure API keys for lead generation integrations</p>
                    </div>
                </div>

                <div class="api-settings-grid">
                    <!-- HubSpot API -->
                    <div class="api-card">
                        <div class="api-card-header">
                            <div class="api-icon" style="background: linear-gradient(135deg, #ff7a45, #ff4d4f);">
                                <i class="fab fa-hubspot"></i>
                            </div>
                            <div class="api-info">
                                <h3>HubSpot</h3>
                                <p>All-in-one CRM & Lead Management</p>
                            </div>
                            <a href="https://developers.hubspot.com/" target="_blank" class="btn btn-ghost btn-sm">
                                <i class="fas fa-external-link-alt"></i>
                                Docs
                            </a>
                        </div>
                        <div class="api-form">
                            <div class="form-group">
                                <label class="form-label">API Key</label>
                                <input type="password" class="form-input" placeholder="Enter HubSpot API key" id="hubspotKey">
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="saveApiKey('hubspot')">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>

                    <!-- Apify API -->
                    <div class="api-card">
                        <div class="api-card-header">
                            <div class="api-icon" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                                <i class="fas fa-spider"></i>
                            </div>
                            <div class="api-info">
                                <h3>Apify</h3>
                                <p>Scraping leads from the web</p>
                            </div>
                            <a href="https://docs.apify.com/api/v2" target="_blank" class="btn btn-ghost btn-sm">
                                <i class="fas fa-external-link-alt"></i>
                                Docs
                            </a>
                        </div>
                        <div class="api-form">
                            <div class="form-group">
                                <label class="form-label">API Token</label>
                                <input type="password" class="form-input" placeholder="Enter Apify API token" id="apifyKey">
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="saveApiKey('apify')">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>

                    <!-- Hunter.io API -->
                    <div class="api-card">
                        <div class="api-card-header">
                            <div class="api-icon" style="background: linear-gradient(135deg, #ff5722, #ff9800);">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                            <div class="api-info">
                                <h3>Hunter.io</h3>
                                <p>Finding/Verifying professional emails</p>
                            </div>
                            <a href="https://hunter.io/api" target="_blank" class="btn btn-ghost btn-sm">
                                <i class="fas fa-external-link-alt"></i>
                                Docs
                            </a>
                        </div>
                        <div class="api-form">
                            <div class="form-group">
                                <label class="form-label">API Key</label>
                                <input type="password" class="form-input" placeholder="Enter Hunter.io API key" id="hunterKey">
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="saveApiKey('hunter')">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>

                    <!-- Apollo.io API -->
                    <div class="api-card">
                        <div class="api-card-header">
                            <div class="api-icon" style="background: linear-gradient(135deg, #2563eb, #3b82f6);">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <div class="api-info">
                                <h3>Apollo.io</h3>
                                <p>B2B lead database</p>
                            </div>
                            <a href="https://apolloio.github.io/apollo-api-docs/" target="_blank" class="btn btn-ghost btn-sm">
                                <i class="fas fa-external-link-alt"></i>
                                Docs
                            </a>
                        </div>
                        <div class="api-form">
                            <div class="form-group">
                                <label class="form-label">API Key</label>
                                <input type="password" class="form-input" placeholder="Enter Apollo.io API key" id="apolloKey">
                            </div>
                            <button class="btn btn-primary btn-sm" onclick="saveApiKey('apollo')">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Web Scraper Page -->
            <section class="page-content" id="page-scraper">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">Web Scraper</h1>
                        <p class="page-subtitle">Scrape leads from Google Maps and other sources</p>
                    </div>
                </div>

                <div class="scraper-container">
                    <div class="scraper-form-card">
                        <h3>Google Maps Scraper</h3>
                        <p>Extract business information from Google Maps based on search query and location.</p>
                        
                        <form id="scraperForm" class="scraper-form" onsubmit="runScraper(event)">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Search Query</label>
                                    <input type="text" class="form-input" placeholder="e.g., restaurants, dentists, lawyers" id="scraperQuery" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-input" placeholder="e.g., New York, USA" id="scraperLocation" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Max Results</label>
                                    <select class="form-select" id="scraperLimit">
                                        <option value="10">10 results</option>
                                        <option value="25">25 results</option>
                                        <option value="50" selected>50 results</option>
                                        <option value="100">100 results</option>
                                    </select>
                                </div>
                                <div class="form-group" style="display: flex; align-items: flex-end;">
                                    <button type="submit" class="btn btn-primary" id="scraperBtn">
                                        <i class="fas fa-search"></i>
                                        Start Scraping
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="scraper-note">
                            <i class="fas fa-info-circle"></i>
                            <span>Requires Apify API key. Configure in <a href="#api-settings" onclick="navigateTo('api-settings')">API Settings</a>.</span>
                        </div>
                    </div>

                    <div class="scraper-results" id="scraperResults" style="display: none;">
                        <div class="results-header">
                            <h3>Scraped Results</h3>
                            <button class="btn btn-primary btn-sm" onclick="importScrapedLeads()">
                                <i class="fas fa-download"></i>
                                Import All as Leads
                            </button>
                        </div>
                        <div class="results-table">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <label class="custom-checkbox">
                                                <input type="checkbox" id="selectAllScraped">
                                                <span class="checkmark"></span>
                                            </label>
                                        </th>
                                        <th>Business Name</th>
                                        <th>Phone</th>
                                        <th>Website</th>
                                        <th>Address</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody id="scrapedResultsBody">
                                    <!-- Results will appear here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Get Leads Page (Free Data Sources) -->
            <section class="page-content" id="page-get-leads">
                <div class="page-header">
                    <div class="page-title-section">
                        <h1 class="page-title">
                            <i class="fas fa-download" style="color: #10b981; margin-right: 12px;"></i>
                            Get Real Leads
                        </h1>
                        <p class="page-subtitle">Fetch actual business and contact data from free public APIs</p>
                    </div>
                </div>

                <!-- Real Data Sources -->
                <div class="free-sources-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 32px;">
                    
                    <!-- GitHub Developer Contacts -->
                    <div class="source-card-featured" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #333, #666);"></div>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 56px; height: 56px; background: rgba(51, 51, 51, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fab fa-github" style="font-size: 28px; color: var(--text-primary);"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">GitHub Developers</h3>
                                <span style="font-size: 12px; background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 20px; font-weight: 600;">REAL DATA</span>
                            </div>
                        </div>
                        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.6;">
                            Real tech professionals with public profiles, emails, and company info from GitHub API.
                        </p>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-input" id="githubLocation" value="San Francisco" placeholder="City name">
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">Limit</label>
                            <select class="form-select" id="githubLimit">
                                <option value="10">10 contacts</option>
                                <option value="20" selected>20 contacts</option>
                                <option value="30">30 contacts</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" onclick="fetchGitHubLeads()" id="githubBtn" style="width: 100%;">
                            <i class="fab fa-github"></i>
                            Get Developer Leads
                        </button>
                    </div>

                    <!-- Universities -->
                    <div class="source-card-featured" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #6366f1, #8b5cf6);"></div>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 56px; height: 56px; background: rgba(99, 102, 241, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-university" style="font-size: 24px; color: #6366f1;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Universities</h3>
                                <span style="font-size: 12px; background: rgba(99, 102, 241, 0.15); color: #6366f1; padding: 4px 10px; border-radius: 20px; font-weight: 600;">VERIFIED</span>
                            </div>
                        </div>
                        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.6;">
                            Real universities with official domains and contact information worldwide.
                        </p>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">Country</label>
                            <select class="form-select" id="uniCountry">
                                <option value="United States">United States</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="Canada">Canada</option>
                                <option value="Australia">Australia</option>
                                <option value="Germany">Germany</option>
                                <option value="India">India</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" onclick="fetchUniversityLeads()" id="uniBtn" style="width: 100%;">
                            <i class="fas fa-university"></i>
                            Get University Contacts
                        </button>
                    </div>

                    <!-- OpenStreetMap Businesses -->
                    <div class="source-card-featured" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #10b981, #059669);"></div>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 56px; height: 56px; background: rgba(16, 185, 129, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-map-marker-alt" style="font-size: 24px; color: #10b981;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Local Businesses</h3>
                                <span style="font-size: 12px; background: rgba(16, 185, 129, 0.15); color: #10b981; padding: 4px 10px; border-radius: 20px; font-weight: 600;">REAL LOCATIONS</span>
                            </div>
                        </div>
                        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.6;">
                            Real business locations with addresses from OpenStreetMap database.
                        </p>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label class="form-label">Business Type</label>
                            <input type="text" class="form-input" id="businessQuery" value="restaurant" placeholder="e.g., restaurant, hotel, cafe">
                        </div>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">City</label>
                            <input type="text" class="form-input" id="businessCity" value="New York" placeholder="City name">
                        </div>
                        <button class="btn btn-primary" onclick="fetchBusinessLeads()" id="businessBtn" style="width: 100%;">
                            <i class="fas fa-store"></i>
                            Find Businesses
                        </button>
                    </div>

                    <!-- Tech Companies -->
                    <div class="source-card-featured" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; position: relative; overflow: hidden;">
                        <div style="position: absolute; top: 0; left: 0; right: 0; height: 4px; background: linear-gradient(135deg, #f59e0b, #d97706);"></div>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="width: 56px; height: 56px; background: rgba(245, 158, 11, 0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-building" style="font-size: 24px; color: #f59e0b;"></i>
                            </div>
                            <div>
                                <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Tech Companies</h3>
                                <span style="font-size: 12px; background: rgba(245, 158, 11, 0.15); color: #f59e0b; padding: 4px 10px; border-radius: 20px; font-weight: 600;">TOP 30</span>
                            </div>
                        </div>
                        <p style="color: var(--text-secondary); font-size: 14px; margin-bottom: 16px; line-height: 1.6;">
                            Contact info for major tech companies including Google, Meta, Apple, Microsoft, and more.
                        </p>
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label class="form-label">Number of Companies</label>
                            <select class="form-select" id="techLimit">
                                <option value="10">10 companies</option>
                                <option value="20" selected>20 companies</option>
                                <option value="30">All 30 companies</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" onclick="fetchTechCompanyLeads()" id="techBtn" style="width: 100%;">
                            <i class="fas fa-rocket"></i>
                            Get Tech Contacts
                        </button>
                    </div>

                </div>

                <!-- Generated Leads Results -->
                <div class="generated-leads-section" id="generatedLeadsSection" style="display: none;">
                    <div class="card" style="border-radius: 16px; overflow: hidden;">
                        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary);">
                                    <i class="fas fa-list" style="color: #10b981; margin-right: 8px;"></i>
                                    Generated Leads
                                </h3>
                                <p style="font-size: 14px; color: var(--text-secondary); margin-top: 4px;" id="generatedLeadsCount">0 leads ready to import</p>
                            </div>
                            <div style="display: flex; gap: 12px;">
                                <button class="btn btn-outline" onclick="clearGeneratedLeads()">
                                    <i class="fas fa-trash"></i>
                                    Clear
                                </button>
                                <button class="btn btn-primary" onclick="importGeneratedLeads()">
                                    <i class="fas fa-download"></i>
                                    Import All to CRM
                                </button>
                            </div>
                        </div>
                        <div class="table-wrapper" style="max-height: 450px; overflow-y: auto;">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>
                                            <label class="custom-checkbox">
                                                <input type="checkbox" id="selectAllGenerated" onchange="toggleSelectAllGenerated()">
                                                <span class="checkmark"></span>
                                            </label>
                                        </th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody id="generatedLeadsBody">
                                    <!-- Generated leads will appear here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- API Info Section -->
                <div class="api-info-section" style="margin-top: 32px;">
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 16px; padding: 24px;">
                        <h3 style="font-size: 18px; font-weight: 600; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-info-circle" style="color: #6366f1;"></i>
                            About Free Lead Sources
                        </h3>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div style="padding: 16px; background: var(--bg-glass); border-radius: 12px;">
                                <h4 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                                    <i class="fas fa-check-circle" style="color: #10b981; margin-right: 6px;"></i>
                                    No API Key Required
                                </h4>
                                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                                    All these data sources are completely free and don't require any API keys or authentication.
                                </p>
                            </div>
                            <div style="padding: 16px; background: var(--bg-glass); border-radius: 12px;">
                                <h4 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                                    <i class="fas fa-shield-alt" style="color: #6366f1; margin-right: 6px;"></i>
                                    Test & Development
                                </h4>
                                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                                    Perfect for testing your CRM workflows and developing lead management features.
                                </p>
                            </div>
                            <div style="padding: 16px; background: var(--bg-glass); border-radius: 12px;">
                                <h4 style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px;">
                                    <i class="fas fa-sync-alt" style="color: #f59e0b; margin-right: 6px;"></i>
                                    Realistic Data
                                </h4>
                                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                                    Generated data follows realistic patterns for names, emails, and contact information.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- Add/Edit Lead Modal -->
    <div class="modal" id="leadModal">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add New Lead</h2>
                <button class="modal-close" onclick="LeadGenerator.closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="leadForm">
                    <input type="hidden" id="leadId">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-input" id="leadName" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" class="form-input" id="leadEmail" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-input" id="leadPhone">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-input" id="leadCompany">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Source</label>
                            <select class="form-select" id="leadSource">
                                <option value="Manual">Manual Entry</option>
                                <option value="Contact Form">Contact Form</option>
                                <option value="HubSpot">HubSpot</option>
                                <option value="Google Maps">Google Maps</option>
                                <option value="Hunter.io">Hunter.io</option>
                                <option value="Apify">Apify</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" id="leadStatus">
                                <option value="new">New</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="converted">Converted</option>
                                <option value="lost">Lost</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea class="form-textarea" id="leadNotes" rows="3" placeholder="Add any notes about this lead..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="LeadGenerator.closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="LeadGenerator.saveLead()">
                    <i class="fas fa-save"></i>
                    Save Lead
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="js/leadgen.js"></script>
</body>
</html>
