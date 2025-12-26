<?php
/**
 * LeadGen CMS - Authentication API
 * 
 * Handles login, logout, and session management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/User.php';

header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($action) {
        case 'login':
            if ($method !== 'POST') {
                jsonResponse(['error' => 'Method not allowed'], 405);
            }
            handleLogin();
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'check':
            handleCheck();
            break;
            
        case 'user':
            handleGetUser();
            break;
            
        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}

/**
 * Handle login request
 */
function handleLogin() {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['email']) || empty($data['password'])) {
        jsonResponse(['error' => 'Email and password are required'], 400);
    }
    
    $user = new User();
    $result = $user->authenticate($data['email'], $data['password']);
    
    if (!$result['success']) {
        jsonResponse(['error' => $result['error']], 401);
    }
    
    // Start session
    User::startSession($result['user']);
    
    jsonResponse([
        'success' => true,
        'message' => 'Login successful',
        'user' => $result['user']
    ]);
}

/**
 * Handle logout request
 */
function handleLogout() {
    $userId = User::getCurrentUserId();
    
    if ($userId) {
        logActivity('user_logout', "User logged out", $userId);
    }
    
    User::endSession();
    
    jsonResponse([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

/**
 * Check if user is authenticated
 */
function handleCheck() {
    $isLoggedIn = User::isLoggedIn();
    $currentUser = User::getCurrentUser();
    
    jsonResponse([
        'success' => true,
        'authenticated' => $isLoggedIn,
        'user' => $currentUser
    ]);
}

/**
 * Get current user details
 */
function handleGetUser() {
    if (!User::isLoggedIn()) {
        jsonResponse(['error' => 'Not authenticated'], 401);
    }
    
    $userId = User::getCurrentUserId();
    $user = new User();
    $userData = $user->getById($userId);
    
    if (!$userData) {
        User::endSession();
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'user' => $userData
    ]);
}
