<?php
session_start();
require_once 'config.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

// Validate token format
if (!$token || strlen($token) !== 64) {
    $error = 'Invalid or missing reset token.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'] ?? '';
    $re_password = $_POST['re_password'] ?? '';

    // Validation
    if (empty($password) || empty($re_password)) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $re_password) {
        $error = 'Passwords do not match.';
    } else {
        // Verify token and get user
        try {
            // FIX: Check if token exists AND expiry time is in the future (comparing unix timestamps)
            $current_time = time();
            $stmt = $pdo->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_token_expires > ? LIMIT 1");
            $stmt->execute([$token, $current_time]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("[v0] Token verification failed for token: " . substr($token, 0, 10) . "...");
                $error = 'Invalid or expired reset token.';
            } else {
                // Update password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
                $result = $stmt->execute([$password_hash, $user['id']]);

                if ($result) {
                    $success = 'Password reset successfully! Redirecting to login...';
                    error_log("[v0] Password reset successful for user: " . $user['id']);
                    header('refresh:2;url=login.php');
                } else {
                    error_log("[v0] Password update failed: " . json_encode($stmt->errorInfo()));
                    $error = 'Failed to update password. Please try again.';
                }
            }
        } catch (PDOException $e) {
            error_log("[v0] Database error in reset-password.php: " . $e->getMessage());
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reset Password - Lena Gym Fitness</title>
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
        .form-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 0.5rem;
            text-align: center;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            animation: textGlow 2s ease-in-out infinite alternate;
        }
        @keyframes textGlow {
            from { text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5), 0 0 10px rgba(236, 19, 19, 0.2); }
            to { text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5), 0 0 20px rgba(236, 19, 19, 0.4); }
        }
        .form-subtitle {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.8);
            text-align: center;
            margin-bottom: 1rem;
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
        .notification {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            text-align: center;
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.3s ease, transform 0.3s ease;
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
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #ec1313;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.75rem;
            position: relative;
        }
        .back-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #ec1313;
            transition: width 0.3s ease;
        }
        .back-link a:hover {
            color: #c51111;
            text-shadow: 0 0 5px rgba(236, 19, 19, 0.5);
            transform: scale(1.05);
        }
        .back-link a:hover::after {
            width: 100%;
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
                
                <h2 class="form-title">Reset Password</h2>
                <p class="form-subtitle">Enter your new password below</p>

                <?php if ($error): ?>
                    <div class="notification error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="notification success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <form method="POST" class="<?php echo $error && strpos($error, 'Invalid or missing') !== false ? 'opacity-50 pointer-events-none' : ''; ?>">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" class="form-input" placeholder="Enter new password" required />
                            <span class="password-toggle" data-target="password">👁️</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <div style="position: relative;">
                            <input type="password" id="re_password" name="re_password" class="form-input" placeholder="Confirm password" required />
                            <span class="password-toggle" data-target="re_password">👁️</span>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" <?php echo $error && strpos($error, 'Invalid or missing') !== false ? 'disabled' : ''; ?>>
                            Reset Password
                        </button>
                    </div>
                </form>

                <div class="back-link">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        function addPasswordToggleListeners() {
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                const targetId = toggle.dataset.target;
                const input = document.getElementById(targetId);
                
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
    </script>
</body>
</html>