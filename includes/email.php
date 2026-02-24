<?php
// SendGrid Mail Sender using pure cURL (No PHP SDK required)
// Relies on SENDGRID_API_KEY and SENDGRID_FROM_EMAIL environment variables

function send_reset_password_email($to_email, $to_name, $reset_link)
{
    $api_key = getenv('SENDGRID_API_KEY') ?: '';
    $from_email = getenv('SENDGRID_FROM_EMAIL') ?: 'noreply@sokosafi.com'; // Default fallback

    if (!$api_key) {
        error_log('SendGrid Error: API Key is missing. Check SENDGRID_API_KEY env var.');
        return false;
    }

    $url = 'https://api.sendgrid.com/v3/mail/send';

    // SendGrid v3 JSON Payload
    $data = [
        'personalizations' => [
            [
                'to' => [
                    [
                        'email' => $to_email,
                        'name' => $to_name ?: 'Customer'
                    ]
                ],
                'subject' => 'Reset Your SokoSafi Password'
            ]
        ],
        'from' => [
            'email' => $from_email,
            'name' => 'SokoSafi Support'
        ],
        'content' => [
            [
                'type' => 'text/html',
                'value' => "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #4f46e5;'>Password Reset Request</h2>
                        <p>Hello,</p>
                        <p>We received a request to reset the password for your SokoSafi account associated with this email address.</p>
                        <p>Click the button below to choose a new password. This link will expire in 1 hour.</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$reset_link}' style='background-color: #4f46e5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; display: inline-block;'>Reset Password</a>
                        </div>
                        <p>If you did not request this, you can safely ignore this email.</p>
                        <hr style='border: none; border-top: 1px solid #e2e8f0; margin: 30px 0;'/>
                        <p style='color: #64748b; font-size: 12px;'>SokoSafi E-Commerce<br>If the button doesn't work, copy and paste this link: {$reset_link}</p>
                    </div>
                "
            ]
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($http_code >= 200 && $http_code < 300) {
        return true;
    }
    else {
        error_log("SendGrid Error [$http_code]: $response | cURL: $curl_error");
        return false;
    }
}
?>
