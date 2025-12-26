<?php
/**
 * LeadGen CMS - User Model
 * 
 * Handles user authentication and management
 */

require_once __DIR__ . '/../config/config.php';

class User {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Authenticate user with email and password
     */
    public function authenticate($email, $password) {
        $user = $this->getByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }
        
        if (!$user['is_active']) {
            return ['success' => false, 'error' => 'Account is deactivated'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Log activity
        logActivity('user_login', "User logged in: {$user['email']}", $user['id']);
        
        // Remove password from response
        unset($user['password']);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        return $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE id = ?",
            [$id]
        );
        
        if ($user) {
            unset($user['password']);
        }
        
        return $user;
    }
    
    /**
     * Update last login timestamp
     */
    public function updateLastLogin($id) {
        $this->db->query(
            "UPDATE users SET last_login_at = NOW() WHERE id = ?",
            [$id]
        );
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        // Validate required fields
        if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new Exception('Name, email and password are required');
        }
        
        // Check if email exists
        if ($this->getByEmail($data['email'])) {
            throw new Exception('Email already exists');
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (name, email, password, role, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, 1, NOW(), NOW())";
        
        $this->db->query($sql, [
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role'] ?? 'user'
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Log activity
        logActivity('user_created', "New user created: {$data['email']}");
        
        return $userId;
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'email', 'role', 'avatar', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        // Handle password update
        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        return $this->getById($id);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $user = $this->getById($id);
        if (!$user) {
            return false;
        }
        
        $this->db->query("DELETE FROM users WHERE id = ?", [$id]);
        
        // Log activity
        logActivity('user_deleted', "User deleted: {$user['email']}");
        
        return true;
    }
    
    /**
     * Get all users
     */
    public function getAll() {
        return $this->db->fetchAll(
            "SELECT id, name, email, role, avatar, is_active, last_login_at, created_at 
             FROM users ORDER BY created_at DESC"
        );
    }
    
    /**
     * Start session for user
     */
    public static function startSession($user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Regenerate session ID for security
        session_regenerate_id(true);
    }
    
    /**
     * End user session (logout)
     */
    public static function endSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear session data
        $_SESSION = [];
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Get current logged in user ID
     */
    public static function getCurrentUserId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current logged in user data
     */
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Check if current user has role
     */
    public static function hasRole($role) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_role'])) {
            return false;
        }
        
        $roleHierarchy = ['user' => 1, 'manager' => 2, 'admin' => 3];
        $userLevel = $roleHierarchy[$_SESSION['user_role']] ?? 0;
        $requiredLevel = $roleHierarchy[$role] ?? 99;
        
        return $userLevel >= $requiredLevel;
    }
}
