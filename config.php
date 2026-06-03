<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. MySQL Database Configuration (cPanel)
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'asbfash2_kavizz');
define('DB_PASS', 'ASBfash2026#');
define('DB_NAME', 'asbfash2_loyal_customer');

try {
    $mysql = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("MySQL Connection failed: " . $e->getMessage());
}

// 2. MS SQL / ERP API Configurations
define('API_BASE_URL', 'http://124.43.17.54/api/customer.php');
define('API_SECRET_KEY', 'ASB2026SECRET');

// 3. Local Hutch SMS Endpoints
define('SMS_BATCH_URL', 'https://whats.asbfashion.com/send_sms_batch.php');

/**
 * Helper function to format mobile numbers to Sri Lankan 947XXXXXXXX format
 */
function formatMobileSriLanka($mobile) {
    $mobile = preg_replace('/\D/', '', $mobile); // Strip out non-numeric noise
    
    if (strlen($mobile) == 9 && strpos($mobile, '7') === 0) {
        return '94' . $mobile;
    }
    if (strlen($mobile) == 10 && strpos($mobile, '07') === 0) {
        return '94' . substr($mobile, 1);
    }
    if (strlen($mobile) == 11 && strpos($mobile, '947') === 0) {
        return $mobile;
    }
    return false;
}

/**
 * Dispatches system-critical authentication messages via the modified Hutch utility
 */
function sendSMSNotification($to, $message) {
    $payload = json_encode([
        "numbers" => [$to],
        "content" => $message
    ]);

    $ch = curl_init(SMS_BATCH_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false 
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $resData = json_decode($result, true);
        if (isset($resData['success']) && $resData['success'] === true) {
            return true;
        }
    }
    
    // Fallback debug notice if the internal loop fails to connect
    $_SESSION['sms_debug'] = "Hutch internal endpoint route error or returned rejection code: " . $result;
    return false;
}

/**
 * Fetches customer identity data live from the MS SQL / ERP master database API
 */
function fetchMasterCustomerData($searchQuery) {
    $apiUrl = API_BASE_URL . "?key=" . API_SECRET_KEY . "&search=" . urlencode(trim($searchQuery));
    
    $response = @file_get_contents($apiUrl);
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] == true && !empty($data['customer'])) {
            return $data['customer'];
        }
    }
    return null;
}
?>