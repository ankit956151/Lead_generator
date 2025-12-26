<?php
/**
 * LeadGen CMS - API Keys Management API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/ApiKey.php';

header('Content-Type: application/json');

$apiKey = new ApiKey();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

try {
    switch ($method) {
        case 'GET':
            $keys = $apiKey->getAll();
            jsonResponse(['success' => true, 'data' => $keys]);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['service']) || empty($data['api_key'])) {
                jsonResponse(['error' => 'Service and API key are required'], 400);
            }
            
            $id = $apiKey->save(
                sanitize($data['service']),
                $data['api_key'],
                $data['api_secret'] ?? null
            );
            
            jsonResponse([
                'success' => true,
                'message' => 'API key saved successfully',
                'id' => $id
            ]);
            break;
            
        case 'DELETE':
            $service = $_GET['service'] ?? null;
            if (!$service) {
                jsonResponse(['error' => 'Service name required'], 400);
            }
            
            $apiKey->delete($service);
            jsonResponse(['success' => true, 'message' => 'API key deleted']);
            break;
            
        default:
            jsonResponse(['error' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
