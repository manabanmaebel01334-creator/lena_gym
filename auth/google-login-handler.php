<?php
/**
 * google-login-handler.php
 * Dedicated handler for Google OAuth login verification and user routing
 * 
 * This file handles:
 * 1. Verification of Google ID tokens
 * 2. Checking if user exists in database
 * 3. Routing to appropriate flow (login OTP or signup with pre-filled data)
 */

session_start();
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$idToken = $data['id_token'] ?? '';

if (empty($idToken)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing Google ID token']);
    exit;
}

try {
    // Initialize Google Client and verify the ID Token
    $client = new Google\Client(['client_id' => GOOGLE_CLIENT_ID]);
    
    try {
        $payload = $client->verifyIdToken($idToken);
    } catch (Exception $e) {
        error_log('[v0] Google ID Token verification failed: ' . $e->getMessage());
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Google verification failed']);
        exit;
    }

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid Google ID token']);
        exit;
    }

    // Extract user information from Google token
    $google_email = $payload['email'] ?? '';
    $google_name = $payload['name'] ?? 'Google User';
    $google_id = $payload['sub'] ?? '';
    $google_picture = $payload['picture'] ?? '';

    if (!$google_email || !$google_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid Google profile data']);
        exit;
    }

    error_log("[v0] Google login attempt: email=$google_email, name=$google_name");

    // Check if user exists by Google ID (already registered via Google)
    $stmt = $pdo->prepare("SELECT id, name, email, role, provider FROM users WHERE provider_id = ? AND provider = 'google' LIMIT 1");
    $stmt->execute([$google_id]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        // CASE 1: User exists with Google provider -> Initiate OTP login
        error_log("[v0] Existing Google user found: user_id=" . $existing_user['id']);
        
        // Generate OTP for 2FA
        $otp = (string)random_int(100000, 999999);
        $expiresAt = time() + 5 * 60; // 5 minutes

        $_SESSION['pending_login'] = [
            'user_id' => $existing_user['id'],
            'email' => $existing_user['email'],
            'name' => $existing_user['name'],
            'role' => $existing_user['role'] ?? 'member',
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ];

        // Send OTP email
        require_once __DIR__ . '/../mailer.php';
        $mailOk = email_otp_send($existing_user['email'], 'Your Verification Code for Lena Gym', $otp);

        echo json_encode([
            'success' => true,
            'message' => 'Google account verified. Please enter the OTP sent to your email.',
            'action' => 'verify_otp',
            'email' => $existing_user['email'],
            'redirect_to_otp_login' => true
        ]);
        exit;

    } else {
        // CASE 2: New user or user exists by email only
        $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$google_email]);
        $user_by_email = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_by_email && !empty($user_by_email['password_hash'])) {
            // Email exists with local password - prevent account takeover
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'This email is already registered with a local account. Please use the standard login form or reset your password.'
            ]);
            exit;
        }

        // Store Google registration data in session for signup form pre-filling
        $_SESSION['google_reg_data'] = [
            'email' => $google_email,
            'name' => $google_name,
            'provider_id' => $google_id,
            'picture' => $google_picture,
            'provider' => 'google'
        ];

        error_log("[v0] New Google user - redirecting to signup: email=$google_email");

        echo json_encode([
            'success' => true,
            'message' => 'Welcome! Please complete your registration.',
            'action' => 'signup',
            'redirect_to_otp_login' => false,
            'google_data' => $_SESSION['google_reg_data']
        ]);
        exit;
    }

} catch (PDOException $e) {
    error_log("[v0] Database Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
    exit;
} catch (Exception $e) {
    error_log("[v0] Unexpected Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred']);
    exit;
}
?>
