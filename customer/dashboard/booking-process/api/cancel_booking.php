<?php
// cancel_booking.php
// Location: /GYMRAT/customer/dashboard/booking-process/api/cancel_booking.php

header('Content-Type: application/json');

session_start();

// *** CRITICAL FIX: The path must go up three directories to find config.php ***
include_once('../../../../config.php'); 

// Function to handle JSON response (helps prevent accidental output)
function send_json_response($success, $message, $code = 200) {
    // Prevent accidental PHP output from reaching the JSON stream
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

// ✅ FIX: ENFORCE USE OF LOGGED-IN USER ID
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Send 401 Unauthorized if no session is found, preventing the use of a hardcoded ID.
    send_json_response(false, 'Unauthorized access. Please log in.', 401);
} 

$user_id = (int)$_SESSION['user_id'];


// Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Method not allowed.', 405);
}

// Check if $pdo object was successfully created in config.php
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // This often means the include_once path above is wrong or the config file failed.
    send_json_response(false, 'Database connection failed. Check config.php path/credentials.', 500);
}

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id'])) {
    send_json_response(false, 'Missing booking ID.', 400);
}

$booking_id = (int)$data['booking_id'];

try {
    // 1. Verify the booking belongs to the user and is 'Confirmed'
    $stmt = $pdo->prepare("
        SELECT status 
        FROM bookings 
        WHERE booking_id = :booking_id AND user_id = :user_id
    ");
    $stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        // This fails if the booking ID doesn't exist OR it doesn't belong to the logged-in user.
        send_json_response(false, 'Booking not found or does not belong to your account.', 404);
    }
    
    if ($booking['status'] !== 'Confirmed') {
        send_json_response(false, "Booking status is '{$booking['status']}'. Only 'Confirmed' bookings can be cancelled.", 400);
    }

    // 2. Update the booking status to 'Cancelled'
    $stmt = $pdo->prepare("
        UPDATE bookings 
        SET status = 'Cancelled' 
        WHERE booking_id = :booking_id AND user_id = :user_id
    ");
    
    $success = $stmt->execute([':booking_id' => $booking_id, ':user_id' => $user_id]);

    if ($success) {
        // Return a successful response
        send_json_response(true, 'Booking successfully cancelled.');
    } else {
        send_json_response(false, 'Failed to update database. Please try again.', 500);
    }

} catch (PDOException $e) {
    // Log the detailed error for backend inspection, send a generic message to the user
    error_log("Booking cancellation PDO error: " . $e->getMessage()); 
    send_json_response(false, 'A critical database error occurred. Please contact support.', 500);
} catch (Exception $e) {
    send_json_response(false, 'Server error: ' . $e->getMessage(), 500);
}
?>