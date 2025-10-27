<?php
// paymentAPI.php
// Handles AJAX requests for fetching bookings (and their payment status) and recording payments.

session_start();
// Include the configuration file for database connection
// NOTE: Assuming your config.php is located one level up from the current directory (../config.php).
require_once '../../config.php'; 

header('Content-Type: application/json');

// --- 0. Database Schema Check/Update (Simulated) ---
/*
// **NOTE: As an AI, I cannot execute this, but you MUST run this on your DB.**
// We need to ensure the 'transactions' table can store 'Pending' and has a dedicated 'user_email' field for the display drawer.
try {
    // 1. Add email column to transactions (if needed, though client email is in 'users')
    // A better approach is to fetch client email during handleFetchBookings.
    
    // 2. Ensure 'transactions' table structure is suitable (status can be 'Paid' or 'Pending')
    // No explicit ALTER TABLE is strictly needed here, as the logic will handle status.
} catch (PDOException $e) {
    // Log error but continue
    error_log("Database structure check failed (simulated): " . $e->getMessage());
}
*/
// --- 1. Security Check ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access. Please log in.']);
    exit;
}

// Basic authorization check for staff roles (trainer or admin)
try {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_role = $stmt->fetchColumn();

    if ($user_role !== 'trainer' && $user_role !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied. Insufficient privileges.']);
        exit;
    }
} catch (PDOException $e) {
    error_log("API Auth Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error during authorization.']);
    exit;
}


// Determine the action to perform
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No action specified.']);
    exit;
}

// --- 2. Action Handlers ---

switch ($action) {
    case 'fetchBookings':
        handleFetchBookings($pdo);
        break;
    
    case 'savePayment':
        handleSavePayment($pdo, $_SESSION['user_id']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid action.']);
        break;
}

// --- 3. Function Definitions ---

/**
 * Fetches all 'Confirmed' bookings and their associated payment status.
 */
function handleFetchBookings($pdo) {
    // MODIFICATION: Fetching user.email and transaction status without filtering for 'Paid'
    // This allows fetching 'Pending' transactions as well.
    $sql = "
        SELECT 
            b.booking_id,
            u.name AS client_name,
            u.email AS client_email, -- ADDED: Client email for the Details drawer
            s.name AS service_name,
            s.price AS service_price,
            b.start_time,
            b.end_time,
            -- MODIFIED: Check for ANY associated transaction
            COALESCE(t.status, 'Unpaid') AS payment_status, 
            t.transaction_id,
            COALESCE(t.amount, s.price) AS paid_amount,
            COALESCE(t.payment_method, '') AS payment_method,
            COALESCE(t.description, '') AS payment_remarks -- Stored remarks from new logic will be here
        FROM 
            bookings b
        JOIN 
            users u ON b.user_id = u.id
        JOIN 
            services s ON b.service_id = s.service_id
        LEFT JOIN 
            transactions t ON b.booking_id = t.related_booking_id
        WHERE 
            b.status = 'Confirmed'
        ORDER BY 
            b.start_time DESC
    ";

    try {
        $stmt = $pdo->query($sql);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedBookings = array_map(function($b) {
            // Format datetime and currency for display
            $start = new DateTime($b['start_time']);
            $end = new DateTime($b['end_time']);
            
            $b['datetime'] = $start->format('M d, Y') . ' ' . $start->format('H:i') . ' - ' . $end->format('H:i');
            $b['amount_due'] = number_format((float)$b['service_price'], 2);
            $b['paid_amount'] = number_format((float)$b['paid_amount'], 2); // Display paid amount or service price
            
            // MODIFIED LOGIC: Use the actual payment_status from the transaction (Paid, Pending, or Unpaid)
            if (empty($b['transaction_id'])) {
                $b['payment_status'] = 'Unpaid';
            }
            // If there's a transaction, $b['payment_status'] already contains 'Paid' or 'Pending' (or another status)

            unset($b['start_time'], $b['end_time'], $b['service_price']);
            return $b;
        }, $bookings);

        echo json_encode(['success' => true, 'bookings' => $formattedBookings]);

    } catch (PDOException $e) {
        error_log("Fetch Bookings Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error while fetching bookings.']);
    }
}

/**
 * Records a new 'Pending' payment or updates an existing 'Pending' transaction for a booking.
 * The logic is simplified as per the new requirement: no payment method/remarks are saved, 
 * and status is always 'Pending' on staff submission.
 */
function handleSavePayment($pdo, $staff_user_id) {
    $booking_id = filter_var($_POST['booking_id'] ?? null, FILTER_VALIDATE_INT);
    $transaction_id = filter_var($_POST['transaction_id'] ?? 0, FILTER_VALIDATE_INT);
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    
    // MODIFICATION: Payment method and remarks are no longer mandatory/used in this flow
    $payment_method = 'Staff-Recorded'; // Default method for internal tracking
    $remarks = 'Pending payment recorded by staff.';

    if (!$booking_id || $amount === null || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing or invalid required payment amount.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Get User ID for the transaction
        $stmt = $pdo->prepare("SELECT user_id, service_id FROM bookings WHERE booking_id = ?");
        $stmt->execute([$booking_id]);
        $booking_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking_data) {
            $pdo->rollBack();
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Booking not found.']);
            exit;
        }
        $user_id = $booking_data['user_id'];
        $service_id = $booking_data['service_id'];

        $description = "Set for payment: {$amount} for Booking ID: {$booking_id}";

        // 2. Insert or Update Transaction
        if ($transaction_id > 0) {
            // Update existing transaction. MODIFICATION: Status is now forced to 'Pending'
            $sql = "
                UPDATE transactions 
                SET 
                    amount = ?, 
                    description = ?, 
                    payment_method = ?, 
                    status = 'Pending', -- FORCED PENDING STATUS
                    transaction_date = CURRENT_TIMESTAMP
                WHERE 
                    transaction_id = ? AND related_booking_id = ?
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$amount, $description, $payment_method, $transaction_id, $booking_id]);
            $message = "Payment intention (Transaction ID: {$transaction_id}) updated successfully to PENDING.";

        } else {
            // Insert new transaction. MODIFICATION: Status is now forced to 'Pending'
            $sql = "
                INSERT INTO transactions 
                    (user_id, amount, description, payment_method, status, related_booking_id) 
                VALUES 
                    (?, ?, ?, ?, 'Pending', ?) -- FORCED PENDING STATUS
            ";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $amount, $description, $payment_method, $booking_id]);
            $transaction_id = $pdo->lastInsertId();
            $message = "New PENDING payment intention (Transaction ID: {$transaction_id}) recorded successfully.";
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => $message]);

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Save Payment Error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error while saving payment: ' . $e->getMessage()]);
    }
}
?>