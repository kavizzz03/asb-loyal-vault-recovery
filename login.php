<?php
// Force strict Sri Lanka timezone offsets globally across the runtime landscape
date_default_timezone_set('Asia/Colombo');

ini_set('display_errors', 0); // Turned off for security on live production environments
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config.php';

// Force MySQL runtime environment configuration to match local Sri Lankan time (+05:30)
if (isset($mysql)) {
    $mysql->exec("SET time_zone = '+05:30'");
}

$error = "";
$flash = isset($_SESSION['login_flash_success']) ? $_SESSION['login_flash_success'] : "";
unset($_SESSION['login_flash_success']);

if (isset($_POST['login'])) {
    $identity = trim($_POST['login_identity'] ?? '');
    $password = $_POST['login_password'] ?? '';

    if ($identity !== '' && $password !== '') {
        $formattedMobile = function_exists('formatMobileSriLanka') ? formatMobileSriLanka($identity) : false;
        $user = null;

        try {
            // Index-optimized database queries targeting specific attributes sequentially
            if ($formattedMobile) {
                $stmt = $mysql->prepare("SELECT * FROM users WHERE mobile = ? LIMIT 1");
                $stmt->execute([$formattedMobile]);
                $user = $stmt->fetch();
            }

            if (!$user) {
                $stmt = $mysql->prepare("SELECT * FROM users WHERE customer_code = ? LIMIT 1");
                $stmt->execute([$identity]);
                $user = $stmt->fetch();
            }

            if (!$user) {
                $stmt = $mysql->prepare("SELECT * FROM users WHERE nic = ? LIMIT 1");
                $stmt->execute([$identity]);
                $user = $stmt->fetch();
            }

            // Validate matched credentials and process sessions
            if ($user && password_verify($password, $user['password_hash'])) {
                if (function_exists('fetchMasterCustomerData')) {
                    $masterData = fetchMasterCustomerData($user['customer_code']);
                } else {
                    throw new Exception("Core ERP integration functions missing.");
                }
                
                if ($masterData) {
                    $_SESSION['authenticated_user'] = true;
                    $_SESSION['customer_profile']   = $masterData;
                    $_SESSION['user_id']            = $user['id'];
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Authentication succeeded, but live master ERP records were unreachable.";
                }
            } else {
                $error = "Invalid identity attributes or password combination.";
            }
        } catch (Exception $e) {
            $error = "System exception encountered: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "All credential identification parameters are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>ASB Loyal Vault | Identity Gateway</title>
     <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Premium Google Fonts + FontAwesome System -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        :root {
            --bg-main: #f8fafc;
            --bg-side: #ffffff;
            --bg-card: #ffffff;
            --primary: #e11d48;
            --primary-hover: #be123c;
            --primary-glow: rgba(225, 29, 72, 0.08);
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent-gold: #b45309;
            --trust-green: #16a34a;
            --transition-luxury: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
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
            min-height: 100vh;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ==========================================================================
           DESKTOP ARCHITECTURE (VIEWPORTS > 992px)
           ========================================================================== */
        
        .split-viewport-container {
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            min-height: 100vh;
            width: 100%;
        }

        /* Cinematic Left Side Panel - Light Luxury Edition */
        .desktop-branding-panel {
            background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 5rem 4rem;
            overflow: hidden;
            border-right: 1px solid var(--border-color);
        }

        /* Ambient Glow Layers */
        .desktop-branding-panel::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(225, 29, 72, 0.04) 0%, rgba(0, 0, 0, 0) 70%);
            pointer-events: none;
        }

        .desktop-brand-showcase {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .desktop-logo-wrapper img {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        .brand-divider {
            width: 1px;
            height: 32px;
            background: #cbd5e1;
        }

        .brand-label-text {
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--text-main);
        }

        .brand-label-text span {
            color: var(--primary);
            font-family: 'Playfair Display', serif;
            text-transform: capitalize;
            font-style: italic;
            letter-spacing: 0px;
            font-size: 1rem;
            margin-left: 4px;
        }

        .desktop-promo-hero-wrapper {
            max-width: 480px;
            margin-top: auto;
            margin-bottom: auto;
            animation: revealUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .vault-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(225, 29, 72, 0.06);
            border: 1px solid rgba(225, 29, 72, 0.15);
            color: var(--primary);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2rem;
        }

        .desktop-promo-hero-wrapper h2 {
            font-size: clamp(2.2rem, 3.8vw, 3.2rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1.5px;
            color: #0f172a;
        }

        .desktop-promo-hero-wrapper h2 span {
            background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .desktop-promo-hero-wrapper p {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-top: 1.5rem;
        }

        /* Support Hub Info Blocks */
        .gateway-support-meta {
            margin-top: 2rem;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 1.5rem;
        }

        .support-meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-muted);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .support-meta-item i {
            color: var(--primary);
            width: 16px;
            text-align: center;
        }

        .support-meta-item a {
            color: var(--text-main);
            text-decoration: none;
            transition: var(--transition-luxury);
        }

        .support-meta-item a:hover {
            color: var(--primary);
        }

        .desktop-branding-panel-footer {
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        /* Right Side Form Interaction Panel */
        .auth-interaction-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem 2rem;
            background-color: var(--bg-main);
            position: relative;
        }

        .gateway-form-card {
            width: 100%;
            max-width: 400px;
            animation: revealUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .mobile-only-header {
            display: none;
        }
        
        .mobile-trust-banner {
            display: none;
        }

        /* Form Visual Identity Sub-headers */
        .form-identity-header {
            margin-bottom: 2.25rem;
        }

        .form-identity-header h3 {
            font-size: 1.75rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #0f172a;
        }

        .form-identity-header h3 span {
            color: var(--primary);
        }

        .form-identity-header p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 500;
        }

        /* Input Controls Setup */
        .input-group-field {
            position: relative;
            margin-bottom: 1.25rem;
            width: 100%;
        }

        .input-icon-left {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1rem;
            transition: var(--transition-luxury);
            pointer-events: none;
        }

        .premium-input-box {
            width: 100%;
            padding: 1.1rem 1rem 1.1rem 48px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            color: var(--text-main);
            transition: var(--transition-luxury);
            outline: none;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .premium-input-box::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .premium-input-box:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px var(--primary-glow);
            background: #ffffff;
        }

        .input-group-field:focus-within .input-icon-left {
            color: var(--primary);
        }

        /* Form Submissions Call To Actions */
        .submit-action-btn {
            width: 100%;
            padding: 1.1rem;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition-luxury);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(225, 29, 72, 0.15);
        }

        .submit-action-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(225, 29, 72, 0.25);
        }

        /* Notification Banner Frameworks */
        .system-banner-state {
            border-radius: 14px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            line-height: 1.4;
        }

        .state-error {
            background: #fff5f5;
            border: 1px solid #fee2e2;
            border-left: 4px solid var(--primary);
            color: #991b1b;
        }

        .state-success {
            background: #f0fdf4;
            border: 1px solid #dcfce7;
            border-left: 4px solid #16a34a;
            color: #166534;
        }

        /* Bottom Anchored Nav Controls */
        .navigation-routing-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.85rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .navigation-routing-footer a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition-luxury);
        }

        .navigation-routing-footer a:hover {
            color: var(--primary);
        }

        .route-separator {
            color: #cbd5e1;
        }

        /* Elite Tech Industry Signature Badge */
        .signature-attribution-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            padding: 8px 16px;
            border-radius: 100px;
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 3rem;
            letter-spacing: 0.3px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .signature-attribution-badge strong {
            color: var(--text-main);
            font-weight: 700;
        }

        .signature-attribution-badge i {
            color: var(--primary);
        }

        .mobile-branded-footer {
            display: none;
        }

        @keyframes revealUp {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ==========================================================================
           MOBILE ARCHITECTURE (DEDICATED VISUAL OVERRIDES <= 992px)
           ========================================================================== */
        
        @media (max-width: 992px) {
            body {
                padding: 1.25rem;
                background-color: #f8fafc;
                align-items: center;
            }

            .split-viewport-container {
                display: block;
                min-height: auto;
                width: 100%;
            }

            .desktop-branding-panel {
                display: none;
            }

            .auth-interaction-panel {
                padding: 0;
            }

            .form-identity-header {
                display: none;
            }

            /* Dedicated Floating Card UI Container Blocks */
            .gateway-form-card {
                background: var(--bg-card);
                border: 1px solid var(--border-color);
                border-radius: 24px;
                padding: 2.5rem 1.75rem;
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
                position: relative;
                overflow: hidden;
            }

            /* Dynamic Core Branding System Header For Mobile Systems */
            .mobile-only-header {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                margin-bottom: 1.25rem;
            }

            .mobile-logo-group {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 0.5rem;
            }

            .mobile-logo-group img {
                height: 36px;
                width: auto;
            }

            .mobile-brand-title {
                font-size: 1.45rem;
                font-weight: 800;
                color: #0f172a;
                letter-spacing: -0.5px;
            }

            .mobile-brand-title span {
                color: var(--primary);
            }

            .mobile-vault-tag {
                font-size: 0.65rem;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 2px;
                color: var(--text-muted);
                background: #f1f5f9;
                padding: 4px 10px;
                border-radius: 6px;
                border: 1px solid #e2e8f0;
            }

            /* Trust Indicator Sub-Banner (SSL/Encrypted Shield) */
            .mobile-trust-banner {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                background: #f0fdf4;
                border: 1px solid #bbf7d0;
                border-radius: 12px;
                padding: 10px;
                margin-bottom: 1.75rem;
                text-align: center;
            }

            .mobile-trust-banner i {
                color: var(--trust-green);
                font-size: 0.9rem;
            }

            .mobile-trust-banner span {
                font-size: 0.78rem;
                font-weight: 700;
                color: #166534;
                letter-spacing: 0.2px;
            }

            /* Optimized Touch Interfaces */
            .premium-input-box {
                padding: 1.2rem 1rem 1.2rem 48px;
                font-size: 1rem;
                border-radius: 16px;
            }

            .submit-action-btn {
                padding: 1.2rem;
                font-size: 1rem;
                border-radius: 16px;
                margin-top: 0.5rem;
            }

            .navigation-routing-footer {
                flex-direction: column;
                gap: 14px;
                margin-top: 2.25rem;
            }

            .route-separator {
                display: none;
            }

            .signature-attribution-badge {
                display: none;
            }

            /* Seamless Bottom Adaptive Footer Contexts */
            .mobile-branded-footer {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 16px;
                margin-top: 2.5rem;
                width: 100%;
            }

            .mobile-support-block {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 6px;
                font-size: 0.8rem;
                color: var(--text-muted);
                font-weight: 500;
            }

            .mobile-support-block a {
                color: var(--text-main);
                text-decoration: none;
            }

            /* Compliance & Trust Logo Strip */
            .mobile-security-badges {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 20px;
                padding: 10px 0;
                opacity: 0.45;
                font-size: 1.2rem;
                color: var(--text-main);
                border-top: 1px dashed var(--border-color);
                width: 100%;
                max-width: 280px;
            }

            .mobile-dev-badge {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: #ffffff;
                border: 1px solid var(--border-color);
                padding: 8px 16px;
                border-radius: 100px;
                color: var(--text-muted);
                font-size: 0.75rem;
                font-weight: 600;
                box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            }

            .mobile-dev-badge i {
                color: var(--primary);
            }
            
            .mobile-dev-badge strong {
                color: #0f172a;
            }
        }
    </style>
</head>
<body>

<div class="split-viewport-container">
    
    <!-- ==========================================
         DESKTOP MODE: BRANDING LEFT PANEL
         ========================================== -->
    <aside class="desktop-branding-panel">
        <div class="desktop-brand-showcase">
            <div class="desktop-logo-wrapper">
                <img src="logo.png" alt="ASB Logo" onerror="this.style.visibility='hidden';">
            </div>
            <div class="brand-divider"></div>
            <div class="brand-label-text">ASB Fashion & <span>Glamour</span></div>
        </div>
        
        <div class="desktop-promo-hero-wrapper">
            <div class="vault-status-badge">
                <i class="fas fa-shield-halved"></i> ASB Loyal Vault Active
            </div>
            <h2>The Portal To Elite <span>Privileges</span></h2>
            <p>Step inside our unified operational core. Review personal real time transaction points statements, manage cross branch redemption portfolios, and track absolute priority membership balances.</p>
            
            <!-- Contextual Corporate Contact Details Section -->
            <div class="gateway-support-meta">
                <div class="support-meta-item">
                    <i class="fas fa-phone-volume"></i> Support line: <a href="tel:0719057057">071 905 7057</a>
                </div>
                <div class="support-meta-item">
                    <i class="fas fa-envelope-open-text"></i> Network ops: <a href="mailto:info@asbfashion.com">info@asbfashion.com</a>
                </div>
            </div>
        </div>
        
        <footer class="desktop-branding-panel-footer">
            <span>&copy; 2026 ASB Fashion Network Operations.</span>
        </footer>
    </aside>

    <!-- ==========================================
         RESPONSIVE WORKSPACE: AUTHENTICATION CONTAINER
         ========================================== -->
    <main class="auth-interaction-panel">
        
        <div class="gateway-form-card">
            
            <!-- MOBILE MODE: ADAPTIVE RUNTIME LOGO HEADER -->
            <header class="mobile-only-header">
                <div class="mobile-logo-group">
                    <img src="logo.png" alt="ASB Logo" onerror="this.style.visibility='hidden';">
                    <div class="mobile-brand-title">ASB <span>Loyal Vault</span></div>
                </div>
                <span class="mobile-vault-tag">Fashion & Glamour Gateway</span>
            </header>

            <!-- TRUST METRIC: SECURE SSL SHIELD INSIGNIA (MOBILE VIEW ONLY) -->
            <div class="mobile-trust-banner">
                <i class="fas fa-lock"></i>
                <span>End-to-End Encrypted Session</span>
            </div>

            <!-- DESKTOP MODE: FORM TITLES -->
            <header class="form-identity-header">
                <h3>Sign In to <span>Vault</span></h3>
                <p>Please enter your corporate authorization credentials.</p>
            </header>

            <!-- Alerts System Pipelines -->
            <?php if (!empty($error)): ?>
                <div class="system-banner-state state-error">
                    <i class="fas fa-circle-exclamation" style="flex-shrink: 0;"></i>
                    <span><?= htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($flash)): ?>
                <div class="system-banner-state state-success">
                    <i class="fas fa-circle-check" style="flex-shrink: 0;"></i>
                    <span><?= htmlspecialchars($flash); ?></span>
                </div>
            <?php endif; ?>

            <!-- Core Logic Form Dispatcher -->
            <form method="post" action="login.php" id="secureLoginForm">
                
                <!-- Unified Identity Mapping (Code / Mobile / NIC) -->
                <div class="input-group-field">
                    <i class="fas fa-user-shield input-icon-left"></i>
                    <input 
                        type="text" 
                        name="login_identity" 
                        class="premium-input-box"
                        placeholder="Customer Code / Mobile / NIC" 
                        value="<?= isset($_POST['login_identity']) ? htmlspecialchars($_POST['login_identity']) : ''; ?>"
                        required 
                        autocomplete="username">
                </div>
                
                <!-- Security Cryptographic Key Matching -->
                <div class="input-group-field">
                    <i class="fas fa-lock input-icon-left"></i>
                    <input 
                        type="password" 
                        name="login_password" 
                        class="premium-input-box"
                        placeholder="Security Password" 
                        required
                        autocomplete="current-password">
                </div>
                
                <!-- Action Execution Dispatcher -->
                <button type="submit" name="login" class="submit-action-btn">
                    <span>Access Secure Vault</span> <i class="fas fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <!-- Shared System Navigation Mapping -->
            <nav class="navigation-routing-footer">
                <a href="register.php">Register Account</a>
                <span class="route-separator">&bull;</span>
                <a href="forgot_password.php">Recover Password</a>
            </nav>

            <!-- DESKTOP MODE: SIGNATURE BADGE -->
            <div class="signature-attribution-badge">
                <i class="fas fa-code-branch"></i> Engine Developed by: <strong>Vexel IT by Kavizz</strong>
            </div>

            <!-- MOBILE MODE: FLOATING FOOTER CONTEXT + TRUST SIGNALS -->
            <footer class="mobile-branded-footer">
                <div class="mobile-support-block">
                    <span>Support: <a href="tel:0719057057">071 905 7057</a></span>
                    <span>Email: <a href="mailto:info@asbfashion.com">info@asbfashion.com</a></span>
                </div>
                
                <!-- Security Token Indicators -->
                <div class="mobile-security-badges">
                    <i class="fab fa-cc-visa" title="Visa Secure"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard Identity Check"></i>
                    <i class="fas fa-shield-cat" title="PCI-DSS Compliant Infrastructure"></i>
                </div>

                <div class="mobile-dev-badge">
                    <i class="fas fa-code-branch"></i> Developed by: <strong>Vexel IT by Kavizz</strong>
                </div>
            </footer>
            
        </div>
    </main>
</div>

<script>
    // Visual processing state transition handlers
    document.getElementById('secureLoginForm')?.addEventListener('submit', function() {
        const btn = this.querySelector('.submit-action-btn');
        if (btn) {
            btn.style.opacity = '0.85';
            btn.innerHTML = '<span>Verifying Identity Credentials...</span> <i class="fas fa-circle-notch fa-spin"></i>';
        }
    });
</script>
</body>
</html>