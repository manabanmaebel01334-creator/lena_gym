<?php
/**
 * mailer.php
 * Handles OTP email sending using PHPMailer with Gmail SMTP
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send OTP email to user
 * @param string $to - Recipient email address
 * @param string $subject - Email subject
 * @param string $otp - 6-digit OTP code
 * @return bool - True if email sent successfully, false otherwise
 */
function email_otp_send($to, $subject, $otp) {
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        $mail->Body = "
            <html>
                <head>
                    <style>
                        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
                        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                        .header { text-align: center; color: #ec1313; margin-bottom: 20px; }
                        .otp-box { background-color: #f9f9f9; border: 2px solid #ec1313; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
                        .otp-code { font-size: 32px; font-weight: bold; color: #ec1313; letter-spacing: 5px; }
                        .footer { text-align: center; color: #666; font-size: 12px; margin-top: 20px; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <div class='header'>
                            <h1>Lena Gym Fitness</h1>
                        </div>
                        <p>Hello,</p>
                        <p>Your verification code for Lena Gym is:</p>
                        <div class='otp-box'>
                            <div class='otp-code'>$otp</div>
                        </div>
                        <p>This code will expire in <strong>5 minutes</strong>.</p>
                        <p>If you didn't request this code, please ignore this email.</p>
                        <div class='footer'>
                            <p>&copy; 2025 Lena Gym Fitness. All rights reserved.</p>
                        </div>
                    </div>
                </body>
            </html>
        ";
        
        // Plain text alternative
        $mail->AltBody = "Your verification code for Lena Gym is: $otp\n\nThis code will expire in 5 minutes.\n\nIf you didn't request this code, please ignore this email.";
        
        // Send email
        $mail->send();
        error_log("[v0] OTP email sent successfully to: $to");
        return true;
        
    } catch (Exception $e) {
        error_log("[v0] PHPMailer Error: " . $mail->ErrorInfo);
        error_log("[v0] Failed to send OTP to: $to");
        return false;
    }
}
?>
