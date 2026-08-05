<?php

function sendVerificationEmail($toEmail, $token)
{

  $apiKey = "YOUR_BREVO_API_KEY";

  $verifyLink = "http://" . $_SERVER['HTTP_HOST'] . "/DarkHunter/Public/verify-email.php?token=" . $token;

  $data = [
    "sender" => [
      "name" => "DarkHunter",
      "email" => "mahmoudazaro908@gmail.com"
    ],
    "to" => [
      ["email" => $toEmail]
    ],
    "subject" => "Verify your account",
    "htmlContent" => str_replace(
      "{{VERIFY_LINK}}",
      $verifyLink,
      <<<HTML
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>DarkHunter Verification</title>

<style>
  body{
    margin:0;
    background:#050505;
    font-family:Segoe UI, Tahoma;
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
  }

  .header{
    text-align:center;
    padding:40px 20px 20px;
  }

  .logo{
    font-size:28px;
    font-weight:bold;
    letter-spacing:3px;
  }

  .logo span{color:#00ff88}

  .content{
    padding:25px 30px;
    background:#0f0f0f;
    margin:0 20px 30px;
    border-radius:12px;
    border:1px solid #1f1f1f;
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
  }

  .footer{
    text-align:center;
    font-size:12px;
    color:#666;
    padding:20px;
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

    <h2 style="margin:0 0 10px;color:#fff;">
      مرحباً بك في النخبة 👾
    </h2>

    <p style="color:#bbb;line-height:1.7;font-size:14px;">
      تم إنشاء حسابك بنجاح. لتفعيل حسابك والبدء في رحلتك داخل DarkHunter،
      اضغط الزر بالأسفل.
    </p>

    <div style="text-align:center">
      <a href="{{VERIFY_LINK}}" class="btn">
        تفعيل الحساب
      </a>
    </div>

    <p style="margin-top:20px;font-size:12px;color:#777;text-align:center;">
      إذا لم تطلب هذا الحساب يمكنك تجاهل الرسالة
    </p>

  </div>

  <div class="footer">
    © 2024 DarkHunter - All rights reserved
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