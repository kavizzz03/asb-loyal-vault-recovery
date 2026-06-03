<?php
require_once 'config.php';

$step = 1; 
$error = "";
$success = "";

if (isset($_SESSION['reg_step'])) {
    $step = $_SESSION['reg_step'];
}

// STEP 1: Search MS SQL Master Database
if (isset($_POST['find_profile']) && $step == 1) {
    $search = trim($_POST['search_text']);
    $customer = fetchMasterCustomerData($search);

    if ($customer) {
        $formattedMobile = formatMobileSriLanka($customer['CM_MOBILE']);
        if (!$formattedMobile) {
            $error = "The master record contains an invalid mobile number configuration.";
        } else {
            // Check if user already exists in Web System
            $stmt = $mysql->prepare("SELECT id FROM users WHERE customer_code = ? OR nic = ? OR mobile = ?");
            $stmt->execute([$customer['CM_CODE'], $customer['CM_NIC'], $formattedMobile]);
            if ($stmt->fetch()) {
                $error = "An account with these details already stands registered. Please use login.";
            } else {
                // Generate and persist OTP profile
                $otp = rand(100000, 999999);
                $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

                $stmt = $mysql->prepare("INSERT INTO otp_requests (mobile, otp_code, purpose, expires_at) VALUES (?, ?, 'register', ?)");
                $stmt->execute([$formattedMobile, $otp, $expiry]);

                // Send OTP
                sendSMSNotification($formattedMobile, "Your ASB Loyalty Registration OTP code is: $otp. Valid for 5 minutes.");

                // Save trace values inside state arrays
                $_SESSION['reg_temp_customer'] = $customer;
                $_SESSION['reg_temp_mobile'] = $formattedMobile;
                $_SESSION['reg_step'] = 2;
                $_SESSION['otp_timestamp'] = time(); // Fixed reference for JS syncing
                $step = 2;
                $success = "An OTP code has been dispatched to your registered phone number ending in: " . substr($formattedMobile, -4);
            }
        }
    } else {
        $error = "No corresponding identity found inside Master Data pools.";
    }
}

// OPTIONAL: Dedicated handling for Resend Requests via Step 2 Form
if (isset($_POST['resend_otp_trigger']) && $step == 2) {
    $formattedMobile = $_SESSION['reg_temp_mobile'] ?? '';
    if (!empty($formattedMobile)) {
        $otp = rand(100000, 999999);
        $expiry = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        $stmt = $mysql->prepare("INSERT INTO otp_requests (mobile, otp_code, purpose, expires_at) VALUES (?, ?, 'register', ?)");
        $stmt->execute([$formattedMobile, $otp, $expiry]);

        sendSMSNotification($formattedMobile, "Your NEW ASB Loyalty Registration OTP code is: $otp. Valid for 5 minutes.");
        $_SESSION['otp_timestamp'] = time(); 
        $success = "A fresh identity authentication code has been transmitted successfully.";
    } else {
        $error = "Unable to process resend payload. Session scope missing variables.";
        $step = 1;
        unset($_SESSION['reg_step']);
    }
}

// STEP 2: Validate OTP Code
if (isset($_POST['verify_otp']) && $step == 2) {
    $otp_input = trim($_POST['otp_code']);
    $mobile = $_SESSION['reg_temp_mobile'];

    $stmt = $mysql->prepare("SELECT id FROM otp_requests WHERE mobile = ? AND otp_code = ? AND purpose = 'register' AND expires_at >= NOW() AND is_verified = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$mobile, $otp_input]);
    $otpRecord = $stmt->fetch();

    if ($otpRecord) {
        $stmt = $mysql->prepare("UPDATE otp_requests SET is_verified = 1 WHERE id = ?");
        $stmt->execute([$otpRecord['id']]);

        $_SESSION['reg_step'] = 3;
        $step = 3;
        $success = "Phone identity verified successfully. Please configure your entry access password.";
    } else {
        $error = "Invalid or expired OTP code token matching parameters.";
    }
}

// STEP 3: Create Passwords
if (isset($_POST['complete_registration']) && $step == 3) {
    $pass = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    if (strlen($pass) < 6) {
        $error = "Password must span at least 6 alphanumeric variations.";
    } elseif ($pass !== $confirm_pass) {
        $error = "The password input sets do not match.";
    } else {
        $customer = $_SESSION['reg_temp_customer'];
        $mobile = $_SESSION['reg_temp_mobile'];
        $passwordHash = password_hash($pass, PASSWORD_BCRYPT);

        try {
            $stmt = $mysql->prepare("INSERT INTO users (customer_code, nic, mobile, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->execute([$customer['CM_CODE'], $customer['CM_NIC'], $mobile, $passwordHash]);

            // Clear temporary registry states
            unset($_SESSION['reg_step'], $_SESSION['reg_temp_customer'], $_SESSION['reg_temp_mobile'], $_SESSION['otp_timestamp']);
            
            $_SESSION['login_flash_success'] = "Account creation complete! You may now sign in.";
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $error = "A system structural database handling fault was encountered: " . $e->getMessage();
        }
    }
}

// Reset processing chain
if (isset($_POST['restart_process'])) {
    unset($_SESSION['reg_step'], $_SESSION['reg_temp_customer'], $_SESSION['reg_temp_mobile'], $_SESSION['otp_timestamp']);
    header("Location: register.php");
    exit;
}

// Compute active offsets for JavaScript hydration syncing
$elapsedSeconds = 0;
if (isset($_SESSION['otp_timestamp'])) {
    $elapsedSeconds = time() - $_SESSION['otp_timestamp'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>ASB Loyal Vault | Registration</title>
    <link rel="icon" type="image/png" href="logo.png">
    
    <!-- Premium Typography & FontAwesome Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@1,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
            -webkit-font-smoothing: antialiased;
            position: relative;
        }

        /* Ambient Crimson Subtle Light Glow Layers */
        body::before {
            content: '';
            position: absolute;
            top: 5%;
            left: 20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(225, 29, 72, 0.03) 0%, rgba(248, 250, 252, 0) 70%);
            z-index: -1;
            pointer-events: none;
        }

        .recovery-container {
            width: 100%;
            max-width: 520px;
            animation: revealUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            position: relative;
            z-index: 10;
        }

        @keyframes revealUp {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* --- IDENTITY CONTAINER HEADER --- */
        .brand-header-cluster {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .brand-logo-img {
            height: 52px;
            max-width: 200px;
            width: auto;
            object-fit: contain;
            margin-bottom: 1.25rem;
        }

        .brand-title-main {
            font-weight: 800;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            color: var(--text-main);
            line-height: 1.2;
        }

        .brand-title-main span {
            color: var(--primary);
        }

        .brand-subtitle-sub {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            color: var(--text-muted);
            font-weight: 700;
            margin-top: 6px;
        }

        .brand-subtitle-sub em {
            font-family: 'Playfair Display', serif;
            text-transform: none;
            letter-spacing: 1px;
            font-style: italic;
            font-weight: 600;
            color: var(--accent-amber);
        }

        /* --- PREMIUM CARD FRAME --- */
        .premium-card {
            background: var(--bg-sub);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 3rem 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
            transition: var(--transition-smooth);
        }

        .premium-card:hover {
            border-color: var(--border-hover);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05), 0 0 30px rgba(225, 29, 72, 0.01);
        }

        .step-indicator-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0, 0, 0, 0.02);
            border: 1px solid var(--border-color);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .step-indicator-pill span {
            color: var(--primary);
        }

        .form-instruction {
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
            font-weight: 400;
        }

        /* --- TIMER INTERFACES --- */
        .timer-display-box {
            background: rgba(0, 0, 0, 0.015);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .timer-info-pane h5 {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .timer-info-pane p {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .timer-countdown-clock {
            font-family: monospace;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            background: rgba(225, 29, 72, 0.05);
            padding: 4px 12px;
            border-radius: 10px;
            letter-spacing: 1px;
        }

        .timer-countdown-clock.expired {
            color: var(--text-muted);
            background: rgba(0, 0, 0, 0.05);
        }

        /* --- GROUPS AND CONTROLS --- */
        .form-group-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group-wrapper i.input-context-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            transition: var(--transition-smooth);
            pointer-events: none;
        }

        .premium-input-field {
            width: 100%;
            background: rgba(0, 0, 0, 0.01);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 1.1rem 1.1rem 1.1rem 3.25rem;
            color: var(--text-main);
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            outline: none;
            transition: var(--transition-smooth);
        }

        .premium-input-field:focus {
            border-color: var(--primary);
            background: var(--bg-card);
            box-shadow: 0 0 20px rgba(225, 29, 72, 0.06);
        }

        .premium-input-field::placeholder {
            color: #94a3b8;
            font-weight: 500;
        }

        .premium-input-field:focus + i.input-context-icon {
            color: var(--primary);
        }

        /* --- ACTION TRIGGERS --- */
        .action-button-stack {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 2rem;
        }

        .btn-premium-primary {
            width: 100%;
            padding: 1.1rem;
            background: var(--primary);
            color: #ffffff;
            border: 1px solid var(--primary);
            border-radius: 16px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition-smooth);
        }

        .btn-premium-primary:hover {
            background: var(--primary-hover);
            border-color: var(--primary-hover);
            box-shadow: 0 10px 20px var(--primary-glow);
            transform: translateY(-2px);
        }

        .btn-premium-primary:disabled {
            background: #cbd5e1;
            border-color: #cbd5e1;
            color: #94a3b8;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-premium-secondary {
            width: 100%;
            padding: 1.1rem;
            background: rgba(0, 0, 0, 0.02);
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition-smooth);
        }

        .btn-premium-secondary:hover {
            background: rgba(0, 0, 0, 0.04);
            color: var(--text-main);
            border-color: rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .back-navigation-link {
            display: block;
            text-align: center;
            margin-top: 2rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition-smooth);
        }

        .back-navigation-link:hover {
            color: var(--primary);
        }

        /* --- NOTIFICATION PIPELINES --- */
        .system-notification-banner {
            border-radius: 16px;
            padding: 1.1rem 1.4rem;
            margin-bottom: 2rem;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.5;
        }

        .banner-type-error {
            background: rgba(225, 29, 72, 0.05);
            border: 1px solid rgba(225, 29, 72, 0.15);
            border-left: 4px solid var(--primary);
            color: #9f1239;
        }

        .banner-type-success {
            background: rgba(16, 185, 129, 0.05);
            border: 1px solid rgba(16, 185, 129, 0.15);
            border-left: 4px solid var(--accent-green);
            color: #065f46;
        }

        .system-debug-window {
            background: #fef08a;
            border: 1px dashed var(--accent-amber);
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: 2rem;
            font-family: monospace;
            font-size: 0.78rem;
            color: #713f12;
            line-height: 1.4;
        }

        /* --- FOOTER SPECIFICATIONS --- */
        .system-footer-container {
            text-align: center;
            margin-top: 2.5rem;
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* Responsive Breakpoint Scaling */
        @media (max-width: 576px) {
            body { padding: 1.5rem 0.75rem; }
            .premium-card { padding: 2.5rem 1.5rem; border-radius: 20px; }
            .brand-title-main { font-size: 1.4rem; }
        }
    </style>
</head>
<body>

<div class="recovery-container">
    
    <!-- BRAND IDENTITY BLOCK -->
    <div class="brand-header-cluster">
        <img src="logo.png" alt="ASB Logo" class="brand-logo-img" onerror="this.style.display='none';">
        <div class="brand-title-main">ASB <span>Loyal Vault</span></div>
        <div class="brand-subtitle-sub">Fashion & <em>Glamour</em></div>
    </div>

    <!-- DYNAMIC STATUS NOTIFICATIONS -->
    <?php if($error): ?>
        <div class="system-notification-banner banner-type-error" id="phpErrorBanner">
            <i class="fas fa-circle-exclamation" style="margin-top: 3px;"></i>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="system-notification-banner banner-type-success">
            <i class="fas fa-circle-check" style="margin-top: 3px;"></i>
            <div><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>

    <!-- DEPLOYED SMS DEBUG EMULATOR -->
    <?php if(isset($_SESSION['sms_debug'])): ?>
        <div class="system-debug-window">
            <strong style="color: var(--accent-amber); text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Internal Debug Stream:</strong>
            <?= htmlspecialchars($_SESSION['sms_debug']); unset($_SESSION['sms_debug']); ?>
        </div>
    <?php endif; ?>

    <!-- JAVASCRIPT SYSTEM CLIENT-SIDE NOTIFICATION BANNER -->
    <div class="system-notification-banner banner-type-error" id="jsAlertBanner" style="display: none;">
        <i class="fas fa-circle-exclamation" style="margin-top: 3px;"></i>
        <div id="jsAlertMessage"></div>
    </div>

    <!-- PIPELINE STEPS ROUTING EXECUTIONS -->
    <div class="premium-card">
        
        <!-- STEP 1: Search Master Database -->
        <?php if($step == 1){ ?>
            <div class="step-indicator-pill">Registration Phase <span>1 of 3</span></div>
            <p class="form-instruction">Please supply either your Customer Code, NIC Identifier, or Mobile Number configuration to extract your loyalty profile details.</p>
            
            <form method="post" autocomplete="off">
                <div class="form-group-wrapper">
                    <input type="text" name="search_text" class="premium-input-field" placeholder="Customer Code / Mobile / NIC" required autofocus>
                    <i class="fas fa-search-dollar input-context-icon"></i>
                </div>
                
                <div class="action-button-stack">
                    <button type="submit" name="find_profile" class="btn-premium-primary">
                        Link Profile & Send OTP <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
            <p style="text-align:center;"><a href="login.php" class="back-navigation-link">Already have an account? Login here</a></p>
        <?php } ?>

        <!-- STEP 2: Validate OTP Code Token & Dynamic Handling -->
        <?php if($step == 2){ ?>
            <div class="step-indicator-pill">Identity Authentication <span>2 of 3</span></div>
            <p class="form-instruction">Provide the 6-Digit authorization OTP token sent to your device to verify communication integrity.</p>
            
            <!-- Real-time Status Tracker Canvas -->
            <div class="timer-display-box">
                <div class="timer-info-pane">
                    <h5 id="timerStatusHeader">Verification Token Active</h5>
                    <p id="timerStatusCaption">Code expires in 5 minutes</p>
                </div>
                <div class="timer-countdown-clock" id="countdownClock">05:00</div>
            </div>

            <form method="post" autocomplete="off" id="otpSubmitForm">
                <div class="form-group-wrapper">
                    <input type="text" name="otp_code" id="otpInputField" class="premium-input-field" placeholder="Enter 6-digit OTP" required maxlength="6" pattern="\d{6}" autofocus style="letter-spacing: 4px; font-size: 1.2rem; text-align: center; padding-left: 1.1rem;">
                </div>
                
                <div class="action-button-stack">
                    <button type="submit" name="verify_otp" id="submitTokenBtn" class="btn-premium-primary">
                        Verify Token Match <i class="fas fa-shield-halved"></i>
                    </button>
                    
                    <!-- Resend Module via POST Postback execution -->
                    <button type="submit" name="resend_otp_trigger" id="resendOtpBtn" class="btn-premium-secondary" disabled style="display: none;">
                        Resend OTP Code via SMS <i class="fas fa-arrows-rotate"></i>
                    </button>
                    
                    <button type="submit" name="restart_process" class="btn-premium-secondary" formnovalidate>
                        Cancel & Start Over
                    </button>
                </div>
            </form>
        <?php } ?>

        <!-- STEP 3: Cryptographic Passwords Configuration -->
        <?php if($step == 3){ ?>
            <div class="step-indicator-pill">Secure Vault Setup <span>3 of 3</span></div>
            <p class="form-instruction">Set secure account access credentials. Ensure structural configurations contain proper layout threshold variables.</p>
            
            <form method="post" autocomplete="off">
                <div class="form-group-wrapper">
                    <input type="password" name="password" class="premium-input-field" placeholder="Choose Password (Min 6 Characters)" required autofocus>
                    <i class="fas fa-lock-open input-context-icon"></i>
                </div>
                
                <div class="form-group-wrapper">
                    <input type="password" name="confirm_password" class="premium-input-field" placeholder="Confirm Chosen Password" required>
                    <i class="fas fa-lock input-context-icon"></i>
                </div>
                
                <div class="action-button-stack">
                    <button type="submit" name="complete_registration" class="btn-premium-primary">
                        Save System Credentials <i class="fas fa-fingerprint"></i>
                    </button>
                </div>
            </form>
        <?php } ?>

    </div>

    <!-- PIPELINE FOOTER SPECIFICATIONS -->
    <footer class="system-footer-container">
        <p>&copy; 2026 ASB Fashion. Secure Access Authorization System.</p>
    </footer>

</div>

<!-- COUNTDOWN TIME SCHEDULING ENGINE -->
<?php if ($step == 2): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalExpiryLimit = 300; // 5 Minutes total lifespan
    const resendCoolingLimit = 120; // 2 Minutes lock restriction
    
    // Server state offset counter synchronization
    let secondsElapsed = parseInt(<?= json_encode($elapsedSeconds) ?>) || 0;

    const clockDisplay = document.getElementById('countdownClock');
    const statusHeader = document.getElementById('timerStatusHeader');
    const statusCaption = document.getElementById('timerStatusCaption');
    const resendBtn = document.getElementById('resendOtpBtn');
    const submitBtn = document.getElementById('submitTokenBtn');
    const otpInput = document.getElementById('otpInputField');
    const jsBanner = document.getElementById('jsAlertBanner');
    const jsMessage = document.getElementById('jsAlertMessage');
    const phpBanner = document.getElementById('phpErrorBanner');

    function displayError(msg) {
        if(phpBanner) phpBanner.style.display = 'none';
        jsMessage.innerText = msg;
        jsBanner.style.display = 'flex';
    }

    function runClockPipeline() {
        let remainingTime = totalExpiryLimit - secondsElapsed;
        
        if (remainingTime <= 0) {
            remainingTime = 0;
            clearInterval(clockInterval);
            
            // Refactor UI to represent an invalid expired validation context
            clockDisplay.innerText = "00:00";
            clockDisplay.classList.add('expired');
            statusHeader.innerText = "Security Token Expired";
            statusHeader.style.color = "var(--primary)";
            statusCaption.innerText = "This access profile code has expired.";
            
            if(otpInput) otpInput.disabled = true;
            if(submitBtn) submitBtn.disabled = true;
            displayError("The current OTP window has lapsed. Please click below to generate a new authentication SMS token.");
        } else {
            let mins = Math.floor(remainingTime / 60);
            let secs = remainingTime % 60;
            clockDisplay.innerText = 
                (mins < 10 ? "0" : "") + mins + ":" + 
                (secs < 10 ? "0" : "") + secs;
        }

        // Evaluate state rules for the 2-Minute Resend cooling restriction
        if (secondsElapsed >= resendCoolingLimit) {
            if(resendBtn) {
                resendBtn.style.display = 'flex';
                resendBtn.removeAttribute('disabled');
            }
        } else {
            if(resendBtn) {
                resendBtn.style.display = 'flex';
                resendBtn.setAttribute('disabled', 'disabled');
                let waitRemaining = resendCoolingLimit - secondsElapsed;
                let wMins = Math.floor(waitRemaining / 60);
                let wSecs = waitRemaining % 60;
                resendBtn.innerText = `Resend available in (${wMins}:${wSecs < 10 ? "0" : ""}${wSecs})`;
            }
        }

        secondsElapsed++;
    }

    // Initialize execution and configure interval loop tracking
    runClockPipeline();
    const clockInterval = setInterval(runClockPipeline, 1000);
});
</script>
<?php endif; ?>

</body>
</html>