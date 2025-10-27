<?php
// fetch_sessions.php
// AJAX endpoint to fetch assigned sessions for a specific date

header('Content-Type: application/json');

// 1. Start session and include config
session_start();
// **IMPORTANT: Ensure this path is correct for your configuration**
include_once('../../../config.php');

// --- Input Validation ---
$staff_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 101; // Default to 101 for testing/fallback
$selected_date = $_GET['date'] ?? null;
$error = null;
$sessions = [];
$display_date_name = 'Selected Day'; // Default title

if (!$selected_date || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $selected_date)) {
    $error = "Invalid or missing date parameter.";
    echo json_encode(['success' => false, 'error' => $error]);
    exit;
}

// Set Timezone
date_default_timezone_set('Asia/Manila');

// Determine the day name for the title
try {
    $date_obj = new DateTime($selected_date);
    $today_obj = new DateTime();
    $today_date_str = $today_obj->format('Y-m-d');

    if ($date_obj->format('Y-m-d') === $today_date_str) {
        $display_date_name = 'Today';
    } else {
        $display_date_name = $date_obj->format('l'); // e.g., Monday, Tuesday
    }
} catch (Exception $e) {
    $display_date_name = "Schedule for {$selected_date}";
}

// --- Database Fetching Logic ---
if (!isset($pdo)) {
    $error = "Database connection error. PDO object not available.";
} else {
    try {
        $sql = "
            SELECT 
                b.start_time,
                u.name AS client_name,
                s.name AS service_name,
                b.status
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            JOIN services s ON b.service_id = s.service_id
            WHERE b.trainer_id = :trainer_id
            AND DATE(b.start_time) = :selected_date
            ORDER BY b.start_time ASC;
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':trainer_id', $staff_id, PDO::PARAM_INT);
        $stmt->bindParam(':selected_date', $selected_date, PDO::PARAM_STR);
        $stmt->execute();

        $raw_sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format the output for the front-end
        foreach($raw_sessions as $session) {
            $startTime = new DateTime($session['start_time']);
            $sessions[] = [
                'client_name' => htmlspecialchars($session['client_name']),
                'time' => $startTime->format('H:i A'),
                'service_name' => htmlspecialchars($session['service_name']),
                'status' => htmlspecialchars($session['status']),
            ];
        }

    } catch (PDOException $e) {
        $error = "Database query error: " . $e->getMessage();
    }
}

// --- Output ---
if ($error) {
    echo json_encode(['success' => false, 'error' => $error]);
} else {
    echo json_encode([
        'success' => true,
        'date_title' => $display_date_name,
        'sessions' => $sessions,
        'date' => $selected_date,
    ]);
}
?>