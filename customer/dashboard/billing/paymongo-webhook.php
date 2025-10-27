<?php
// paymongo-webhook.php - FIXED VERSION WITH ROBUST ERROR HANDLING
require_once __DIR__ . '/../../../config.php'; 

error_log("[WEBHOOK] Starting PayMongo webhook processing at " . date('Y-m-d H:i:s'));

// CRITICAL FIX: Ensure $pdo is available before proceeding 
if (!isset($pdo) || !$pdo instanceof PDO) {
    error_log("[WEBHOOK CRITICAL] PDO connection object (\$pdo) not initialized. Check config.php path and connection status.");
    http_response_code(500);
    exit();
}

// --- 1. Retrieve Request Data ---
$payload = @file_get_contents('php://input');
$signature = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? '';

error_log("[WEBHOOK] Received payload: " . substr($payload, 0, 500) . "...");

// --- 2. Verify Webhook Signature (CRUCIAL SECURITY STEP) ---
$secret = PAYMONGO_WEBHOOK_SECRET; 
$data = json_decode($payload, true);

if (!$data) {
    error_log("[WEBHOOK] Failed to decode JSON payload");
    http_response_code(400);
    exit();
}

$event_type = $data['data']['attributes']['type'] ?? '';
error_log("[WEBHOOK] Event type: " . $event_type);

// 2a. Check if signature exists and parse it
if (empty($signature)) {
    http_response_code(400); 
    error_log("[WEBHOOK] Missing PayMongo Signature.");
    exit();
}

$parts = explode(',', $signature);
$timestamp = '';
$signature_hash = '';

foreach ($parts as $part) {
    if (strpos($part, 't=') === 0) {
        $timestamp = substr($part, 2);
    } elseif (strpos(strval($part), 'v1=') === 0) {
        $signature_hash = substr($part, 3);
    }
}

$signature_data = $timestamp . '.' . $payload;
$expected_signature = hash_hmac('sha256', $signature_data, $secret);

if (!hash_equals($expected_signature, $signature_hash)) {
    http_response_code(400);
    error_log("[WEBHOOK] Signature Mismatch. Expected: $expected_signature, Received: $signature_hash");
    exit();
}

// Respond immediately with 200 OK to prevent retries while processing
http_response_code(200); 

// --- 3. Process the Event ---
if ($event_type === 'checkout_session.payment.paid') {
    error_log("[WEBHOOK] Processing checkout_session.payment.paid event");
    
    $checkout_attributes = $data['data']['attributes']['data']['attributes'] ?? [];
    $metadata = $checkout_attributes['metadata'] ?? [];
    
    // Payment Data
    $payments = $checkout_attributes['payments'] ?? [];
    $status = $checkout_attributes['status'] ?? 'N/A';
    
    error_log("[WEBHOOK] Checkout status: $status, Payments count: " . count($payments));
    
    $payment_id = 'N/A';
    $payment_method_type = 'Unknown';
    $payment_type = $metadata['type'] ?? 'membership_upgrade'; // Default to existing behavior
    
    // Get PayMongo Payment ID and Method Type
    if (!empty($payments) && is_array($payments)) {
        $payment_id = $payments[0] ?? 'N/A';
        error_log("[WEBHOOK] Payment ID: $payment_id");
        
        // Fetch full payment details to get the method type securely
        if ($payment_id !== 'N/A') {
            $ch = curl_init("https://api.paymongo.com/v1/payments/$payment_id");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
            ]);
            $payment_response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200) {
                $payment_data = json_decode($payment_response, true);
                // Source type provides the method (card, gcash, paymaya, etc.)
                $payment_method_type = $payment_data['data']['attributes']['source']['type'] ?? 'Unknown'; 
                error_log("[WEBHOOK] Payment method: $payment_method_type");
            } else {
                error_log("[WEBHOOK] Failed to fetch payment details for $payment_id (HTTP $http_code)");
            }
        }
    }

    if ($status !== 'paid') {
        error_log("[WEBHOOK] Payment status is not 'paid'. Status: $status. Skipping database update.");
        exit();
    }
    
    // Convert user_id to integer for security
    $user_id = (int)($metadata['user_id'] ?? 0);
    if ($user_id === 0) {
        error_log("[WEBHOOK] CRITICAL: Missing user_id in metadata.");
        exit();
    }
    
    // --- NEW LOGIC: Handle Staff-Initiated Custom Payment ---
    if ($payment_type === 'custom_payment') {
        $local_transaction_id = $metadata['local_transaction_id'] ?? null;
        $amount_paid = is_numeric($metadata['amount_paid'] ?? 0.00) ? (float)($metadata['amount_paid'] ?? 0.00) : 0.00;

        if ($local_transaction_id) {
            $pdo->beginTransaction();
            try {
                // 1. Update the local transaction record
                error_log("[WEBHOOK] Processing custom_payment ID: $local_transaction_id");
                // Update status, payment_method, reference_id, and amount (for final verification)
                $stmt = $pdo->prepare("
                    UPDATE transactions 
                    SET status = 'Paid', 
                        payment_method = ?, 
                        reference_id = ?,
                        amount = ?
                    WHERE transaction_id = ? AND user_id = ? AND status = 'Pending'
                ");
                
                if (!$stmt->execute([
                    $payment_method_type,
                    $payment_id,
                    $amount_paid,
                    $local_transaction_id,
                    $user_id
                ])) {
                    throw new Exception("Failed to update transaction status: " . implode(", ", $stmt->errorInfo()));
                }

                if ($stmt->rowCount() === 0) {
                     error_log("[WEBHOOK] WARNING: Transaction ID $local_transaction_id not found or already Paid/Cancelled. No update made.");
                }

                // 2. Commit transaction
                $pdo->commit();
                error_log("[WEBHOOK] SUCCESS: Custom Transaction ID $local_transaction_id updated to Paid. PayMongo ID: $payment_id.");
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("[WEBHOOK] PDO/DB Error updating custom_payment: " . $e->getMessage() . ". All changes rolled back.");
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("[WEBHOOK] Logic Error updating custom_payment: " . $e->getMessage());
            }
            exit(); // Stop processing after handling custom payment
        } else {
            error_log("[WEBHOOK] ERROR: Missing local_transaction_id in metadata for custom_payment type. Cannot process.");
            exit();
        }
    }
    // --- END NEW LOGIC ---


    // --- EXISTING LOGIC: Handle Membership Upgrade (Only runs if payment_type is NOT 'custom_payment') ---
    
    // User/Plan Data (from our metadata)
    $plan_id = $metadata['plan_id'] ?? null;
    $plan_name = $metadata['plan_name'] ?? 'Monthly';
    $amount_paid = is_numeric($metadata['amount_paid'] ?? 0.00) ? (float)($metadata['amount_paid'] ?? 0.00) : 0.00;
    
    error_log("[WEBHOOK] Extracted metadata (Membership) - User ID: $user_id, Plan ID: $plan_id, Plan Name: $plan_name, Amount: $amount_paid");
    
    if ($plan_id === null) {
        error_log("[WEBHOOK] CRITICAL: Missing plan_id for membership upgrade. Skipping.");
        exit(); 
    }

    try {
        error_log("[WEBHOOK] Starting database transaction for membership upgrade for user $user_id");
        $pdo->beginTransaction();

        // 3a. Update User's Role in the `users` table
        error_log("[WEBHOOK] Updating user role to: $plan_name");
        $stmt_user = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if (!$stmt_user->execute([$plan_name, $user_id])) {
            throw new Exception("Failed to update user role: " . implode(", ", $stmt_user->errorInfo()));
        }
        error_log("[WEBHOOK] User role updated successfully");

        // 3b. Insert/Update User Membership in the `user_memberships` table
        $expiry_date_initial = date('Y-m-d', strtotime('+30 days'));
        error_log("[WEBHOOK] Setting membership expiry to: $expiry_date_initial");

        $stmt_membership = $pdo->prepare("
            INSERT INTO user_memberships (user_id, membership_id, start_date, end_date, status)
            VALUES (?, ?, CURDATE(), ?, 'Active')
            ON DUPLICATE KEY UPDATE 
                membership_id = VALUES(membership_id), 
                start_date = 
                    CASE WHEN end_date < CURDATE() THEN CURDATE() ELSE start_date END,
                end_date = 
                    CASE 
                        WHEN end_date > CURDATE() THEN DATE_ADD(end_date, INTERVAL 30 DAY)
                        ELSE DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                    END, 
                status = 'Active', 
                purchase_date = CURRENT_TIMESTAMP
        ");
        
        if (!$stmt_membership->execute([$user_id, $plan_id, $expiry_date_initial])) {
            throw new Exception("Failed to insert/update membership: " . implode(", ", $stmt_membership->errorInfo()));
        }
        error_log("[WEBHOOK] Membership inserted/updated successfully");
        
        // --- START ROBUST ID RETRIEVAL ---
        $related_user_membership_id = $pdo->lastInsertId();
        error_log("[WEBHOOK] lastInsertId returned: $related_user_membership_id");
        
        // If it was an UPDATE (renewal) due to the UNIQUE KEY on user_id, lastInsertId() is 0. 
        // We must SELECT the existing ID.
        if (empty($related_user_membership_id)) {
            error_log("[WEBHOOK] lastInsertId was empty, selecting existing membership ID");
            $stmt_select_membership = $pdo->prepare("SELECT user_membership_id FROM user_memberships WHERE user_id = ?");
            if (!$stmt_select_membership->execute([$user_id])) {
                throw new Exception("Failed to select membership ID: " . implode(", ", $stmt_select_membership->errorInfo()));
            }
            $related_user_membership_id = $stmt_select_membership->fetchColumn();
            error_log("[WEBHOOK] Selected membership ID: $related_user_membership_id");
        }
        
        if (empty($related_user_membership_id)) {
            throw new Exception("Membership ID retrieval failed for user: $user_id. The user_memberships table update may have failed.");
        }
        // --- END ROBUST ID RETRIEVAL ---

        // 3c. Log the Transaction in the `transactions` table
        error_log("[WEBHOOK] Inserting transaction record");
        $stmt_trans = $pdo->prepare("
            INSERT INTO transactions (user_id, amount, description, payment_method, reference_id, status, related_user_membership_id)
            VALUES (?, ?, ?, ?, ?, 'Paid', ?)
        ");
        
        if (!$stmt_trans->execute([
            $user_id, 
            $amount_paid, 
            "Membership Upgrade to $plan_name", 
            $payment_method_type, 
            $payment_id, 
            $related_user_membership_id 
        ])) {
            throw new Exception("Failed to insert transaction: " . implode(", ", $stmt_trans->errorInfo()));
        }
        error_log("[WEBHOOK] Transaction inserted successfully");

        $pdo->commit();
        error_log("[WEBHOOK] SUCCESS: User $user_id upgraded to $plan_name. Transaction $payment_id logged. Membership ID: $related_user_membership_id");

    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("[WEBHOOK] PDO/DB Error: " . $e->getMessage() . " for user $user_id. All changes rolled back.");
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("[WEBHOOK] LOGIC Error: " . $e->getMessage());
    }
} else {
    error_log("[WEBHOOK] Received unhandled event type: $event_type. Ignoring.");
}

// Exit here to ensure the 200 OK is the only thing the webhook receives
exit();
?>