<?php

/**
 * resend-verification.php
 * Backend handler for resending verification emails via AJAX
 * Returns JSON response for Toast notifications
 */

session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/Config/db.php');

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode([
    'success' => false,
    'message' => 'Method not allowed'
  ]);
  exit();
}

// Check if it's an AJAX request
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (!$isAjax) {
  http_response_code(400);
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request type'
  ]);
  exit();
}

// Get email from session (set during failed login attempt)
$email = $_SESSION['resend_email'] ?? null;

if (empty($email)) {
  echo json_encode([
    'success' => false,
    'message' => 'No email found in session. Please try logging in again.'
  ]);
  exit();
}

try {
  // Verify user exists and is not already verified
  $stmt = $pdo->prepare("
        SELECT id, username, is_verified, verification_token 
        FROM users 
        WHERE email = ?
    ");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    echo json_encode([
      'success' => false,
      'message' => 'User not found'
    ]);
    exit();
  }

  if ($user['is_verified'] == 1) {
    echo json_encode([
      'success' => false,
      'message' => 'Account is already verified. Please login.'
    ]);
    exit();
  }

  // Generate new verification token if needed
  $token = $user['verification_token'];
  if (empty($token)) {
    $token = bin2hex(random_bytes(32));
    $updateStmt = $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
    $updateStmt->execute([$token, $user['id']]);
  }

  // ============================================
  // EMAIL SENDING LOGIC (Replace with your mailer)
  // ============================================

  $verificationLink = "https://" . $_SERVER['HTTP_HOST'] . "/DarkHunter/Public/verify.php?token=" . urlencode($token);

  // TODO: Implement your actual email sending here
  // Example using PHPMailer or mail():
  /*
    $to = $email;
    $subject = "DarkHunter - Verify Your Email";
    $message = "
    <html>
    <body style='background:#0a0a0f;color:#e8e8f0;font-family:Inter,sans-serif;padding:40px;'>
      <div style='max-width:500px;margin:0 auto;background:#12121a;border:1px solid rgba(255,107,53,0.2);border-radius:14px;padding:40px;text-align:center;'>
        <h2 style='color:#ff6b35;font-family:Orbitron,sans-serif;'>DarkHunter</h2>
        <p>Hi {$user['username']},</p>
        <p>Click the button below to verify your email:</p>
        <a href='{$verificationLink}' style='display:inline-block;background:linear-gradient(135deg,#ff6b35,#ff8c42);color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:600;margin:20px 0;'>Verify Email</a>
        <p style='font-size:0.85rem;color:#6b6b8a;margin-top:20px;'>Or copy this link: {$verificationLink}</p>
        <p style='font-size:0.8rem;color:#6b6b8a;'>This link expires in 24 hours.</p>
      </div>
    </body>
    </html>
    ";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: noreply@darkhunter.com\r\n";
    
    mail($to, $subject, $message, $headers);
    */

  require_once($_SERVER['DOCUMENT_ROOT'] . '/DarkHunter/api/sendVerificationEmail.php');
  $result = sendVerificationEmail($email, $token);
  $emailSent = $result['success'];

  if ($emailSent) {
    echo json_encode([
      'success' => true,
      'message' => 'Verification email sent! Please check your inbox and spam folder.',
      'email' => $email
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'message' => 'Failed to send email. Please try again later.'
    ]);
  }
} catch (PDOException $e) {
  error_log("Resend verification error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'Database error occurred. Please try again.'
  ]);
} catch (Exception $e) {
  error_log("Resend verification error: " . $e->getMessage());
  echo json_encode([
    'success' => false,
    'message' => 'An unexpected error occurred.'
  ]);
}