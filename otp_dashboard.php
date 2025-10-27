<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
require_once 'config.php'; 

// Check if there is a pending login/registration session
$sessionKey = isset($_SESSION['pending_login']) ? 'pending_login' : (isset($_SESSION['pending_registration']) ? 'pending_registration' : null);

// If no session, redirect back to login.php
if (!$sessionKey) {
    header('Location: login.php');
    exit;
}

// Get the user's email from the pending session for display
$pendingEmail = $_SESSION[$sessionKey]['email'] ?? 'your email';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OTP Verification - Lena Gym Fitness</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <!-- Removed reCAPTCHA script -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#ec1313',
                    },
                    fontFamily: {
                        display: ['Lexend'],
                    },
                },
            },
        }
    </script>
    <style>
        /* CSS copied exactly from login.php for identical styling */
        body {
            min-height: 100vh;
            background-image: url('https://i.pinimg.com/originals/98/a8/16/98a8167962ef295419ee2194c8d933d2.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            font-family: 'Lexend', sans-serif;
            overflow-x: hidden;
            position: relative;
            margin: 0;
            padding: 0;
        }
        @media (max-width: 768px) {
            body {
                background-attachment: scroll;
            }
        }
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(236, 19, 19, 0.05), rgba(0, 0, 0, 0.1));
            pointer-events: none;
            animation: shimmer 10s ease-in-out infinite;
            z-index: -1;
        }
        @keyframes shimmer {
            0%, 100% { opacity: 0.5; transform: translateX(-100%); }
            50% { opacity: 1; transform: translateX(100%); }
        }
        .auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
            overflow-y: auto;
            position: relative;
            z-index: 1;
            box-sizing: border-box;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 100%;
            height: auto;
            animation: slideUp 0.8s ease-out, glow 3s ease-in-out infinite alternate;
            position: relative;
            box-sizing: border-box;
        }
        @media (max-width: 768px) {
            .auth-card {
                background: rgba(255, 255, 255, 0.2);
            }
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes glow {
            from { box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 20px rgba(236, 19, 19, 0.1); }
            to { box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px rgba(236, 19, 19, 0.2); }
        }
        .form-side {
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            box-sizing: border-box;
        }
        .logo-image {
            width: 50px;
            height: auto;
            margin: 0 auto 0.75rem;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
            transition: transform 0.3s ease;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        .logo-image:hover {
            transform: scale(1.05) rotate(5deg);
        }
        /* Hidden elements in login.php style block */
        .tab-buttons, .tab-btn, .tab-content.active { display: none; }
        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 1rem;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            animation: textGlow 2s ease-in-out infinite alternate;
        }
        @keyframes textGlow {
            from { text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5), 0 0 10px rgba(236, 19, 19, 0.2); }
            to { text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5), 0 0 20px rgba(236, 19, 19, 0.4); }
        }
        .form-group {
            margin-bottom: 0.75rem;
            position: relative;
        }
        .form-group:focus-within .form-label {
            color: #ec1313;
            text-shadow: 0 0 10px rgba(236, 19, 19, 0.8);
        }
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            color: #000;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: relative;
            box-sizing: border-box;
        }
        .form-input::placeholder {
            color: rgba(0, 0, 0, 0.5);
        }
        .form-input:focus {
            border-color: #ec1313;
            outline: none;
            background: rgba(255, 255, 255, 1);
            box-shadow: 0 0 0 0.25rem rgba(236, 19, 19, 0.25);
        }
        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 500;
            color: #fff;
            margin-top: 0.25rem;
            transition: all 0.3s ease;
            background: transparent;
            padding: 0;
            border-radius: 0;
            text-align: left;
            text-shadow: 0 0 5px rgba(236, 19, 19, 0.5);
        }
        .btn-group {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.75rem;
            flex-wrap: wrap;
        }
        .btn {
            flex: 1;
            min-width: 120px;
            padding: 0.5rem;
            border: none;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            z-index: 1;
            touch-action: manipulation;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
            z-index: -1;
        }
        .btn:hover::before {
            left: 100%;
        }
        .btn-primary {
            background: #ec1313;
            color: white;
        }
        .btn-primary:hover {
            background: #c51111;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 12px rgba(236, 19, 19, 0.4), 0 0 20px rgba(236, 19, 19, 0.3);
        }
        .btn-primary:active {
            transform: translateY(-1px) scale(1.02);
        }
        .btn-secondary {
            background: transparent;
            color: #ec1313;
            border: 1px solid #ec1313;
        }
        .btn-secondary:hover {
            background: #ec1313;
            color: white;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 12px rgba(236, 19, 19, 0.4), 0 0 20px rgba(236, 19, 19, 0.3);
        }
        .btn-secondary:active {
            transform: translateY(-1px) scale(1.02);
        }
        .switch-link {
            color: #ec1313;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.75rem;
            position: relative;
        }
        .switch-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #ec1313;
            transition: width 0.3s ease;
        }
        .switch-link:hover {
            color: #c51111;
            text-shadow: 0 0 5px rgba(236, 19, 19, 0.5);
            transform: scale(1.05);
        }
        .switch-link:hover::after {
            width: 100%;
        }
        .notification {
            margin-top: 1rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            text-align: center;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .notification.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .notification.success {
            background: rgba(0, 128, 0, 0.2);
            color: #00ff00;
            border: 1px solid #00ff00;
        }
        .notification.error {
            background: rgba(255, 0, 0, 0.2);
            color: #ff0000;
            border: 1px solid #ff0000;
        }
        .hidden {
            display: none;
        }
        .pulsing {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); opacity: 0.8; }
            100% { transform: scale(1); }
        }
        .particle {
            position: absolute;
            width: 5px;
            height: 5px;
            background: rgba(236, 19, 19, 0.5);
            border-radius: 50%;
            animation: float 6s linear infinite;
            z-index: -1;
            pointer-events: none;
        }
        @keyframes float {
            0% { transform: translateY(100vh) translateX(0) scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(-100%) translateX(calc(50vw * var(--direction, 1))) scale(1); opacity: 0; }
        }
        #particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div id="particles"></div>
    <div class="auth-container">
        <div class="auth-card">
            <div class="form-side">
                <img src="assets/image/logo.png" alt="Lena Gym Logo" class="logo-image" />
                
                <div id="otp-verification-content">
                    <h2 class="form-title">Enter Verification Code</h2>
                    <p class="text-center text-sm mb-4 text-white/80">We sent a 6-digit code to **<?php echo htmlspecialchars($pendingEmail); ?>**. The code expires in **5 minutes**.</p>
                    
                    <form id="otp-form">
                        <div class="form-group">
                            <input 
                                type="text" 
                                id="otp_code" 
                                name="otp_code" 
                                class="form-input text-center text-lg tracking-widest" 
                                placeholder="123456" 
                                maxlength="6"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                required
                            />
                        </div>
                        
                        <!-- Removed reCAPTCHA container -->

                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary" id="verify-btn">Verify</button>
                        </div>
                    </form>

                    <div id="otp-notification" class="notification hidden"></div>

                    <p class="text-center text-xs mt-4 text-white/80">
                        Didn't receive the code? 
                        <a href="#" class="switch-link" id="resend-otp-link">Resend OTP</a>
                    </p>
                    <p class="text-center text-xs mt-2 text-white/60">
                        <a href="login.php" class="switch-link">Cancel and return to Login</a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        // JS helpers copied from login.php
        function createParticles(count) {
            const container = document.getElementById('particles');
            if (!container) return;
            for (let i = 0; i < count; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                const size = Math.random() * 8 + 3;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${Math.random() * 100}vw`;
                particle.style.top = `${Math.random() * 100}vh`;
                particle.style.animationDelay = `${Math.random() * 5}s`;
                particle.style.setProperty('--direction', Math.random() > 0.5 ? 1 : -1);
                container.appendChild(particle);
            }
        }
        
        function showNotification(containerId, message, isSuccess = true) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.textContent = message;
            container.classList.remove('hidden', 'success', 'error');
            container.classList.add('visible', isSuccess ? 'success' : 'error');
            // Auto-hide after 4 seconds
            setTimeout(() => {
                container.classList.remove('visible');
                container.classList.add('hidden');
            }, 4000);
        }

        // OTP page logic
        document.addEventListener('DOMContentLoaded', () => {
            createParticles(20);

            // Check for initial message from sessionStorage (set by login.php)
            const initialMessage = sessionStorage.getItem('otp_message');
            if (initialMessage) {
                showNotification('otp-notification', initialMessage, true);
                sessionStorage.removeItem('otp_message'); 
            }
            
            // --- OTP Verification Form Submission ---
            const otpForm = document.getElementById('otp-form');
            if (otpForm) {
                otpForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const otp_code = otpForm.querySelector('#otp_code').value.trim();

                    if (otp_code.length !== 6 || isNaN(otp_code)) {
                        showNotification('otp-notification', 'Please enter a valid 6-digit code.', false);
                        return;
                    }

                    const verifyBtn = document.getElementById('verify-btn');
                    verifyBtn.textContent = 'Verifying...';
                    verifyBtn.classList.add('pulsing');
                    
                    const formData = {
                        action: 'verify_otp',
                        otp_code: otp_code
                    };
                    
                    try {
                        const response = await fetch('auth.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(formData)
                        });
                        const result = await response.json();

                        if (result.success) {
                            showNotification('otp-notification', result.message, true);
                            sessionStorage.removeItem('otp_email');
                            setTimeout(() => {
                                window.location.href = result.redirect || 'login.php';
                            }, 1000);
                        } else {
                            showNotification('otp-notification', result.message, false);
                            verifyBtn.textContent = 'Verify';
                            verifyBtn.classList.remove('pulsing');
                        }
                    } catch (error) {
                        showNotification('otp-notification', 'An unexpected error occurred. Please try again.', false);
                        console.error('OTP Verification Error:', error);
                        verifyBtn.textContent = 'Verify';
                        verifyBtn.classList.remove('pulsing');
                    }
                });
            }

            // --- Resend OTP functionality ---
            const resendLink = document.getElementById('resend-otp-link');
            if (resendLink) {
                resendLink.addEventListener('click', async (e) => {
                    e.preventDefault();
                    
                    // Disable link temporarily to prevent spamming
                    resendLink.style.pointerEvents = 'none';
                    resendLink.textContent = 'Sending...';

                    try {
                        const response = await fetch('auth.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'resend_otp' })
                        });
                        const result = await response.json();

                        if (result.success) {
                            showNotification('otp-notification', result.message, true);
                        } else {
                            showNotification('otp-notification', result.message, false);
                        }
                    } catch (error) {
                        showNotification('otp-notification', 'An unexpected error occurred while trying to resend the code.', false);
                        console.error('Resend OTP Error:', error);
                    } finally {
                        // Re-enable link after a delay
                        setTimeout(() => {
                            resendLink.textContent = 'Resend OTP';
                            resendLink.style.pointerEvents = 'auto';
                        }, 5000); // 5 second delay before allowing resend
                    }
                });
            }
        });
    </script>
</body>
</html>
