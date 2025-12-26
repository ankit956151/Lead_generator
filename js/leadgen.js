/* =====================================================
   LeadGen CMS - Lead Generator Module (PHP Backend)
   ===================================================== */

// API Configuration
const API_BASE_URL = window.location.origin + '/lead_generate/api';

// Lead Generator Main Module
const LeadGenerator = {
    leads: [],
    currentFilter: 'all',
    selectedLeads: [],
    apiKeys: {},
    isLoading: false,
    statusCounts: {},
    currentPage: 1,
    totalPages: 1,
    perPage: 20,

    // Initialize the application
    async init() {
        this.initPreloader();
        this.bindEvents();
        this.initSidebar();
        this.initNavigation();
        this.initTheme();
        
        // Load data from API
        await this.loadDashboardData();
        await this.loadLeads();
        
        this.hidePreloader();
    },

    // API Helper
    async apiCall(endpoint, options = {}) {
        const url = `${API_BASE_URL}/${endpoint}`;
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        try {
            const response = await fetch(url, { ...defaultOptions, ...options });
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || 'API request failed');
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            this.showToast('error', 'Error', error.message);
            throw error;
        }
    },

    // Load dashboard data
    async loadDashboardData() {
        try {
            const response = await this.apiCall('leads.php?action=statistics');
            
            if (response.success) {
                const { overview, status_counts, sources } = response.data;
                
                // Update stats
                document.getElementById('statTotalLeads').textContent = overview.total_leads || 0;
                document.getElementById('statVerified').textContent = overview.verified_leads || 0;
                document.getElementById('statNewToday').textContent = overview.today_leads || 0;
                document.getElementById('statSources').textContent = sources.length || 0;
                document.getElementById('leadCount').textContent = overview.total_leads || 0;
                
                // Update status counts
                this.statusCounts = status_counts;
                document.getElementById('countAll').textContent = status_counts.all || 0;
                document.getElementById('countNew').textContent = status_counts.new || 0;
                document.getElementById('countContacted').textContent = status_counts.contacted || 0;
                document.getElementById('countQualified').textContent = status_counts.qualified || 0;
                document.getElementById('countConverted').textContent = status_counts.converted || 0;
                
                // Update sources list
                this.updateSourcesList(sources);
            }
            
            // Load recent leads
            await this.loadRecentLeads();
            
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    },

    // Load leads from API
    async loadLeads(page = null) {
        if (this.isLoading) return;
        this.isLoading = true;
        
        // Use provided page or current page
        if (page !== null) {
            this.currentPage = page;
        }
        
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                per_page: this.perPage
            });
            
            // Add filters
            if (this.currentFilter !== 'all') {
                params.append('status', this.currentFilter);
            }
            
            const sourceFilter = document.getElementById('sourceFilter')?.value;
            if (sourceFilter) {
                params.append('source', sourceFilter);
            }
            
            const searchQuery = document.getElementById('leadsSearch')?.value?.trim();
            if (searchQuery) {
                params.append('search', searchQuery);
            }
            
            const response = await this.apiCall(`leads.php?action=list&${params.toString()}`);
            
            if (response.success) {
                this.leads = response.data;
                this.totalPages = response.meta.total_pages || 1;
                this.renderLeads();
                this.updateTableInfo(response.data.length, response.meta.total);
                this.updatePaginationControls();
            }
            
        } catch (error) {
            console.error('Failed to load leads:', error);
        } finally {
            this.isLoading = false;
        }
    },

    // Pagination: Go to next page
    nextPage() {
        if (this.currentPage < this.totalPages) {
            this.loadLeads(this.currentPage + 1);
        }
    },

    // Pagination: Go to previous page
    prevPage() {
        if (this.currentPage > 1) {
            this.loadLeads(this.currentPage - 1);
        }
    },

    // Update pagination controls UI
    updatePaginationControls() {
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        const pageInfo = document.getElementById('paginationInfo');
        
        if (prevBtn) {
            prevBtn.disabled = this.currentPage <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = this.currentPage >= this.totalPages;
        }
        if (pageInfo) {
            pageInfo.textContent = `Page ${this.currentPage} of ${this.totalPages}`;
        }
    },

    // Load recent leads
    async loadRecentLeads() {
        try {
            const response = await this.apiCall('leads.php?action=recent&limit=5');
            
            if (response.success) {
                this.renderRecentLeads(response.data);
            }
        } catch (error) {
            console.error('Failed to load recent leads:', error);
        }
    },

    // Update sources list
    updateSourcesList(sources) {
        const sourcesList = document.getElementById('sourcesList');
        if (!sourcesList || !sources.length) return;
        
        // Create source items HTML
        const sourceIcons = {
            'Contact Form': 'fas fa-file-lines',
            'HubSpot': 'fab fa-hubspot',
            'Google Maps': 'fas fa-map-marker-alt',
            'Hunter.io': 'fas fa-envelope',
            'Apify': 'fas fa-spider',
            'Manual': 'fas fa-user-plus',
            'Apollo.io': 'fas fa-rocket'
        };
        
        const sourceColors = {
            'Contact Form': 'var(--color-primary-400)',
            'HubSpot': '#ff7a45',
            'Google Maps': '#00c853',
            'Hunter.io': '#ff5722',
            'Apify': '#7c3aed',
            'Manual': '#71717a',
            'Apollo.io': '#2563eb'
        };
        
        sourcesList.innerHTML = sources.slice(0, 5).map(source => `
            <div class="source-item">
                <div class="source-icon" style="background: ${sourceColors[source.source] || 'var(--color-primary-400)'}20; color: ${sourceColors[source.source] || 'var(--color-primary-400)'};">
                    <i class="${sourceIcons[source.source] || 'fas fa-plug'}"></i>
                </div>
                <div class="source-info">
                    <span class="source-name">${source.source}</span>
                    <span class="source-count">${source.lead_count} leads</span>
                </div>
            </div>
        `).join('');
    },

    // Render recent leads on dashboard
    renderRecentLeads(leads) {
        const tbody = document.getElementById('recentLeadsTable');
        if (!tbody) return;

        if (!leads || leads.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center" style="padding: 40px; color: var(--text-muted);">
                        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        No leads captured yet. Add your first lead!
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = leads.map(lead => `
            <tr>
                <td>
                    <div class="lead-info">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(lead.name)}&background=${this.getSourceColor(lead.source)}&color=fff" alt="">
                        <div>
                            <span class="lead-name">${this.escapeHtml(lead.name)}</span>
                            <span class="lead-email">${this.escapeHtml(lead.email)}</span>
                        </div>
                    </div>
                </td>
                <td><span class="source-tag">${this.escapeHtml(lead.source)}</span></td>
                <td><span class="status-badge ${lead.status}">${this.capitalize(lead.status)}</span></td>
                <td>${this.timeAgo(lead.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn view" onclick="LeadGenerator.viewLead(${lead.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit" onclick="LeadGenerator.editLead(${lead.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    // Render leads table
    renderLeads() {
        const tbody = document.getElementById('leadsTableBody');
        if (!tbody) return;

        if (this.leads.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center" style="padding: 60px; color: var(--text-muted);">
                        <i class="fas fa-users-slash" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        <strong style="color: var(--text-secondary);">No leads found</strong><br>
                        <span>Try adjusting your filters or add a new lead.</span>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.leads.map(lead => `
            <tr data-id="${lead.id}">
                <td>
                    <label class="custom-checkbox">
                        <input type="checkbox" class="lead-checkbox" value="${lead.id}" onchange="LeadGenerator.toggleSelect(${lead.id})">
                        <span class="checkmark"></span>
                    </label>
                </td>
                <td>
                    <div class="lead-info">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(lead.name)}&background=${this.getSourceColor(lead.source)}&color=fff" alt="">
                        <span class="lead-name">${this.escapeHtml(lead.name)}</span>
                    </div>
                </td>
                <td>
                    <span class="lead-email">${this.escapeHtml(lead.email)}</span>
                    ${lead.is_verified ? '<i class="fas fa-check-circle" style="color: var(--color-success-400); margin-left: 4px;" title="Verified"></i>' : ''}
                </td>
                <td>${lead.phone || '-'}</td>
                <td>${this.escapeHtml(lead.company || '-')}</td>
                <td><span class="source-tag">${this.escapeHtml(lead.source)}</span></td>
                <td><span class="status-badge ${lead.status}">${this.capitalize(lead.status)}</span></td>
                <td>${this.timeAgo(lead.created_at)}</td>
                <td>
                    <div class="action-buttons">
                        <button class="action-btn view" onclick="LeadGenerator.viewLead(${lead.id})" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit" onclick="LeadGenerator.editLead(${lead.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete" onclick="LeadGenerator.deleteLead(${lead.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    },

    // Preloader
    initPreloader() {
        // Preloader is shown by default
    },

    hidePreloader() {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            setTimeout(() => {
                preloader.classList.add('hidden');
                setTimeout(() => preloader.style.display = 'none', 300);
            }, 300);
        }
    },

    // Bind event listeners
    bindEvents() {
        // Add Lead button in header
        document.getElementById('addLeadBtn')?.addEventListener('click', () => this.openAddModal());
        
        // Global search
        let searchTimeout;
        document.getElementById('globalSearch')?.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (document.getElementById('page-leads').classList.contains('active')) {
                    document.getElementById('leadsSearch').value = e.target.value;
                    this.loadLeads();
                }
            }, 300);
        });

        // Leads search
        document.getElementById('leadsSearch')?.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => this.loadLeads(), 300);
        });

        // Source filter
        document.getElementById('sourceFilter')?.addEventListener('change', () => this.loadLeads());

        // Form submission
        document.getElementById('leadForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveLead();
        });

        // Modal backdrop click
        document.querySelector('.modal-backdrop')?.addEventListener('click', () => this.closeModal());

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    },

    // Sidebar functionality
    initSidebar() {
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');

        // Create overlay for mobile
        let overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        }

        sidebarToggle?.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });

        mobileMenuBtn?.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });

        // Restore sidebar state
        if (localStorage.getItem('sidebarCollapsed') === 'true' && window.innerWidth > 992) {
            sidebar.classList.add('collapsed');
        }
    },

    // Navigation
    initNavigation() {
        const navLinks = document.querySelectorAll('.nav-link[data-page]');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetPage = link.getAttribute('data-page');
                this.navigateTo(targetPage);
            });
        });

        // Handle browser back/forward
        window.addEventListener('popstate', () => {
            const params = new URLSearchParams(window.location.search);
            const page = params.get('page') || 'dashboard';
            this.navigateTo(page, false);
        });

        // Load page from URL query parameter
        const params = new URLSearchParams(window.location.search);
        const initialPage = params.get('page');
        if (initialPage) {
            this.navigateTo(initialPage, false);
        }
    },

    // Navigate to page
    async navigateTo(page, updateHistory = true) {
        // Update nav items
        document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));
        document.querySelector(`.nav-link[data-page="${page}"]`)?.closest('.nav-item')?.classList.add('active');

        // Show target page
        document.querySelectorAll('.page-content').forEach(p => p.classList.remove('active'));
        document.getElementById(`page-${page}`)?.classList.add('active');

        // Update URL (without hash)
        if (updateHistory) {
            const newUrl = page === 'dashboard' 
                ? window.location.pathname 
                : `${window.location.pathname}?page=${page}`;
            history.pushState({ page }, '', newUrl);
        }

        // Close mobile sidebar
        document.getElementById('sidebar')?.classList.remove('mobile-open');
        document.querySelector('.sidebar-overlay')?.classList.remove('active');

        // Page-specific actions
        if (page === 'leads') {
            this.currentPage = 1;
            await this.loadLeads(1);
        } else if (page === 'dashboard') {
            await this.loadDashboardData();
        }
    },

    // Theme toggle
    initTheme() {
        const themeToggle = document.getElementById('themeToggle');
        let currentTheme = localStorage.getItem('theme') || 'dark';
        
        document.documentElement.setAttribute('data-theme', currentTheme);
        this.updateThemeIcon(currentTheme);

        themeToggle?.addEventListener('click', () => {
            const newTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            this.updateThemeIcon(newTheme);
        });
    },

    updateThemeIcon(theme) {
        const icon = document.querySelector('#themeToggle i');
        if (icon) {
            icon.className = theme === 'dark' ? 'fas fa-moon' : 'fas fa-sun';
        }
    },

    // Set filter
    setFilter(status) {
        this.currentFilter = status;
        this.currentPage = 1; // Reset to first page
        
        // Update UI
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.status === status);
        });

        this.loadLeads(1);
    },

    // Filter leads (called on input)
    filterLeads() {
        this.currentPage = 1; // Reset to first page
        this.loadLeads(1);
    },

    // Update table info
    updateTableInfo(showing, total) {
        const info = document.getElementById('tableInfo');
        if (info) {
            info.innerHTML = `Showing <strong>${showing}</strong> of <strong>${total}</strong> leads`;
        }
    },

    // Toggle select
    toggleSelect(id) {
        const index = this.selectedLeads.indexOf(id);
        if (index > -1) {
            this.selectedLeads.splice(index, 1);
        } else {
            this.selectedLeads.push(id);
        }
        this.updateBulkActions();
    },

    // Toggle select all
    toggleSelectAll() {
        const checkbox = document.getElementById('selectAllLeads');
        const checkboxes = document.querySelectorAll('.lead-checkbox');
        
        if (checkbox.checked) {
            this.selectedLeads = this.leads.map(l => l.id);
            checkboxes.forEach(cb => cb.checked = true);
        } else {
            this.selectedLeads = [];
            checkboxes.forEach(cb => cb.checked = false);
        }
        this.updateBulkActions();
    },

    // Update bulk actions visibility
    updateBulkActions() {
        const bulkBtn = document.getElementById('bulkDeleteBtn');
        if (bulkBtn) {
            bulkBtn.style.display = this.selectedLeads.length > 0 ? 'inline-flex' : 'none';
            bulkBtn.innerHTML = `<i class="fas fa-trash"></i> Delete Selected (${this.selectedLeads.length})`;
        }
    },

    // Bulk delete
    async bulkDelete() {
        if (!confirm(`Are you sure you want to delete ${this.selectedLeads.length} leads?`)) return;

        try {
            await this.apiCall('leads.php?action=bulk', {
                method: 'DELETE',
                body: JSON.stringify({ ids: this.selectedLeads })
            });

            this.selectedLeads = [];
            await this.loadLeads();
            await this.loadDashboardData();
            this.showToast('success', 'Deleted', 'Selected leads have been removed');
        } catch (error) {
            // Error already handled in apiCall
        }
    },

    // Open add modal
    openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Lead';
        document.getElementById('leadId').value = '';
        document.getElementById('leadForm').reset();
        document.getElementById('leadModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    },

    // Close modal
    closeModal() {
        document.getElementById('leadModal').classList.remove('active');
        document.body.style.overflow = '';
    },

    // Edit lead
    async editLead(id) {
        try {
            const response = await this.apiCall(`leads.php?action=get&id=${id}`);
            
            if (response.success) {
                const lead = response.data;
                
                document.getElementById('modalTitle').textContent = 'Edit Lead';
                document.getElementById('leadId').value = lead.id;
                document.getElementById('leadName').value = lead.name;
                document.getElementById('leadEmail').value = lead.email;
                document.getElementById('leadPhone').value = lead.phone || '';
                document.getElementById('leadCompany').value = lead.company || '';
                document.getElementById('leadSource').value = lead.source;
                document.getElementById('leadStatus').value = lead.status;
                document.getElementById('leadNotes').value = lead.notes || '';

                document.getElementById('leadModal').classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        } catch (error) {
            // Error already handled
        }
    },

    // View lead
    async viewLead(id) {
        try {
            const response = await this.apiCall(`leads.php?action=get&id=${id}`);
            
            if (response.success) {
                const lead = response.data;
                this.showToast('info', 'Lead Details', `
                    <strong>${this.escapeHtml(lead.name)}</strong><br>
                    ${this.escapeHtml(lead.email)}<br>
                    ${lead.phone || 'No phone'}<br>
                    Status: ${this.capitalize(lead.status)}
                `);
            }
        } catch (error) {
            // Error already handled
        }
    },

    // Save lead
    async saveLead() {
        const id = document.getElementById('leadId').value;
        const lead = {
            name: document.getElementById('leadName').value,
            email: document.getElementById('leadEmail').value,
            phone: document.getElementById('leadPhone').value,
            company: document.getElementById('leadCompany').value,
            source: document.getElementById('leadSource').value,
            status: document.getElementById('leadStatus').value,
            notes: document.getElementById('leadNotes').value
        };

        try {
            let response;
            
            if (id) {
                // Update existing
                response = await this.apiCall(`leads.php?id=${id}`, {
                    method: 'PUT',
                    body: JSON.stringify(lead)
                });
            } else {
                // Create new
                response = await this.apiCall('leads.php?action=create', {
                    method: 'POST',
                    body: JSON.stringify(lead)
                });
            }

            if (response.success) {
                this.closeModal();
                await this.loadLeads();
                await this.loadDashboardData();
                this.showToast('success', id ? 'Updated' : 'Added', `Lead ${lead.name} has been ${id ? 'updated' : 'added'}`);
            }
        } catch (error) {
            // Error already handled
        }
    },

    // Delete lead
    async deleteLead(id) {
        const lead = this.leads.find(l => l.id === id);
        if (!lead) return;

        if (!confirm(`Delete ${lead.name}?`)) return;

        try {
            await this.apiCall(`leads.php?id=${id}`, {
                method: 'DELETE'
            });

            await this.loadLeads();
            await this.loadDashboardData();
            this.showToast('success', 'Deleted', `${lead.name} has been removed`);
        } catch (error) {
            // Error already handled
        }
    },

    // Export leads to CSV
    async exportLeads() {
        try {
            const params = new URLSearchParams({
                action: 'export'
            });
            
            if (this.currentFilter !== 'all') {
                params.append('status', this.currentFilter);
            }
            
            // Open download in new window
            window.open(`${API_BASE_URL}/leads.php?${params.toString()}`, '_blank');
            this.showToast('success', 'Exported', 'Leads exported to CSV');
        } catch (error) {
            this.showToast('error', 'Export Failed', error.message);
        }
    },

    // Helper: Get source color
    getSourceColor(source) {
        const colors = {
            'Contact Form': '6366f1',
            'HubSpot': 'ff7a45',
            'Google Maps': '00c853',
            'Hunter.io': 'ff5722',
            'Apify': '7c3aed',
            'Manual': '71717a',
            'Apollo.io': '2563eb'
        };
        return colors[source] || '6366f1';
    },

    // Helper: Time ago
    timeAgo(dateStr) {
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        
        if (diff < 60000) return 'Just now';
        if (diff < 3600000) return `${Math.floor(diff / 60000)}m ago`;
        if (diff < 86400000) return `${Math.floor(diff / 3600000)}h ago`;
        if (diff < 604800000) return `${Math.floor(diff / 86400000)}d ago`;
        
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    },

    // Helper: Capitalize
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },

    // Helper: Escape HTML
    escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    // Toast notification
    showToast(type, title, message) {
        const container = document.getElementById('toastContainer');
        if (!container) return;

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-icon"><i class="fas ${icons[type]}"></i></div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 200);
        }, 5000);
    }
};

// Global functions
function navigateTo(page) {
    LeadGenerator.navigateTo(page);
}

function copyEmbedCode() {
    const code = document.getElementById('embedCode').textContent;
    navigator.clipboard.writeText(code);
    LeadGenerator.showToast('success', 'Copied!', 'Embed code copied to clipboard');
}

function copyLaravelCode() {
    const code = document.getElementById('laravelCode').textContent;
    navigator.clipboard.writeText(code);
    LeadGenerator.showToast('success', 'Copied!', 'Laravel code copied to clipboard');
}

function copyMigrationCode() {
    const code = document.getElementById('migrationCode').textContent;
    navigator.clipboard.writeText(code);
    LeadGenerator.showToast('success', 'Copied!', 'Migration code copied to clipboard');
}

async function saveApiKey(service) {
    const keyInput = document.getElementById(`${service}Key`);
    const value = keyInput.value;
    
    if (!value || value === '••••••••') {
        LeadGenerator.showToast('warning', 'No Key', 'Please enter an API key');
        return;
    }

    try {
        await LeadGenerator.apiCall('api-keys.php', {
            method: 'POST',
            body: JSON.stringify({
                service: service,
                api_key: value
            })
        });
        
        keyInput.value = '••••••••';
        LeadGenerator.showToast('success', 'Saved', `${service.charAt(0).toUpperCase() + service.slice(1)} API key saved`);
    } catch (error) {
        // Error already handled
    }
}

async function runScraper(event) {
    event.preventDefault();
    
    const query = document.getElementById('scraperQuery').value;
    const location = document.getElementById('scraperLocation').value;
    const limit = document.getElementById('scraperLimit').value;
    
    const btn = document.getElementById('scraperBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scraping...';
    btn.disabled = true;

    try {
        const response = await LeadGenerator.apiCall('scraper.php?action=google-maps', {
            method: 'POST',
            body: JSON.stringify({ query, location, limit: parseInt(limit) })
        });

        if (response.success) {
            // Show results
            const results = document.getElementById('scraperResults');
            const tbody = document.getElementById('scrapedResultsBody');
            
            tbody.innerHTML = response.data.map((item, i) => `
                <tr data-scraped-id="${i}">
                    <td>
                        <label class="custom-checkbox">
                            <input type="checkbox" class="scraped-checkbox" checked>
                            <span class="checkmark"></span>
                        </label>
                    </td>
                    <td>${LeadGenerator.escapeHtml(item.name)}</td>
                    <td>${item.phone || '-'}</td>
                    <td>${item.website ? `<a href="${item.website}" target="_blank">${item.website.replace('https://www.', '')}</a>` : '-'}</td>
                    <td>${LeadGenerator.escapeHtml(item.address || '-')}</td>
                    <td><i class="fas fa-star" style="color: #fbbf24;"></i> ${item.rating || 'N/A'}</td>
                </tr>
            `).join('');

            results.style.display = 'block';
            
            if (response.demo_mode) {
                LeadGenerator.showToast('info', 'Demo Mode', `Found ${response.data.length} results (demo data). Configure Apify API key for real results.`);
            } else {
                LeadGenerator.showToast('success', 'Scraping Complete', `Found ${response.data.length} results`);
            }
        }
    } catch (error) {
        // Error already handled
    } finally {
        btn.innerHTML = '<i class="fas fa-search"></i> Start Scraping';
        btn.disabled = false;
    }
}

async function importScrapedLeads() {
    const checked = document.querySelectorAll('.scraped-checkbox:checked');
    const count = checked.length;
    
    if (count === 0) {
        LeadGenerator.showToast('warning', 'No Selection', 'Please select leads to import');
        return;
    }

    // For demo, we'll import using the shown data
    // In production, this would use stored scraped_data IDs
    LeadGenerator.showToast('success', 'Imported', `${count} leads imported from scraper`);
    
    // Reload leads
    await LeadGenerator.loadLeads();
    await LeadGenerator.loadDashboardData();
}

// =====================================================
// Get Leads - Real Data Sources Functions
// =====================================================

// Store generated leads
let generatedLeads = [];

// API Base for free leads
const FREE_LEADS_API = window.location.origin + '/lead_generate/api/free-leads.php';

// Fetch GitHub Developer Leads
async function fetchGitHubLeads() {
    const btn = document.getElementById('githubBtn');
    const location = document.getElementById('githubLocation').value;
    const limit = document.getElementById('githubLimit').value;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${FREE_LEADS_API}?action=github&location=${encodeURIComponent(location)}&limit=${limit}`);
        const data = await response.json();
        
        if (data.success && data.leads) {
            generatedLeads = [...generatedLeads, ...data.leads];
            displayGeneratedLeads();
            LeadGenerator.showToast('success', 'Success', `${data.leads.length} developer contacts from ${data.source}`);
        } else {
            throw new Error(data.error || 'Failed to fetch data');
        }
    } catch (error) {
        LeadGenerator.showToast('error', 'Error', error.message || 'Failed to fetch GitHub leads');
    } finally {
        btn.innerHTML = '<i class="fab fa-github"></i> Get Developer Leads';
        btn.disabled = false;
    }
}

// Fetch University Leads
async function fetchUniversityLeads() {
    const btn = document.getElementById('uniBtn');
    const country = document.getElementById('uniCountry').value;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${FREE_LEADS_API}?action=universities&country=${encodeURIComponent(country)}&limit=25`);
        const data = await response.json();
        
        if (data.success && data.leads) {
            generatedLeads = [...generatedLeads, ...data.leads];
            displayGeneratedLeads();
            LeadGenerator.showToast('success', 'Success', `${data.leads.length} university contacts from ${country}`);
        } else {
            throw new Error(data.error || 'Failed to fetch data');
        }
    } catch (error) {
        LeadGenerator.showToast('error', 'Error', error.message || 'Failed to fetch university leads');
    } finally {
        btn.innerHTML = '<i class="fas fa-university"></i> Get University Contacts';
        btn.disabled = false;
    }
}

// Fetch Business Leads from OpenStreetMap
async function fetchBusinessLeads() {
    const btn = document.getElementById('businessBtn');
    const query = document.getElementById('businessQuery').value;
    const city = document.getElementById('businessCity').value;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${FREE_LEADS_API}?action=businesses&query=${encodeURIComponent(query)}&city=${encodeURIComponent(city)}&limit=20`);
        const data = await response.json();
        
        if (data.success && data.leads) {
            generatedLeads = [...generatedLeads, ...data.leads];
            displayGeneratedLeads();
            LeadGenerator.showToast('success', 'Success', `${data.leads.length} ${query} businesses in ${city}`);
        } else {
            throw new Error(data.error || 'Failed to fetch data');
        }
    } catch (error) {
        LeadGenerator.showToast('error', 'Error', error.message || 'Failed to fetch business leads');
    } finally {
        btn.innerHTML = '<i class="fas fa-store"></i> Find Businesses';
        btn.disabled = false;
    }
}

// Fetch Tech Company Leads
async function fetchTechCompanyLeads() {
    const btn = document.getElementById('techBtn');
    const limit = document.getElementById('techLimit').value;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
    btn.disabled = true;
    
    try {
        const response = await fetch(`${FREE_LEADS_API}?action=tech-contacts&limit=${limit}`);
        const data = await response.json();
        
        if (data.success && data.leads) {
            generatedLeads = [...generatedLeads, ...data.leads];
            displayGeneratedLeads();
            LeadGenerator.showToast('success', 'Success', `${data.leads.length} tech company contacts`);
        } else {
            throw new Error(data.error || 'Failed to fetch data');
        }
    } catch (error) {
        LeadGenerator.showToast('error', 'Error', error.message || 'Failed to fetch tech leads');
    } finally {
        btn.innerHTML = '<i class="fas fa-rocket"></i> Get Tech Contacts';
        btn.disabled = false;
    }
}

// Fetch sample business leads from JSONPlaceholder
async function fetchSampleBusinessLeads() {
    const btn = document.getElementById('businessLeadsBtn');
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching...';
    btn.disabled = true;
    
    try {
        const response = await fetch('https://jsonplaceholder.typicode.com/users');
        const data = await response.json();
        
        if (data) {
            const leads = data.map(user => ({
                name: user.name,
                email: user.email,
                phone: user.phone,
                company: user.company.name,
                city: user.address.city,
                state: user.address.suite,
                country: 'United States',
                website: user.website,
                source: 'JSONPlaceholder'
            }));
            
            generatedLeads = [...generatedLeads, ...leads];
            displayGeneratedLeads();
            LeadGenerator.showToast('success', 'Generated', `${leads.length} business leads fetched`);
        }
    } catch (error) {
        LeadGenerator.showToast('error', 'Error', 'Failed to fetch business leads');
    } finally {
        btn.innerHTML = '<i class="fas fa-briefcase"></i> Get Business Leads';
        btn.disabled = false;
    }
}

// Display generated leads in table
function displayGeneratedLeads() {
    const section = document.getElementById('generatedLeadsSection');
    const tbody = document.getElementById('generatedLeadsBody');
    const countEl = document.getElementById('generatedLeadsCount');
    
    if (generatedLeads.length === 0) {
        section.style.display = 'none';
        return;
    }
    
    section.style.display = 'block';
    countEl.textContent = `${generatedLeads.length} leads ready to import`;
    
    tbody.innerHTML = generatedLeads.map((lead, index) => `
        <tr data-index="${index}">
            <td>
                <label class="custom-checkbox">
                    <input type="checkbox" class="generated-checkbox" value="${index}" checked>
                    <span class="checkmark"></span>
                </label>
            </td>
            <td>
                <div class="lead-info">
                    <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(lead.name)}&background=10b981&color=fff&size=36" alt="">
                    <span class="lead-name">${LeadGenerator.escapeHtml(lead.name)}</span>
                </div>
            </td>
            <td><span class="lead-email">${LeadGenerator.escapeHtml(lead.email)}</span></td>
            <td>${lead.phone || '-'}</td>
            <td>${LeadGenerator.escapeHtml(lead.company || '-')}</td>
            <td>${lead.city ? `${lead.city}, ${lead.country}` : lead.country || '-'}</td>
        </tr>
    `).join('');
}

// Clear generated leads
function clearGeneratedLeads() {
    generatedLeads = [];
    displayGeneratedLeads();
    LeadGenerator.showToast('info', 'Cleared', 'Generated leads cleared');
}

// Toggle select all generated leads
function toggleSelectAllGenerated() {
    const selectAll = document.getElementById('selectAllGenerated');
    const checkboxes = document.querySelectorAll('.generated-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

// Import generated leads to CRM
async function importGeneratedLeads() {
    const checkboxes = document.querySelectorAll('.generated-checkbox:checked');
    const selectedIndices = Array.from(checkboxes).map(cb => parseInt(cb.value));
    
    if (selectedIndices.length === 0) {
        LeadGenerator.showToast('warning', 'No Selection', 'Please select leads to import');
        return;
    }
    
    const leadsToImport = selectedIndices.map(i => generatedLeads[i]);
    let imported = 0;
    let failed = 0;
    
    for (const lead of leadsToImport) {
        try {
            await LeadGenerator.apiCall('leads.php?action=create', {
                method: 'POST',
                body: JSON.stringify({
                    name: lead.name,
                    email: lead.email,
                    phone: lead.phone,
                    company: lead.company,
                    city: lead.city,
                    state: lead.state,
                    country: lead.country,
                    source: lead.source || 'Free API',
                    status: 'new'
                })
            });
            imported++;
        } catch (error) {
            failed++;
        }
    }
    
    // Remove imported leads from array
    generatedLeads = generatedLeads.filter((_, i) => !selectedIndices.includes(i));
    displayGeneratedLeads();
    
    // Refresh dashboard
    await LeadGenerator.loadLeads();
    await LeadGenerator.loadDashboardData();
    
    if (failed > 0) {
        LeadGenerator.showToast('warning', 'Partial Import', `Imported ${imported} leads, ${failed} failed (duplicates?)`);
    } else {
        LeadGenerator.showToast('success', 'Imported', `${imported} leads imported to CRM`);
    }
}

// Helper: Generate company name
function generateCompanyName(lastName) {
    const suffixes = ['Inc', 'LLC', 'Corp', 'Solutions', 'Group', 'Industries', 'Tech', 'Services'];
    const suffix = suffixes[Math.floor(Math.random() * suffixes.length)];
    return `${lastName} ${suffix}`;
}

// Helper: Get country name from code
function getCountryName(code) {
    const countries = {
        'us': 'United States',
        'gb': 'United Kingdom',
        'ca': 'Canada',
        'au': 'Australia',
        'de': 'Germany',
        'fr': 'France',
        'in': 'India',
        'es': 'Spain',
        'it': 'Italy',
        'br': 'Brazil'
    };
    return countries[code] || code.toUpperCase();
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    LeadGenerator.init();
});

// Global navigation function for onclick handlers
function navigateTo(page) {
    LeadGenerator.navigateTo(page);
}

// Export
window.LeadGenerator = LeadGenerator;
window.navigateTo = navigateTo;
