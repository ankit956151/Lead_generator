-- =====================================================
-- LeadGen CMS - Complete Database Setup for XAMPP
-- Version: 2.0.0
-- =====================================================
-- Instructions:
-- 1. Open phpMyAdmin in XAMPP
-- 2. Go to SQL tab
-- 3. Copy and paste this entire file
-- 4. Click "Go" to execute
-- 
-- Default Admin Login:
-- Email: admin@leadgen.com
-- Password: admin123
-- =====================================================

-- Drop existing database and recreate (CAUTION: This will delete all data!)
DROP DATABASE IF EXISTS leadgen_cms;
CREATE DATABASE leadgen_cms 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE leadgen_cms;

-- =====================================================
-- 1. USERS TABLE
-- =====================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL COMMENT 'Hashed password',
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    last_login_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='User accounts and authentication';

-- =====================================================
-- 2. LEAD SOURCES TABLE
-- =====================================================
CREATE TABLE lead_sources (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    type ENUM('inbound', 'outbound') NOT NULL DEFAULT 'inbound',
    icon VARCHAR(50) DEFAULT 'fas fa-plug',
    color VARCHAR(20) DEFAULT '#6366f1',
    is_active TINYINT(1) DEFAULT 1,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lead source configurations';

-- =====================================================
-- 3. LEADS TABLE
-- =====================================================
CREATE TABLE leads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(191) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    company VARCHAR(255) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    state VARCHAR(100) DEFAULT NULL,
    country VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    source_id INT UNSIGNED DEFAULT NULL,
    source VARCHAR(100) DEFAULT 'Contact Form',
    status ENUM('new', 'contacted', 'qualified', 'converted', 'lost') DEFAULT 'new',
    score INT UNSIGNED DEFAULT 0 COMMENT 'Lead quality score 0-100',
    is_verified TINYINT(1) DEFAULT 0,
    tags JSON DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    custom_fields JSON DEFAULT NULL,
    assigned_to INT UNSIGNED DEFAULT NULL,
    created_by INT UNSIGNED DEFAULT NULL,
    last_contacted_at TIMESTAMP NULL DEFAULT NULL,
    converted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_source (source),
    INDEX idx_status (status),
    INDEX idx_score (score),
    INDEX idx_created_at (created_at),
    INDEX idx_assigned_to (assigned_to),
    FULLTEXT idx_search (name, email, company),
    
    CONSTRAINT fk_leads_source FOREIGN KEY (source_id) 
        REFERENCES lead_sources(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_leads_assigned FOREIGN KEY (assigned_to) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_leads_created_by FOREIGN KEY (created_by) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Main leads database';

-- =====================================================
-- 4. API KEYS TABLE
-- =====================================================
CREATE TABLE api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service VARCHAR(50) NOT NULL COMMENT 'Service name (e.g., HubSpot, Hunter.io)',
    api_key TEXT NOT NULL,
    api_secret TEXT DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_used_at TIMESTAMP NULL DEFAULT NULL,
    usage_count INT UNSIGNED DEFAULT 0,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_service (service),
    INDEX idx_is_active (is_active),
    
    CONSTRAINT fk_api_keys_user FOREIGN KEY (created_by) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API keys for external services';

-- =====================================================
-- 5. SCRAPED DATA TABLE
-- =====================================================
CREATE TABLE scraped_data (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    scraper_type VARCHAR(50) NOT NULL COMMENT 'e.g., google_maps, hunter, apollo',
    search_query VARCHAR(255) DEFAULT NULL,
    location VARCHAR(255) DEFAULT NULL,
    business_name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    email VARCHAR(191) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    rating DECIMAL(3,2) DEFAULT NULL,
    reviews_count INT UNSIGNED DEFAULT 0,
    category VARCHAR(100) DEFAULT NULL,
    raw_data JSON DEFAULT NULL,
    is_imported TINYINT(1) DEFAULT 0,
    imported_lead_id INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_scraper_type (scraper_type),
    INDEX idx_is_imported (is_imported),
    INDEX idx_created_at (created_at),
    INDEX idx_business_name (business_name),
    
    CONSTRAINT fk_scraped_lead FOREIGN KEY (imported_lead_id) 
        REFERENCES leads(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Scraped data from external sources';

-- =====================================================
-- 6. ACTIVITY LOGS TABLE
-- =====================================================
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED DEFAULT NULL,
    action VARCHAR(100) NOT NULL COMMENT 'Action performed (e.g., create, update, delete)',
    details TEXT DEFAULT NULL,
    model_type VARCHAR(100) DEFAULT NULL COMMENT 'e.g., Lead, User, Setting',
    model_id INT UNSIGNED DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at),
    INDEX idx_model (model_type, model_id),
    
    CONSTRAINT fk_activity_user FOREIGN KEY (user_id) 
        REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System activity and audit logs';

-- =====================================================
-- 7. SETTINGS TABLE
-- =====================================================
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT DEFAULT NULL,
    is_public TINYINT(1) DEFAULT 0 COMMENT 'Whether setting is publicly accessible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_setting_key (setting_key),
    INDEX idx_is_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Application settings and configurations';

-- =====================================================
-- INSERT DEFAULT DATA
-- =====================================================

-- Default Admin User
-- Email: admin@leadgen.com
-- Password: admin123 (Change this immediately after first login!)
INSERT INTO users (name, email, password, role, is_active, email_verified_at) VALUES
('System Admin', 'admin@leadgen.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW());

-- Default Lead Sources
INSERT INTO lead_sources (name, type, icon, color, description) VALUES
('Contact Form', 'inbound', 'fas fa-file-lines', '#6366f1', 'Website contact form submissions'),
('HubSpot', 'inbound', 'fab fa-hubspot', '#ff7a45', 'Synced from HubSpot CRM'),
('OptinMonster', 'inbound', 'fab fa-wpforms', '#ffc107', 'Popup and slide-in form leads'),
('Google Maps', 'outbound', 'fas fa-map-marker-alt', '#00c853', 'Scraped from Google Maps business listings'),
('Hunter.io', 'outbound', 'fas fa-envelope-open-text', '#ff5722', 'Email finder and verification service'),
('Apify', 'outbound', 'fas fa-spider', '#7c3aed', 'Web scraping and automation platform'),
('Apollo.io', 'outbound', 'fas fa-rocket', '#2563eb', 'B2B contact and company database'),
('Manual Entry', 'inbound', 'fas fa-user-plus', '#71717a', 'Manually added leads'),
('Website Chat', 'inbound', 'fas fa-comments', '#06b6d4', 'Live chat conversations'),
('Referral', 'inbound', 'fas fa-handshake', '#10b981', 'Customer referrals and recommendations'),
('Social Media', 'inbound', 'fas fa-share-alt', '#ec4899', 'Facebook, LinkedIn, Twitter leads'),
('Cold Email', 'outbound', 'fas fa-envelope', '#f59e0b', 'Outbound email campaigns'),
('Random User API', 'inbound', 'fas fa-users', '#10b981', 'Generated from Random User API'),
('Country API', 'inbound', 'fas fa-globe', '#6366f1', 'Country-based lead generation'),
('JSONPlaceholder', 'inbound', 'fas fa-building', '#f59e0b', 'Sample business leads from JSONPlaceholder'),
('Free API', 'inbound', 'fas fa-download', '#10b981', 'Leads from free public APIs');

-- Default System Settings
INSERT INTO settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'LeadGen CMS', 'string', 'Application name displayed in UI', 1),
('site_description', 'Powerful Lead Generation & Management Platform', 'string', 'Application description', 1),
('site_logo', '/assets/images/logo.png', 'string', 'Path to site logo', 1),
('leads_per_page', '20', 'integer', 'Number of leads displayed per page', 0),
('enable_email_notifications', 'true', 'boolean', 'Send email notifications for new leads', 0),
('enable_auto_assignment', 'false', 'boolean', 'Automatically assign leads to users', 0),
('scraper_rate_limit', '100', 'integer', 'Maximum scraping requests per hour', 0),
('default_lead_status', 'new', 'string', 'Default status assigned to new leads', 0),
('enable_lead_scoring', 'true', 'boolean', 'Enable automatic lead quality scoring', 0),
('max_lead_score', '100', 'integer', 'Maximum possible lead score', 0),
('enable_duplicate_detection', 'true', 'boolean', 'Check for duplicate leads by email', 0),
('timezone', 'UTC', 'string', 'Default system timezone', 0),
('date_format', 'Y-m-d', 'string', 'Date display format', 0),
('time_format', 'H:i:s', 'string', 'Time display format', 0),
('currency', 'USD', 'string', 'Default currency for pricing', 1);

-- =====================================================
-- CREATE VIEWS FOR ANALYTICS
-- =====================================================

-- Lead Statistics View
CREATE OR REPLACE VIEW vw_lead_statistics AS
SELECT 
    COUNT(*) as total_leads,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_leads,
    SUM(CASE WHEN status = 'contacted' THEN 1 ELSE 0 END) as contacted_leads,
    SUM(CASE WHEN status = 'qualified' THEN 1 ELSE 0 END) as qualified_leads,
    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_leads,
    SUM(CASE WHEN status = 'lost' THEN 1 ELSE 0 END) as lost_leads,
    SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_leads,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_leads,
    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_leads,
    SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as month_leads,
    ROUND(AVG(score), 2) as avg_lead_score
FROM leads;

-- Source Performance View
CREATE OR REPLACE VIEW vw_source_performance AS
SELECT 
    ls.name as source_name,
    ls.type as source_type,
    ls.color,
    COUNT(l.id) as lead_count,
    SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as converted_count,
    SUM(CASE WHEN l.status = 'qualified' THEN 1 ELSE 0 END) as qualified_count,
    ROUND(AVG(l.score), 2) as avg_score,
    ROUND(
        CASE 
            WHEN COUNT(l.id) > 0 
            THEN (SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(l.id))
            ELSE 0 
        END, 2
    ) as conversion_rate
FROM lead_sources ls
LEFT JOIN leads l ON ls.id = l.source_id
WHERE ls.is_active = 1
GROUP BY ls.id, ls.name, ls.type, ls.color
ORDER BY lead_count DESC;

-- Daily Lead Trends (Last 30 Days)
CREATE OR REPLACE VIEW vw_daily_lead_trends AS
SELECT 
    DATE(created_at) as lead_date,
    COUNT(*) as lead_count,
    SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_count,
    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted_count,
    ROUND(AVG(score), 2) as avg_score
FROM leads
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(created_at)
ORDER BY lead_date DESC;

-- User Performance View
CREATE OR REPLACE VIEW vw_user_performance AS
SELECT 
    u.id as user_id,
    u.name as user_name,
    u.email,
    u.role,
    COUNT(l.id) as assigned_leads,
    SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as converted_leads,
    SUM(CASE WHEN l.status = 'contacted' THEN 1 ELSE 0 END) as contacted_leads,
    ROUND(
        CASE 
            WHEN COUNT(l.id) > 0 
            THEN (SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) * 100.0 / COUNT(l.id))
            ELSE 0 
        END, 2
    ) as conversion_rate
FROM users u
LEFT JOIN leads l ON u.id = l.assigned_to
WHERE u.is_active = 1
GROUP BY u.id, u.name, u.email, u.role
ORDER BY assigned_leads DESC;

-- Recent Activity View (Last 100 entries)
CREATE OR REPLACE VIEW vw_recent_activity AS
SELECT 
    a.id,
    a.action,
    a.details,
    a.model_type,
    a.model_id,
    a.created_at,
    u.name as user_name,
    u.email as user_email
FROM activity_logs a
LEFT JOIN users u ON a.user_id = u.id
ORDER BY a.created_at DESC
LIMIT 100;

-- =====================================================
-- CREATE SAMPLE DATA (Optional - for testing)
-- =====================================================

-- Sample Leads (Uncomment to insert test data)
/*
INSERT INTO leads (name, email, phone, company, website, source, status, score, assigned_to, created_by) VALUES
('John Smith', 'john.smith@example.com', '+1-555-0101', 'Tech Corp', 'https://techcorp.com', 'Contact Form', 'new', 75, 1, 1),
('Sarah Johnson', 'sarah.j@business.com', '+1-555-0102', 'Business Inc', 'https://businessinc.com', 'Google Maps', 'contacted', 85, 1, 1),
('Michael Brown', 'mbrown@startup.io', '+1-555-0103', 'Startup Labs', 'https://startuplabs.io', 'Hunter.io', 'qualified', 90, 1, 1),
('Emily Davis', 'emily.davis@company.com', '+1-555-0104', 'Digital Solutions', NULL, 'Social Media', 'converted', 95, 1, 1),
('David Wilson', 'david.w@enterprise.org', '+1-555-0105', 'Enterprise Co', 'https://enterprise.org', 'Apollo.io', 'new', 70, 1, 1);
*/

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================

SELECT 'Database setup completed successfully!' as Status,
       'Database: leadgen_cms' as Database_Name,
       'Default Admin: admin@leadgen.com' as Admin_Email,
       'Default Password: admin123' as Admin_Password,
       'IMPORTANT: Change the default password immediately!' as Security_Note;

-- Show table count
SELECT 
    COUNT(*) as Total_Tables,
    (SELECT COUNT(*) FROM information_schema.VIEWS WHERE TABLE_SCHEMA = 'leadgen_cms') as Total_Views
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'leadgen_cms' AND TABLE_TYPE = 'BASE TABLE';
