<?php
// config.php
// Database connection and API/Mailer configuration constants

// Database connection
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "gymrat";

// Create PDO connection 
try {
    // This PDO object is used by auth.php
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    error_log("[v0] Database connection successful to $dbname");
} catch (PDOException $e) {
    error_log("[v0] Database Connection failed: " . $e->getMessage());
    die("Database Connection failed: " . $e->getMessage());
}

// --- Google Sign-In API (OAuth 2.0) ---
// NOTE: Replace this with your actual Google Client ID.
define('GOOGLE_CLIENT_ID', '756912758197-l4971m2s67dr40shopc84nerltiiaabl.apps.googleusercontent.com');
define('GOOGLE_REDIRECT_URI', 'http://localhost/gymrat/auth/google-auth.php');
define('GOOGLE_SCOPE', ['email', 'profile']);

define('PAYMONGO_SECRET_KEY', 'sk_test_pUKRrqdcawdEgwDhDXbquB1h'); // Placeholder from documentation
define('PAYMONGO_PUBLIC_KEY', 'pk_test_CUPTSXkcqZG2MujBfqeDCfpD'); // Placeholder for test public key
define('PAYMONGO_WEBHOOK_SECRET', 'hook_GcNFNGjXG3WAwBS3rzvrWjWC');

// --- PHPMailer SMTP setup (for OTP) ---
// NOTE: Replace the SMTP_PASSWORD with your actual Gmail App Password (16-character code).
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'crit083024@gmail.com');
define('SMTP_PASSWORD', 'txzm aqrg vpan cxqf'); // YOUR GMAIL APP PASSWORD
define('SMTP_FROM_EMAIL', 'crit083024@gmail.com');
define('SMTP_FROM_NAME', 'Lena Gym');

// Redirection path after successful OTP verification
define('DASHBOARD_REDIRECT_PATH', 'customer/dashboard/dashboard.php');
?>
