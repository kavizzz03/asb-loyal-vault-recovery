<?php
require_once 'config.php';

// Route back unauthorized actors attempting access
if (!isset($_SESSION['authenticated_user']) || $_SESSION['authenticated_user'] !== true) {
    header("Location: login.php");
    exit;
}

$customer = $_SESSION['customer_profile'];

$earned = (float)($customer['POINTS_ADDED'] ?? 0);
$redeemed = (float)($customer['POINTS_DEDUCTED'] ?? 0);
$available = $earned - $redeemed;
if ($available < 0) { $available = 0; }

$address = implode(', ', array_filter([
    $customer['CM_ADD1'] ?? '',
    $customer['CM_ADD2'] ?? '',
    $customer['CM_ADD3'] ?? '',
    $customer['CM_ADD4'] ?? ''
]));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>ASB Loyal Vault | Customer Portal</title>
     <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Premium Typography & FontAwesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-sub: #ffffff;
            --bg-card: #ffffff;
            --primary: #e11d48;
            --primary-hover: #be123c;
            --primary-glow: rgba(225, 29, 72, 0.12);
            --border-color: rgba(0, 0, 0, 0.06);
            --border-hover: rgba(225, 29, 72, 0.25);
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent-green: #10b981;
            --accent-amber: #d97706;
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
            overflow-x: hidden;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* Ambient Crimson Subtle Light Glow Layers */
        body::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(225, 29, 72, 0.04) 0%, rgba(248, 250, 252, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -5%;
            right: -5%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(225, 29, 72, 0.02) 0%, rgba(248, 250, 252, 0) 75%);
            z-index: -1;
            pointer-events: none;
        }

        .dashboard-wrapper {
            width: 100%;
            max-width: 1300px;
            margin: 0 auto;
            padding: 2.5rem 2rem 5rem;
            animation: revealUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes revealUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* --- NAVBAR HEADER --- */
        .navbar-premium {
            background: var(--bg-sub);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 1.25rem 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            gap: 24px;
        }

        .brand-cluster {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .brand-logo-img {
            height: 48px;
            max-width: 180px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .brand-text-identity {
            border-left: 1px solid rgba(0, 0, 0, 0.08);
            padding-left: 20px;
        }

        .brand-title-main {
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
            color: var(--text-main);
            line-height: 1.2;
        }

        .brand-title-main span {
            color: var(--primary);
        }

        .brand-subtitle-sub {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--text-muted);
            font-weight: 700;
            margin-top: 2px;
        }

        .brand-subtitle-sub em {
            font-family: 'Playfair Display', serif;
            text-transform: none;
            letter-spacing: 1px;
            font-style: italic;
            font-weight: 600;
            color: var(--accent-amber);
            font-style: italic;
        }

        .user-action-cluster {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .welcome-back-text {
            text-align: right;
        }

        .welcome-back-text p {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .welcome-back-text h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
            margin-top: 2px;
        }

        .logout-action-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.8rem 1.6rem;
            color: var(--text-main);
            background: rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border-color);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
            border-radius: 16px;
            transition: var(--transition-smooth);
        }

        .logout-action-pill:hover {
            background: var(--primary);
            border-color: var(--primary);
            color: #ffffff;
            box-shadow: 0 10px 20px var(--primary-glow);
            transform: translateY(-2px);
        }

        /* --- PREMIUM CARD COMPONENT --- */
        .premium-card {
            background: var(--bg-sub);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            transition: var(--transition-smooth);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.015);
        }

        .premium-card:hover {
            border-color: var(--border-hover);
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.04), 0 0 30px rgba(225, 29, 72, 0.01);
        }

        /* --- STATS METRICS GRID --- */
        .metrics-panel-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 2.5rem;
        }

        .metric-data-card {
            padding: 2.25rem;
            background: linear-gradient(145deg, var(--bg-sub) 0%, #fdfdfd 100%);
        }

        .metric-top-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .metric-label-title {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .metric-icon-avatar {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: var(--transition-smooth);
        }
        
        .type-earned { background: rgba(16, 185, 129, 0.08); color: var(--accent-green); border: 1px solid rgba(16, 185, 129, 0.15); }
        .type-redeemed { background: rgba(217, 119, 6, 0.08); color: var(--accent-amber); border: 1px solid rgba(217, 119, 6, 0.15); }
        .type-available { background: rgba(225, 29, 72, 0.06); color: var(--primary); border: 1px solid rgba(225, 29, 72, 0.15); }

        .metric-data-card:hover .metric-icon-avatar.type-earned { background: var(--accent-green); color: #fff; }
        .metric-data-card:hover .metric-icon-avatar.type-redeemed { background: var(--accent-amber); color: #fff; }
        .metric-data-card:hover .metric-icon-avatar.type-available { background: var(--primary); color: #fff; box-shadow: 0 6px 15px var(--primary-glow); }

        .metric-main-value {
            font-size: clamp(2rem, 2.5vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1.1;
            color: var(--text-main);
            margin-bottom: 0.75rem;
        }

        .metric-active-value {
            background: linear-gradient(135deg, var(--text-main) 40%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .metric-sub-caption {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* --- ANALYTICS SPLIT ROW --- */
        .split-analytics-row {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 20px;
            margin-bottom: 2.5rem;
        }

        .card-padded-box {
            padding: 2.5rem;
        }

        .component-title-header {
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-main);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 1.25rem;
        }

        .component-title-header i {
            color: var(--primary);
        }

        /* Profile Structural Alignment */
        .profile-table-array {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .profile-data-row {
            display: flex;
            align-items: center;
            padding: 1.1rem 1.4rem;
            background: rgba(0, 0, 0, 0.015);
            border: 1px solid transparent;
            border-radius: 16px;
            transition: var(--transition-smooth);
            gap: 20px;
        }

        .profile-data-row:hover {
            background: rgba(0, 0, 0, 0.03);
            border-color: rgba(225, 29, 72, 0.15);
            padding-left: 1.75rem;
        }

        .profile-data-label {
            width: 35%;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .profile-data-label i {
            color: var(--text-muted);
            font-size: 0.95rem;
            transition: var(--transition-smooth);
        }

        .profile-data-row:hover .profile-data-label i {
            color: var(--primary);
            transform: scale(1.1);
        }

        .profile-data-value {
            width: 65%;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            word-break: break-word;
        }

        /* Graphical Chart Interface */
        .chart-rendering-canvas {
            position: relative;
            min-height: 310px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* --- PRIVILEGES GRID MODULE --- */
        .privileges-grid-system {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .privilege-node {
            padding: 2.25rem 2rem;
            background: rgba(0, 0, 0, 0.015);
            border: 1px solid transparent;
            border-radius: 20px;
            transition: var(--transition-smooth);
        }

        .privilege-node:hover {
            border-color: rgba(225, 29, 72, 0.15);
            background: rgba(0, 0, 0, 0.03);
        }

        .privilege-icon-shield {
            width: 50px;
            height: 50px;
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-size: 1.25rem;
            transition: var(--transition-smooth);
        }

        .privilege-node:hover .privilege-icon-shield {
            background: var(--primary);
            color: #ffffff;
            transform: rotate(-10deg) scale(1.05);
            box-shadow: 0 6px 15px var(--primary-glow);
        }

        .privilege-node h4 {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
            color: var(--text-main);
        }

        .privilege-node p {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* --- NOTIFICATION BANNERS --- */
        .system-error-banner {
            background: rgba(225, 29, 72, 0.06);
            border: 1px solid rgba(225, 29, 72, 0.2);
            border-left: 4px solid var(--primary);
            border-radius: 16px;
            padding: 1.1rem 1.6rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #9f1239;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .system-toast-alert {
            position: fixed;
            bottom: 40px;
            right: 40px;
            background: #ffffff;
            color: var(--text-main);
            padding: 18px 28px;
            border-radius: 18px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 1000;
            opacity: 0;
            transform: translateY(15px) scale(0.95);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1px solid var(--border-color);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }

        .system-toast-alert.trigger-show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        /* --- FOOTER SPECIFICATIONS --- */
        .system-footer-container {
            margin-top: 6rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.88rem;
            color: var(--text-muted);
            font-weight: 500;
            gap: 20px;
        }

        .developer-signature-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.02);
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            font-weight: 700;
            font-size: 0.82rem;
        }

        /* ==========================================================================
           RESPONSIVE VIEWPORT ARCHITECTURE
           ========================================================================== */
        
        @media (max-width: 1200px) {
            .metrics-panel-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .metrics-panel-grid > div:last-child {
                grid-column: span 2;
            }
            .privileges-grid-system {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 992px) {
            .split-analytics-row {
                grid-template-columns: 1fr;
            }
            .navbar-premium {
                padding: 1.25rem 1.75rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-wrapper {
                padding: 1.5rem 1.25rem 4rem;
            }
            
            .navbar-premium {
                flex-direction: column;
                gap: 20px;
                text-align: center;
                padding: 2rem 1.5rem;
                border-radius: 20px;
            }
            
            .brand-cluster {
                flex-direction: column;
                gap: 14px;
            }
            
            .brand-text-identity {
                border-left: none;
                padding-left: 0;
            }
            
            .user-action-cluster {
                flex-direction: column;
                gap: 16px;
                width: 100%;
            }
            
            .welcome-back-text {
                text-align: center;
            }
            
            .logout-action-pill {
                width: 100%;
                justify-content: center;
                padding: 0.9rem;
            }

            .metrics-panel-grid {
                grid-template-columns: 1fr;
            }
            .metrics-panel-grid > div:last-child {
                grid-column: span 1;
            }

            .privileges-grid-system {
                grid-template-columns: 1fr;
            }

            .card-padded-box {
                padding: 1.75rem;
            }

            .profile-data-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
                padding: 1.1rem;
            }
            
            .profile-data-label, 
            .profile-data-value {
                width: 100%;
            }

            .system-footer-container {
                flex-direction: column;
                text-align: center;
                margin-top: 4rem;
                gap: 18px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    
    <!-- Dynamic Error Warning Bar -->
    <div id="globalAlert" style="display: none;" class="system-error-banner">
        <i class="fas fa-triangle-exclamation"></i> <span id="alertMessage"></span>
    </div>
    
    <!-- PREMIUM LOGO HEADER -->
    <header class="navbar-premium">
        <div class="brand-cluster">
            <img src="logo.png" alt="ASB Logo" class="brand-logo-img" onerror="this.style.display='none';">
            <div class="brand-text-identity">
                <div class="brand-title-main">ASB <span>Loyal Vault</span></div>
                <div class="brand-subtitle-sub">Fashion & <em>Glamour</em></div>
            </div>
        </div>
        
        <div class="user-action-cluster">
            <div class="welcome-back-text">
                <p>Authenticated Member</p>
                <h4 id="customerNamePlaceholder">Loading Session...</h4>
            </div>
            <a href="logout.php" class="logout-action-pill" id="logoutBtn">
                <i class="fas fa-right-from-bracket"></i> Sign Out
            </a>
        </div>
    </header>

    <!-- Metrics Monitoring Field -->
    <div class="metrics-panel-grid" id="statsContainer">
        <!-- Filled dynamically by core execution engine -->
    </div>

    <!-- Data Layout Matrix Row -->
    <div class="split-analytics-row">
        
        <!-- Profile Identity Sheet Card -->
        <div class="premium-card card-padded-box">
            <div class="component-title-header">
                <i class="fas fa-address-card"></i> Personal Identity Verification
            </div>
            <div id="profileDetails" class="profile-table-array">
                <!-- Filled dynamically by document object mapper -->
            </div>
        </div>

        <!-- Distribution Ratio Graphical Engine -->
        <div class="premium-card card-padded-box">
            <div class="component-title-header">
                <i class="fas fa-chart-pie"></i> Ledger Distribution Balance
            </div>
            <div class="chart-rendering-canvas">
                <canvas id="pointsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Exclusive Store Perks Module -->
    <div class="premium-card card-padded-box" style="margin-bottom: 1rem;">
        <div class="component-title-header">
            <i class="fas fa-wand-magic-sparkles"></i> Connected Vault Benefits
        </div>
        <div class="privileges-grid-system">
            <div class="privilege-node">
                <div class="privilege-icon-shield"><i class="fas fa-tags"></i></div>
                <h4>2% Store Cashback</h4>
                <p>Accumulate a baseline of 2% reward volume back into your vault on transactions completed across both ASB Fashion and ASB Glamour chains.</p>
            </div>
            <div class="privilege-node">
                <div class="privilege-icon-shield"><i class="fas fa-bolt-lightning"></i></div>
                <h4>Real-Time Redemptions</h4>
                <p>Instantly convert verified ledger balances directly to immediate line-item purchase reductions directly at physical checkouts.</p>
            </div>
            <div class="privilege-node">
                <div class="privilege-icon-shield"><i class="fas fa-ticket"></i></div>
                <h4>Collection Access</h4>
                <p>Unlock priority notification pipelines and digital passes providing exclusive reservation options for newly arriving seasonal lookbooks.</p>
            </div>
        </div>
    </div>

    <!-- System Exec Footer Specifications -->
    <footer class="system-footer-container">
        <p>&copy; 2026 ASB Fashion. All rights reserved.</p>
        <div class="developer-signature-badge">
            <i class="fas fa-code-branch" style="margin-right: 6px;"></i> Powered by Vexel IT by Kavizz
        </div>
    </footer>
</div>

<!-- Global System Feedback Node -->
<div id="liveToast" class="system-toast-alert">
    <i class="fas fa-circle-check" style="color: var(--accent-green)"></i> 
    <span id="toastText">Secure data pathway initialized</span>
</div>

<script>
    let customerData = null;
    let pointsEarned = 0, pointsRedeemed = 0, pointsAvailable = 0;
    
    try {
        const phpCustomerProfile = {
            CM_TITLE: '<?= htmlspecialchars($customer['CM_TITLE'] ?? 'Mr.', ENT_QUOTES, 'UTF-8') ?>',
            CM_NAME: '<?= htmlspecialchars($customer['CM_NAME'] ?? 'Loyal Member', ENT_QUOTES, 'UTF-8') ?>',
            CM_CODE: '<?= htmlspecialchars($customer['CM_CODE'] ?? 'ASB000', ENT_QUOTES, 'UTF-8') ?>',
            CM_NIC: '<?= htmlspecialchars($customer['CM_NIC'] ?? 'XXXXXXXXX', ENT_QUOTES, 'UTF-8') ?>',
            CM_MOBILE: '<?= htmlspecialchars($customer['CM_MOBILE'] ?? '+94XXXXXXXX', ENT_QUOTES, 'UTF-8') ?>',
            CM_DOB: '<?= !empty($customer['CM_DOB']) ? date('d-m-Y', strtotime($customer['CM_DOB'])) : 'Not Provided' ?>',
            CM_ADD1: '<?= htmlspecialchars($customer['CM_ADD1'] ?? '', ENT_QUOTES, 'UTF-8') ?>',
            CM_ADD2: '<?= htmlspecialchars($customer['CM_ADD2'] ?? '', ENT_QUOTES, 'UTF-8') ?>',
            CM_ADD3: '<?= htmlspecialchars($customer['CM_ADD3'] ?? '', ENT_QUOTES, 'UTF-8') ?>',
            CM_ADD4: '<?= htmlspecialchars($customer['CM_ADD4'] ?? '', ENT_QUOTES, 'UTF-8') ?>'
        };
        
        pointsEarned = parseFloat(<?= json_encode($earned) ?>) || 0;
        pointsRedeemed = parseFloat(<?= json_encode($redeemed) ?>) || 0;
        pointsAvailable = parseFloat(<?= json_encode($available) ?>) || 0;
        
        customerData = phpCustomerProfile;
    } catch(e) {
        console.error("Data pipeline instantiation failure", e);
        showErrorAlert("System error compiling application state tables.");
        customerData = { CM_TITLE: '', CM_NAME: 'Guest User', CM_CODE: '--', CM_NIC: '--', CM_MOBILE: '--', CM_DOB: '--' };
    }

    function showErrorAlert(msg) {
        const alertDiv = document.getElementById('globalAlert');
        const alertMsg = document.getElementById('alertMessage');
        if(alertDiv && alertMsg) {
            alertMsg.innerText = msg;
            alertDiv.style.display = 'flex';
        }
    }

    function triggerSystemToast(msg) {
        const toastEl = document.getElementById('liveToast');
        const textSpan = document.getElementById('toastText');
        if(toastEl && textSpan) {
            textSpan.innerText = msg;
            toastEl.classList.add('trigger-show');
            setTimeout(() => { toastEl.classList.remove('trigger-show'); }, 3500);
        }
    }

    function processMetricsOutput() {
        const container = document.getElementById('statsContainer');
        if(!container) return;
        container.innerHTML = `
            <div class="premium-card metric-data-card">
                <div class="metric-top-row">
                    <span class="metric-label-title">Total Points Earned</span>
                    <div class="metric-icon-avatar type-earned"><i class="fas fa-circle-plus"></i></div>
                </div>
                <div class="metric-main-value">${pointsEarned.toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})}</div>
                <div class="metric-sub-caption"><i class="fas fa-chart-line"></i> Lifetime accumulated volume</div>
            </div>
            <div class="premium-card metric-data-card">
                <div class="metric-top-row">
                    <span class="metric-label-title">Total Points Redeemed</span>
                    <div class="metric-icon-avatar type-redeemed"><i class="fas fa-circle-minus"></i></div>
                </div>
                <div class="metric-main-value">${pointsRedeemed.toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})}</div>
                <div class="metric-sub-caption"><i class="fas fa-arrow-right-arrow-left"></i> Settled adjustments balance</div>
            </div>
            <div class="premium-card metric-data-card">
                <div class="metric-top-row">
                    <span class="metric-label-title">Current Available Balance</span>
                    <div class="metric-icon-avatar type-available"><i class="fas fa-vault"></i></div>
                </div>
                <div class="metric-main-value metric-active-value">${pointsAvailable.toLocaleString(undefined, {minimumFractionDigits: 1, maximumFractionDigits: 1})}</div>
                <div class="metric-sub-caption" style="color: var(--primary); font-weight: 700;"><i class="fas fa-shield-check"></i> Active fluid invoice value</div>
            </div>
        `;
    }

    function processProfileOutput() {
        const profileDiv = document.getElementById('profileDetails');
        const placeholder = document.getElementById('customerNamePlaceholder');
        if(placeholder && customerData) {
            placeholder.innerText = `${customerData.CM_TITLE} ${customerData.CM_NAME}`.trim();
        }
        if(!profileDiv) return;
        
        const addressBlock = [customerData.CM_ADD1, customerData.CM_ADD2, customerData.CM_ADD3, customerData.CM_ADD4].filter(v => v && v.trim() !== '').join(', ');
        
        profileDiv.innerHTML = `
            <div class="profile-data-row"><div class="profile-data-label"><i class="fas fa-hashtag"></i> ID Index</div><div class="profile-data-value">${customerData.CM_CODE}</div></div>
            <div class="profile-data-row"><div class="profile-data-label"><i class="fas fa-id-card-clip"></i> National ID</div><div class="profile-data-value">${customerData.CM_NIC}</div></div>
            <div class="profile-data-row"><div class="profile-data-label"><i class="fas fa-mobile-screen"></i> Mobile Connection</div><div class="profile-data-value">${customerData.CM_MOBILE}</div></div>
            <div class="profile-data-row"><div class="profile-data-label"><i class="fas fa-calendar"></i> Birth Registration</div><div class="profile-data-value">${customerData.CM_DOB}</div></div>
            <div class="profile-data-row"><div class="profile-data-label"><i class="fas fa-location-dot"></i> Street Address</div><div class="profile-data-value">${addressBlock || 'No address attributes recorded'}</div></div>
        `;
    }

    let dynamicChartInstance = null;
    function renderChartInstance() {
        const canvas = document.getElementById('pointsChart');
        if(!canvas) return;
        const ctx = canvas.getContext('2d');
        if(dynamicChartInstance) dynamicChartInstance.destroy();
        
        try {
            dynamicChartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Earned Volume', 'Settled Adjustments', 'Active Balance'],
                    datasets: [{
                        data: [pointsEarned, pointsRedeemed, pointsAvailable],
                        backgroundColor: ['#10b981', '#fbbf24', '#e11d48'],
                        borderWidth: 0,
                        cutout: '76%',
                        borderRadius: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#64748b',
                                font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
                                padding: 24,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            padding: 14,
                            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: '700' },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                            backgroundColor: '#ffffff',
                            titleColor: '#0f172a',
                            bodyColor: '#334155',
                            borderColor: 'rgba(0,0,0,0.06)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.label}: ${context.raw.toLocaleString()} pts`;
                                }
                            }
                        }
                    }
                }
            });
        } catch(e) {
            console.error("Chart infrastructure drawing error", e);
        }
    }

    window.addEventListener('resize', () => {
        if(dynamicChartInstance) {
            dynamicChartInstance.resize();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        processMetricsOutput();
        processProfileOutput();
        renderChartInstance();
        triggerSystemToast("Ledger instances securely loaded.");
    });

    document.getElementById('logoutBtn')?.addEventListener('click', () => {
        triggerSystemToast("Terminating active session...");
    });
</script>
</body>
</html>