<?php
/**
 * google-auth.php
 * Handles the Google OAuth 2.0 callback and processes user authentication/registration flow.
 */
session_start();
require __DIR__ . '/vendor/autoload.php';
require_once 'config.php';

// Check if the OAuth code is present in the URL
if (!isset($_GET['code'])) {
    // If no code, redirect back to login page
    header('Location: login.php');
    exit;
}

try {
    // 1. Initialize the Google Client
    $client = new Google\Client();
    $client->setClientId(GOOGLE_CLIENT_ID);
    $client->setRedirectUri(GOOGLE_REDIRECT_URI);
    // The client is configured to use the scopes defined in config.php
    $client->setScopes(GOOGLE_SCOPE); 

    // 2. Exchange the Authorization Code for an Access Token
    // This is the crucial step where the client communicates with Google's servers
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    
    // Check for errors during token fetch
    if (isset($token['error'])) {
        throw new Exception("Error fetching access token: " . $token['error_description']);
    }

    $client->setAccessToken($token);

    // 3. Get User Profile Information
    $oauth2 = new Google\Service\Oauth2($client);
    $google_user_info = $oauth2->userinfo->get();

    $email = $google_user_info->email;
    $google_id = $google_user_info->id;
    $name = $google_user_info->name;

    // 4. Check if the user exists in the database
    $stmt = $pdo->prepare("SELECT id, name, provider FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // --- CASE 1: User EXISTS (Login) ---
        
        // Update provider_id if it's a local account now logging in with Google for the first time
        if ($user['provider'] === 'local') {
             $update_stmt = $pdo->prepare("UPDATE users SET provider = 'google', provider_id = :provider_id WHERE id = :id");
             $update_stmt->execute([':provider_id' => $google_id, ':id' => $user['id']]);
        }
        
        // Log the user in
        $_SESSION['auth_id'] = $user['id'];
        $_SESSION['auth_name'] = $user['name'];
        $_SESSION['auth_email'] = $email;
        $_SESSION['auth_provider'] = 'google';
        
        // Redirect to dashboard/home page
        header('Location: login.php');
        exit;
        
    } else {
        // --- CASE 2: User DOES NOT Exist (Redirect to Sign-Up) ---
        
        // Store temporary data in session
        $_SESSION['google_reg_data'] = [
            'email' => $email,
            'name' => $name,
            'provider' => 'google',
            'provider_id' => $google_id
        ];
        
        // Redirect to the login page (which will automatically switch to the Sign-up tab)
        header('Location: login.php');
        exit;
    }

} catch (Exception $e) {
    // Handle errors (e.g., API communication failure)
    $_SESSION['auth_message'] = "Google Authentication Failed: " . $e->getMessage();
    header('Location: login.php');
    exit;
}
?>