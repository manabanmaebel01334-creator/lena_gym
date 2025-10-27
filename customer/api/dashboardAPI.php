<?php
/**
 * dashboard_api.php
 *
 * JSON backend for dashboard.php and billing.php.
 * Fetches all dynamic data from the database for the dashboard frontend. (GET)
 * Also handles updating user metrics. (POST)
 *
 * Depends on config.php for PDO $pdo and CORS/JSON headers.
 */

// config.php sets headers and provides $pdo
// Ensure this path is correct: __DIR__ . '/../../config.php'
require_once __DIR__ . '/../../config.php'; 

// --- FIX 1: Add Session Start and Authentication Check ---
session_start();

// Ensure this API is only accessible to logged-in users.
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401); 
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Please log in.']);
    exit;
}

try {
    // --- Use the logged-in user's ID ---
    $userId = (int) $_SESSION['user_id'];
    
    // =======================================================================
    // --- POST Request: Update User Metrics (Placeholder for existing logic) ---
    // =======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle your existing POST request logic for user metrics here
        
        // Example POST logic structure:
        // if (isset($_POST['update_metrics'])) {
        //     // ... perform update logic
        //     echo json_encode(['success' => true, 'message' => 'Metrics updated.']);
        //     exit;
        // }
    }

    // =======================================================================
    // --- GET Request: Fetch All Dashboard Data (UPDATED) ---
    // =======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // 1. Fetch User Data
        $stmt = $pdo->prepare("SELECT id, email, name, username, role FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $user_raw = $stmt->fetch(PDO::FETCH_ASSOC);

        $user = [
            'id' => $user_raw['id'],
            'name' => $user_raw['name'],
            'role' => $user_raw['role'],
            'avatar_initial' => strtoupper(substr($user_raw['name'], 0, 1))
        ];

        // 🛠️ NEW: 2. Fetch Current Active Membership Status
        $stmt_membership = $pdo->prepare("
            SELECT 
                m.name,
                um.end_date
            FROM user_memberships um
            JOIN memberships m ON um.membership_id = m.membership_id
            WHERE um.user_id = :user_id AND um.status = 'Active'
            ORDER BY um.end_date DESC
            LIMIT 1
        ");
        $stmt_membership->execute(['user_id' => $userId]);
        $current_membership = $stmt_membership->fetch(PDO::FETCH_ASSOC) ?: ['name' => $user['role'], 'end_date' => null];

        
        // 🛠️ NEW: 3. Fetch Transaction History
        $stmt_trans = $pdo->prepare("
            SELECT 
                transaction_date, 
                amount, 
                description, 
                status
            FROM transactions
            WHERE user_id = :user_id
            ORDER BY transaction_date DESC
            LIMIT 10
        ");
        $stmt_trans->execute(['user_id' => $userId]);
        $transactions = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);

        // (Assuming you have logic to fetch progress, schedule, and notifications here...)
        $progress_result = ['weight_goal_pct' => 0, 'calories_today' => 0];
        $upcoming_schedule = [];
        $notifications = [];

        // 4. Assemble Final Payload
        $final_data = [
            'user' => $user, 
            'current_membership' => $current_membership,
            'transactions' => $transactions, 
            'next_session' => $upcoming_schedule[0] ?? null, 
            'rewards' => [ 'points' => 150, 'tier' => 'Bronze' ], 
            'progress' => $progress_result, 
            'notifications' => $notifications,
            'success' => true
        ];

        header('Content-Type: application/json');
        echo json_encode($final_data);
        exit;
        
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed.']);
        exit;
    }

} catch (PDOException $e) {
    error_log("dashboard_api.php PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load user data due to a database error.']);
    exit;
} catch (Exception $e) {
    error_log("dashboard_api.php General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
    exit;
}