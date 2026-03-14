<?php
// pages/reset_password.php

require_once __DIR__ . '/../includes/db_functions.php';

$error = null;
$success = null;
$token = $_GET['token'] ?? '';
$user = null;

if (empty($token)) {
    $error = "Invalid or missing password reset token.";
}
else {
    $user = get_user_by_reset_token($token);
    if (!$user) {
        $error = "This password reset link is invalid or has expired. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    }
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if (update_user_password($user['id'], $hash)) {
            $success = "Your password has been successfully reset! You can now log in.";
            $user = null; // Hide the form
        }
        else {
            $error = "An error occurred while saving your new password. Please try again.";
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
                        <img src="https://res.cloudinary.com/dmnbjskbz/image/upload/v1773442661/sokosafi/logo.png" alt="Logo" style="width: 80px; height: auto;">
                    </div>
                </div>
                <h1 class="h3 fw-bold text-dark mb-3">Reset Password</h1>
                <?php if ($user): ?>
                    <p class="text-muted">Enter a new password for <?php echo htmlspecialchars($user['email']); ?></p>
                <?php
endif; ?>
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
                        <div class="alert alert-success d-flex align-items-center mb-4" role="alert" style="background-color: var(--accent); color: white; border: none;">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                        <a href="index.php?page=login" class="btn-elegant w-100 py-3 fw-semibold text-center text-decoration-none">
                            <span class="btn-content">Go to Login</span>
                        </a>
                    <?php
endif; ?>

                    <?php if ($user && !isset($success)): ?>
                    <form method="post" action="index.php?page=reset_password&token=<?php echo urlencode($token); ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="form-group-elegant mb-4">
                            <label for="password" class="form-label fw-semibold text-dark">New Password</label>
                            <div class="input-group-elegant">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control-elegant" 
                                       id="password" 
                                       name="password" 
                                       placeholder="New password" 
                                       required minlength="6">
                                <button type="button" class="toggle-password" aria-label="Show password" title="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group-elegant mb-4">
                            <label for="confirm_password" class="form-label fw-semibold text-dark">Confirm Password</label>
                            <div class="input-group-elegant">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control-elegant" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       placeholder="Confirm new password" 
                                       required minlength="6">
                            </div>
                        </div>

                        <button class="btn-elegant w-100 py-3 fw-semibold" type="submit">
                            <span class="btn-content">
                                <i class="fas fa-save me-2"></i>
                                Save New Password
                            </span>
                        </button>
                    </form>
                    <?php
elseif (!$user && !$success): ?>
                        <div class="text-center mt-4 pt-4 border-top-elegant">
                            <a href="index.php?page=forgot_password" class="btn btn-outline-primary w-100">Request a new link</a>
                        </div>
                    <?php
endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($user && !isset($success)): ?>
<script>
    // Password toggle for the reset page
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('.toggle-password');
        const passInput = document.getElementById('password');
        
        if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function() {
                const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passInput.setAttribute('type', type);
                
                const icon = this.querySelector('i');
                if (type === 'text') {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        }
    });
</script>
<?php
endif; ?>
