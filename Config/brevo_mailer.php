<?php

function sendVerificationEmail($toEmail, $token)
{

  $apiKey = "YOUR_BREVO_API_KEY";

  $verifyLink = "http://localhost/DarkHunter/Public/verify-email.php?token=" . $token;

  $data = [
    "sender" => [
      "name" => "DarkHunter",
      "email" => "mahmoudazaro908@gmail.com"
    ],
    "to" => [
      ["email" => $toEmail]
    ],
    "subject" => "Verify your DarkHunter account",
    "htmlContent" => str_replace(
      "{{VERIFY_LINK}}",
      $verifyLink,
      <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="rtl" lang="ar">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="dark" />
    <meta name="supported-color-schemes" content="dark" />
    <title>مرحباً بك في DarkHunter - نخبة الأمن السيبراني</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style type="text/css">
        @media (prefers-color-scheme: dark) {
            .email-wrapper { background-color: #050505 !important; }
            .main-card { background-color: #0a0a0a !important; border-color: #1a1a1a !important; }
            .content-bg { background-color: #0f0f0f !important; }
        }
        [data-ogsc] .email-wrapper { background-color: #050505 !important; }
        [data-ogsc] .main-card { background-color: #0a0a0a !important; }
        @media screen and (max-width: 600px) {
            .mobile-padding { padding: 20px !important; }
            .mobile-text { font-size: 14px !important; }
            .mobile-title { font-size: 22px !important; }
            .mobile-button { width: 100% !important; max-width: 300px !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #050505; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; -webkit-font-smoothing: antialiased;">
    
    <!-- Preview Text -->
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        مرحباً بك في النخبة. منصة DarkHunter تفتح لك أبواب عالم الـ Ethical Hunting. فعّل حسابك الآن وابدأ رحلتك نحو الاحتراف.
    </div>

    <!-- Main Wrapper -->
    <table role="presentation" class="email-wrapper" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #050505; background-image: radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.03) 0%, transparent 50%), radial-gradient(circle at 80% 80%, rgba(0, 255, 136, 0.03) 0%, transparent 40%); margin: 0; padding: 0;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <!-- Main Card Container -->
                <table role="presentation" class="main-card" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; width: 100%; background: linear-gradient(180deg, #0a0a0a 0%, #0d0d0d 100%); border: 1px solid #1a1a1a; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(0, 255, 136, 0.05);">
                    
                    <!-- Top Accent Line -->
                    <tr>
                        <td style="height: 4px; background: linear-gradient(90deg, #00ff88 0%, #8b5cf6 50%, #00ff88 100%); background-size: 200% 100%;">
                            <!--[if !mso]><!-- -->
                            <div style="height: 4px; background: linear-gradient(90deg, #00ff88 0%, #8b5cf6 50%, #00ff88 100%); background-size: 200% 100%;"></div>
                            <!--<![endif]-->
                        </td>
                    </tr>

                    <!-- Header Section -->
                    <tr>
                        <td align="center" style="padding: 48px 40px 32px 40px;" class="mobile-padding">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                <tr>
                                    <td style="text-align: center;">
                                        <!-- Logo Icon -->
                                        <div style="width: 80px; height: 80px; margin: 0 auto 20px auto; background: linear-gradient(135deg, rgba(0, 255, 136, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%); border: 2px solid rgba(0, 255, 136, 0.3); border-radius: 50%; display: inline-block; line-height: 76px; text-align: center; box-shadow: 0 0 30px rgba(0, 255, 136, 0.2);">
                                            <span style="font-size: 36px;">👾</span>
                                        </div>
                                        <!-- Brand Name -->
                                        <h1 style="margin: 0; font-family: 'Courier New', monospace; font-size: 28px; font-weight: 700; color: #ffffff; letter-spacing: 4px; text-transform: uppercase; text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);">
                                            DARK<span style="color: #00ff88;">HUNTER</span>
                                        </h1>
                                        <p style="margin: 8px 0 0 0; font-size: 12px; color: #6b7280; letter-spacing: 2px; font-family: 'Courier New', monospace;">CYBERSECURITY LABS</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Welcome Title -->
                    <tr>
                        <td align="center" style="padding: 0 40px 24px 40px;" class="mobile-padding">
                            <h2 class="mobile-title" style="margin: 0; font-size: 32px; font-weight: 700; color: #ffffff; line-height: 1.3; text-align: center;">
                                مرحباً بك في 
                                <span style="color: #00ff88; text-shadow: 0 0 10px rgba(0, 255, 136, 0.5);">النخبة</span>
                            </h2>
                            <p style="margin: 12px 0 0 0; font-size: 16px; color: #8b5cf6; font-weight: 600; letter-spacing: 1px;">
                                Welcome to the Elite Circle
                            </p>
                        </td>
                    </tr>

                    <!-- Main Content -->
                    <tr>
                        <td style="padding: 0 40px 32px 40px;" class="mobile-padding">
                            <table role="presentation" class="content-bg" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #0f0f0f; border: 1px solid #1f1f1f; border-radius: 12px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 32px;">
                                        
                                        <!-- Opening Paragraph -->
                                        <p class="mobile-text" style="margin: 0 0 20px 0; font-size: 16px; color: #e5e5e5; line-height: 1.8; text-align: right;">
                                            <strong style="color: #00ff88;">القرار الذي اتخذته اليوم سيغير مسار حياتك المهنية للأبد.</strong>
                                            انضمامك إلى DarkHunter ليس مجرد تسجيل في منصة تعليمية، بل هو انخراط في عالم سري من المحترفين الذين يحمون الأنظمة الرقمية ويكشفون الثغرات قبل أن تستغل.
                                        </p>

                                        <!-- Divider -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 24px 0;">
                                            <tr>
                                                <td style="border-top: 1px solid #2a2a2a; font-size: 0; line-height: 0;">&nbsp;</td>
                                            </tr>
                                        </table>

                                        <!-- Why DarkHunter Section -->
                                        <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: #8b5cf6; text-align: right;">
                                            🔐 لماذا DarkHunter مختلفة؟
                                        </h3>
                                        
                                        <p class="mobile-text" style="margin: 0 0 16px 0; font-size: 15px; color: #d1d5db; line-height: 1.8; text-align: right;">
                                            في عالم يتزايد فيه التهديد السيبراني بشكل جنوني، نحن نمنحك الأدوات والمعرفة التي تحتاجها لتصبح <span style="color: #00ff88; font-weight: 600;">صياداً أخلاقياً (Ethical Hacker)</span> من الطراز العالمي. مختبراتنا ليست محاكاة، بل هي بيئات حقيقية مصممة لاختبار مهاراتك في مواجهة أقوى التحديات الأمنية.
                                        </p>

                                        <!-- Features Grid -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 24px 0;">
                                            <tr>
                                                <td style="padding: 16px; background-color: rgba(0, 255, 136, 0.05); border-right: 3px solid #00ff88; border-radius: 0 8px 8px 0;">
                                                    <p style="margin: 0; font-size: 14px; color: #ffffff; font-weight: 600; text-align: right;">🎯 مختبرات حقيقية</p>
                                                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #9ca3af; text-align: right;">تدرب على أنظمة حقيقية مع ثغرات حقيقية</p>
                                                </td>
                                            </tr>
                                            <tr><td style="height: 12px; font-size: 0;">&nbsp;</td></tr>
                                            <tr>
                                                <td style="padding: 16px; background-color: rgba(139, 92, 246, 0.05); border-right: 3px solid #8b5cf6; border-radius: 0 8px 8px 0;">
                                                    <p style="margin: 0; font-size: 14px; color: #ffffff; font-weight: 600; text-align: right;">⚡ تحديات يومية</p>
                                                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #9ca3af; text-align: right;">CTFs ومسابقات أسبوعية بجوائز قيمة</p>
                                                </td>
                                            </tr>
                                            <tr><td style="height: 12px; font-size: 0;">&nbsp;</td></tr>
                                            <tr>
                                                <td style="padding: 16px; background-color: rgba(0, 255, 136, 0.05); border-right: 3px solid #00ff88; border-radius: 0 8px 8px 0;">
                                                    <p style="margin: 0; font-size: 14px; color: #ffffff; font-weight: 600; text-align: right;">👥 مجتمع نخبوي</p>
                                                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #9ca3af; text-align: right;">تواصل مع أفضل الهكر الأخلاقيين في المنطقة</p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Motivational Quote -->
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 24px 0; background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(0, 255, 136, 0.1) 100%); border-radius: 8px; border: 1px solid rgba(139, 92, 246, 0.2);">
                                            <tr>
                                                <td style="padding: 20px; text-align: center;">
                                                    <p style="margin: 0; font-size: 15px; color: #ffffff; font-style: italic; line-height: 1.6;">
                                                        "الفرق بين المبتدئ والمحترف ليس في الذكاء، بل في ساعات التدريب العميق على استغلال الثغرات. هنا، كل يوم هو فرصة لتصبح أفضل."
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>

                                        <!-- Next Steps -->
                                        <h3 style="margin: 24px 0 16px 0; font-size: 18px; font-weight: 700; color: #00ff88; text-align: right;">
                                            🚀 خطوتك التالية
                                        </h3>
                                        
                                        <p class="mobile-text" style="margin: 0 0 16px 0; font-size: 15px; color: #d1d5db; line-height: 1.8; text-align: right;">
                                            لإكمال انضمامك إلى النخبة، يجب عليك تفعيل حسابك الآن. هذا الإجراء يضمن أمان منصتنا ويؤكد أنك جاد في رحلتك نحو الاحتراف. بعد التفعيل، ستصلك رسالة ترحيبية تحتوي على خارطة طريقك الشخصية للبدء.
                                        </p>

                                        <p class="mobile-text" style="margin: 0; font-size: 15px; color: #9ca3af; line-height: 1.8; text-align: right;">
                                            <strong style="color: #ffffff;">تذكر:</strong> كل خبير أمن سيبراني بدأ من نقطة الصفر. الفرق الوحيد هو أنهم قرروا البدء. أنت الآن على وشك اتخاذ ذلك القرار.
                                        </p>

                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA Button Section -->
                    <tr>
                        <td align="center" style="padding: 0 40px 40px 40px;" class="mobile-padding">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" class="mobile-button" style="width: 100%; max-width: 320px;">
                                <tr>
                                    <td align="center" style="border-radius: 10px; background: linear-gradient(135deg, #00ff88 0%, #00cc6a 100%); box-shadow: 0 10px 40px -10px rgba(0, 255, 136, 0.5), 0 0 0 1px rgba(0, 255, 136, 0.3), inset 0 1px 0 rgba(255,255,255,0.2);">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{VERIFY_LINK}}" style="height:56px;v-text-anchor:middle;width:320px;" arcsize="10%" stroke="f" fillcolor="#00ff88">
                                            <w:anchorlock/>
                                            <center style="color:#000000;font-family:'Segoe UI',Tahoma,sans-serif;font-size:18px;font-weight:700;">تفعيل الحساب الآن</center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-- -->
                                        <a href="{{VERIFY_LINK}}" style="display: block; padding: 18px 32px; color: #000000; text-decoration: none; font-size: 18px; font-weight: 700; text-align: center; border-radius: 10px; font-family: 'Segoe UI', Tahoma, sans-serif; letter-spacing: 0.5px; text-shadow: 0 1px 0 rgba(255,255,255,0.2);">
                                            تفعيل الحساب الآن
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 16px 0 0 0; font-size: 13px; color: #6b7280; text-align: center;">
                                أو انسخ هذا الرابط: <span dir="ltr" style="color: #8b5cf6; font-family: 'Courier New', monospace;">{{VERIFY_LINK}}</span>
                            </p>
                        </td>
                    </tr>

                    <!-- Security Note -->
                    <tr>
                        <td style="padding: 0 40px 32px 40px;" class="mobile-padding">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: rgba(239, 68, 68, 0.05); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 8px;">
                                <tr>
                                    <td style="padding: 16px; text-align: right;">
                                        <p style="margin: 0; font-size: 13px; color: #fca5a5; line-height: 1.6;">
                                            <strong>🔒 ملاحظة أمنية:</strong> إذا لم تقم أنت بطلب الانضمام إلى DarkHunter، يرجى تجاهل هذا البريد الإلكتروني. لا تشارك رابط التفعيل مع أحد أبداً.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 32px 40px; background-color: #070707; border-top: 1px solid #1a1a1a;" class="mobile-padding">
                            
                            <!-- Social Links -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 24px;">
                                <tr>
                                    <td style="padding: 0 8px;">
                                        <a href="#" style="display: block; width: 40px; height: 40px; background-color: #1f1f1f; border-radius: 50%; text-align: center; line-height: 40px; text-decoration: none; border: 1px solid #2a2a2a;">
                                            <span style="color: #9ca3af; font-size: 18px;">𝕏</span>
                                        </a>
                                    </td>
                                    <td style="padding: 0 8px;">
                                        <a href="#" style="display: block; width: 40px; height: 40px; background-color: #1f1f1f; border-radius: 50%; text-align: center; line-height: 40px; text-decoration: none; border: 1px solid #2a2a2a;">
                                            <span style="color: #9ca3af; font-size: 18px;">📘</span>
                                        </a>
                                    </td>
                                    <td style="padding: 0 8px;">
                                        <a href="#" style="display: block; width: 40px; height: 40px; background-color: #1f1f1f; border-radius: 50%; text-align: center; line-height: 40px; text-decoration: none; border: 1px solid #2a2a2a;">
                                            <span style="color: #9ca3af; font-size: 18px;">💬</span>
                                        </a>
                                    </td>
                                    <td style="padding: 0 8px;">
                                        <a href="#" style="display: block; width: 40px; height: 40px; background-color: #1f1f1f; border-radius: 50%; text-align: center; line-height: 40px; text-decoration: none; border: 1px solid #2a2a2a;">
                                            <span style="color: #9ca3af; font-size: 18px;">🐙</span>
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Footer Links -->
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 0 12px;">
                                        <a href="#" style="color: #6b7280; text-decoration: none; font-size: 13px;">سياسة الخصوصية</a>
                                    </td>
                                    <td style="color: #374151; font-size: 13px;">|</td>
                                    <td style="padding: 0 12px;">
                                        <a href="#" style="color: #6b7280; text-decoration: none; font-size: 13px;">شروط الاستخدام</a>
                                    </td>
                                    <td style="color: #374151; font-size: 13px;">|</td>
                                    <td style="padding: 0 12px;">
                                        <a href="#" style="color: #6b7280; text-decoration: none; font-size: 13px;">الدعم الفني</a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Copyright -->
                            <p style="margin: 0; text-align: center; font-size: 12px; color: #4b5563; line-height: 1.6;">
                                © 2024 DarkHunter Labs. جميع الحقوق محفوظة.
                            </p>
                            <p style="margin: 8px 0 0 0; text-align: center; font-size: 11px; color: #374151;">
                                تم إرسال هذا البريد الإلكتروني إلى {$toEmail}
                            </p>
                        </td>
                    </tr>

                </table>
                
                <!-- Bottom Spacing -->
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="height: 20px; font-size: 0; line-height: 0;">&nbsp;</td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

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
  $error = curl_error($ch);

  curl_close($ch);

  return [
    "success" => ($httpCode == 201),
    "http_code" => $httpCode,
    "error" => $error,
    "brevo_response" => json_decode($response, true)
  ];
}