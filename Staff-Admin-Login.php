<?php
session_start();
error_reporting(0);
require __DIR__ . '/vendor/autoload.php';
require_once 'config.php'; 

$success_message = '';
if (isset($_SESSION['auth_message'])) {
    $success_message = $_SESSION['auth_message'];
    unset($_SESSION['auth_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Staff/Admin Access - Lena Gym Fitness</title>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script id="tailwind-config">
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
        };
    </script>
    <style>
        /* CSS for overall design and animation */
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
            /* Container for login/signup forms */
            perspective: 1000px; 
            min-height: 450px; /* Ensure card has height for transition */
        }
        /* Form Transition Styles */
        .form-content {
            transition: transform 0.6s ease-in-out, opacity 0.6s ease-in-out, height 0.6s ease;
            backface-visibility: hidden;
            position: absolute;
            width: 100%;
            top: 0;
            left: 0;
            padding: 1.5rem;
            box-sizing: border-box;
        }
        .login-form-container {
            z-index: 2;
        }
        .signup-form-container {
            transform: rotateY(180deg);
            opacity: 0;
            z-index: 1;
        }
        .auth-card.active-signup .login-form-container {
            transform: rotateY(-180deg);
            opacity: 0;
        }
        .auth-card.active-signup .signup-form-container {
            transform: rotateY(0deg);
            opacity: 1;
        }
        /* End Form Transition Styles */

        @media (max-width: 768px) {
            .auth-card { background: rgba(255, 255, 255, 0.2); }
        }
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes glow {
            from { box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 20px rgba(236, 19, 19, 0.1); }
            to { box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3), 0 0 30px rgba(236, 19, 19, 0.2); }
        }
        .logo-image {
            width: 50px; height: auto; margin: 0 auto 0.75rem; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
            transition: transform 0.3s ease; animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }
        .form-title {
            font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 1rem; text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); animation: textGlow 2s ease-in-out infinite alternate;
        }
        /* Input and Button Styles */
        .form-group { margin-bottom: 0.75rem; position: relative; }
        .form-input {
            width: 100%; padding: 0.75rem; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 0.5rem;
            font-size: 0.875rem; background: rgba(255, 255, 255, 0.95); color: #000;
            backdrop-filter: blur(10px); box-sizing: border-box; padding-right: 2.5rem;
        }
        .form-label {
            display: block; font-size: 0.75rem; font-weight: 500; color: #fff; margin-top: 0.25rem;
            text-align: left; text-shadow: 0 0 5px rgba(236, 19, 19, 0.5);
        }
        .btn {
            padding: 0.5rem; border: none; border-radius: 0.5rem; font-weight: 600; font-size: 0.75rem;
            cursor: pointer; transition: all 0.3s ease; position: relative; overflow: hidden;
            text-transform: uppercase; letter-spacing: 0.05em; z-index: 1;
        }
        .btn-primary { background: #ec1313; color: white; }
        .btn-primary:hover { background: #c51111; transform: translateY(-2px) scale(1.05); }
        .notification {
            margin-top: 1rem; padding: 0.75rem; border-radius: 0.5rem; font-size: 0.75rem;
            text-align: center; opacity: 0; transition: opacity 0.3s ease;
        }
        .notification.success { background: rgba(0, 128, 0, 0.2); color: #00ff00; border: 1px solid #00ff00; }
        .notification.error { background: rgba(255, 0, 0, 0.2); color: #ff0000; border: 1px solid #ff0000; }
        
        /* Icon and Link styles */
        .password-toggle {
            position: absolute; right: 10px; top: calc(50% + 8px); transform: translateY(-50%); 
            cursor: pointer; user-select: none; font-size: 1.1em; z-index: 10; color: #4b5563; 
            transition: color 0.2s ease;
        }
        .password-toggle:hover { color: #ec1313; }
        .switch-link {
            color: #ec1313; font-weight: 600; text-decoration: none; transition: all 0.3s ease;
            font-size: 0.75rem; position: relative;
        }
        .form-select {
            appearance: none; -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='none'%3e%3cpath d='M7 7l3-3 3 3m0 6l-3 3-3-3' stroke='%234B5563' stroke-width='1.5' stroke-linecap='round' stroke-linejoin='round'/%3e%3c/svg%3e");
            background-repeat: no-repeat; background-position: right 0.75rem center;
            background-size: 1.5em 1.5em; padding-right: 2.5rem;
        }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div id="particles"></div>
    <div class="auth-container">
        <div class="auth-card" id="authCard">
            
            <div class="form-content login-form-container" id="loginContainer">
                <img src="assets/image/logo.png" alt="Lena Gym Logo" class="logo-image" />
                <h2 class="form-title">Staff & Admin Sign In</h2>
                
                <?php if ($success_message): ?>
                    <div id="php-notification" class="notification success visible" style="opacity: 1; transform: translateY(0); margin-bottom: 1rem;">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <form class="space-y-6" id="loginForm">
                    <div class="form-group">
                        <label for="login-email" class="form-label">Email Address</label>
                        <input id="login-email" name="email" type="email" autocomplete="email" required 
                            class="form-input" placeholder="Enter email" >
                    </div>

                    <div class="form-group relative">
                        <label for="login-password" class="form-label">Password</label>
                        <input id="login-password" name="password" type="password" autocomplete="current-password" required 
                            class="form-input" placeholder="Enter password">
                        <span class="password-toggle" data-target="login-password">👁️</span>
                    </div>

                    <div id="login-error-message" class="notification error hidden"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Sign In
                        </button>
                    </div>
                </form>

                <p class="text-center text-xs mt-4 text-white/80">
                    Need an account? 
                    <a href="#" id="showSignup" class="switch-link">Register Now</a>
                </p>
                <p class="text-center text-xs mt-2 text-white/80">
                    <a href="login.php" class="switch-link">Return to Member Login</a>
                </p>
            </div>

            <div class="form-content signup-form-container" id="signupContainer">
                <img src="assets/image/logo.png" alt="Lena Gym Logo" class="logo-image" />
                <h2 class="form-title">Staff & Admin Registration</h2>

                <form class="space-y-6" id="signupForm">
                    <div class="form-group">
                        <label for="signup-email" class="form-label">Email Address</label>
                        <input id="signup-email" name="email" type="email" autocomplete="email" required 
                            class="form-input" placeholder="Enter email" >
                    </div>

                    <div class="form-group relative">
                        <label for="signup-password" class="form-label">Password</label>
                        <input id="signup-password" name="password" type="password" autocomplete="new-password" required 
                            class="form-input" placeholder="Create password">
                        <span class="password-toggle" data-target="signup-password">👁️</span>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Account Role</label>
                        <select id="role" name="role" required class="form-input form-select">
                            <option value="trainer">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>

                    <div id="signup-error-message" class="notification error hidden"></div>
                    <div id="signup-success-message" class="notification success hidden"></div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            Register Account
                        </button>
                    </div>
                </form>

                <p class="text-center text-xs mt-4 text-white/80">
                    Already registered? 
                    <a href="#" id="showLogin" class="switch-link">Sign In</a>
                </p>
                <p class="text-center text-xs mt-2 text-white/80">
                    <a href="login.php" class="switch-link">Return to Member Login</a>
                </p>
            </div>

        </div>
    </div>

    <script>
        const authCard = document.getElementById('authCard');
        const loginForm = document.getElementById('loginForm');
        const signupForm = document.getElementById('signupForm');
        const showSignupLink = document.getElementById('showSignup');
        const showLoginLink = document.getElementById('showLogin');

        const loginErrorMsg = document.getElementById('login-error-message');
        const signupErrorMsg = document.getElementById('signup-error-message');
        const signupSuccessMsg = document.getElementById('signup-success-message');
        
        // --- Modal Toggling Logic ---
        showSignupLink.addEventListener('click', (e) => {
            e.preventDefault();
            authCard.classList.add('active-signup');
            // Reset notifications when switching views
            loginErrorMsg.classList.add('hidden');
            signupErrorMsg.classList.add('hidden');
            signupSuccessMsg.classList.add('hidden');
            signupForm.reset(); // Optionally clear the signup form
        });

        showLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            authCard.classList.remove('active-signup');
             // Reset notifications when switching views
            loginErrorMsg.classList.add('hidden');
            signupErrorMsg.classList.add('hidden');
            signupSuccessMsg.classList.add('hidden');
            loginForm.reset(); // Optionally clear the login form
        });

        // --- Common Utility Functions (Copied from previous versions) ---
        function createParticles() {
            const container = document.getElementById('particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 6 + 's';
                particle.style.animationDuration = (Math.random() * 3 + 3) + 's';
                particle.style.setProperty('--direction', Math.random() > 0.5 ? '1' : '-1');
                container.appendChild(particle);
            }
        }
        
        function showNotification(container, message, isSuccess = true) {
            // Hide the *other* notification containers in the current view
            const siblingContainer = isSuccess ? signupErrorMsg : signupSuccessMsg;
            if(siblingContainer) siblingContainer.classList.add('hidden');

            container.textContent = message;
            container.classList.remove('hidden');
            container.classList.add('visible', isSuccess ? 'success' : 'error');
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
            
            setTimeout(() => {
                container.classList.remove('visible');
                container.classList.add('hidden');
                container.style.opacity = '0';
            }, 4000);
        }

        function addPasswordToggleListeners() {
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                const targetId = toggle.dataset.target;
                let input = document.getElementById(targetId);
                
                if (!input) return;
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
            addPasswordToggleListeners();
        };

        // --- Form Submission Logic ---
        async function handleFormSubmission(e, action, errorContainer, successContainer = null, resetForm = false) {
            e.preventDefault();
            errorContainer.classList.add('hidden');
            if (successContainer) successContainer.classList.add('hidden');

            const form = e.target;
            const submitBtn = form.querySelector('.btn-primary');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.textContent = (action === 'backend_login') ? 'Verifying...' : 'Processing...';
            submitBtn.classList.add('pulsing');

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            data.action = action;

            try {
                const response = await fetch('adminStaffAuth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data),
                });

                const result = await response.json();

                if (result.success) {
                    const msgContainer = successContainer || errorContainer;
                    showNotification(msgContainer, result.message || 'Success!', true);
                    
                    if (resetForm) form.reset();
                    
                    if (action === 'backend_login' && result.redirect_url) {
                        setTimeout(() => { window.location.href = result.redirect_url; }, 500);
                    } else if (action === 'backend_signup') {
                        // Switch to login form after successful signup
                        setTimeout(() => { authCard.classList.remove('active-signup'); }, 1500);
                    }
                } else {
                    showNotification(errorContainer, result.message || 'Operation failed. Please check your input.', false);
                }
            } catch (error) {
                console.error('Form error:', error);
                showNotification(errorContainer, 'An unexpected error occurred. Please try again.', false);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                submitBtn.classList.remove('pulsing');
            }
        }
        
        loginForm.addEventListener('submit', (e) => handleFormSubmission(e, 'backend_login', loginErrorMsg, null, false));
        signupForm.addEventListener('submit', (e) => handleFormSubmission(e, 'backend_signup', signupErrorMsg, signupSuccessMsg, true));

        // Ripple Effect
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn');
            if (!btn) return;

            const ripple = document.createElement('span');
            const rect = btn.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;

            ripple.style.cssText = `
                position: absolute; border-radius: 50%; background: rgba(255, 255, 255, 0.6);
                transform: scale(0); animation: ripple 0.6s linear; width: ${size}px; height: ${size}px;
                left: ${x}px; top: ${y}px; pointer-events: none; z-index: 10;
            `;
            ripple.classList.add('ripple-effect');
            btn.appendChild(ripple);
            setTimeout(() => ripple.remove(), 600);
        });

        const rippleStyle = document.createElement('style');
        rippleStyle.textContent = `@keyframes ripple { to { transform: scale(4); opacity: 0; } }`;
        document.head.appendChild(rippleStyle);
    </script>
</body>
</html>