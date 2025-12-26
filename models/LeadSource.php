<?php
/**
 * LeadGen CMS - Lead Source Model
 * 
 * Handles lead source database operations
 */

require_once __DIR__ . '/../config/config.php';

class LeadSource {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get all lead sources
     */
    public function getAll($activeOnly = false) {
        $sql = "SELECT * FROM lead_sources";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get sources by type (inbound/outbound)
     */
    public function getByType($type) {
        return $this->db->fetchAll(
            "SELECT * FROM lead_sources WHERE type = ? AND is_active = 1 ORDER BY name",
            [$type]
        );
    }
    
    /**
     * Get single source by ID
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM lead_sources WHERE id = ?", [$id]);
    }
    
    /**
     * Get source by name
     */
    public function getByName($name) {
        return $this->db->fetch("SELECT * FROM lead_sources WHERE name = ?", [$name]);
    }
    
    /**
     * Create new source
     */
    public function create($data) {
        $sql = "INSERT INTO lead_sources (name, type, icon, color, description, is_active) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['name'],
            $data['type'] ?? 'inbound',
            $data['icon'] ?? 'fas fa-plug',
            $data['color'] ?? '#6366f1',
            $data['description'] ?? null,
            $data['is_active'] ?? 1
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update source
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'type', 'icon', 'color', 'description', 'is_active'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE lead_sources SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        return $this->getById($id);
    }
    
    /**
     * Delete source (soft delete by deactivating)
     */
    public function delete($id) {
        // Check if source has leads
        $leadCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM leads WHERE source_id = ?",
            [$id]
        )['count'];
        
        if ($leadCount > 0) {
            // Soft delete - just deactivate
            $this->db->query("UPDATE lead_sources SET is_active = 0 WHERE id = ?", [$id]);
        } else {
            // Hard delete if no leads
            $this->db->query("DELETE FROM lead_sources WHERE id = ?", [$id]);
        }
        
        return true;
    }
    
    /**
     * Get source statistics (lead count per source)
     */
    public function getStatistics() {
        $sql = "SELECT ls.*, COUNT(l.id) as lead_count,
                SUM(CASE WHEN l.status = 'converted' THEN 1 ELSE 0 END) as converted_count
                FROM lead_sources ls
                LEFT JOIN leads l ON ls.id = l.source_id
                WHERE ls.is_active = 1
                GROUP BY ls.id
                ORDER BY lead_count DESC";
        return $this->db->fetchAll($sql);
    }
}
