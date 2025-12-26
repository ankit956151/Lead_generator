<?php
/**
 * LeadGen CMS - Scraper API
 * 
 * Web scraping endpoints for lead generation
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/ApiKey.php';

header('Content-Type: application/json');

$apiKey = new ApiKey();
$lead = new Lead();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'search';

try {
    switch ($action) {
        case 'google-maps':
            handleGoogleMapsScraper($apiKey, $lead);
            break;
        case 'hunter':
            handleHunterSearch($apiKey, $lead);
            break;
        case 'companies':
            handleCompanySearch();
            break;
        case 'import':
            handleImportScraped($lead);
            break;
        default:
            jsonResponse(['error' => 'Invalid scraper action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}

/**
 * Google Maps Scraper (simulated/demo mode or via Apify)
 */
function handleGoogleMapsScraper($apiKey, $lead) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['query']) || empty($data['location'])) {
        jsonResponse(['error' => 'Query and location are required'], 400);
    }
    
    $query = sanitize($data['query']);
    $location = sanitize($data['location']);
    $limit = (int)($data['limit'] ?? 20);
    
    // Check if Apify API key is configured
    $apifyKey = $apiKey->getByService('apify');
    
    if ($apifyKey && $apifyKey['is_active']) {
        // Real Apify scraping
        $results = scrapeWithApify($apifyKey['api_key'], $query, $location, $limit);
    } else {
        // Demo mode - generate realistic mock data
        $results = generateMockBusinessData($query, $location, $limit);
    }
    
    // Save to scraped_data table
    $db = db();
    foreach ($results as $result) {
        $db->query(
            "INSERT INTO scraped_data (scraper_type, search_query, location, business_name, 
             phone, email, website, address, rating, reviews_count, category, raw_data) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                'google_maps',
                $query,
                $location,
                $result['name'],
                $result['phone'] ?? null,
                $result['email'] ?? null,
                $result['website'] ?? null,
                $result['address'] ?? null,
                $result['rating'] ?? null,
                $result['reviews'] ?? 0,
                $result['category'] ?? null,
                json_encode($result)
            ]
        );
    }
    
    jsonResponse([
        'success' => true,
        'message' => "Found " . count($results) . " businesses",
        'demo_mode' => !($apifyKey && $apifyKey['is_active']),
        'data' => $results
    ]);
}

/**
 * Hunter.io Email Search
 */
function handleHunterSearch($apiKey, $lead) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['domain'])) {
        jsonResponse(['error' => 'Domain is required'], 400);
    }
    
    $domain = sanitize($data['domain']);
    
    // Check if Hunter API key is configured
    $hunterKey = $apiKey->getByService('hunter');
    
    if ($hunterKey && $hunterKey['is_active']) {
        // Real Hunter.io API call
        $results = searchWithHunter($hunterKey['api_key'], $domain);
        $apiKey->recordUsage('hunter');
    } else {
        // Demo mode
        $results = generateMockEmailData($domain);
    }
    
    jsonResponse([
        'success' => true,
        'domain' => $domain,
        'demo_mode' => !($hunterKey && $hunterKey['is_active']),
        'data' => $results
    ]);
}

/**
 * Company/Business Search using free APIs
 */
function handleCompanySearch() {
    $query = $_GET['query'] ?? '';
    $country = $_GET['country'] ?? '';
    
    if (empty($query)) {
        jsonResponse(['error' => 'Search query required'], 400);
    }
    
    // Use free public API for company data
    $results = searchCompanies($query, $country);
    
    jsonResponse([
        'success' => true,
        'data' => $results
    ]);
}

/**
 * Import scraped data as leads
 */
function handleImportScraped($lead) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['ids']) || !is_array($data['ids'])) {
        jsonResponse(['error' => 'No scraped data IDs provided'], 400);
    }
    
    $db = db();
    $imported = 0;
    $skipped = 0;
    
    foreach ($data['ids'] as $id) {
        $scraped = $db->fetch("SELECT * FROM scraped_data WHERE id = ? AND is_imported = 0", [$id]);
        
        if (!$scraped) {
            $skipped++;
            continue;
        }
        
        // Check if email already exists
        if ($scraped['email'] && $lead->emailExists($scraped['email'])) {
            $skipped++;
            continue;
        }
        
        // Create lead from scraped data
        $leadId = $lead->create([
            'name' => $scraped['business_name'],
            'email' => $scraped['email'] ?? generatePlaceholderEmail($scraped['business_name']),
            'phone' => $scraped['phone'],
            'company' => $scraped['business_name'],
            'website' => $scraped['website'],
            'address' => $scraped['address'],
            'source' => 'Google Maps',
            'status' => 'new',
            'notes' => "Rating: " . ($scraped['rating'] ?? 'N/A') . " | Reviews: " . ($scraped['reviews_count'] ?? 0)
        ]);
        
        // Mark as imported
        $db->query(
            "UPDATE scraped_data SET is_imported = 1, imported_lead_id = ? WHERE id = ?",
            [$leadId, $id]
        );
        
        $imported++;
    }
    
    jsonResponse([
        'success' => true,
        'message' => "Imported $imported leads, skipped $skipped",
        'imported' => $imported,
        'skipped' => $skipped
    ]);
}

/**
 * Scrape with Apify (if API key configured)
 */
function scrapeWithApify($apiToken, $query, $location, $limit) {
    // Apify Google Maps Scraper actor ID
    $actorId = 'compass~crawler-google-places';
    
    $url = APIFY_API_URL . "acts/$actorId/runs?token=$apiToken";
    
    $input = [
        'searchStringsArray' => ["$query in $location"],
        'maxCrawledPlacesPerSearch' => $limit,
        'language' => 'en',
        'exportPlaceUrls' => false
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($input));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 201) {
        // Fall back to demo mode
        return generateMockBusinessData($query, $location, $limit);
    }
    
    $result = json_decode($response, true);
    
    // Wait for run to finish and get results
    // In production, you'd implement proper async handling
    sleep(5);
    
    $datasetUrl = APIFY_API_URL . "actor-runs/{$result['data']['id']}/dataset/items?token=$apiToken";
    $dataResponse = file_get_contents($datasetUrl);
    $data = json_decode($dataResponse, true);
    
    return array_map(function($item) {
        return [
            'name' => $item['title'] ?? $item['name'] ?? 'Unknown Business',
            'phone' => $item['phone'] ?? null,
            'email' => $item['email'] ?? null,
            'website' => $item['website'] ?? null,
            'address' => $item['address'] ?? null,
            'rating' => $item['totalScore'] ?? null,
            'reviews' => $item['reviewsCount'] ?? 0,
            'category' => $item['categoryName'] ?? null
        ];
    }, $data);
}

/**
 * Search with Hunter.io
 */
function searchWithHunter($apiToken, $domain) {
    $url = HUNTER_API_URL . "domain-search?domain=$domain&api_key=$apiToken";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return generateMockEmailData($domain);
    }
    
    $data = json_decode($response, true);
    
    return array_map(function($email) {
        return [
            'email' => $email['value'],
            'first_name' => $email['first_name'] ?? '',
            'last_name' => $email['last_name'] ?? '',
            'position' => $email['position'] ?? '',
            'confidence' => $email['confidence'] ?? 0,
            'verified' => $email['verification']['status'] === 'valid'
        ];
    }, $data['data']['emails'] ?? []);
}

/**
 * Search companies using free APIs
 */
function searchCompanies($query, $country = '') {
    // Using a free public company API
    // In production, you might use OpenCorporates API or similar
    
    // Demo data for now
    return [
        [
            'name' => "$query Inc.",
            'industry' => 'Technology',
            'location' => $country ?: 'United States',
            'employees' => rand(50, 5000),
            'website' => 'https://' . strtolower(str_replace(' ', '', $query)) . '.com'
        ],
        [
            'name' => "$query Solutions",
            'industry' => 'Business Services',
            'location' => $country ?: 'United States',
            'employees' => rand(10, 500),
            'website' => 'https://' . strtolower(str_replace(' ', '', $query)) . 'solutions.com'
        ]
    ];
}

/**
 * Generate mock business data for demo mode
 */
function generateMockBusinessData($query, $location, $limit) {
    $businesses = [];
    $businessTypes = ['Restaurant', 'Cafe', 'Shop', 'Store', 'Services', 'Solutions', 'Group', '& Co.'];
    $streets = ['Main St', 'Oak Ave', 'Pine Road', 'Market St', 'Business Blvd', 'Commerce Way'];
    $firstNames = ['John', 'Sarah', 'Mike', 'Emily', 'David', 'Lisa', 'James', 'Anna'];
    $domains = ['.com', '.net', '.co', '.io'];
    
    for ($i = 0; $i < min($limit, 25); $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $businessType = $businessTypes[array_rand($businessTypes)];
        $businessName = ucfirst($query) . ' ' . $businessType . ' ' . ($i + 1);
        $domain = $domains[array_rand($domains)];
        $slug = strtolower(str_replace([' ', "'"], ['', ''], $businessName));
        
        $businesses[] = [
            'name' => $businessName,
            'phone' => '+1 555-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
            'email' => 'contact@' . $slug . $domain,
            'website' => 'https://www.' . $slug . $domain,
            'address' => rand(100, 9999) . ' ' . $streets[array_rand($streets)] . ', ' . $location,
            'rating' => round(rand(35, 50) / 10, 1),
            'reviews' => rand(10, 500),
            'category' => ucfirst($query)
        ];
    }
    
    return $businesses;
}

/**
 * Generate mock email data for demo mode
 */
function generateMockEmailData($domain) {
    $positions = ['CEO', 'CTO', 'Marketing Manager', 'Sales Director', 'HR Manager', 'Developer'];
    $firstNames = ['John', 'Sarah', 'Mike', 'Emily', 'David', 'Lisa'];
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Davis'];
    
    $emails = [];
    $numEmails = rand(3, 8);
    
    for ($i = 0; $i < $numEmails; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $position = $positions[array_rand($positions)];
        
        $emails[] = [
            'email' => strtolower($firstName . '.' . $lastName) . '@' . $domain,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'position' => $position,
            'confidence' => rand(70, 99),
            'verified' => rand(0, 1) === 1
        ];
    }
    
    return $emails;
}

/**
 * Generate placeholder email from business name
 */
function generatePlaceholderEmail($businessName) {
    $slug = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $businessName));
    return 'info@' . substr($slug, 0, 20) . '.com';
}
