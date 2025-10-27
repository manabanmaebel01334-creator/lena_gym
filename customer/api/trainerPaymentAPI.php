<?php
/**
 * trainerPaymentAPI.php
 *
 * JSON backend for billing.php trainer payment section.
 * Fetches trainer payment data from the database for the billing frontend. (GET)
 *
 * Depends on config.php for PDO $pdo and CORS/JSON headers.
 */

// config.php sets headers and provides $pdo
// Ensure this path is correct: __DIR__ . '/../../config.php'
require_once __DIR__ . '/../../config.php'; 

// --- Add Session Start and Authentication Check ---
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
    // --- GET Request: Fetch Trainer Payment Data ---
    // =======================================================================
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        
        // Fetch Trainer Payments (assumed to be transactions with description starting with 'Trainer Fee')
        $stmt = $pdo->prepare("
            SELECT 
                transaction_date, 
                amount, 
                description, 
                payment_method, 
                status
            FROM transactions
            WHERE user_id = :user_id AND description LIKE 'Trainer Fee%'
            ORDER BY transaction_date DESC
            LIMIT 10
        ");
        $stmt->execute(['user_id' => $userId]);
        $trainer_payments_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process data to extract trainer_name and rename fields
        $trainer_payments = [];
        foreach ($trainer_payments_raw as $payment) {
            $trainer_name = 'Unknown';
            if (preg_match('/Trainer Fee for (.*)/i', $payment['description'], $matches)) {
                $trainer_name = trim($matches[1]);
            }

            $trainer_payments[] = [
                'trainer_name' => $trainer_name,
                'amount' => $payment['amount'],
                'payment_date' => $payment['transaction_date'],
                'payment_method' => $payment['payment_method'] ?? 'N/A',
                'status' => $payment['status']
            ];
        }

        // Assemble Final Payload
        $final_data = [
            'trainer_payments' => $trainer_payments, 
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
    error_log("trainerPaymentAPI.php PDO Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load trainer payment data due to a database error.']);
    exit;
} catch (Exception $e) {
    error_log("trainerPaymentAPI.php General Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
    exit;
}