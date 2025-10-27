<?php
/**
 * paymongo-success-handler.php
 * Simplified payment verification and data insertion
 * No PayMongo API verification - data inserted on successful redirect
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include config
require_once __DIR__ . '/../../../config.php';

error_log("[PAYMENT] ===== PAYMENT SUCCESS HANDLER START =====");
error_log("[PAYMENT] User ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));
error_log("[PAYMENT] GET Parameters: " . json_encode($_GET));

// Verify user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("[PAYMENT] ERROR: User not logged in");
    header("Location: /customer/dashboard/billing.php?status=error&message=not_logged_in");
    exit();
}

$user_id = (int)$_SESSION['user_id'];

// Get plan info from session (set during checkout)
$plan_id = $_SESSION['pending_plan_id'] ?? null;
$plan_name = $_SESSION['pending_plan_name'] ?? null;
$amount_paid = $_SESSION['pending_amount'] ?? null;

error_log("[PAYMENT] Plan ID: $plan_id, Plan Name: $plan_name, Amount: $amount_paid");

// Validate data
if (!$plan_id || !$plan_name || !$amount_paid) {
    error_log("[PAYMENT] ERROR: Missing plan information in session");
    header("Location: /customer/dashboard/billing.php?status=error&message=missing_plan_info");
    exit();
}

try {
    error_log("[PAYMENT] Starting database operations");
    
    // 1. Delete old membership if exists (to avoid UNIQUE KEY issues)
    $stmt = $pdo->prepare("DELETE FROM user_memberships WHERE user_id = ?");
    $stmt->execute([$user_id]);
    error_log("[PAYMENT] Deleted old memberships for user $user_id");
    
    // 2. Insert new membership
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+30 days'));
    
    $stmt = $pdo->prepare("
        INSERT INTO user_memberships (user_id, membership_id, start_date, end_date, status)
        VALUES (?, ?, ?, ?, 'Active')
    ");
    $stmt->execute([$user_id, $plan_id, $start_date, $end_date]);
    $membership_id = $pdo->lastInsertId();
    error_log("[PAYMENT] Inserted membership - ID: $membership_id, User: $user_id, Plan: $plan_id");
    
    // 3. Insert transaction
    $payment_id = 'paymongo_' . time() . '_' . $user_id;
    $stmt = $pdo->prepare("
        INSERT INTO transactions (user_id, amount, description, payment_method, reference_id, status, related_user_membership_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $user_id,
        $amount_paid,
        "Membership: $plan_name",
        'card',
        $payment_id,
        'Paid',
        $membership_id
    ]);
    error_log("[PAYMENT] Inserted transaction for user $user_id");
    
    // 4. Update user role
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$plan_name, $user_id]);
    error_log("[PAYMENT] Updated user role to: $plan_name");
    
    // 5. Clear session variables
    unset($_SESSION['pending_plan_id']);
    unset($_SESSION['pending_plan_name']);
    unset($_SESSION['pending_amount']);
    
    error_log("[PAYMENT] ===== PAYMENT SUCCESS =====");
    header("Location: /customer/dashboard/billing.php?status=success");
    exit();
    
} catch (Exception $e) {
    error_log("[PAYMENT] Database Error: " . $e->getMessage());
    error_log("[PAYMENT] Error Code: " . $e->getCode());
    header("Location: /customer/dashboard/billing.php?status=error&message=database_error");
    exit();
}
?>
