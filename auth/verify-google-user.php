<?php
/**
 * verify-google-user.php
 * Utility function to verify if a Google user exists in the database
 * Returns user data if found, null otherwise
 */

function verifyGoogleUserExists($pdo, $google_id, $google_email) {
    try {
        // First, try to find by Google ID (most reliable)
        $stmt = $pdo->prepare("SELECT id, name, email, role, provider, provider_id FROM users WHERE provider_id = ? AND provider = 'google' LIMIT 1");
        $stmt->execute([$google_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return [
                'exists' => true,
                'user' => $user,
                'status' => 'existing_google_user'
            ];
        }

        // Check if email exists (for linking scenarios)
        $stmt = $pdo->prepare("SELECT id, name, email, role, provider, password_hash FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$google_email]);
        $user_by_email = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user_by_email) {
            if (!empty($user_by_email['password_hash'])) {
                // Email has local password - cannot link
                return [
                    'exists' => true,
                    'user' => $user_by_email,
                    'status' => 'email_exists_with_password',
                    'error' => 'This email is already registered with a local account'
                ];
            } else {
                // Email exists but no password - can link
                return [
                    'exists' => true,
                    'user' => $user_by_email,
                    'status' => 'email_exists_no_password'
                ];
            }
        }

        // User doesn't exist
        return [
            'exists' => false,
            'status' => 'new_user'
        ];

    } catch (PDOException $e) {
        error_log("[v0] Database error in verifyGoogleUserExists: " . $e->getMessage());
        return [
            'exists' => false,
            'status' => 'error',
            'error' => 'Database error'
        ];
    }
}

/**
 * Create OTP session for login
 */
function createLoginOtpSession($user_id, $email, $name, $role) {
    $otp = (string)random_int(100000, 999999);
    $expiresAt = time() + 5 * 60; // 5 minutes

    $_SESSION['pending_login'] = [
        'user_id' => $user_id,
        'email' => $email,
        'name' => $name,
        'role' => $role ?? 'member',
        'otp' => $otp,
        'expires_at' => $expiresAt,
    ];

    return $otp;
}

/**
 * Store Google registration data for signup form pre-filling
 */
function storeGoogleRegistrationData($google_email, $google_name, $google_id, $google_picture = '') {
    $_SESSION['google_reg_data'] = [
        'email' => $google_email,
        'name' => $google_name,
        'provider_id' => $google_id,
        'picture' => $google_picture,
        'provider' => 'google'
    ];

    return $_SESSION['google_reg_data'];
}
?>
