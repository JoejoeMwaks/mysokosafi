<?php
// pages/forgot_password.php

require_once __DIR__ . '/../includes/db_functions.php';
require_once __DIR__ . '/../includes/email.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    else {
        // We always show a success message to prevent email enumeration attacks
        $success = "If an account with that email exists, a password reset link has been sent.";

        $user = get_user_by_email($email);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            if (set_password_reset_token($email, $token)) {
                // Construct reset link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $reset_link = $protocol . '://' . $host . '/index.php?page=reset_password&token=' . urlencode($token);

                $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));

                // Keep trying to send in background, ignore error on UI
                send_reset_password_email($email, $name, $reset_link);
            }
        }
    }
}
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="text-center mb-5">
                <div class="elegant-icon mb-4">
                    <div class="icon-wrapper">
                        <img src="https://res.cloudinary.com/dmnbjskbz/image/upload/v1771605277/sokosafi/logo.png" alt="Logo" style="width: 60%; height: auto;">
                    </div>
                </div>
                <h1 class="h3 fw-bold text-dark mb-3">Forgot Password?</h1>
                <p class="text-muted">Enter your email address to receive a secure password reset link.</p>
            </div>

            <div class="login-card">
                <div class="card-body p-5">
                    <?php if ($error): ?>
                        <div class="alert alert-elegant d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php
elseif ($success): ?>
                        <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: var(--accent); color: white; border: none;">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php
endif; ?>

                    <form method="post" action="index.php?page=forgot_password">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-group-elegant mb-4">
                            <label for="email" class="form-label fw-semibold text-dark">Email Address</label>
                            <div class="input-group-elegant">
                                <span class="input-icon">
                                    <i class="fas fa-envelope"></i>
                                </span>
                                <input type="email" 
                                       class="form-control-elegant" 
                                       id="email" 
                                       name="email" 
                                       placeholder="you@example.com" 
                                       required>
                            </div>
                        </div>

                        <button class="btn-elegant w-100 py-3 fw-semibold" type="submit">
                            <span class="btn-content">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Reset Link
                            </span>
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-4 border-top-elegant">
                        <p class="text-muted mb-0">Remembered your password? <a href="index.php?page=login" class="text-primary text-decoration-none fw-semibold">Sign In</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
