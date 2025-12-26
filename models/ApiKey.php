<?php
/**
 * LeadGen CMS - API Key Model
 * 
 * Handles API key storage and retrieval
 */

require_once __DIR__ . '/../config/config.php';

class ApiKey {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get all API keys (masked)
     */
    public function getAll() {
        $keys = $this->db->fetchAll("SELECT id, service, is_active, last_used_at, usage_count, created_at FROM api_keys ORDER BY service");
        
        foreach ($keys as &$key) {
            $key['has_key'] = true;
        }
        
        return $keys;
    }
    
    /**
     * Get API key by service name (decrypted)
     */
    public function getByService($service) {
        $key = $this->db->fetch("SELECT * FROM api_keys WHERE service = ?", [$service]);
        
        if ($key) {
            $key['api_key'] = $this->decrypt($key['api_key']);
            if ($key['api_secret']) {
                $key['api_secret'] = $this->decrypt($key['api_secret']);
            }
        }
        
        return $key;
    }
    
    /**
     * Save or update API key
     */
    public function save($service, $apiKey, $apiSecret = null, $userId = null) {
        $encryptedKey = $this->encrypt($apiKey);
        $encryptedSecret = $apiSecret ? $this->encrypt($apiSecret) : null;
        
        $existing = $this->db->fetch("SELECT id FROM api_keys WHERE service = ?", [$service]);
        
        if ($existing) {
            $this->db->query(
                "UPDATE api_keys SET api_key = ?, api_secret = ?, is_active = 1, updated_at = NOW() WHERE service = ?",
                [$encryptedKey, $encryptedSecret, $service]
            );
            $id = $existing['id'];
        } else {
            $this->db->query(
                "INSERT INTO api_keys (service, api_key, api_secret, created_by) VALUES (?, ?, ?, ?)",
                [$service, $encryptedKey, $encryptedSecret, $userId]
            );
            $id = $this->db->lastInsertId();
        }
        
        logActivity('api_key_saved', "API key for $service saved", $userId);
        
        return $id;
    }
    
    /**
     * Delete API key
     */
    public function delete($service, $userId = null) {
        $this->db->query("DELETE FROM api_keys WHERE service = ?", [$service]);
        logActivity('api_key_deleted', "API key for $service deleted", $userId);
        return true;
    }
    
    /**
     * Toggle API key status
     */
    public function toggle($service) {
        $this->db->query(
            "UPDATE api_keys SET is_active = NOT is_active, updated_at = NOW() WHERE service = ?",
            [$service]
        );
        return $this->db->fetch("SELECT is_active FROM api_keys WHERE service = ?", [$service]);
    }
    
    /**
     * Record API usage
     */
    public function recordUsage($service) {
        $this->db->query(
            "UPDATE api_keys SET usage_count = usage_count + 1, last_used_at = NOW() WHERE service = ?",
            [$service]
        );
    }
    
    /**
     * Simple encryption (in production, use more robust encryption)
     */
    private function encrypt($data) {
        $key = $this->getEncryptionKey();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    private function decrypt($data) {
        $key = $this->getEncryptionKey();
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }
    
    /**
     * Get encryption key (should be set in env in production)
     */
    private function getEncryptionKey() {
        // In production, this should come from environment variable
        return hash('sha256', APP_NAME . '_secret_key_2024', true);
    }
    
    /**
     * Check if service has valid API key
     */
    public function isConfigured($service) {
        $key = $this->db->fetch(
            "SELECT id FROM api_keys WHERE service = ? AND is_active = 1",
            [$service]
        );
        return $key !== false;
    }
}
