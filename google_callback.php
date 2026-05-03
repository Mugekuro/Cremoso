<?php
require_once 'config/database.php';
require_once 'config/google.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { header('Location: index.php'); exit(); }

$error = '';

// ── 1. Exchange code for tokens ──────────────────────────────────────────────
if (empty($_GET['code'])) {
    header('Location: login.php');
    exit();
}

$mode = $_GET['state'] ?? 'login'; // 'login' or 'signup'

$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false,
    stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]),
    ]])
);

if (!$tokenResponse) {
    die('Failed to contact Google token endpoint.');
}

$tokens     = json_decode($tokenResponse, true);
$idToken    = $tokens['id_token'] ?? null;
$accessToken = $tokens['access_token'] ?? null;

if (!$accessToken) {
    die('Google OAuth failed: ' . htmlspecialchars($tokenResponse));
}

// ── 2. Fetch user info ───────────────────────────────────────────────────────
$userInfoResponse = file_get_contents('https://www.googleapis.com/oauth2/v3/userinfo', false,
    stream_context_create(['http' => [
        'header' => 'Authorization: Bearer ' . $accessToken,
    ]])
);

$googleUser = json_decode($userInfoResponse, true);
$googleId   = $googleUser['sub'];
$email      = $googleUser['email'];
$fullname   = $googleUser['name'];

// ── 3. Find existing user by google_id or email ──────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM users WHERE google_id = ? OR (email = ? AND email IS NOT NULL)");
$stmt->execute([$googleId, $email]);
$user = $stmt->fetch();

// ── 4. Route: login vs signup ────────────────────────────────────────────────
if ($user) {
    // Always update google_id if missing
    if (empty($user['google_id'])) {
        $pdo->prepare("UPDATE users SET google_id = ? WHERE user_id = ?")->execute([$googleId, $user['user_id']]);
    }
    
    // Check if staff account is confirmed
    if ($user['role'] === 'staff' && !$user['is_confirmed']) {
        $_SESSION['oauth_error'] = 'Your account is pending approval. Please contact an administrator.';
        header('Location: login.php');
        exit();
    }
    
    // Log in
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['fullname']  = $user['fullname'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['branch_id'] = $user['branch_id'];
    $_SESSION['just_logged_in'] = true;
    
    header('Location: index.php');
    exit();
}

// No existing account — always go to signup flow regardless of mode
$_SESSION['oauth_pending'] = [
    'google_id' => $googleId,
    'email'     => $email,
    'fullname'  => $fullname,
];
header('Location: staff/signup.php');
exit();
