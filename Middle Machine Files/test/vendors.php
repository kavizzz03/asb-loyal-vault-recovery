<?php
session_start();
include 'db.php';

/* ================= MYSQL ================= */
$mysqli = new mysqli("127.0.0.1", "root", "", "return_qc");

if ($mysqli->connect_error) {
    die("MySQL Connection Failed");
}

$mysqli->set_charset("utf8mb4");

/* ================= LOGOUT ================= */
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: vendors.php");
    exit;
}

/* ================= LOGIN ================= */
$error = "";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $mysqli->prepare("SELECT password FROM admin_users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($db_pass);
    $stmt->fetch();

    if ($db_pass && $password === $db_pass) {
        $_SESSION['admin'] = $username;
        header("Location: vendors.php");
        exit;
    } else {
        $error = "❌ Invalid username or password";
    }
}

/* ================= PHONE CLEANER ================= */
function extractBestPhone($raw) {
    if ($raw === null || trim($raw) === '') return "";

    $parts = preg_split('/[\/,;|\s]+/', $raw);
    $mobiles = [];
    $valid = [];

    foreach ($parts as $p) {
        $p = preg_replace('/[^0-9]/', '', $p);
        if ($p === '') continue;

        if (preg_match('/^0[0-9]{9}$/', $p)) {
            $p = "94" . substr($p, 1);
        } elseif (preg_match('/^[0-9]{9}$/', $p)) {
            $p = "94" . $p;
        } elseif (!preg_match('/^94[0-9]{9}$/', $p)) {
            continue;
        }

        $valid[] = $p;

        if (preg_match('/^947[0-9]{8}$/', $p)) {
            $mobiles[] = $p;
        }
    }

    return $mobiles[0] ?? ($valid[0] ?? "");
}

/* ================= INIT ================= */
$inserted = 0;
$skipped = 0;
$errors = [];

/* ================= SYNC ================= */
if (isset($_POST['sync'])) {
    $sql = "SELECT VM_CODE, VM_DESC, VM_TELEPHONE1, VM_EMAIL,
                   VM_ADD1, VM_ADD2, VM_ADD3, VM_ADD4
            FROM M_TBLVENDORS";

    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die("SQL Server Query Failed");
    }

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $system_id = trim($row['VM_CODE'] ?? '');
        if ($system_id === '') continue;

        $name  = trim($row['VM_DESC'] ?? '');
        $email = trim($row['VM_EMAIL'] ?? '');
        $phone = extractBestPhone($row['VM_TELEPHONE1'] ?? '');

        $address = trim(
            ($row['VM_ADD1'] ?? '') . ' ' .
            ($row['VM_ADD2'] ?? '') . ' ' .
            ($row['VM_ADD3'] ?? '') . ' ' .
            ($row['VM_ADD4'] ?? '')
        );

        /* check duplicate */
        $check = $mysqli->prepare("SELECT supplier_id FROM suppliers WHERE system_id=?");
        $check->bind_param("s", $system_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $skipped++;
            continue;
        }

        /* insert */
        $insert = $mysqli->prepare("
            INSERT INTO suppliers
            (supplier_name, system_id, contact_number, email, address)
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->bind_param("sssss", $name, $system_id, $phone, $email, $address);

        if (!$insert->execute()) {
            $errors[] = "Insert failed: $system_id - " . $insert->error;
            continue;
        }

        $inserted++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>ASB Fashion | Supplier Import</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f8;
            overflow-x: hidden;
        }

        /* Dashboard Layout */
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR - RED THEME ========== */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #991b1b 0%, #7f1d1d 50%, #450a0a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.15);
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 10px;
        }

        .sidebar-header {
            padding: 28px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.12);
            margin-bottom: 10px;
            position: relative;
        }

        .sidebar-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 20%;
            width: 60%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #fbbf24, transparent);
        }

        .sidebar-header h2 {
            font-size: 1.7rem;
            font-weight: 700;
            background: linear-gradient(135deg, #fff, #fecaca);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .sidebar-header h2 i {
            background: none;
            color: #fbbf24;
            margin-right: 8px;
        }

        .sidebar-header p {
            font-size: 0.7rem;
            opacity: 0.8;
            letter-spacing: 1px;
            margin-top: 6px;
        }

        .user-profile {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.2);
            margin: 10px 15px;
            border-radius: 20px;
            transition: all 0.3s;
        }

        .user-profile:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #7f1a1a;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        .user-info h4 {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .user-info p {
            font-size: 0.7rem;
            opacity: 0.8;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 20px 12px;
        }

        .nav-section {
            margin-bottom: 25px;
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.5);
            padding: 0 15px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            margin: 4px 0;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 14px;
            position: relative;
            overflow: hidden;
        }

        .nav-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: #fbbf24;
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .nav-item:hover::before,
        .nav-item.active::before {
            transform: scaleY(1);
        }

        .nav-item:hover {
            background: rgba(255,255,255,0.12);
            color: white;
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(255,255,255,0.15);
            color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        .nav-item i {
            width: 24px;
            font-size: 1.1rem;
            text-align: center;
        }

        .nav-item span {
            font-size: 0.85rem;
            font-weight: 500;
        }

        .sidebar-footer {
            padding: 20px 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(180deg, transparent, rgba(0,0,0,0.2));
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.9);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 14px;
            transition: all 0.3s;
            background: rgba(220,38,38,0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .logout-btn:hover {
            background: #dc2626;
            color: white;
            transform: translateX(5px);
            border-color: transparent;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 25px 30px;
            min-height: 100vh;
            background: #f0f2f8;
            transition: all 0.3s ease;
        }

        /* Top Bar */
        .top-bar {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 18px 28px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid rgba(255,255,255,0.5);
        }

        .page-title h1 {
            font-size: 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e293b, #7f1d1d);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .page-title p {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .date-badge {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            padding: 8px 20px;
            border-radius: 40px;
            color: #dc2626;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(220,38,38,0.1);
        }

        /* Import Card */
        .import-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
            max-width: 800px;
            margin: 0 auto;
        }

        .import-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .import-header i {
            font-size: 3rem;
            color: #dc2626;
            margin-bottom: 1rem;
        }

        .import-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .import-header p {
            color: #6b7280;
            font-size: 0.875rem;
        }

        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        .info-box i {
            color: #10b981;
            margin-right: 8px;
        }

        .info-box ul {
            margin-top: 8px;
            margin-left: 20px;
            color: #374151;
            font-size: 0.8rem;
        }

        .info-box li {
            margin: 4px 0;
        }

        .sync-btn {
            width: 100%;
            background: #dc2626;
            color: white;
            padding: 1rem;
            border: none;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sync-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220,38,38,0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
        }

        .stat-label {
            font-size: 0.7rem;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .success-bg {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        }
        
        .warning-bg {
            background: linear-gradient(135deg, #fffbeb, #fef3c7);
        }
        
        .error-bg {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
        }

        /* Error Log */
        .error-log {
            background: #fef2f2;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            max-height: 200px;
            overflow-y: auto;
        }

        .error-log h4 {
            color: #dc2626;
            font-size: 0.8rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-log ul {
            margin-left: 1.5rem;
            color: #991b1b;
            font-size: 0.75rem;
        }

        .error-log li {
            margin: 4px 0;
        }

        /* Login Box */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #020617 100%);
        }

        .login-box {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .login-box h2 {
            color: #1f2937;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .login-box .subtitle {
            color: #6b7280;
            font-size: 0.8rem;
            margin-bottom: 1.5rem;
        }

        .input-group {
            margin-bottom: 1rem;
        }

        .input-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-family: inherit;
            transition: all 0.2s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220,38,38,0.1);
        }

        .login-btn {
            width: 100%;
            background: #dc2626;
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-btn:hover {
            background: #b91c1c;
            transform: translateY(-2px);
        }

        .error-message {
            background: #fef2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        /* Footer */
        .footer {
            background: white;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 200;
            background: #dc2626;
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            cursor: pointer;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 90;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .mobile-menu-toggle {
                display: flex;
            }
            .top-bar {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 15px;
            }
            .import-card {
                padding: 1.5rem;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['admin'])) { ?>
    <!-- LOGIN SCREEN -->
    <div class="login-container">
        <div class="login-box">
            <div style="text-align: center; margin-bottom: 1rem;">
                <i class="fas fa-tshirt" style="font-size: 3rem; color: #dc2626;"></i>
            </div>
            <h2>ASB Admin Login</h2>
            <p class="subtitle">Supplier Import Portal</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" name="username" placeholder="Enter username" required>
                </div>
                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="Enter password" required>
                </div>
                <button type="submit" name="login" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
        </div>
    </div>
    <?php exit; 
} ?>

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle" id="mobileMenuToggle">
    <i class="fas fa-bars"></i>
</button>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-tshirt"></i> ASB</h2>
            <p>QUALITY CONTROL & RETURNS</p>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-info">
                <h4><?= htmlspecialchars($_SESSION['admin']) ?></h4>
                <p><i class="fas fa-tag"></i> Administrator</p>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">
                    <i class="fas fa-chart-line"></i> MAIN
                </div>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i> <span>Dashboard</span>
                </a>
                <a href="suppliers.php" class="nav-item">
                    <i class="fas fa-truck"></i> <span>Suppliers</span>
                </a>
                <a href="suppliers_import.php" class="nav-item active">
                    <i class="fas fa-database"></i> <span>Supplier Import</span>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">
                    <i class="fas fa-microscope"></i> QUALITY CONTROL
                </div>
                <a href="qc_modes.php" class="nav-item">
                    <i class="fas fa-cog"></i> <span>QC Modes</span>
                </a>
                <a href="aql.php" class="nav-item">
                    <i class="fas fa-chart-bar"></i> <span>AQL</span>
                </a>
                <a href="inspections.php" class="nav-item">
                    <i class="fas fa-clipboard-list"></i> <span>Inspections</span>
                </a>
                <a href="flag.php" class="nav-item">
                    <i class="fas fa-flag-checkered"></i> <span>Flag Update Portal</span>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">
                    <i class="fas fa-exchange-alt"></i> RETURNS
                </div>
                <a href="return_reasons.php" class="nav-item">
                    <i class="fas fa-question-circle"></i> <span>Return Reasons</span>
                </a>
                <a href="returns.php" class="nav-item">
                    <i class="fas fa-undo-alt"></i> <span>Returns</span>
                </a>
            </div>
            <div class="nav-section">
                <div class="nav-section-title">
                    <i class="fas fa-building"></i> MANAGEMENT
                </div>
                <a href="products.php" class="nav-item">
                    <i class="fas fa-box"></i> <span>Products</span>
                </a>
                <a href="categories.php" class="nav-item">
                    <i class="fas fa-folder"></i> <span>Categories</span>
                </a>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <a href="?logout=1" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="top-bar">
            <div class="page-title">
                <h1>Supplier Import</h1>
                <p><i class="fas fa-home"></i> Home / Supplier Import</p>
            </div>
            <div class="date-badge">
                <i class="fas fa-calendar-alt"></i> <?= date('F j, Y') ?>
            </div>
        </div>

        <div class="import-card">
            <div class="import-header">
                <i class="fas fa-database"></i>
                <h2>SQL Server → MySQL Sync</h2>
                <p>Import suppliers from SQL Server to MySQL database</p>
            </div>

            <div class="info-box">
                <i class="fas fa-info-circle"></i> <strong>Sync Information:</strong>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Automatically detects duplicate suppliers by System ID</li>
                    <li><i class="fas fa-phone-alt"></i> Phone numbers are formatted to Sri Lankan standard (947XXXXXXXX)</li>
                    <li><i class="fas fa-sync"></i> Existing suppliers will be skipped to avoid duplicates</li>
                </ul>
            </div>

            <form method="POST">
                <button type="submit" name="sync" class="sync-btn">
                    <i class="fas fa-sync-alt"></i> START SYNC
                </button>
            </form>

            <?php if (isset($_POST['sync'])): ?>
                <div class="stats-grid">
                    <div class="stat-card success-bg">
                        <div class="stat-icon"><i class="fas fa-check-circle" style="color: #10b981;"></i></div>
                        <div class="stat-value"><?= number_format($inserted) ?></div>
                        <div class="stat-label">Inserted</div>
                    </div>
                    <div class="stat-card warning-bg">
                        <div class="stat-icon"><i class="fas fa-ban" style="color: #f59e0b;"></i></div>
                        <div class="stat-value"><?= number_format($skipped) ?></div>
                        <div class="stat-label">Skipped (Duplicates)</div>
                    </div>
                    <div class="stat-card error-bg">
                        <div class="stat-icon"><i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i></div>
                        <div class="stat-value"><?= number_format(count($errors)) ?></div>
                        <div class="stat-label">Errors</div>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="error-log">
                        <h4><i class="fas fa-bug"></i> Error Log</h4>
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><i class="fas fa-times-circle"></i> <?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>© <?= date('Y') ?> ASB Fashion - Quality Control & Returns Management System</p>
        </div>
    </main>
</div>

<script>
    // Mobile menu functionality
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('mobileMenuToggle');
    const overlay = document.getElementById('sidebarOverlay');
    
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (sidebar?.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992) {
            closeSidebar();
            sidebar?.classList.remove('open');
            overlay?.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
    
    const navLinks = document.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });
</script>

</body>
</html>