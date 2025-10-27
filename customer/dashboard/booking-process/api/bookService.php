<?php
// bookService.php
// Location: /GYMRAT/customer/dashboard/booking-process/api/bookService.php
// Handles the database insertion for new service bookings.

header('Content-Type: application/json');

session_start();

// Path consistent with user's environment to reach /GYMRAT/config.php
include_once('../../../../config.php'); 

// Function to handle JSON response (helps prevent accidental output)
function send_json_response($success, $message, $data = [], $code = 200) {
    // Clean buffer to ensure only JSON is output
    if (ob_get_length()) {
        ob_clean();
    }
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

// Set timezone for accurate date/time operations
date_default_timezone_set('Asia/Manila');

// ✅ FIX: ENFORCE USE OF SESSION USER ID
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    send_json_response(false, 'Unauthorized access. Please log in.', [], 401);
}
$user_id = (int)$_SESSION['user_id'];

// Check for POST request and database connection
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, 'Method not allowed.', [], 405);
}
if (!isset($pdo) || !($pdo instanceof PDO)) {
    send_json_response(false, 'Database connection failed. Check config.php path/credentials.', [], 500);
}

// 1. Collect and Sanitize Data
// Note: We expect data to be sent via application/json by the frontend now.
$data = json_decode(file_get_contents('php://input'), true);

$service_id = filter_var($data['service_id'] ?? null, FILTER_VALIDATE_INT);
// ✅ FIX: Correctly map keys to expected AJAX payload (booking_date, booking_time)
$booking_date = filter_var($data['booking_date'] ?? null, FILTER_SANITIZE_STRING);
$booking_time = filter_var($data['booking_time'] ?? null, FILTER_SANITIZE_STRING);

// ✨ FIX: Robustly validate trainer_id, treating 0 or non-int as null initially
$trainer_id = filter_var($data['trainer_id'] ?? null, FILTER_VALIDATE_INT); 
if ($trainer_id === 0) {
    $trainer_id = null; // Treat 0 as null if the form passes it
}

// Basic Input Validation
if (!$service_id || !$booking_date || !$booking_time) {
    send_json_response(false, 'Missing required booking details (Service, Date, or Time).', [], 400);
}

try {
    // 2. Fetch Service Details (Duration and Type)
    $stmt = $pdo->prepare("
        SELECT is_class
        FROM services 
        WHERE service_id = :service_id AND is_active = 1
    ");
    $stmt->execute([':service_id' => $service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$service) {
        send_json_response(false, 'Invalid or inactive service selected.', [], 400);
    }
    
    $is_class = (bool)$service['is_class'];

    // 3. Prepare Time Strings (Assuming 1 hour duration, regardless of 5-min start step)
    $start_datetime = new DateTime("{$booking_date} {$booking_time}");
    $end_datetime = clone $start_datetime;
    $end_datetime->modify("+1 hour"); // Booking duration is 1 hour

    $start_time_str = $start_datetime->format('Y-m-d H:i:s');
    $end_time_str = $end_datetime->format('Y-m-d H:i:s');
    $current_datetime = date('Y-m-d H:i:s');

    // Prevent booking in the past
    if ($start_time_str <= $current_datetime) {
        send_json_response(false, 'Cannot book a service in the past.', [], 400);
    }
    
    // ✨ FIX: Validate and Enforce Trainer ID for non-class bookings
    if (!$is_class) {
        if (empty($trainer_id)) { // Checks for null, 0, or false
            send_json_response(false, 'A trainer must be selected for this personal service.', [], 400);
        }
        
        // Ensure trainer is valid (must be a user with role 'trainer' - crucial integrity check)
        $trainer_check_stmt = $pdo->prepare("SELECT id FROM users WHERE id = :trainer_id AND role = 'trainer'");
        $trainer_check_stmt->execute([':trainer_id' => $trainer_id]);
        if (!$trainer_check_stmt->fetch()) {
             send_json_response(false, 'The selected trainer is invalid or not a registered trainer.', [], 400);
        }
    }

    // 4. Conflict Checks (User, Trainer, Class Capacity)

    // A. User Conflict Check (Does the user have any other booking at this time?)
    $user_conflict_stmt = $pdo->prepare("
        SELECT 1
        FROM bookings
        WHERE user_id = :user_id 
        AND status = 'Confirmed' 
        AND ((start_time < :end_time AND end_time > :start_time))
    ");
    $user_conflict_stmt->execute([
        ':user_id' => $user_id,
        ':start_time' => $start_time_str,
        ':end_time' => $end_time_str
    ]);
    if ($user_conflict_stmt->fetch(PDO::FETCH_ASSOC)) {
        send_json_response(false, 'You already have a confirmed booking during this time slot.', [], 409);
    }
    
    // B. Trainer Conflict Check (Only for non-class services)
    if (!$is_class) {
        $trainer_conflict_stmt = $pdo->prepare("
            SELECT 1
            FROM bookings
            WHERE trainer_id = :trainer_id
            AND status = 'Confirmed' 
            AND ((start_time < :end_time AND end_time > :start_time))
        ");
        $trainer_conflict_stmt->execute([
            ':trainer_id' => $trainer_id,
            ':start_time' => $start_time_str,
            ':end_time' => $end_time_str
        ]);
        if ($trainer_conflict_stmt->fetch(PDO::FETCH_ASSOC)) {
            send_json_response(false, "The selected trainer is already booked for this time slot.", [], 409);
        }
    }

    // 5. Insert the Booking into the Database
    $stmt = $pdo->prepare("
        INSERT INTO bookings (user_id, service_id, trainer_id, start_time, end_time, status) 
        VALUES (:user_id, :service_id, :trainer_id, :start_time, :end_time, 'Confirmed')
    ");
    
    $success = $stmt->execute([
        ':user_id' => $user_id,
        ':service_id' => $service_id,
        // The value of $trainer_id will be the INT ID for private sessions or NULL for classes.
        ':trainer_id' => $is_class ? null : $trainer_id, 
        ':start_time' => $start_time_str,
        ':end_time' => $end_time_str
    ]);

    if ($success) {
        $new_booking_id = $pdo->lastInsertId();
        send_json_response(true, 'Service successfully booked! We look forward to seeing you.', ['booking_id' => $new_booking_id]);
    } else {
        send_json_response(false, 'Failed to save booking. Please try again.', [], 500);
    }

} catch (PDOException $e) {
    // Log the detailed error for backend debugging
    error_log("Booking Error: " . $e->getMessage());
    send_json_response(false, 'A database error occurred. Please check logs for details.', [], 500);
} catch (Exception $e) {
    // Catch general exceptions (e.g., date parsing error)
    error_log("General Error: " . $e->getMessage());
    send_json_response(false, 'An unexpected error occurred.', [], 500);
}
?>