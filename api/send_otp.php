<?php
session_start();
header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email']);
    exit;
}

// Generate random 6-digit OTP
$otp = rand(100000, 999999);

// Store OTP in session (for demo; in production, store in DB with expiry)
$_SESSION['otp'] = $otp;
$_SESSION['otp_email'] = $email;
$_SESSION['otp_expires'] = time() + 300; // 5 minutes

// Prepare email
$subject = 'Your OTP Code';
$message = "Your OTP code is: $otp";
$headers = 'From: upantachowdhury89@gmail.com' . "\r\n" .
           'Reply-To: upantachowdhury89@gmail.com' . "\r\n" .
           'X-Mailer: PHP/' . phpversion();

// Send email
if (mail($email, $subject, $message, $headers)) {
    echo json_encode(['success' => true, 'message' => 'OTP sent']);
} else {
    // For local/dev: show OTP in response for testing
    echo json_encode(['success' => true, 'message' => 'OTP (for testing): ' . $otp]);
}
exit;
?>