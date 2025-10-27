<?php
session_start();
// This file assumes that 'config.php' defines the $pdo object for database connection.
require_once 'config.php';

// Function to send a standardized JSON response
function json_response($success, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

// 1. Get raw JSON input from the request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 2. Check for the requested action
$action = $data['action'] ?? '';

if (empty($action)) {
    json_response(false, 'No action specified.');
}

// --- Staff/Admin Registration Logic (backend_signup) ---
if ($action === 'backend_signup') {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $role = trim($data['role'] ?? '');
    
    // Basic server-side validation
    if (empty($email) || empty($password) || empty($role)) {
        json_response(false, 'Email, password, and role are required fields.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(false, 'Please use a valid email address format.');
    }
    if (!in_array($role, ['trainer', 'admin'])) {
        json_response(false, 'Invalid role specified for registration.');
    }

    try {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            json_response(false, 'This email is already registered. Please sign in instead.');
        }

        // Securely hash the password before storing
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Use the email as the default name (can be updated later in the dashboard)
        $name = explode('@', $email)[0]; 

        // Insert new staff/admin user into the database
        $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, name, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$email, $password_hash, $name, $role]);

        // Success response, prompting user to log in
        json_response(true, "Successfully registered as **" . ucfirst($role) . "**. You can now sign in.", ['email' => $email]);

    } catch (PDOException $e) {
        // Log the error for debugging, but send a generic message to the user
        error_log("DB Signup Error: " . $e->getMessage());
        json_response(false, 'A database error occurred during registration. Please try again later.');
    }
}


// --- Staff/Admin Login Logic (backend_login) ---
if ($action === 'backend_login') {
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        json_response(false, 'Email and password are required for sign-in.');
    }

    try {
        // 1. Fetch user by email, restricting to staff or admin roles
        $stmt = $pdo->prepare("SELECT id, password_hash, role FROM users WHERE email = ? AND (role = 'trainer' OR role = 'admin')");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // 2. Authentication Successful
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            
            // Determine redirect URL
            $redirect_url = ($user['role'] === 'admin') ? 'admin/dashboard/dashboard.php' : 'staff/dashboard/dashboard.php';
            
            json_response(true, 'Login successful!', ['redirect_url' => $redirect_url]);

        } else {
            // 3. Authentication Failed (either bad credentials or user exists but has the wrong role)
            json_response(false, 'Invalid credentials or account not authorized for staff/admin access.');
        }

    } catch (PDOException $e) {
        error_log("DB Login Error: " . $e->getMessage());
        json_response(false, 'A database error occurred during login.');
    }
}

// Default response for unhandled or invalid actions
json_response(false, 'Invalid or unsupported action provided.');
