<?php
// includes/auth_google.php

require_once __DIR__ . '/db_functions.php';

function handle_google_oauth()
{
    $client_id = getenv('GOOGLE_CLIENT_ID');
    $client_secret = getenv('GOOGLE_CLIENT_SECRET');

    // Determine exact redirect URI (Handle reverse proxy correctly)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $protocol = 'https';
    }
    $host = $_SERVER['HTTP_HOST'];
    $redirect_uri = $protocol . '://' . $host . '/index.php?page=google_oauth';

    if (!$client_id || !$client_secret) {
        $_SESSION['flash'] = "Google Login is not configured on this server.";
        header('Location: index.php?page=login');
        exit;
    }

    // Phase 1: Redirect to Google
    if (!isset($_GET['code'])) {
        $state = bin2hex(random_bytes(16));
        $_SESSION['google_oauth_state'] = $state;

        $params = [
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'state' => $state,
            'access_type' => 'online',
            'prompt' => 'select_account'
        ];

        $auth_url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
        header('Location: ' . $auth_url);
        exit;
    }

    // Phase 2: Handle callback from Google
    if (isset($_GET['code'])) {
        // CSRF Check
        if (empty($_GET['state']) || (isset($_SESSION['google_oauth_state']) && $_GET['state'] !== $_SESSION['google_oauth_state'])) {
            $_SESSION['flash'] = 'Invalid Google OAuth state. Please try again.';
            header('Location: index.php?page=login');
            exit;
        }

        $code = $_GET['code'];

        // Exchange code for token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'code' => $code,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ]));

        $response = curl_exec($ch);
        curl_close($ch);
        $token_data = json_decode($response, true);

        if (isset($token_data['error'])) {
            $_SESSION['flash'] = "Google Authentication Failed: " . ($token_data['error_description'] ?? 'Unknown error');
            header('Location: index.php?page=login');
            exit;
        }

        // Fetch user profile info
        $access_token = $token_data['access_token'];
        $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $access_token]);
        $profile_response = curl_exec($ch);
        curl_close($ch);
        $profile_data = json_decode($profile_response, true);

        if (!isset($profile_data['id']) || !isset($profile_data['email'])) {
            $_SESSION['flash'] = 'Failed to retrieve profile from Google.';
            header('Location: index.php?page=login');
            exit;
        }

        $google_id = $profile_data['id'];
        $email = $profile_data['email'];
        $given_name = $profile_data['given_name'] ?? '';
        $family_name = $profile_data['family_name'] ?? '';
        $full_name = trim($given_name . ' ' . $family_name);
        if ($full_name === '')
            $full_name = $email; // Fallback

        // Check if user exists by Google ID OR by Email
        $user = get_user_by_google_id($google_id);

        if (!$user) {
            // Check if user exists by email (to merge accounts)
            $existing_user = get_user_by_email($email);
            if ($existing_user) {
                link_google_id_to_user($existing_user['id'], $google_id);
                $user = get_user_by_google_id($google_id); // Re-fetch to normalize structure

                // Fallback in case account is locked/inactive
                if (!$user) {
                    $_SESSION['flash'] = 'Your account has been deactivated. Please contact support.';
                    header('Location: index.php?page=login');
                    exit;
                }
            }
            else {
                // Completely new user
                $new_id = create_google_user($email, $given_name, $family_name, $google_id);
                if ($new_id) {
                    $user = get_user_by_google_id($google_id);
                }
                else {
                    $_SESSION['flash'] = 'Failed to create an account from your Google profile.';
                    header('Location: index.php?page=register');
                    exit;
                }
            }
        }

        // Log the user in securely with fallbacks
        $first_name = is_array($user) && isset($user['first_name']) ? $user['first_name'] : '';
        $last_name = is_array($user) && isset($user['last_name']) ? $user['last_name'] : '';
        $email_val = is_array($user) && isset($user['email']) ? $user['email'] : $email;
        $id_val = is_array($user) && isset($user['id']) ? (int)$user['id'] : 0;

        $display_name = trim($first_name . ' ' . $last_name);
        if ($display_name === '') {
            $display_name = $email_val;
        }

        $roles_arr = ['customer'];
        if (is_array($user) && !empty($user['roles'])) {
            $roles_arr = explode(', ', $user['roles']);
        }

        $_SESSION['user'] = [
            'id' => $id_val,
            'name' => $display_name,
            'email' => $email_val,
            'roles' => $roles_arr
        ];

        // Reset any invalid login attempts
        $_SESSION['login_attempts'] = [];

        // Generate Remember Me token automatically for Google users since they are verified
        $token = bin2hex(random_bytes(32));
        set_user_remember_token($user['id'], $token);
        setcookie('remember_token', $token, time() + (86400 * 7), '/', '', isset($_SERVER['HTTPS']), true);

        // Redirect appropriately
        $next = $_SESSION['redirect_after_login'] ?? null;
        if ($next) {
            unset($_SESSION['redirect_after_login']);
            header('Location: ' . $next);
        }
        else {
            header('Location: index.php?page=home');
        }
        exit;
    }
}
?>
