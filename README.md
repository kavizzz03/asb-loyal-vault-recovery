# ASB Fashion Loyalty Portal (v1.0.0)

A high-performance, secure, cross-platform customer loyalty engine engineered for ASB Fashion and ASB Glamour. This system delivers real-time loyalty tracking capabilities directly to public users while strictly insulating enterprise internal core data repositories from direct edge infrastructure exposures.

---

## 📊 Executive Summary & Topology

The ASB Fashion Loyalty Portal allows retail consumers across Sri Lanka to safely view points summaries, earned values, and membership parameters online. The platform relies on a hybrid architectural topology designed to balance cost-effective public delivery with rigid internal network perimeter isolation:

[ Public Customer Browser ]│▼ (HTTPS Traffic / Responsive Tailwind UI)[ cPanel Linux Web Hosting Node ]│▼ (Secure HTTP Custom JSON Payload over cURL)[ Intermediate Windows API Gateway ] <-- (XAMPP / PHP 8.x Subnet Router)│▼ (Native Windows sqlsrv Parameterized PDO Queries)[ Internal Microsoft SQL Server Database ] ➔ [ Master Customer Store ]
---

## 🛠️ System Technology Matrix

### 1. Customer Interface Node (Public Web)
* **Environment:** cPanel Linux Hosting Environment (PHP 8.2 Runtime Stack)
* **Layout Standards:** HTML5, CSS3 Custom Properties, Tailwind CSS Utilities, Inter Type Spec, Font Awesome Assets
* **Core Responsibilities:** Stateless Customer Lookup Routers, SEO Execution Layer, Multi-Device Component Hydration, Non-Blocking Outbound API Communication Operations.

### 2. Integration Node (Intermediate API Gateway)
* **Environment:** Windows Integration Client PC (Apache Reverse Proxy Server / XAMPP Core)
* **Driver Engine:** Microsoft Drivers for PHP for SQL Server (`php_sqlsrv.dll` / `php_pdo_sqlsrv.dll` enabled)
* **Core Responsibilities:** Inbound Node Verification, Symmetric Token Audits, Native Binary Data Formatting, Database Query Execution, Encrypted Stream Conversions.

### 3. Core Enterprise Storage Node (Database Backend)
* **Environment:** Microsoft SQL Server Environment (MSSQL Server deployment instances)
* **Core Responsibilities:** Real-Time Data Storage, Parameterized Aggregate Calculation Computations, Table Index Injections, Secure Point Accounting.

---

## 🗄️ Relational Database Layout & Optimizations

This system links against existing internal structures via specific table schemas. To hit query compilation intervals under **50ms** even when scanning tables holding **1,000,000+ customer accounts**, implement these non-clustered optimization mappings:


-- 1. Master Customer Data Repository
CREATE TABLE [dbo].[M_TBLCUSTOMERS] (
    [CM_CODE] VARCHAR(30) NOT NULL PRIMARY KEY,
    [CM_NAME] NVARCHAR(150) NOT NULL,
    [CM_MOBILE] VARCHAR(20) NOT NULL,
    [CM_NIC] VARCHAR(20) NULL,
    [CM_DOB] DATE NULL,
    [CM_ADDRESS] NVARCHAR(500) NULL,
    [CREATED_AT] DATETIME DEFAULT GETDATE()
);

-- Look-Up Index Configurations
CREATE NONCLUSTERED INDEX [IX_CM_CODE] ON [dbo].[M_TBLCUSTOMERS]([CM_CODE]);
CREATE NONCLUSTERED INDEX [IX_CM_MOBILE] ON [dbo].[M_TBLCUSTOMERS]([CM_MOBILE]);
CREATE NONCLUSTERED INDEX [IX_CM_NIC] ON [dbo].[M_TBLCUSTOMERS]([CM_NIC]);

-- 2. Transactional Loyalty Ledger Storage
CREATE TABLE [dbo].[U_TBLLOYALTY_POINTS] (
    [ID] BIGINT IDENTITY(1,1) PRIMARY KEY,
    [POINT_MEMBER] VARCHAR(30) NOT NULL,
    [POINTS_EARNED] DECIMAL(18,2) DEFAULT 0.00,
    [POINTS_REDEEMED] DECIMAL(18,2) DEFAULT 0.00,
    [TRANSACTION_DATE] DATETIME NOT NULL,
    CONSTRAINT [FK_Loyalty_Customer] FOREIGN KEY ([POINT_MEMBER]) REFERENCES [M_TBLCUSTOMERS]([CM_CODE])
);

-- Aggregation Query Index Setup
CREATE NONCLUSTERED INDEX [IX_POINT_MEMBER] ON [dbo].[U_TBLLOYALTY_POINTS]([POINT_MEMBER]) 
INCLUDE ([POINTS_EARNED], [POINTS_REDEEMED]);
🖥️ Core Infrastructure Source Modules1. Frontend Linux Controller Snippet (forgot_password.php)This secure state controller handles multi-step session boundaries on the public Linux cPanel node without direct database exposure:PHP<?php
/**
 * ASB Loyalty Portal - Public Node State Controller
 * Handles user tracking lifecycle via session boundaries.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enforce baseline step boundaries if unassigned
if (!isset($_SESSION['forgot_step'])) {
    $_SESSION['forgot_step'] = 1;
}

/**
 * Executes a secure transport call out to the Windows API Gateway.
 */
function queryGatewayEndpoint($action, $searchParameter) {
    $gatewayUrl = "http://YOUR_ROUTER_IP:8080/api/customer.php"; 
    $apiKey = "ASB_GATEWAY_AUTHENTICATION_SECRET_TOKEN_2026";
    
    $targetUrl = $gatewayUrl . "?key=" . urlencode($apiKey) . "&action=" . urlencode($action) . "&search=" . urlencode($searchParameter);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $targetUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        return ['success' => false, 'message' => 'API Gateway connectivity disruption.'];
    }
    
    return json_decode($response, true);
}
2. Windows API Router Gateway (api/customer.php)Deployed locally on your intermediate Windows PC via XAMPP to capture cPanel queries, validate API keys, and access the internal MSSQL instance safely using prepared statements:PHP<?php
/**
 * ASB Loyalty Portal - Intermediate Windows API Gateway Node
 * Handles secure parameters passing to Microsoft SQL Server via native drivers.
 */
header('Content-Type: application/json');

define('EXPECTED_API_KEY', 'ASB_GATEWAY_AUTHENTICATION_SECRET_TOKEN_2026');
define('ALLOWED_CPANEL_IP', 'YOUR_LINUX_HOST_IP_ADDRESS'); // Optional server-level validation boundary

// 1. Structural Security Checklist
$providedKey = $_GET['key'] ?? '';
if (!hash_equals(EXPECTED_API_KEY, $providedKey)) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Cryptographic Security Token Mismatch."]);
    exit;
}

$searchQuery = $_GET['search'] ?? '';
if (empty($searchQuery)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Empty Search Execution String Parameter."]);
    exit;
}

// 2. Establish Native Database Connection Hook
$serverName = "INTERNAL-SQL-SERVER\\SQLEXPRESS";
$connectionParameters = [
    "Database" => "ASB_Loyalty_DB",
    "Uid" => "asb_portal_connector",
    "PWD" => "StrongSubnetPassword2026!",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionParameters);
if (!$conn) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Internal Storage Target Connection Interruption."]);
    exit;
}

// 3. Process Prepared Safe Extraction Queries
$tsql = "SELECT TOP 1 CM_CODE, CM_NAME, CM_MOBILE, CM_NIC 
         FROM M_TBLCUSTOMERS 
         WHERE CM_CODE = ? OR CM_MOBILE = ? OR CM_NIC = ?";

$params = [$searchQuery, $searchQuery, $searchQuery];
$stmt = sqlsrv_query($conn, $tsql, $params);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Data Core Processing Error."]);
    exit;
}

if ($customer = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Collect points totals safely using optimized indexes
    $pointsQuery = "SELECT SUM(POINTS_EARNED) as TotalEarned, SUM(POINTS_REDEEMED) as TotalRedeemed 
                    FROM U_TBLLOYALTY_POINTS WHERE POINT_MEMBER = ?";
    $pointsStmt = sqlsrv_query($conn, $pointsQuery, [$customer['CM_CODE']]);
    $pointsData = sqlsrv_fetch_array($pointsStmt, SQLSRV_FETCH_ASSOC);
    
    $earned = $pointsData['TotalEarned'] ?? 0.00;
    $redeemed = $pointsData['TotalRedeemed'] ?? 0.00;
    $available = $earned - $redeemed;

    echo json_encode([
        "success" => true,
        "customer" => [
            "CM_CODE" => trim($customer['CM_CODE']),
            "CM_NAME" => trim($customer['CM_NAME']),
            "AVAILABLE_POINTS" => (float)$available
        ]
    ]);
} else {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Customer details not matching record index."]);
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

🎨 User Interface & Interaction Guide

The front-end design relies on a premium, clean layout with smooth user tracking motions:PropertyValue ConfigurationApplied ScopePrimary Theme Color#dc2626 (Red-600)High-end Luxury Fashion Brand Identity AccentsCanvas Background#ffffff / #f8fafcLow-fatigue white minimal slate layoutCard Highlight Panel#fef2f2 (Red-50)Background frames for active customer dashboard statisticsTypography SpecInter, Sans-SerifHigh-legibility text alignment across mobile screensCore Verification Sequence MappingLanding View: Customer reaches index page and supplies either their Code, Mobile number, or NIC sequence.Gateway Evaluation: Request hits cPanel, triggers an outbound cURL packet over dedicated network paths to port forward mapping addresses on the local SLT Fiber Router router (Ports 80 / 8080).Dashboard Hydration: Gateway reviews MSSQL parameters, aggregates computational sums, and builds a clean JSON object for the Tailwind UI engine to display instantly without reloading the shell page.

🚀 Forward Architectural Roadmap

To support scale increases and minimize system load across the operational tracking pipeline, the following modular upgrades are scheduled:
[ v1.0.0 Stable ] ➔ Direct MSSQL Parameterized Query Routing & Customer Query Layouts
        ↓
[ v1.1.0 Build  ] ➔ Ledger Aggregation Records, Detailed Member Dashboards, History Tables
        ↓
[ v1.2.0 Build  ] ➔ OTP Verification Pipelines, Mobile Registrations, Verification Keys
        ↓
[ v2.0.0 Master ] ➔ Redis Enterprise Caching Core, JWT Authorization Chains, Native Mobile Applications

High-Volume Performance Modifications

Pre-Calculated Totals (v1.1.0):Replace raw runtime SUM() aggregates with a calculated database summary ledger (LOYALTY_BALANCE) reducing extraction execution overhead below 20ms.

Redis Caching Injection (v2.0.0): Build an index query intercept layer on the intermediate server. If matching profiles reside inside cache storage memory, return it instantly to cut active physical query pipelines to the main SQL Server instance by 80%.

📄 Licensing & Verification Parameters

Project Reference: ASB Fashion Loyalty Portal
Target Marketplace: Retail Loyalty Management Core System
Deployment Region: Sri Lanka
Operating License: Private Core Configuration – Internal Distribution Only
