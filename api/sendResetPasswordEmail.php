<?php

function sendResetPasswordEmail($toEmail, $token)
{
  $apiKey = "YOUR_BREVO_API_KEY";

  $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/DarkHunter/Public/reset-password.php?token=" . $token;

  $data = [
    "sender" => [
      "name" => "DarkHunter",
      "email" => "mahmoudazaro908@gmail.com"
    ],
    "to" => [
      ["email" => $toEmail]
    ],
    "subject" => "Reset Your Password - DarkHunter",
    "htmlContent" => str_replace(
      "{{RESET_LINK}}",
      $resetLink,
      <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DarkHunter - Password Reset</title>

<style>
  body{
    margin:0;
    background:#050505;
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  }

  .wrapper{
    max-width:600px;
    margin:40px auto;
    background:linear-gradient(180deg,#0a0a0a,#0d0d0d);
    border:1px solid #1a1a1a;
    border-radius:16px;
    overflow:hidden;
    color:#fff;
    box-shadow:0 20px 50px rgba(0,0,0,.6);
  }

  .top{
    height:4px;
    background:linear-gradient(90deg,#00ff88,#8b5cf6,#00ff88);
    animation: borderGlow 3s ease-in-out infinite;
  }

  @keyframes borderGlow {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
  }

  .header{
    text-align:center;
    padding:40px 20px 20px;
  }

  .logo{
    font-family:'Orbitron', monospace;
    font-size:28px;
    font-weight:bold;
    letter-spacing:3px;
    background:linear-gradient(90deg,#fff,#00ff88);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
  }

  .logo span{
    color:#00ff88;
    -webkit-text-fill-color:#00ff88;
  }

  .content{
    padding:25px 30px;
    background:#0f0f0f;
    margin:0 20px 30px;
    border-radius:12px;
    border:1px solid #1f1f1f;
  }

  .content h2{
    margin:0 0 10px;
    color:#fff;
    font-family:'Orbitron', monospace;
    font-size:1.3rem;
  }

  .content p{
    color:#bbb;
    line-height:1.7;
    font-size:14px;
    margin:0 0 15px;
  }

  .warning-box{
    background:rgba(255, 0, 64, 0.1);
    border:1px solid rgba(255, 0, 64, 0.3);
    border-radius:8px;
    padding:12px 16px;
    margin:15px 0;
    color:#ff0040;
    font-size:13px;
    font-family:'JetBrains Mono', monospace;
  }

  .warning-box i{
    margin-right:8px;
  }

  .btn{
    display:inline-block;
    margin-top:20px;
    padding:14px 26px;
    background:linear-gradient(135deg,#00ff88,#00cc66);
    color:#000;
    font-weight:bold;
    text-decoration:none;
    border-radius:10px;
    font-family:'JetBrains Mono', monospace;
    font-size:0.9rem;
    transition:all 0.3s ease;
    box-shadow:0 0 20px rgba(0,255,136,0.3);
  }

  .btn:hover{
    transform:translateY(-2px);
    box-shadow:0 0 30px rgba(0,255,136,0.5);
  }

  .link-box{
    background:rgba(0,0,0,0.4);
    border:1px solid #2a2a2a;
    border-radius:8px;
    padding:12px;
    margin:15px 0;
    word-break:break-all;
    font-family:'JetBrains Mono', monospace;
    font-size:11px;
    color:#888;
  }

  .footer{
    text-align:center;
    font-size:12px;
    color:#666;
    padding:20px;
    border-top:1px solid #1a1a1a;
  }

  .footer a{
    color:#00ff88;
    text-decoration:none;
  }
</style>

</head>

<body>

<div class="wrapper">

  <div class="top"></div>

  <div class="header">
    <div class="logo">DARK<span>HUNTER</span></div>
    <p style="color:#888;margin-top:10px;font-size:13px;">
      Cyber Security Platform
    </p>
  </div>

  <div class="content">

    <h2>Password Reset Requested 🔐</h2>

    <p>
      We received a request to reset your DarkHunter account password.
      Click the button below to set a new password.
    </p>

    <div style="text-align:center">
      <a href="{{RESET_LINK}}" class="btn">
        <i class="fas fa-key"></i> Reset Password
      </a>
    </div>

    <div class="warning-box">
      <i class="fas fa-clock"></i>
      This link will expire in <strong>1 hour</strong> for security reasons.
    </div>

    <p style="margin-top:20px;font-size:12px;color:#777;">
      If the button doesn't work, copy and paste this link into your browser:
    </p>

    <div class="link-box">
      {{RESET_LINK}}
    </div>

    <p style="margin-top:20px;font-size:12px;color:#777;text-align:center;">
      If you didn't request a password reset, you can safely ignore this email.
      Your account will remain secure.
    </p>

  </div>

  <div class="footer">
    © 2026 DarkHunter - All rights reserved<br>
    <a href="http://{$_SERVER['HTTP_HOST']}/DarkHunter/Public/">Visit DarkHunter</a>
  </div>

</div>

</body>
</html>
HTML
    )
  ];

  $ch = curl_init();

  curl_setopt($ch, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "api-key: $apiKey",
    "Content-Type: application/json"
  ]);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);

  curl_close($ch);

  if ($curlError) {
    return [
      "success" => false,
      "error" => $curlError
    ];
  }

  return [
    "success" => ($httpCode == 201),
    "http_code" => $httpCode,
    "brevo_response" => json_decode($response, true)
  ];
}