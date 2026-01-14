<?php
/**
 * OAuth Login Initiator
 * Redirect user to OAuth provider
 */

session_start();

$provider = $_GET['provider'] ?? '';
$config = require 'oauth_config.php';

if (!isset($config[$provider])) {
    die('Invalid OAuth provider');
}

$providerConfig = $config[$provider];

// Check if OAuth is configured
if ($provider === 'google' && $providerConfig['client_id'] === 'YOUR_GOOGLE_CLIENT_ID') {
    session_start();
    $_SESSION['oauth_error'] = 'Google OAuth chưa được cấu hình. Vui lòng xem hướng dẫn trong file OAUTH-SETUP-GUIDE.md';
    header('Location: login.php');
    exit;
}

if ($provider === 'facebook' && $providerConfig['app_id'] === 'YOUR_FACEBOOK_APP_ID') {
    session_start();
    $_SESSION['oauth_error'] = 'Facebook OAuth chưa được cấu hình. Vui lòng xem hướng dẫn trong file OAUTH-SETUP-GUIDE.md';
    header('Location: login.php');
    exit;
}

// Generate and store state for security
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = $provider;

// Store redirect URL if provided
if (isset($_GET['redirect'])) {
    $_SESSION['oauth_redirect'] = $_GET['redirect'];
}

// Build authorization URL
if ($provider === 'google') {
    $params = [
        'client_id' => $providerConfig['client_id'],
        'redirect_uri' => $providerConfig['redirect_uri'],
        'response_type' => 'code',
        'scope' => $providerConfig['scope'],
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];
} else if ($provider === 'facebook') {
    $params = [
        'client_id' => $providerConfig['app_id'],
        'redirect_uri' => $providerConfig['redirect_uri'],
        'state' => $state,
        'scope' => $providerConfig['scope'],
        'response_type' => 'code'
    ];
}

$authUrl = $providerConfig['auth_url'] . '?' . http_build_query($params);

// Redirect to OAuth provider
header('Location: ' . $authUrl);
exit;
?>

