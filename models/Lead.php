<?php
/**
 * LeadGen CMS - Lead Model
 * 
 * Handles all lead-related database operations
 */

require_once __DIR__ . '/../config/config.php';

class Lead {
    
    private $db;
    
    public function __construct() {
        $this->db = db();
    }
    
    /**
     * Get all leads with optional filtering and pagination
     */
    public function getAll($filters = [], $page = 1, $perPage = 20) {
        $where = [];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = "status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['source'])) {
            $where[] = "source = ?";
            $params[] = $filters['source'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(name LIKE ? OR email LIKE ? OR company LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "DATE(created_at) >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "DATE(created_at) <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (isset($filters['is_verified'])) {
            $where[] = "is_verified = ?";
            $params[] = $filters['is_verified'] ? 1 : 0;
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM leads $whereClause";
        $total = $this->db->fetch($countSql, $params)['total'];
        
        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT l.*, ls.name as source_name, ls.icon as source_icon, ls.color as source_color 
                FROM leads l 
                LEFT JOIN lead_sources ls ON l.source_id = ls.id 
                $whereClause 
                ORDER BY l.created_at DESC 
                LIMIT $perPage OFFSET $offset";
        
        $leads = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $leads,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }
    
    /**
     * Get single lead by ID
     */
    public function getById($id) {
        $sql = "SELECT l.*, ls.name as source_name, ls.icon as source_icon 
                FROM leads l 
                LEFT JOIN lead_sources ls ON l.source_id = ls.id 
                WHERE l.id = ?";
        return $this->db->fetch($sql, [$id]);
    }
    
    /**
     * Get lead by email
     */
    public function getByEmail($email) {
        return $this->db->fetch("SELECT * FROM leads WHERE email = ?", [$email]);
    }
    
    /**
     * Create new lead
     */
    public function create($data) {
        $sql = "INSERT INTO leads (name, email, phone, company, website, address, city, state, 
                country, postal_code, source_id, source, status, score, is_verified, tags, 
                notes, custom_fields, assigned_to, created_by, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $params = [
            $data['name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['company'] ?? null,
            $data['website'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['country'] ?? null,
            $data['postal_code'] ?? null,
            $data['source_id'] ?? null,
            $data['source'] ?? 'Manual',
            $data['status'] ?? 'new',
            $data['score'] ?? 0,
            $data['is_verified'] ?? 0,
            isset($data['tags']) ? json_encode($data['tags']) : null,
            $data['notes'] ?? null,
            isset($data['custom_fields']) ? json_encode($data['custom_fields']) : null,
            $data['assigned_to'] ?? null,
            $data['created_by'] ?? null
        ];
        
        $this->db->query($sql, $params);
        $leadId = $this->db->lastInsertId();
        
        // Log activity
        logActivity('lead_created', "New lead created: {$data['name']}", $data['created_by'] ?? null);
        
        return $leadId;
    }
    
    /**
     * Update lead
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'email', 'phone', 'company', 'website', 'address', 
                          'city', 'state', 'country', 'postal_code', 'source_id', 'source',
                          'status', 'score', 'is_verified', 'notes', 'assigned_to'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        // Handle JSON fields
        if (isset($data['tags'])) {
            $fields[] = "tags = ?";
            $params[] = json_encode($data['tags']);
        }
        
        if (isset($data['custom_fields'])) {
            $fields[] = "custom_fields = ?";
            $params[] = json_encode($data['custom_fields']);
        }
        
        // Handle status change events
        if (isset($data['status'])) {
            if ($data['status'] === 'contacted') {
                $fields[] = "last_contacted_at = NOW()";
            } elseif ($data['status'] === 'converted') {
                $fields[] = "converted_at = NOW()";
            }
        }
        
        $fields[] = "updated_at = NOW()";
        $params[] = $id;
        
        $sql = "UPDATE leads SET " . implode(', ', $fields) . " WHERE id = ?";
        $this->db->query($sql, $params);
        
        // Log activity
        logActivity('lead_updated', "Lead updated: ID $id");
        
        return $this->getById($id);
    }
    
    /**
     * Delete lead
     */
    public function delete($id) {
        $lead = $this->getById($id);
        if (!$lead) {
            return false;
        }
        
        $this->db->query("DELETE FROM leads WHERE id = ?", [$id]);
        
        // Log activity
        logActivity('lead_deleted', "Lead deleted: {$lead['name']}");
        
        return true;
    }
    
    /**
     * Bulk delete leads
     */
    public function bulkDelete($ids) {
        if (empty($ids)) {
            return 0;
        }
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $this->db->query("DELETE FROM leads WHERE id IN ($placeholders)", $ids);
        
        // Log activity
        logActivity('leads_bulk_deleted', "Deleted " . count($ids) . " leads");
        
        return count($ids);
    }
    
    /**
     * Get statistics
     */
    public function getStatistics() {
        return $this->db->fetch("SELECT * FROM vw_lead_statistics");
    }
    
    /**
     * Get source statistics
     */
    public function getSourceStatistics() {
        return $this->db->fetchAll("SELECT * FROM vw_source_performance");
    }
    
    /**
     * Get daily trends
     */
    public function getDailyTrends($days = 30) {
        $sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                FROM leads 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                GROUP BY DATE(created_at) 
                ORDER BY date ASC";
        return $this->db->fetchAll($sql, [$days]);
    }
    
    /**
     * Get status counts
     */
    public function getStatusCounts() {
        $sql = "SELECT status, COUNT(*) as count FROM leads GROUP BY status";
        $results = $this->db->fetchAll($sql);
        
        $counts = [
            'all' => 0,
            'new' => 0,
            'contacted' => 0,
            'qualified' => 0,
            'converted' => 0,
            'lost' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['all'] += (int)$row['count'];
        }
        
        return $counts;
    }
    
    /**
     * Get recent leads
     */
    public function getRecent($limit = 5) {
        $sql = "SELECT l.*, ls.name as source_name 
                FROM leads l 
                LEFT JOIN lead_sources ls ON l.source_id = ls.id 
                ORDER BY l.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }
    
    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        if ($excludeId) {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM leads WHERE email = ? AND id != ?",
                [$email, $excludeId]
            );
        } else {
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM leads WHERE email = ?",
                [$email]
            );
        }
        return $result['count'] > 0;
    }
    
    /**
     * Import leads from array (bulk import)
     */
    public function bulkImport($leads, $source = 'Import', $createdBy = null) {
        $imported = 0;
        $skipped = 0;
        
        $this->db->beginTransaction();
        
        try {
            foreach ($leads as $leadData) {
                // Check if email already exists
                if ($this->emailExists($leadData['email'])) {
                    $skipped++;
                    continue;
                }
                
                $leadData['source'] = $source;
                $leadData['created_by'] = $createdBy;
                $this->create($leadData);
                $imported++;
            }
            
            $this->db->commit();
            
            // Log activity
            logActivity('leads_imported', "Imported $imported leads, skipped $skipped duplicates", $createdBy);
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
        
        return [
            'imported' => $imported,
            'skipped' => $skipped
        ];
    }
    
    /**
     * Export leads to CSV format
     */
    public function exportToCSV($filters = []) {
        $result = $this->getAll($filters, 1, 10000);
        $leads = $result['data'];
        
        $headers = ['ID', 'Name', 'Email', 'Phone', 'Company', 'Website', 'Source', 
                    'Status', 'Score', 'Verified', 'Notes', 'Created At'];
        
        $csv = implode(',', $headers) . "\n";
        
        foreach ($leads as $lead) {
            $row = [
                $lead['id'],
                '"' . str_replace('"', '""', $lead['name']) . '"',
                $lead['email'],
                $lead['phone'] ?? '',
                '"' . str_replace('"', '""', $lead['company'] ?? '') . '"',
                $lead['website'] ?? '',
                $lead['source'],
                $lead['status'],
                $lead['score'],
                $lead['is_verified'] ? 'Yes' : 'No',
                '"' . str_replace('"', '""', $lead['notes'] ?? '') . '"',
                $lead['created_at']
            ];
            $csv .= implode(',', $row) . "\n";
        }
        
        return $csv;
    }
}
