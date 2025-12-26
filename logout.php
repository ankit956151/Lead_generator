<?php
/**
 * LeadGen CMS - Logout Handler
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/models/User.php';

// Log the logout
$userId = User::getCurrentUserId();
if ($userId) {
    logActivity('user_logout', "User logged out", $userId);
}

// End session
User::endSession();

// Redirect to login
header('Location: login.php');
exit;
