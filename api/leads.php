<?php
/**
 * LeadGen CMS - Leads API
 * 
 * RESTful API endpoints for lead management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Lead.php';

header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$lead = new Lead();
$method = $_SERVER['REQUEST_METHOD'];

// Get the action from URL
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            handleGet($lead, $action, $id);
            break;
        case 'POST':
            handlePost($lead, $action);
            break;
        case 'PUT':
            handlePut($lead, $id);
            break;
        case 'DELETE':
            handleDelete($lead, $action, $id);
            break;
        default:
            jsonResponse(['error' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}

/**
 * Handle GET requests
 */
function handleGet($lead, $action, $id) {
    switch ($action) {
        case 'get':
            if (!$id) {
                jsonResponse(['error' => 'Lead ID required'], 400);
            }
            $result = $lead->getById($id);
            if (!$result) {
                jsonResponse(['error' => 'Lead not found'], 404);
            }
            jsonResponse(['success' => true, 'data' => $result]);
            break;
            
        case 'statistics':
            $stats = $lead->getStatistics();
            $statusCounts = $lead->getStatusCounts();
            $sourceStats = $lead->getSourceStatistics();
            jsonResponse([
                'success' => true,
                'data' => [
                    'overview' => $stats,
                    'status_counts' => $statusCounts,
                    'sources' => $sourceStats
                ]
            ]);
            break;
            
        case 'recent':
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $results = $lead->getRecent($limit);
            jsonResponse(['success' => true, 'data' => $results]);
            break;
            
        case 'trends':
            $days = isset($_GET['days']) ? (int)$_GET['days'] : 30;
            $results = $lead->getDailyTrends($days);
            jsonResponse(['success' => true, 'data' => $results]);
            break;
            
        case 'export':
            $filters = getFiltersFromRequest();
            $csv = $lead->exportToCSV($filters);
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="leads_export_' . date('Y-m-d') . '.csv"');
            echo $csv;
            exit;
            
        case 'list':
        default:
            $filters = getFiltersFromRequest();
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 20;
            
            $results = $lead->getAll($filters, $page, $perPage);
            jsonResponse([
                'success' => true,
                'data' => $results['data'],
                'meta' => [
                    'total' => $results['total'],
                    'page' => $results['page'],
                    'per_page' => $results['per_page'],
                    'total_pages' => $results['total_pages']
                ]
            ]);
    }
}

/**
 * Handle POST requests
 */
function handlePost($lead, $action) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    switch ($action) {
        case 'create':
            // Validate required fields
            if (empty($data['name']) || empty($data['email'])) {
                jsonResponse(['error' => 'Name and email are required'], 400);
            }
            
            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                jsonResponse(['error' => 'Invalid email format'], 400);
            }
            
            // Check for duplicate email
            if ($lead->emailExists($data['email'])) {
                jsonResponse(['error' => 'A lead with this email already exists'], 409);
            }
            
            // Sanitize input
            $data = sanitize($data);
            
            $id = $lead->create($data);
            $newLead = $lead->getById($id);
            
            jsonResponse([
                'success' => true,
                'message' => 'Lead created successfully',
                'data' => $newLead
            ], 201);
            break;
            
        case 'import':
            if (empty($data['leads']) || !is_array($data['leads'])) {
                jsonResponse(['error' => 'No leads data provided'], 400);
            }
            
            $source = $data['source'] ?? 'Import';
            $result = $lead->bulkImport($data['leads'], $source);
            
            jsonResponse([
                'success' => true,
                'message' => "Imported {$result['imported']} leads, skipped {$result['skipped']} duplicates",
                'data' => $result
            ]);
            break;
            
        default:
            // Default create action
            handlePost($lead, 'create');
    }
}

/**
 * Handle PUT requests
 */
function handlePut($lead, $id) {
    if (!$id) {
        jsonResponse(['error' => 'Lead ID required'], 400);
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        jsonResponse(['error' => 'No data provided'], 400);
    }
    
    // Check if lead exists
    $existing = $lead->getById($id);
    if (!$existing) {
        jsonResponse(['error' => 'Lead not found'], 404);
    }
    
    // Validate email if being updated
    if (!empty($data['email'])) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            jsonResponse(['error' => 'Invalid email format'], 400);
        }
        
        if ($lead->emailExists($data['email'], $id)) {
            jsonResponse(['error' => 'A lead with this email already exists'], 409);
        }
    }
    
    // Sanitize input
    $data = sanitize($data);
    
    $updated = $lead->update($id, $data);
    
    jsonResponse([
        'success' => true,
        'message' => 'Lead updated successfully',
        'data' => $updated
    ]);
}

/**
 * Handle DELETE requests
 */
function handleDelete($lead, $action, $id) {
    switch ($action) {
        case 'bulk':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['ids']) || !is_array($data['ids'])) {
                jsonResponse(['error' => 'No IDs provided'], 400);
            }
            
            $count = $lead->bulkDelete($data['ids']);
            jsonResponse([
                'success' => true,
                'message' => "Deleted $count leads"
            ]);
            break;
            
        default:
            if (!$id) {
                jsonResponse(['error' => 'Lead ID required'], 400);
            }
            
            $result = $lead->delete($id);
            if (!$result) {
                jsonResponse(['error' => 'Lead not found'], 404);
            }
            
            jsonResponse([
                'success' => true,
                'message' => 'Lead deleted successfully'
            ]);
    }
}

/**
 * Get filters from request
 */
function getFiltersFromRequest() {
    return [
        'status' => $_GET['status'] ?? null,
        'source' => $_GET['source'] ?? null,
        'search' => $_GET['search'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'is_verified' => isset($_GET['is_verified']) ? (bool)$_GET['is_verified'] : null
    ];
}
