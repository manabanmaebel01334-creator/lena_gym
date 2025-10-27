<?php
/**
 * auth.php
 * Handles user registration, secure login, social login, and OTP verification flow.
 */
session_start();

// Ensure composer autoload and config are available
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

// --- Load the dedicated mailer function ---
if (file_exists(__DIR__ . '/mailer.php')) {
    require_once __DIR__ . '/mailer.php';
}
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Decode incoming JSON request
$data = json_decode(file_get_contents('php://input'), true) ?? [];

$action = $data['action'] ?? '';

// Helper: send OTP email (returns true/false)
function sendOtpEmail($to, $otp) {
    $subject = "Your Verification Code for Lena Gym";
    if (function_exists('email_otp_send')) {
        try {
            // Assume email_otp_send is defined in mailer.php and handles the actual sending
            return (bool)email_otp_send($to, $subject, $otp); 
        } catch (Exception $e) {
            error_log('[v0] PHPMailer failed: ' . $e->getMessage());
        }
    }
    // Fallback message for debugging if mailer is not set up
    error_log("[v0] PHPMailer function email_otp_send not found or failed setup. OTP: $otp"); 
    return true; // Assume success in dev environment if mailer fails
}

// Helper: Initiate a session for logged-in user
function establishUserSession($user) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'] ?? $user['name'] ?? '';
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'] ?? 'customer';
}

function prepareGoogleRegistrationData($googleEmail, $googleName, $googleId) {
    return [
        'email' => $googleEmail,
        'name' => $googleName,
        'provider_id' => $googleId
    ];
}

try {
    // --- CLEAR GOOGLE REGISTRATION SESSION (Existing) ---
    if ($action === 'clear_google_reg_session') {
        // This action is vestigial in the new OTP flow, but kept for compatibility.
        unset($_SESSION['google_reg_data']); 
        echo json_encode(['success' => true, 'message' => 'Session cleared.']);
        exit;
    }

    // --- NEW ACTION: GOOGLE SIGN-IN/REGISTRATION (OAuth 2.0) ---
    else if ($action === 'google_auth_login') {
        global $pdo;
        // The client sends the ID Token received from the Google Sign-In JS library.
        $idToken = $data['id_token'] ?? ''; 
        if (empty($idToken)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing Google ID token.']);
            exit;
        }

        // Initialize Google Client and verify the ID Token server-side
        // GOOGLE_CLIENT_ID must be defined in config.php
        $client = new Google\Client(['client_id' => GOOGLE_CLIENT_ID]); 
        try {
            $payload = $client->verifyIdToken($idToken);
        } catch (Exception $e) {
            error_log('[v0] Google ID Token verification failed: ' . $e->getMessage());
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Google verification failed.']);
            exit;
        }

        if (!$payload) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid Google ID token.']);
            exit;
        }

        $google_email = $payload['email'];
        // Use the 'name' field from the payload for the user's full name
        $google_name = $payload['name'] ?? 'Google User'; 
        // 'sub' is the unique Google user ID
        $google_id = $payload['sub']; 
        $userIdToUpdate = null;

        // 1. Check if user exists by Google ID (Already signed up via Google)
        $stmt = $pdo->prepare("SELECT id, name, username, email, role, password_hash FROM users WHERE provider_id = ? AND provider = 'google' LIMIT 1");
        $stmt->execute([$google_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Generate OTP and set expiry (5 minutes) for the 2FA step
        $otp = (string)random_int(100000, 999999);
        $expiresAt = time() + 5 * 60; 

        if ($user) {
            // CASE 1: Found existing Google user -> Initiate OTP Login
            
            $_SESSION['pending_login'] = [
                'user_id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'] ?? $user['name'] ?? '',
                'role' => $user['role'] ?? 'customer',
                'otp' => $otp,
                'expires_at' => $expiresAt,
            ];
            unset($_SESSION['pending_registration']);

            $mailOk = sendOtpEmail($user['email'], $otp);
            $message = $mailOk ? 'Google Sign-In successful. Please enter the OTP sent to your email to continue.' : 'OTP generated but email delivery failed.';

            echo json_encode([
                'success' => true,
                'message' => $message,
                'redirect_to_otp_login' => true,
                'email' => $user['email']
            ]);
            exit;

        } else {
            // CASE 2: New user OR User exists by email but needs social link
            $stmt = $pdo->prepare("SELECT id, password_hash, name FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$google_email]);
            $user_by_email = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user_by_email && !empty($user_by_email['password_hash'])) {
                // Email is registered with a local password (Prevent takeover)
                http_response_code(409);
                echo json_encode(['success' => false, 'message' => 'This email is already registered with a local account. Please use the standard login form or reset your password.']);
                exit;
            } else if ($user_by_email) {
                // Email exists, but is an incomplete social user or needs a social link. Update ID after OTP.
                $userIdToUpdate = $user_by_email['id'];
                // Use the existing user's name if available, otherwise use Google's name
                $google_name = $user_by_email['name'] ?? $google_name; 
            }

            $_SESSION['google_reg_data'] = prepareGoogleRegistrationData($google_email, $google_name, $google_id);

            echo json_encode([
                'success' => true,
                'message' => 'Welcome! Please complete your registration with a password.',
                'redirect_to_otp_login' => false,
                'email' => $google_email,
                'google_data' => $_SESSION['google_reg_data']
            ]);
            exit;
        }
    }

    // --- LOGIN (email + password) -> Direct login without OTP ---
    else if ($action === 'login') {
        global $pdo;
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        error_log("[v0] Login attempt for email: $email");
        
        $stmt = $pdo->prepare("SELECT id, name, username, password_hash, role, email, provider FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            error_log("[v0] Prepare failed: " . json_encode($pdo->errorInfo()));
            throw new Exception("Database prepare error");
        }
        
        $stmt->execute([ $email ]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            exit;
        }
        
        establishUserSession($user);
        unset($_SESSION['pending_login']);
        unset($_SESSION['pending_registration']);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful! Redirecting to dashboard.',
            'redirect' => 'customer/dashboard/dashboard.php',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
        exit;
    } 

    // --- NEW ACTION: REGISTER (Local email registration) -> Leads to OTP Verification ---
    else if ($action === 'register') {
        global $pdo;
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $re_password = $data['re_password'] ?? '';
        $provider_id = $data['provider_id'] ?? null;

        if ($password !== $re_password) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
            exit;
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
            exit;
        }

        // Generate OTP and store pending registration data
        $otp = (string)random_int(100000, 999999);
        $expiresAt = time() + 5 * 60; // 5 minutes expiry

        $_SESSION['pending_registration'] = [
            'email' => $email,
            'name' => $name,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'provider' => $provider_id ? 'google' : 'local',
            'provider_id' => $provider_id,
            'role' => 'member',
            'otp' => $otp,
            'expires_at' => $expiresAt,
            'existing_user_id' => null,
        ];
        unset($_SESSION['pending_login']);

        $mailOk = sendOtpEmail($email, $otp);
        $message = $mailOk ? 'Registration successful! Please enter the OTP sent to your email to complete your account setup.' : 'OTP generated but email delivery failed.';

        echo json_encode([
            'success' => true,
            'message' => $message,
            'redirect_to_otp_login' => true,
            'email' => $email
        ]);
        exit;
    }
    
    // --- NEW ACTION: VERIFY OTP ---
    else if ($action === 'verify_otp') {
        global $pdo;
        $otp_code = $data['otp_code'] ?? '';

        $sessionKey = isset($_SESSION['pending_login']) ? 'pending_login' : (isset($_SESSION['pending_registration']) ? 'pending_registration' : null);

        if (!$sessionKey) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No pending verification session found. Please try logging in/registering again.']);
            exit;
        }

        $pending = $_SESSION[$sessionKey];
        
        if (time() > $pending['expires_at']) {
            unset($_SESSION[$sessionKey]);
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new code.']);
            exit;
        }

        if ($otp_code !== $pending['otp']) {
            // Note: In a production system, you might implement rate limiting/lockouts here.
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Invalid verification code. Please try again.']);
            exit;
        }

        // OTP is correct and not expired. Process the final action.
        
        if ($sessionKey === 'pending_login') {
            // LOGIN SUCCESS: Fetch user data and log them in
            $stmt = $pdo->prepare("SELECT id, name, username, email, role FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$pending['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                establishUserSession($user);
                unset($_SESSION['pending_login']); // Delete OTP on success
                echo json_encode([
                    'success' => true,
                    'message' => 'Verification successful! Log in your account!.',
                    'redirect' => 'login.php'
                ]);
                exit;
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Verification successful, but user data not found.']);
                exit;
            }

        } else if ($sessionKey === 'pending_registration') {
            // REGISTRATION SUCCESS: Insert or Update user data
            
            try {
                if ($pending['existing_user_id']) {
                    // Scenario: Google Sign-In with existing incomplete user. Update row to link provider info.
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, provider = ?, provider_id = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$pending['name'], $pending['provider'], $pending['provider_id'], $pending['existing_user_id']]);
                    
                    if (!$result) {
                        error_log("[v0] UPDATE failed for user_id: " . $pending['existing_user_id']);
                        error_log("[v0] PDO Error: " . json_encode($stmt->errorInfo()));
                        throw new Exception("Failed to update user record");
                    }
                    
                    $userId = $pending['existing_user_id'];
                    $message = "Account linked successfully. Welcome!";
                    error_log("[v0] User updated successfully: user_id=$userId");
                } else {
                    // Scenario: New local or new social registration. Insert a new row.
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, provider, provider_id) VALUES (?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([
                        $pending['name'], 
                        $pending['email'], 
                        $pending['password_hash'], 
                        $pending['role'], 
                        $pending['provider'], 
                        $pending['provider_id']
                    ]);
                    
                    if (!$result) {
                        error_log("[v0] INSERT failed for email: " . $pending['email']);
                        error_log("[v0] PDO Error: " . json_encode($stmt->errorInfo()));
                        error_log("[v0] Data being inserted: " . json_encode([
                            'name' => $pending['name'],
                            'email' => $pending['email'],
                            'role' => $pending['role'],
                            'provider' => $pending['provider'],
                            'provider_id' => $pending['provider_id']
                        ]));
                        throw new Exception("Failed to insert user record");
                    }
                    
                    $userId = $pdo->lastInsertId();
                    $message = "Registration complete! Welcome to Lena Gym.";
                    error_log("[v0] User registered successfully: user_id=$userId, email=" . $pending['email']);
                }

                // Log the newly registered/verified user in
                establishUserSession(['id' => $userId, 'name' => $pending['name'], 'email' => $pending['email'], 'role' => $pending['role']]);
                unset($_SESSION['pending_registration']);

                // Set a temporary message for dashboard.php
                $_SESSION['auth_message'] = $message;

                echo json_encode([
                    'success' => true,
                    'message' => $message,
                    'redirect' => 'login.php'
                ]);
                exit;
                
            } catch (Exception $e) {
                error_log("[v0] Registration verification error: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'An error occurred while completing registration. Please try again.']);
                exit;
            }
        }
    }

    // --- NEW ACTION: FORGOT PASSWORD ---
    else if ($action === 'forgot_password') {
        global $pdo;
        $email = trim($data['email'] ?? '');

        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email address is required.']);
            exit;
        }

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            error_log("[v0] Prepare failed for forgot_password: " . json_encode($pdo->errorInfo()));
            throw new Exception("Database prepare error");
        }
        
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // For security, don't reveal if email exists or not
            echo json_encode([
                'success' => true,
                'message' => 'If an account exists with this email, a password reset link has been sent.'
            ]);
            exit;
        }

        // Generate a unique reset token (valid for 1 hour)
        $reset_token = bin2hex(random_bytes(32));
        $token_expires = time() + 3600;// 1 hour expiry

        // Store reset token in database
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        if (!$stmt) {
            error_log("[v0] Prepare failed for reset token update: " . json_encode($pdo->errorInfo()));
            throw new Exception("Database prepare error");
        }
        
        $result = $stmt->execute([$reset_token, $token_expires, $user['id']]);
        if (!$result) {
            error_log("[v0] UPDATE failed for reset token: " . json_encode($stmt->errorInfo()));
            error_log("[v0] User ID: " . $user['id']);
            throw new Exception("Failed to store reset token");
        }

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        
        // For localhost testing, you can manually set the domain here
        // Uncomment and modify the line below if needed:
        // $host = 'yourdomain.com'; // Replace with your actual domain for production
        
        $reset_link = $protocol . "://" . $host . "/gymrat/reset-password.php?token=" . $reset_token;

        // Send reset email
        $subject = "Password Reset Request - Lena Gym";
        $body = "
            <h2>Password Reset Request</h2>
            <p>Hi {$user['name']},</p>
            <p>We received a request to reset your password. Click the link below to proceed:</p>
            <p><a href='{$reset_link}' style='background-color: #ec1313; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Reset Password</a></p>
            <p>This link will expire in 1 hour.</p>
            <p>If you didn't request this, please ignore this email.</p>
            <p>Best regards,<br>Lena Gym Team</p>
        ";

        if (function_exists('email_otp_send')) {
            try {
                email_otp_send($user['email'], $subject, $body);
            } catch (Exception $e) {
                error_log('[v0] Password reset email failed: ' . $e->getMessage());
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'If an account exists with this email, a password reset link has been sent.'
        ]);
        exit;
    }

    // --- NEW ACTION: RESEND OTP ---
    else if ($action === 'resend_otp') {
        $sessionKey = isset($_SESSION['pending_login']) ? 'pending_login' : (isset($_SESSION['pending_registration']) ? 'pending_registration' : null);
        if (!$sessionKey) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No pending verification session.']);
            exit;
        }

        $pending = $_SESSION[$sessionKey];
        
        // Regenerate OTP (6-digit) and new expiry (5 minutes)
        $otp = (string)random_int(100000, 999999);
        $expiresAt = time() + 5 * 60; // 5 minutes expiry

        $_SESSION[$sessionKey]['otp'] = $otp;
        $_SESSION[$sessionKey]['expires_at'] = $expiresAt;

        $mailOk = sendOtpEmail($pending['email'], $otp);
        $message = $mailOk ? 'A new OTP has been sent to your email.' : 'New OTP generated but email delivery failed.';

        echo json_encode([
            'success' => $mailOk,
            'message' => $message
        ]);
        exit;
    }

    else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
        exit;
    }

} catch (PDOException $e) {
    error_log("[v0] Database Error: " . $e->getMessage());
    error_log("[v0] Error Code: " . $e->getCode());
    error_log("[v0] Error Info: " . json_encode($e->errorInfo ?? []));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'A database error occurred. Please check the server logs.']);
    exit;
} catch (Exception $e) {
    error_log("[v0] General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
    exit;
}
?>
