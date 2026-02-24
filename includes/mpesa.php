<?php
require_once __DIR__ . '/../config/mpesa.php';

function mpesa_get_token(): ?string
{
    global $MPESA_BASE_URL, $MPESA_CONSUMER_KEY, $MPESA_CONSUMER_SECRET;
    if (!$MPESA_CONSUMER_KEY || !$MPESA_CONSUMER_SECRET) {
        error_log('M-Pesa error: Consumer key/secret not configured. Set MPESA_CONSUMER_KEY and MPESA_CONSUMER_SECRET.');
        return null;
    }
    $url = $MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials';
    $ch = curl_init($url);
    $auth = base64_encode($MPESA_CONSUMER_KEY . ':' . $MPESA_CONSUMER_SECRET);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Authorization: Basic ' . $auth],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($resp === false) {
        $err = function_exists('curl_error') ? curl_error($ch) : 'unknown curl error';
        error_log('M-Pesa token request failed: ' . $err);
    }
    curl_close($ch);
    if ($code !== 200 || !$resp)
        return null;
    $json = json_decode($resp, true);
    return $json['access_token'] ?? null;
}

function format_mpesa_phone(string $phone): string
{
    $phone = preg_replace('/[^\d]/', '', $phone); // Remove non-digit chars (like +, spaces)
    if (strpos($phone, '0') === 0 && strlen($phone) === 10) {
        $phone = '254' . substr($phone, 1); // e.g. 07... to 2547...
    }
    elseif (strpos($phone, '254') !== 0 && strlen($phone) === 9) {
        $phone = '254' . $phone; // e.g. 7... to 2547...
    }
    return $phone;
}

function mpesa_stk_push(int $order_id, float $amount_kes, string $phone_msisdn, string $account_ref, string $txn_desc): array
{
    $phone_msisdn = format_mpesa_phone($phone_msisdn);
    global $MPESA_BASE_URL, $MPESA_SHORTCODE, $MPESA_PASSKEY, $MPESA_CALLBACK_URL;
    $token = mpesa_get_token();
    if (!$token) {
        return ['ok' => false, 'error' => 'Failed to obtain M-Pesa token. Verify credentials and network connectivity.'];
    }
    if (!$MPESA_SHORTCODE || !$MPESA_PASSKEY || !$MPESA_CALLBACK_URL) {
        return ['ok' => false, 'error' => 'Incomplete M-Pesa configuration'];
    }
    $timestamp = date('YmdHis');
    $password = base64_encode($MPESA_SHORTCODE . $MPESA_PASSKEY . $timestamp);
    $payload = [
        'BusinessShortCode' => $MPESA_SHORTCODE,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => (int)round($amount_kes),
        'PartyA' => $phone_msisdn,
        'PartyB' => $MPESA_SHORTCODE,
        'PhoneNumber' => $phone_msisdn,
        'CallBackURL' => $MPESA_CALLBACK_URL,
        'AccountReference' => $account_ref,
        'TransactionDesc' => $txn_desc,
    ];
    $url = $MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest';
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $json = $resp ? json_decode($resp, true) : null;
    if ($code === 200 && is_array($json) && ($json['ResponseCode'] ?? '') === '0') {
        return ['ok' => true, 'data' => $json];
    }
    return ['ok' => false, 'error' => $json['errorMessage'] ?? ('HTTP ' . $code)];
}

function mpesa_extract_callback(array $payload): array
{
    $stk = $payload['Body']['stkCallback'] ?? [];
    $resultCode = $stk['ResultCode'] ?? null;
    $resultDesc = $stk['ResultDesc'] ?? null;
    $checkoutId = $stk['CheckoutRequestID'] ?? null;
    $meta = $stk['CallbackMetadata']['Item'] ?? [];
    $receipt = null;
    foreach ($meta as $item) {
        if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
            $receipt = $item['Value'] ?? null;
        }
    }
    return [
        'result_code' => $resultCode,
        'result_desc' => $resultDesc,
        'checkout_id' => $checkoutId,
        'receipt' => $receipt,
    ];
}