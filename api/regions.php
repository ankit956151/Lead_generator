<?php
/**
 * LeadGen CMS - Region Data API
 * 
 * Fetches region-based data using free public APIs
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? 'countries';

try {
    switch ($action) {
        case 'countries':
            $data = getCountries();
            jsonResponse(['success' => true, 'data' => $data]);
            break;
            
        case 'country':
            $code = $_GET['code'] ?? '';
            if (empty($code)) {
                jsonResponse(['error' => 'Country code required'], 400);
            }
            $data = getCountryDetails($code);
            jsonResponse(['success' => true, 'data' => $data]);
            break;
            
        case 'ip-location':
            $ip = $_GET['ip'] ?? getClientIP();
            $data = getLocationFromIP($ip);
            jsonResponse(['success' => true, 'data' => $data]);
            break;
            
        case 'timezones':
            $data = getTimezones();
            jsonResponse(['success' => true, 'data' => $data]);
            break;
            
        case 'currencies':
            $data = getCurrencies();
            jsonResponse(['success' => true, 'data' => $data]);
            break;
            
        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}

/**
 * Get list of all countries
 */
function getCountries() {
    $cacheFile = __DIR__ . '/../cache/countries.json';
    
    // Check cache (24 hour expiry)
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 86400) {
        return json_decode(file_get_contents($cacheFile), true);
    }
    
    // Fetch from REST Countries API
    $url = RESTCOUNTRIES_API_URL . 'all?fields=name,cca2,cca3,capital,region,subregion,population,flags,currencies,languages';
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return getDefaultCountries();
    }
    
    $countries = json_decode($response, true);
    
    // Simplify and cache
    $simplified = array_map(function($country) {
        return [
            'name' => $country['name']['common'] ?? 'Unknown',
            'official_name' => $country['name']['official'] ?? '',
            'code' => $country['cca2'] ?? '',
            'code3' => $country['cca3'] ?? '',
            'capital' => $country['capital'][0] ?? '',
            'region' => $country['region'] ?? '',
            'subregion' => $country['subregion'] ?? '',
            'population' => $country['population'] ?? 0,
            'flag' => $country['flags']['svg'] ?? ''
        ];
    }, $countries);
    
    // Sort by name
    usort($simplified, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
    
    // Cache the result
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    file_put_contents($cacheFile, json_encode($simplified));
    
    return $simplified;
}

/**
 * Get country details by code
 */
function getCountryDetails($code) {
    $url = RESTCOUNTRIES_API_URL . "alpha/$code";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['error' => 'Country not found'];
    }
    
    $data = json_decode($response, true);
    
    if (is_array($data) && isset($data[0])) {
        $country = $data[0];
        return [
            'name' => $country['name']['common'] ?? 'Unknown',
            'official_name' => $country['name']['official'] ?? '',
            'code' => $country['cca2'] ?? '',
            'capital' => $country['capital'] ?? [],
            'region' => $country['region'] ?? '',
            'subregion' => $country['subregion'] ?? '',
            'population' => $country['population'] ?? 0,
            'area' => $country['area'] ?? 0,
            'timezones' => $country['timezones'] ?? [],
            'currencies' => $country['currencies'] ?? [],
            'languages' => $country['languages'] ?? [],
            'borders' => $country['borders'] ?? [],
            'flag' => $country['flags']['svg'] ?? '',
            'maps' => $country['maps'] ?? []
        ];
    }
    
    return ['error' => 'Invalid response'];
}

/**
 * Get location from IP address
 */
function getLocationFromIP($ip) {
    // Skip local IPs
    if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0) {
        return ['error' => 'Cannot geolocate local IP', 'ip' => $ip];
    }
    
    $url = IP_API_URL . $ip;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['error' => 'Failed to get location'];
    }
    
    $data = json_decode($response, true);
    
    if ($data['status'] === 'success') {
        return [
            'ip' => $ip,
            'country' => $data['country'] ?? '',
            'country_code' => $data['countryCode'] ?? '',
            'region' => $data['regionName'] ?? '',
            'city' => $data['city'] ?? '',
            'zip' => $data['zip'] ?? '',
            'lat' => $data['lat'] ?? 0,
            'lon' => $data['lon'] ?? 0,
            'timezone' => $data['timezone'] ?? '',
            'isp' => $data['isp'] ?? ''
        ];
    }
    
    return ['error' => $data['message'] ?? 'Unknown error'];
}

/**
 * Get list of timezones
 */
function getTimezones() {
    return DateTimeZone::listIdentifiers();
}

/**
 * Get list of currencies (from cached countries data)
 */
function getCurrencies() {
    $currencies = [];
    $countries = getCountries();
    
    // Note: This is a simplified version
    // In production, you'd want a dedicated currency API
    $commonCurrencies = [
        ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'],
        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
        ['code' => 'GBP', 'name' => 'British Pound', 'symbol' => '£'],
        ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '¥'],
        ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => '¥'],
        ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => '₹'],
        ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => 'A$'],
        ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => 'C$'],
        ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'Fr'],
        ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$']
    ];
    
    return $commonCurrencies;
}

/**
 * Default countries list (fallback)
 */
function getDefaultCountries() {
    return [
        ['name' => 'United States', 'code' => 'US', 'region' => 'Americas'],
        ['name' => 'United Kingdom', 'code' => 'GB', 'region' => 'Europe'],
        ['name' => 'Canada', 'code' => 'CA', 'region' => 'Americas'],
        ['name' => 'Australia', 'code' => 'AU', 'region' => 'Oceania'],
        ['name' => 'Germany', 'code' => 'DE', 'region' => 'Europe'],
        ['name' => 'France', 'code' => 'FR', 'region' => 'Europe'],
        ['name' => 'Japan', 'code' => 'JP', 'region' => 'Asia'],
        ['name' => 'China', 'code' => 'CN', 'region' => 'Asia'],
        ['name' => 'India', 'code' => 'IN', 'region' => 'Asia'],
        ['name' => 'Brazil', 'code' => 'BR', 'region' => 'Americas'],
        ['name' => 'Mexico', 'code' => 'MX', 'region' => 'Americas'],
        ['name' => 'Italy', 'code' => 'IT', 'region' => 'Europe'],
        ['name' => 'Spain', 'code' => 'ES', 'region' => 'Europe'],
        ['name' => 'South Korea', 'code' => 'KR', 'region' => 'Asia'],
        ['name' => 'Russia', 'code' => 'RU', 'region' => 'Europe']
    ];
}
