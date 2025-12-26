<?php
/**
 * LeadGen CMS - Main Configuration File
 * 
 * Contains application-wide settings and environment configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/database.php';

// Timezone
date_default_timezone_set('UTC');

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security Headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// CORS Headers (for API)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Lead Status Constants
define('LEAD_STATUS_NEW', 'new');
define('LEAD_STATUS_CONTACTED', 'contacted');
define('LEAD_STATUS_QUALIFIED', 'qualified');
define('LEAD_STATUS_CONVERTED', 'converted');
define('LEAD_STATUS_LOST', 'lost');

// Lead Sources
define('LEAD_SOURCES', [
    'Contact Form',
    'HubSpot',
    'Google Maps',
    'Hunter.io',
    'Apify',
    'Apollo.io',
    'Manual',
    'Website',
    'Referral',
    'Social Media'
]);

// API Services Configuration
define('HUBSPOT_API_URL', 'https://api.hubapi.com/');
define('HUNTER_API_URL', 'https://api.hunter.io/v2/');
define('APIFY_API_URL', 'https://api.apify.com/v2/');
define('APOLLO_API_URL', 'https://api.apollo.io/v1/');

// External APIs for Region Data
define('RESTCOUNTRIES_API_URL', 'https://restcountries.com/v3.1/');
define('IP_API_URL', 'http://ip-api.com/json/');

// File Upload Settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'csv', 'xlsx']);

// Pagination
define('ITEMS_PER_PAGE', 20);

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize input
 */
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * JSON Response Helper
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Redirect Helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if request is AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get client IP address
 */
function getClientIP() {
    $ip = $_SERVER['REMOTE_ADDR'];
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $ip;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

/**
 * Time ago helper
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Log activity
 */
function logActivity($action, $details = '', $userId = null) {
    try {
        db()->query(
            "INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
             VALUES (?, ?, ?, ?, NOW())",
            [$userId, $action, $details, getClientIP()]
        );
    } catch (Exception $e) {
        // Silently fail - don't break app if logging fails
    }
}
