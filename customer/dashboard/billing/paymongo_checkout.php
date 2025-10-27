<?php
session_start();
require_once __DIR__ . '/../../../config.php'; 

// --- 1. Security and Initial Validation ---
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /customer/dashboard/billing.php?status=error&message=Invalid_Request_Method"); 
    exit();
}

$user_id = $_SESSION['user_id'];
$user_email = $_SESSION['email'] ?? 'member@example.com'; 
$user_name = $_SESSION['fullname'] ?? 'Gymrat Member';

// --- 2. Determine Payment Type and Fetch Details ---
$is_custom_payment = isset($_POST['transaction_id']) && $_POST['type'] === 'custom_payment';
$is_membership_upgrade = isset($_POST['plan_id']);

$amount = 0; // Amount in CENTS
$description = '';
$metadata = ['user_id' => $user_id]; // Base metadata

// Determine the success/cancel URLs based on the current domain
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
$cancel_url = $base_url . '/customer/dashboard/billing.php?status=cancelled&message=Payment_Process_Canceled_by_User';
$success_redirect_url = $base_url . '/customer/dashboard/billing.php?status=success&message=Payment_Processing_Started'; // Simple redirect for custom payments
$membership_success_handler = $base_url . '/customer/dashboard/billing/paymongo-success-handler.php'; // Existing handler for memberships

if ($is_custom_payment) {
    // --- CUSTOM PAYMENT LOGIC (From Staff/payments.php) ---
    $transaction_id = filter_input(INPUT_POST, 'transaction_id', FILTER_VALIDATE_INT);
    
    if (!$transaction_id) {
        header("Location: /customer/dashboard/billing.php?status=error&message=Missing_Custom_Transaction_ID");
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT amount, description, status 
            FROM transactions 
            WHERE transaction_id = ? AND user_id = ? AND status = 'Pending'
        ");
        $stmt->execute([$transaction_id, $user_id]);
        $transaction_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("DB Error fetching custom transaction: " . $e->getMessage());
        header("Location: /customer/dashboard/billing.php?status=error&message=DB_Error_Fetching_Payment");
        exit();
    }

    if (!$transaction_data) {
        header("Location: /customer/dashboard/billing.php?status=error&message=Invalid_or_Already_Paid_Transaction");
        exit();
    }
    
    $amount = round($transaction_data['amount'] * 100); // Amount must be in CENTS
    $description = "Custom Payment: {$transaction_data['description']}";
    $success_url = $success_redirect_url; 
    
    // CRITICAL: Metadata for webhook to identify this as a custom payment
    $metadata['type'] = 'custom_payment'; 
    $metadata['local_transaction_id'] = $transaction_id;
    $metadata['amount_paid'] = $transaction_data['amount'];

} elseif ($is_membership_upgrade) {
    // --- MEMBERSHIP UPGRADE LOGIC (Existing functionality) ---
    $plan_id = intval($_POST['plan_id']);

    $plans = [
        1 => ['name' => 'Basic Plan', 'amount_cents' => 5000, 'db_plan_name' => 'Basic'],
        2 => ['name' => 'Monthly Plan', 'amount_cents' => 99900, 'db_plan_name' => 'Monthly'],
        3 => ['name' => 'Premium Plan', 'amount_cents' => 149900, 'db_plan_name' => 'Premium'],
    ];

    if (!isset($plans[$plan_id]) || $plan_id < 2) { 
        header("Location: /customer/dashboard/billing.php?status=error&message=Invalid_or_Free_Membership_Plan");
        exit();
    }

    $selected_plan = $plans[$plan_id];
    $amount = $selected_plan['amount_cents'];
    $description = "Gymrat - {$selected_plan['name']} Membership";
    $success_url = $membership_success_handler; 

    // Metadata for webhook to identify this as a membership
    $metadata['type'] = 'membership_upgrade'; 
    $metadata['plan_id'] = $plan_id;
    $metadata['plan_name'] = $selected_plan['db_plan_name'];
    $metadata['amount_paid'] = $amount / 100;

    // Temporary session variables (kept for paymongo-success-handler.php compatibility)
    $_SESSION['pending_plan_id'] = $plan_id;
    $_SESSION['pending_plan_name'] = $selected_plan['db_plan_name'];
    $_SESSION['pending_amount'] = $amount / 100;

} else {
    // Neither membership nor custom payment identified
    header("Location: /customer/dashboard/billing.php?status=error&message=Missing_Payment_Details");
    exit();
}

// --- 3. Construct PayMongo Payload (Generic for both types) ---
$payload = [
    'data' => [
        'attributes' => [
            'billing' => [
                'email' => $user_email,
                'name' => $user_name,
            ],
            'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'], 
            'line_items' => [
                [
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'name' => $description,
                    'quantity' => 1,
                ]
            ],
            'success_url' => $success_url,
            'cancel_url' => $cancel_url,
            'send_email_receipt' => true,
            'metadata' => $metadata // Insert the dynamically generated metadata
        ]
    ]
];

// --- 4. Execute the API Call using cURL ---
$ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode(PAYMONGO_SECRET_KEY . ':')
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$decoded_response = json_decode($response, true);

// --- 5. Handle Response and Redirect ---
if ($http_code === 200 && isset($decoded_response['data']['attributes']['checkout_url'])) {
    $checkout_url = $decoded_response['data']['attributes']['checkout_url'];
    header("Location: " . $checkout_url);
    exit();
} else {
    $error_detail = $decoded_response['errors'][0]['detail'] ?? 'Unknown PayMongo API error.';
    error_log("PayMongo Checkout Error (HTTP $http_code): " . $error_detail . " Response: " . print_r($decoded_response, true)); 
    header("Location: /customer/dashboard/billing.php?status=error&message=PayMongo_API_Error:_$http_code");
    exit();
}