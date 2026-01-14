<?php
/**
 * OAuth Callback Handler
 * Handle callback from OAuth providers
 */

session_start();
require_once 'backend/inc/db.php';

$provider = $_GET['provider'] ?? '';
$code = $_GET['code'] ?? '';
$state = $_GET['state'] ?? '';

// Validate state
if (empty($state) || $state !== ($_SESSION['oauth_state'] ?? '')) {
    die('Invalid state parameter. Possible CSRF attack.');
}

// Validate provider
if ($provider !== ($_SESSION['oauth_provider'] ?? '')) {
    die('Invalid provider.');
}

$config = require 'oauth_config.php';
$providerConfig = $config[$provider];

try {
    // Exchange code for access token
    $tokenData = [
        'code' => $code,
        'redirect_uri' => $providerConfig['redirect_uri'],
    ];
    
    if ($provider === 'google') {
        $tokenData['client_id'] = $providerConfig['client_id'];
        $tokenData['client_secret'] = $providerConfig['client_secret'];
        $tokenData['grant_type'] = 'authorization_code';
    } else if ($provider === 'facebook') {
        $tokenData['client_id'] = $providerConfig['app_id'];
        $tokenData['client_secret'] = $providerConfig['app_secret'];
    }
    
    // Get access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $providerConfig['token_url']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    $tokenResponse = json_decode($response, true);
    $accessToken = $tokenResponse['access_token'] ?? null;
    
    if (!$accessToken) {
        die('Failed to get access token: ' . $response);
    }
    
    // Get user info
    $userInfoUrl = $providerConfig['user_info_url'];
    if ($provider === 'facebook') {
        $userInfoUrl .= '?fields=id,name,email,picture&access_token=' . $accessToken;
    } else {
        $userInfoUrl .= '?access_token=' . $accessToken;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $userInfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($provider === 'google') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
    }
    $userInfoResponse = curl_exec($ch);
    curl_close($ch);
    
    $userInfo = json_decode($userInfoResponse, true);
    
    // Extract user data based on provider
    if ($provider === 'google') {
        $oauthId = $userInfo['id'];
        $email = $userInfo['email'];
        $name = $userInfo['name'];
        $avatar = $userInfo['picture'] ?? null;
    } else if ($provider === 'facebook') {
        $oauthId = $userInfo['id'];
        $email = $userInfo['email'] ?? null;
        $name = $userInfo['name'];
        $avatar = $userInfo['picture']['data']['url'] ?? null;
    }
    
    if (!$email) {
        die('Email not provided by ' . ucfirst($provider) . '. Please grant email permission.');
    }
    
    // Check if user exists
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE oauth_provider = ? AND oauth_id = ?");
    $stmt->execute([$provider, $oauthId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // Link OAuth to existing account
            $stmt = $pdo->prepare("UPDATE users SET oauth_provider = ?, oauth_id = ? WHERE id = ?");
            $stmt->execute([$provider, $oauthId, $existingUser['id']]);
            $user = $existingUser;
        } else {
            // Create new user
            $username = strtolower(preg_replace('/[^a-z0-9]/', '', $name)) . rand(100, 999);
            
            // Make sure avatar URL is valid
            if (empty($avatar)) {
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=38bdf8&color=fff&size=200";
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, full_name, avatar, oauth_provider, oauth_id, role, status)
                VALUES (?, ?, ?, ?, ?, ?, 'user', 1)
            ");
            $stmt->execute([$username, $email, $name, $avatar, $provider, $oauthId]);
            
            $userId = $pdo->lastInsertId();
            
            // Get newly created user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['avatar'] = $user['avatar'];
    $_SESSION['role'] = $user['role'];
    
    // Clean up OAuth session data
    unset($_SESSION['oauth_state']);
    unset($_SESSION['oauth_provider']);
    
    // Redirect
    $redirect = $_SESSION['oauth_redirect'] ?? 'index.php';
    unset($_SESSION['oauth_redirect']);
    
    header('Location: ' . $redirect);
    exit;
    
} catch (Exception $e) {
    die('OAuth Error: ' . $e->getMessage());
}
?>

