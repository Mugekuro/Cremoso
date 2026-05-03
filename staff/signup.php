<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/google.php';
require_once __DIR__ . '/../includes/auth.php';

if (isLoggedIn()) { header('Location: ../index.php'); exit(); }

$branches = $pdo->query("SELECT branch_id, branch_name FROM branches ORDER BY branch_id")->fetchAll();

// Build Google OAuth URL (state=signup so callback always goes to signup flow)
$googleSignupUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'state'         => 'signup',
]);

// ── If arriving from Google OAuth callback ───────────────────────────────────
$pending = $_SESSION['oauth_pending'] ?? null;
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode     = $_POST['mode'] ?? 'manual';
    $branchId = (int) $_POST['branch_id'];
    $username = trim($_POST['username']);

    if (!$branchId || !$username) {
        $error = 'Please fill in all fields.';
    } else {
        $check = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $check->execute([$username]);
        if ($check->fetch()) {
            $error = 'Username already taken. Please choose another.';
        } elseif ($mode === 'google' && $pending) {
            // Google signup - create unconfirmed account
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, fullname, password, role, branch_id, google_id, email, is_confirmed)
                 VALUES (?, ?, '', 'staff', ?, ?, ?, FALSE)"
            );
            $stmt->execute([$username, $pending['fullname'], $branchId, $pending['google_id'], $pending['email']]);
            unset($_SESSION['oauth_pending']);
            
            // Redirect to login with pending message
            $_SESSION['signup_success'] = 'Account created successfully! Please wait for admin approval before logging in.';
            header('Location: ../login.php'); exit();
        } else {
            // Manual signup - create unconfirmed account
            $fullname = trim($_POST['fullname']);
            $password = $_POST['password'];
            if (!$fullname || !$password) { $error = 'Please fill in all fields.'; }
            else {
                $stmt = $pdo->prepare(
                    "INSERT INTO users (username, fullname, password, role, branch_id, is_confirmed)
                     VALUES (?, ?, ?, 'staff', ?, FALSE)"
                );
                $stmt->execute([$username, $fullname, $password, $branchId]);
                
                // Redirect to login with pending message
                $_SESSION['signup_success'] = 'Account created successfully! Please wait for admin approval before logging in.';
                header('Location: ../login.php'); exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cremoso - Staff Sign Up</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-card">
        <img src="../assets/images/logo.jpg" alt="Cremoso" class="login-logo">
        <h2>Staff Sign Up</h2>
        <p class="subtitle">Create your Cremoso staff account</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($pending): ?>
            <!-- ── Google completion form ── -->
            <div class="alert" style="background:#e8f5e9;border:1.5px solid #a5d6a7;border-radius:12px;padding:12px 16px;font-size:13px;color:#2e7d32;margin-bottom:20px;">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="16" style="margin-right:6px;">
                Signed in as <strong><?= htmlspecialchars($pending['email']) ?></strong>
            </div>
            <form method="POST" class="login-form">
                <input type="hidden" name="mode" value="google">
                <div class="mb-3">
                    <input type="text" name="username" placeholder="Choose a username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="mb-4">
                    <select name="branch_id" required class="branch-select">
                        <option value="">Select your branch</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['branch_id'] ?>"
                                <?= (isset($_POST['branch_id']) && $_POST['branch_id'] == $b['branch_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['branch_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="login-btn">Complete Sign Up</button>
            </form>

        <?php else: ?>
            <!-- ── Google button ── -->
            <a href="<?= htmlspecialchars($googleSignupUrl) ?>" class="google-btn">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="20">
                Sign up with Google
            </a>

            <div class="login-divider"><span>or sign up manually</span></div>

            <!-- ── Manual form ── -->
            <form method="POST" class="login-form">
                <input type="hidden" name="mode" value="manual">
                <div class="mb-3">
                    <input type="text" name="fullname" placeholder="Full name"
                           value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <input type="text" name="username" placeholder="Username"
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="mb-4">
                    <select name="branch_id" required class="branch-select">
                        <option value="">Select your branch</option>
                        <?php foreach ($branches as $b): ?>
                            <option value="<?= $b['branch_id'] ?>"
                                <?= (isset($_POST['branch_id']) && $_POST['branch_id'] == $b['branch_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['branch_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="login-btn">Create Account</button>
            </form>
        <?php endif; ?>

        <p class="mt-3" style="font-size:13px; color: var(--text-muted);">
            Already have an account?
            <a href="../login.php" style="color: var(--primary); font-weight:600;">Sign in</a>
        </p>
    </div>
</div>
<style>
.branch-select {
    width: 100%; padding: 14px 18px;
    border: 1.5px solid var(--border); border-radius: 14px;
    font-size: 15px; font-family: inherit;
    background: var(--surface); color: var(--text-dark);
    transition: all 0.25s;
}
.branch-select:focus {
    outline: none; border-color: var(--primary);
    background: var(--white); box-shadow: 0 0 0 4px rgba(45,168,155,0.15);
}
</style>
</body>
</html>
