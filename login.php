<?php
session_start();
error_reporting(0);
require __DIR__ . '/vendor/autoload.php';
require_once 'config.php'; 

$is_google_reg_incomplete = isset($_SESSION['google_reg_data']) && 
                            !empty($_SESSION['google_reg_data']['email']) && 
                            !empty($_SESSION['google_reg_data']['provider_id']);

$google_reg_data = $is_google_reg_incomplete ? json_encode($_SESSION['google_reg_data']) : 'null';

$success_message = '';
if (isset($_SESSION['auth_message'])) {
    $success_message = $_SESSION['auth_message'];
    unset($_SESSION['auth_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Lena Gym Fitness</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
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
        .tab-buttons {
            display: flex;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
        }
        .tab-buttons::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: var(--indicator-left, 0%);
            width: var(--indicator-width, 50%);
            height: 3px;
            background: #ec1313;
            transition: left 0.3s ease, width 0.3s ease;
        }
        .tab-btn.hidden {
            display: none;
        }
        .tab-btn {
            flex: 1;
            padding: 0.5rem;
            background: transparent;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.875rem;
            position: relative;
            z-index: 1;
            touch-action: manipulation;
        }
        .tab-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent, rgba(236, 19, 19, 0.1), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .tab-btn:hover::before {
            opacity: 1;
        }
        .tab-btn.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ec1313;
            box-shadow: 0 -2px 10px rgba(236, 19, 19, 0.1);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: slideUpFadeIn 0.5s ease-out; 
        }
        @keyframes slideUpFadeIn {
            from { opacity: 0; transform: translateY(20px); } 
            to { opacity: 1; transform: translateY(0); }
        }
        .password-toggle {
            position: absolute; 
            right: 0.75rem; 
            top: 50%; 
            transform: translateY(-50%); 
            cursor: pointer; 
            color: rgba(0,0,0,0.7); 
            font-size: 1rem; 
            z-index: 2; 
            user-select: none;
            pointer-events: auto;
        }
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
        .checkbox-group {
            display: flex;
            align-items: flex-start;
            margin: 0.75rem 0;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
        }
        .checkbox-group input[type="checkbox"] {
            width: 0.875rem;
            height: 0.875rem;
            margin-right: 0.5rem;
            margin-top: 0.125rem;
            accent-color: #ec1313;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            cursor: pointer;
        }
        .checkbox-group input[type="checkbox"]:hover {
            box-shadow: 0 0 5px rgba(236, 19, 19, 0.5);
        }
        .checkbox-group input[type="checkbox"]:checked {
            transform: scale(1.1);
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
        .social-divider {
            text-align: center;
            margin: 1rem 0;
            position: relative;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.75rem;
        }
        .social-divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            transition: background 0.3s ease;
        }
        .social-divider:hover::before {
            background: rgba(236, 19, 19, 0.4);
        }
        .social-divider span {
            background: transparent;
            padding: 0 0.5rem;
            position: relative;
            z-index: 1;
        }
        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            transition: all 0.3s ease;
            padding: 0.5rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .social-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: #ec1313;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(236, 19, 19, 0.2);
        }
        .social-btn svg {
            width: 1rem;
            height: 1rem;
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

                <div class="tab-buttons" id="main-tab-buttons">
                    <button class="tab-btn active" data-tab-name="register" onclick="switchTab('register', this)">Sign Up</button>
                    <button class="tab-btn" data-tab-name="login" onclick="switchTab('login', this)">Login</button>
                </div>

                <div id="register-tab" class="tab-content active"> 
                    <h2 class="form-title">Create Account</h2>
                    <form id="register-form">
                        <input type="hidden" id="register-provider-id-hidden" name="provider_id" value="" />
                        <div class="form-group">
                            <input type="text" id="register-name" class="form-input" placeholder="Full Name" required />
                        </div>
                        <div class="form-group">
                            <input type="email" id="register-email" class="form-input" placeholder="Email" required />
                        </div>
                        
                        <div class="form-group">
                            <select id="register-gender" class="form-input" required>
                                <option value="" disabled selected>Select Gender *</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group hidden" id="other-gender-group">
                            <input type="text" id="register-other-gender" class="form-input" placeholder="Please specify your gender" />
                        </div>
                        <div class="form-group relative">
                            <input type="password" id="register-password" class="form-input" placeholder="Password" required minlength="8" />
                            <span class="password-toggle" data-target="register-password">👁️</span>
                        </div>
                        <div class="form-group relative">
                            <input type="password" id="register-re-password" class="form-input" placeholder="Re-enter Password" required minlength="8" />
                            <span class="password-toggle" data-target="register-re-password">👁️</span>
                        </div>

                        <div class="checkbox-group">
                            <input type="checkbox" id="terms" required />
                            <label for="terms">I agree to the terms and conditions</label>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                    
                    <div id="register-notification" class="notification hidden"></div>
                    <div class="social-divider"><span>or continue with</span></div>
                    <div class="btn-group flex justify-center w-full">
                        <div id="google-register-btn" style="width:100%; max-width: 250px; margin: 0 auto;"></div>
                    </div>
                    <p class="text-center text-xs mt-4 text-white/80">Already have an account? <a href="#" class="switch-link" onclick="switchTab('login', document.querySelector('.tab-btn:nth-child(2)'))">Login</a></p>
                </div>

                <div id="login-tab" class="tab-content">
                    <h2 class="form-title">Welcome Back</h2>
                    <form id="login-form">
                        <div class="form-group">
                            <input type="email" id="login-email" class="form-input" placeholder="Email" required />
                        </div>
                        <div class="form-group">
                            <input type="password" id="login-password" class="form-input" placeholder="Password" required />
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin: 0.75rem 0; font-size: 0.75rem; color: rgba(255, 255, 255, 0.8);">
                            <div class="checkbox-group" style="margin: 0;">
                                <input type="checkbox" id="remember" />
                                <label for="remember">Remember me</label>
                            </div>
                            <a href="#" class="switch-link" style="font-size: 0.7rem; margin: 0;" onclick="openForgotPasswordModal(event)">Forgot Password?</a>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                        <div class="btn-group" style="margin-top: 0.5rem;">
                            <a href="Staff-Admin-Login.php" class="btn btn-secondary" style="width: 100%; text-align: center; flex: 1;">
                                Staff Login
                            </a>
                        </div>
                    </form>
                    <div id="login-notification" class="notification hidden"></div>
                    <div class="social-divider"><span>or continue with</span></div>
                    <div class="btn-group flex justify-center w-full">
                        <div id="google-login-btn" style="width:100%; max-width: 250px; margin: 0 auto;"></div>
                    </div>
                    <p class="text-center text-xs mt-4 text-white/80">Don't have an account? <a href="#" class="switch-link" onclick="switchTab('register', document.querySelector('.tab-btn:first-child'))">Sign up</a></p>
                </div>
            </div>
        </div>
    </div>

    <div id="forgot-password-modal" class="hidden" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); align-items: center; justify-content: center; z-index: 1000;">
        <div class="auth-card" style="max-width: 350px; animation: slideUp 0.5s ease-out;">
            <div class="form-side">
                <h2 class="form-title">Reset Password</h2>
                <p style="color: rgba(255, 255, 255, 0.8); font-size: 0.85rem; text-align: center; margin-bottom: 1rem;">Enter your email address and we'll send you a link to reset your password.</p>
                
                <form id="forgot-password-form">
                    <div class="form-group">
                        <input type="email" id="forgot-email" class="form-input" placeholder="Enter your email" required />
                    </div>
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        <button type="button" class="btn btn-secondary" onclick="closeForgotPasswordModal()">Cancel</button>
                    </div>
                </form>
                <div id="forgot-notification" class="notification hidden"></div>
            </div>
        </div>
    </div>

    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <script>
        const GOOGLE_REG_DATA = <?php echo $google_reg_data; ?>;
        const SUCCESS_MESSAGE = '<?php echo $success_message; ?>';
        
        function openForgotPasswordModal(e) {
            e.preventDefault();
            const modal = document.getElementById('forgot-password-modal');
            modal.classList.remove('hidden');
            modal.style.display = 'flex';
            document.getElementById('forgot-email').focus();
        }

        function closeForgotPasswordModal() {
            const modal = document.getElementById('forgot-password-modal');
            modal.classList.add('hidden');
            modal.style.display = 'none';
            document.getElementById('forgot-password-form').reset();
            document.getElementById('forgot-notification').classList.add('hidden');
        }
        
        function autoFillGoogleRegistration(googleData) {
            if (!googleData || !googleData.email) {
                console.log('[v0] No Google registration data available');
                return;
            }

            console.log('[v0] Auto-filling Google registration form with:', {
                email: googleData.email,
                name: googleData.name
            });

            const emailInput = document.getElementById('register-email');
            const nameInput = document.getElementById('register-name');
            const genderSelect = document.getElementById('register-gender'); // NEW
            const otherGenderGroup = document.getElementById('other-gender-group'); // NEW
            const passwordInput = document.getElementById('register-password');
            const rePasswordInput = document.getElementById('register-re-password');
            const providerHidden = document.getElementById('register-provider-id-hidden');

            if (emailInput) {
                emailInput.value = googleData.email;
                emailInput.readOnly = true;
                emailInput.style.background = 'rgba(255, 255, 255, 0.7)';
                emailInput.style.cursor = 'default';
                emailInput.title = "Your Google account email - cannot be changed";
                console.log('[v0] Email field filled:', googleData.email);
            }

            if (nameInput && googleData.name) {
                nameInput.value = googleData.name;
                nameInput.focus();
                console.log('[v0] Name field filled:', googleData.name);
            }
            
            // NEW: Reset gender field state when autofilling
            if (genderSelect) {
                genderSelect.value = '';
            }
            if (otherGenderGroup) {
                otherGenderGroup.classList.add('hidden');
                const otherGenderInput = document.getElementById('register-other-gender');
                if (otherGenderInput) {
                    otherGenderInput.required = false;
                    otherGenderInput.value = '';
                }
            }


            if (passwordInput) {
                passwordInput.value = '';
                passwordInput.placeholder = 'Create a password for your account';
            }
            if (rePasswordInput) {
                rePasswordInput.value = '';
                rePasswordInput.placeholder = 'Confirm your password';
            }

            if (providerHidden) {
                providerHidden.value = googleData.provider_id;
                console.log('[v0] Provider ID stored:', googleData.provider_id);
            }

            showNotification('register-notification', 
                'Welcome! Your Google account details have been pre-filled. Please create a password and select your gender to complete your registration.', 
                true);
        }
        
        function addPasswordToggleListeners() {
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                const targetId = toggle.dataset.target;
                let input = document.getElementById(targetId);
                
                if (!input) {
                    const group = toggle.closest('.form-group');
                    const formInput = group ? group.querySelector('.form-input') : null;
                    if (formInput) {
                        input = formInput;
                    } else {
                        return;
                    }
                }

                toggle.innerHTML = '👁️'; 

                toggle.addEventListener('click', e => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (input.type === 'password') {
                        input.type = 'text';
                        toggle.innerHTML = '🙈';
                    } else {
                        input.type = 'password';
                        toggle.innerHTML = '👁️';
                    }
                });
            });
        }

        window.onload = function() {
            createParticles();
            
            if (SUCCESS_MESSAGE) {
                switchTab('login', document.querySelector('.tab-btn[data-tab-name="login"]'));
                showNotification('login-notification', SUCCESS_MESSAGE, true);
                const tabButtons = document.getElementById('main-tab-buttons');
                if (tabButtons) {
                    tabButtons.style.setProperty('--indicator-left', '50%');
                    tabButtons.style.setProperty('--indicator-width', '50%');
                }
                document.querySelector('.tab-btn[data-tab-name="login"]').classList.add('active');
            }
            else if (GOOGLE_REG_DATA && GOOGLE_REG_DATA.email && GOOGLE_REG_DATA.provider_id) {
                switchTab('register', document.querySelector('.tab-btn[data-tab-name="register"]'));
                autoFillGoogleRegistration(GOOGLE_REG_DATA);
            } else {
                const tabButtons = document.getElementById('main-tab-buttons');
                if (tabButtons) {
                    tabButtons.style.setProperty('--indicator-left', '0%');
                    tabButtons.style.setProperty('--indicator-width', '50%');
                }
                switchTab('register', document.querySelector('.tab-btn[data-tab-name="register"]'));
            }
            
            initGoogleSignIn();
            addPasswordToggleListeners();
        };

        async function handleGoogleCredentialResponse(response) {
            const notificationId = 'register-notification';
            showNotification(notificationId, 'Verifying Google login...', false);
            await handleSocialLogin('google', response.credential);
        }

        function switchTab(tabName, button) {
            const tabButtons = document.getElementById('main-tab-buttons');
            let indicatorLeft = '0%';
            
            if (tabName === 'register') {
                indicatorLeft = '0%';
            } else if (tabName === 'login') {
                indicatorLeft = '50%';
            }
            
            if (tabButtons) {
                tabButtons.style.setProperty('--indicator-left', indicatorLeft);
                tabButtons.style.setProperty('--indicator-width', '50%');
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            }

            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            const targetContent = document.getElementById(tabName + '-tab');
            if (targetContent) {
                targetContent.classList.add('active');
            }
            
            if (button) {
                button.classList.add('active');
            }

            const logo = document.querySelector('.logo-image');
            if (logo) {
                logo.style.animation = 'none';
                setTimeout(() => {
                    logo.style.animation = 'bounce 2s infinite';
                }, 10);
            }
        }

        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                container.appendChild(particle);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            
            // NEW: Listener for Gender Select to show/hide 'Other' field
            const genderSelect = document.getElementById('register-gender');
            const otherGenderGroup = document.getElementById('other-gender-group');
            if (genderSelect) {
                genderSelect.addEventListener('change', (e) => {
                    if (e.target.value === 'other') {
                        otherGenderGroup.classList.remove('hidden');
                        document.getElementById('register-other-gender').required = true;
                    } else {
                        otherGenderGroup.classList.add('hidden');
                        document.getElementById('register-other-gender').required = false;
                    }
                });
            }


            const registerForm = document.getElementById('register-form');
            registerForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('register-email').value.trim();
                const name = document.getElementById('register-name').value.trim();
                const gender = document.getElementById('register-gender').value; // NEW
                const otherGender = document.getElementById('register-other-gender').value.trim(); // NEW
                const password = document.getElementById('register-password').value;
                const rePassword = document.getElementById('register-re-password').value;
                const providerId = document.getElementById('register-provider-id-hidden').value || '';
                const userType = 'Member';
                const terms = document.getElementById('terms').checked;

                if (!email || !name || !password || !rePassword || !terms) {
                    showNotification('register-notification', 'Please fill all fields correctly, ensure password is at least 8 characters, and agree to terms!', false);
                    return;
                }
                
                // NEW: Check for gender selection
                if (!gender) {
                    showNotification('register-notification', 'Please select a gender.', false);
                    return;
                }
                
                // NEW: Check if 'other' is selected but not specified
                if (gender === 'other' && !otherGender) {
                    showNotification('register-notification', 'Please specify your gender.', false);
                    return;
                }

                if (password.length < 8) {
                    showNotification('register-notification', 'Password must be at least 8 characters.', false);
                    return;
                }

                if (password !== rePassword) {
                    showNotification('register-notification', 'Passwords do not match.', false);
                    return;
                }

                const submitBtn = registerForm.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Signing Up...';
                submitBtn.classList.add('pulsing');
                
                // NOTE: We only send the ENUM value ('male', 'female', 'other') to match the database schema.
                const finalGender = gender; 

                const data = {
                    action: 'register',
                    email,
                    name,
                    gender: finalGender, // ADDED
                    // If you update your DB, you can also send the specific text here:
                    // other_gender_specifier: (gender === 'other' ? otherGender : null), 
                    password,
                    re_password: rePassword,
                    provider_id: providerId,
                    role: userType
                };

                try {
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();

                    if (result.success) {
                        showNotification('register-notification', result.message, true);

                        if (result.redirect_to_otp_login) {
                            setTimeout(() => {
                                window.location.href = 'otp_dashboard.php';
                            }, 600);
                            return;
                        }

                        setTimeout(() => { window.location.href = 'login.php'; }, 800);

                    } else {
                        showNotification('register-notification', result.message || 'Registration failed.', false);
                    }
                } catch (err) {
                    console.error(err);
                    alert('An internal server error occurred.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    submitBtn.classList.remove('pulsing');
                }
            });

            const loginForm = document.getElementById('login-form');
            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('login-email').value.trim();
                const password = document.getElementById('login-password').value;

                if (!email || !password) {
                    showNotification('login-notification', 'Please enter email and password!', false);
                    return;
                }

                const submitBtn = loginForm.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Logging In...';
                submitBtn.classList.add('pulsing');

                const data = {
                    action: 'login',
                    email,
                    password
                };

                try {
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });
                    const result = await response.json();

                    if (result.success) {
                        showNotification('login-notification', result.message, true);

                        if (result.redirect) {
                            setTimeout(() => {
                                window.location.href = result.redirect;
                            }, 600);
                            return;
                        }

                        localStorage.setItem('role', result.user?.role || 'member');
                        localStorage.setItem('userName', result.user?.name || '');
                        localStorage.setItem('userEmail', result.user?.email || '');
                        localStorage.setItem('userId', result.user?.id || '');

                        const role = (result.user?.role || 'member').toLowerCase();
                        let redirectUrl = 'dashboard.php';

                        if (role === 'admin') {
                            redirectUrl = 'adminDashboard.php';
                        } else if (role === 'staff') {
                            redirectUrl = 'staffDashboard.php';
                        } else if (role === 'customer') {
                            redirectUrl = 'customer/dashboard/dashboard.php';
                        }

                        setTimeout(() => {
                            window.location.href = redirectUrl;
                        }, 500);
                    } else {
                        showNotification('login-notification', result.message || 'Authentication failed.', false);
                    }
                } catch (err) {
                    console.error('[v0] Login error:', err);
                    showNotification('login-notification', 'An internal server error occurred.', false);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    submitBtn.classList.remove('pulsing');
                }
            });

            // Close modal when clicking outside
            const modal = document.getElementById('forgot-password-modal');
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeForgotPasswordModal();
                }
            });

            const forgotForm = document.getElementById('forgot-password-form');
            forgotForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const email = document.getElementById('forgot-email').value.trim();

                if (!email) {
                    showNotification('forgot-notification', 'Please enter your email address.', false);
                    return;
                }

                const submitBtn = forgotForm.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';
                submitBtn.classList.add('pulsing');

                try {
                    const response = await fetch('auth.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'forgot_password',
                            email: email
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        showNotification('forgot-notification', result.message, true);
                        setTimeout(() => {
                            closeForgotPasswordModal();
                        }, 2000);
                    } else {
                        showNotification('forgot-notification', result.message || 'Failed to send reset link.', false);
                    }
                } catch (err) {
                    console.error('[v0] Forgot password error:', err);
                    showNotification('forgot-notification', 'An error occurred. Please try again.', false);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    submitBtn.classList.remove('pulsing');
                }
            });
        });

        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn');
            if (!btn) return;

            const ripple = document.createElement('span');
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple 0.6s linear;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                pointer-events: none;
            `;
            ripple.classList.add('ripple-effect');
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });

        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(rippleStyle);

        function showNotification(containerId, message, isSuccess = true) {
            const container = document.getElementById(containerId);
            if (!container) return;
            container.textContent = message;
            container.classList.remove('hidden', 'success', 'error');
            container.classList.add('visible', isSuccess ? 'success' : 'error');
            setTimeout(() => {
                container.classList.remove('visible');
                container.classList.add('hidden');
            }, 4000);
        }
        
        async function handleSocialLogin(provider, token) {
            try {
                const response = await fetch('auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'google_auth_login',
                        id_token: token
                    })
                });

                const result = await response.json();
                console.log('[v0] Google auth response:', result);

                if (result.success) {
                    if (result.redirect_to_otp_login) {
                        showNotification('login-notification', result.message, true);
                        setTimeout(() => {
                            window.location.href = 'otp_dashboard.php';
                        }, 600);
                    } else if (result.google_data) {
                        console.log('[v0] New user detected, showing registration form with Google data:', result.google_data);
                        
                        window.GOOGLE_REG_DATA = result.google_data;
                        const registerBtn = document.querySelector('.tab-btn[data-tab-name="register"]');
                        switchTab('register', registerBtn);
                        
                        setTimeout(() => {
                            console.log('[v0] Auto-filling form with:', result.google_data);
                            
                            const emailInput = document.getElementById('register-email');
                            const nameInput = document.getElementById('register-name');
                            const genderSelect = document.getElementById('register-gender'); // NEW
                            const otherGenderGroup = document.getElementById('other-gender-group'); // NEW
                            const passwordInput = document.getElementById('register-password');
                            const rePasswordInput = document.getElementById('register-re-password');
                            const providerHidden = document.getElementById('register-provider-id-hidden');
                            
                            if (emailInput) {
                                emailInput.value = result.google_data.email;
                                emailInput.readOnly = true;
                                emailInput.style.background = 'rgba(255, 255, 255, 0.7)';
                                emailInput.style.cursor = 'default';
                                console.log('[v0] Email filled:', result.google_data.email);
                            }
                            
                            if (nameInput) {
                                nameInput.value = result.google_data.name;
                                nameInput.focus();
                                console.log('[v0] Name filled:', result.google_data.name);
                            }
                            
                            // NEW: Reset gender field state when autofilling
                            if (genderSelect) {
                                genderSelect.value = '';
                            }
                            if (otherGenderGroup) {
                                otherGenderGroup.classList.add('hidden');
                                const otherGenderInput = document.getElementById('register-other-gender');
                                if (otherGenderInput) {
                                    otherGenderInput.required = false;
                                    otherGenderInput.value = '';
                                }
                            }
                            
                            if (passwordInput) {
                                passwordInput.value = '';
                                passwordInput.placeholder = 'Create a password for your account';
                            }
                            if (rePasswordInput) {
                                rePasswordInput.value = '';
                                rePasswordInput.placeholder = 'Confirm your password';
                            }
                            
                            if (providerHidden) {
                                providerHidden.value = result.google_data.provider_id;
                            }
                            
                            showNotification('register-notification', 
                                'Welcome! Your Google account details have been pre-filled. Please create a password and select your gender to complete your registration.', 
                                true);
                        }, 300);
                    }
                } else {
                    const activeTab = document.querySelector('.tab-content.active') ? document.querySelector('.tab-content.active').id : 'login-tab';
                    const notificationId = activeTab === 'register-tab' ? 'register-notification' : 'login-notification';
                    showNotification(notificationId, result.message || 'Google authentication failed.', false);
                }
            } catch (err) {
                console.error('[v0] Social login error:', err);
                showNotification('login-notification', 'An error occurred during Google authentication.', false);
            }
        }

        function initGoogleSignIn() {
            google.accounts.id.initialize({
                client_id: '<?php echo defined('GOOGLE_CLIENT_ID') ? GOOGLE_CLIENT_ID : 'YOUR_GOOGLE_CLIENT_ID'; ?>',
                callback: handleGoogleCredentialResponse,
            });

            const registerBtnContainer = document.getElementById('google-register-btn');
            if (registerBtnContainer) {
                google.accounts.id.renderButton(
                    registerBtnContainer,
                    { 
                        theme: 'filled_black', 
                        size: 'large', 
                        text: 'continue_with', 
                        shape: 'pill',
                        width: '100%' 
                    }
                );
            }
            
            const loginBtnContainer = document.getElementById('google-login-btn');
            if (loginBtnContainer) {
                google.accounts.id.renderButton(
                    loginBtnContainer,
                    { 
                        theme: 'filled_black', 
                        size: 'large', 
                        text: 'signin_with', 
                        shape: 'pill',
                        width: '100%' 
                    }
                );
            }
            
            google.accounts.id.prompt(); 
        }
    </script>
</body>
</html>