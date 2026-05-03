<?php
require_once 'config/database.php';
require_once 'config/google.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { header('Location: index.php'); exit(); }

// Build Google OAuth URL
$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'access_type'   => 'online',
    'prompt'        => 'select_account',
    'state'         => $_GET['mode'] ?? 'login',   // 'login' or 'signup'
]);
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;

$error = '';
$showLogoutNotification = isset($_GET['logged_out']) && $_GET['logged_out'] == '1';
$showErrorModal = false;
$showSuccessModal = false;
$successMessage = '';

if (!empty($_SESSION['oauth_error'])) {
    $error = $_SESSION['oauth_error'];
    $showErrorModal = true;
    unset($_SESSION['oauth_error']);
}

if (!empty($_SESSION['signup_success'])) {
    $successMessage = $_SESSION['signup_success'];
    $showSuccessModal = true;
    unset($_SESSION['signup_success']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        // Check if staff account is confirmed
        if ($user['role'] === 'staff' && !$user['is_confirmed']) {
            $error = 'Your account is pending approval. Please contact an administrator.';
            $showErrorModal = true;
        } else {
            $_SESSION['user_id']   = $user['user_id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['fullname']  = $user['fullname'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['branch_id'] = $user['branch_id'];
            $_SESSION['just_logged_in'] = true;
            
            header('Location: index.php');
            exit();
        }
    } else {
        $error = 'Invalid username or password';
        $showErrorModal = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cremoso - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/Cremoso/assets/css/style.css?v=5">
    <link rel="stylesheet" href="/Cremoso/assets/css/auth.css?v=5">
</head>
<body>

</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <img src="/Cremoso/assets/images/logo.jpg" alt="Cremoso" class="login-logo">
            <h2>CREMOSO</h2>
            <p class="subtitle">Sales & Transaction Management System</p>

            <form method="POST" class="login-form">
                <div class="mb-3">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="mb-4">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="login-btn">Sign In</button>
            </form>

            <div class="login-divider">
                <span>or</span>
            </div>

            <a href="<?= htmlspecialchars($googleAuthUrl) ?>" class="google-btn">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="20">
                Sign in with Google
            </a>

            <p class="mt-3" style="font-size:13px; color: var(--text-muted);">
                New staff?
                <a href="staff/signup.php" style="color: var(--primary); font-weight:600;">Sign up</a>
            </p>
        </div>
    </div>

    <!-- Contact Us Icon Button -->
    <button id="contactUsBtn" class="contact-us-btn" aria-label="Contact Us">
        <i class="fas fa-info"></i>
    </button>

    <!-- Logout Notification Modal -->
    <div id="logoutModal" class="logout-modal" style="display: none;">
        <div class="logout-modal-content">
            <i class="fas fa-check-circle"></i>
            <p>You have been logged out. Thank you!</p>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="errorModal" class="logout-modal" style="display: none;">
        <div class="logout-modal-content error-modal">
            <i class="fas fa-exclamation-circle"></i>
            <p><?= htmlspecialchars($error) ?></p>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="logout-modal" style="display: none;">
        <div class="logout-modal-content success-modal">
            <i class="fas fa-check-circle"></i>
            <p><?= htmlspecialchars($successMessage) ?></p>
        </div>
    </div>

    <!-- Contact Us Modal -->
    <div id="contactModal" class="contact-modal" style="display: none;">
        <div class="contact-modal-content">
            <button class="contact-close-btn" aria-label="Close">&times;</button>
            <h3><i class="fas fa-headset"></i> Contact & Support</h3>
            <p class="contact-subtitle">Need help with the system?</p>
            <div class="contact-info">
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:cremososoftserveph@gmail.com">cremososoftserveph@gmail.com</a>
                </div>
                <div class="contact-item">
                    <i class="fab fa-facebook"></i>
                    <a href="https://www.facebook.com/p/Cremoso-Soft-serve-61575154029909/" target="_blank">Cremoso Soft-serve</a>
                </div>
                <div class="contact-item">
                    <i class="fab fa-instagram"></i>
                    <a href="https://www.instagram.com/cremososoftserve/" target="_blank">cremososoftserve</a>
                </div>
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">
                <div class="contact-item team">
                    <i class="fas fa-users"></i>
                    <div>
                        <strong>Development Team:</strong>
                        <p>Miershan E. Cantiver, Jan Mari E. Cahimtong, Lorenz Jouebren Labadan, Jann Earl Cabana</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:2401107747@student.buksu.edu.ph">2401107747@student.buksu.edu.ph</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show logout notification if logged out
        <?php if ($showLogoutNotification): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('logoutModal');
            modal.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(function() {
                modal.style.opacity = '0';
                setTimeout(function() {
                    modal.style.display = 'none';
                    // Clean up URL
                    const url = new URL(window.location);
                    url.searchParams.delete('logged_out');
                    window.history.replaceState({}, document.title, url.pathname);
                }, 300);
            }, 3000);
        });
        <?php endif; ?>

        // Show error modal if there's an error
        <?php if ($showErrorModal): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('errorModal');
            modal.style.display = 'block';
            
            // Hide after 3 seconds
            setTimeout(function() {
                modal.style.opacity = '0';
                setTimeout(function() {
                    modal.style.display = 'none';
                }, 300);
            }, 3000);
        });
        <?php endif; ?>

        // Show success modal if there's a success message
        <?php if ($showSuccessModal): ?>
        window.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('successModal');
            modal.style.display = 'block';
            
            // Hide after 5 seconds (longer for success messages)
            setTimeout(function() {
                modal.style.opacity = '0';
                setTimeout(function() {
                    modal.style.display = 'none';
                }, 300);
            }, 5000);
        });
        <?php endif; ?>

        // Contact Us Modal functionality
        const contactBtn = document.getElementById('contactUsBtn');
        const contactModal = document.getElementById('contactModal');
        const contactCloseBtn = document.querySelector('.contact-close-btn');
        const contactModalContent = document.querySelector('.contact-modal-content');

        // Show modal
        contactBtn.addEventListener('click', function() {
            contactModal.style.display = 'flex';
        });

        // Close modal via close button
        contactCloseBtn.addEventListener('click', function() {
            contactModal.style.display = 'none';
        });

        // Close modal via backdrop click
        contactModal.addEventListener('click', function(e) {
            if (e.target === contactModal) {
                contactModal.style.display = 'none';
            }
        });

        // Prevent modal content clicks from closing modal
        contactModalContent.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    </script>
</body>
</html>
