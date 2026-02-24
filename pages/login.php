<?php
// Show any server-side auth errors set by index.php
$error = $auth_error ?? null;
$next = isset($_GET['next']) ? $_GET['next'] : null;
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Professional Header -->
            <div class="text-center mb-5">
                <div class="elegant-icon mb-4">
                    <div class="icon-wrapper">
                        <img src="https://res.cloudinary.com/dmnbjskbz/image/upload/v1771605277/sokosafi/logo.png" alt="Logo" style="width: 60%; height: auto;">
                    </div>
                </div>
                <h1 class="h2 fw-bold text-dark mb-3">Welcome Back</h1>
                <p class="text-muted">Sign in to access your account and continue shopping</p>
            </div>

            <!-- Login Form -->
            <div class="login-card">
                <div class="card-body p-5">
                    <?php if ($error): ?>
                        <div class="alert alert-elegant d-flex align-items-center" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php
endif; ?>

                    <form method="post" action="index.php?page=login<?php echo $next ? '&next=' . urlencode($next) : ''; ?>">
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

                        <div class="form-group-elegant mb-4">
                            <label for="password" class="form-label fw-semibold text-dark">Password</label>
                            <div class="input-group-elegant">
                                <span class="input-icon">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" 
                                       class="form-control-elegant" 
                                       id="password" 
                                       name="password" 
                                       placeholder="password" 
                                       required>
                                <button type="button" class="toggle-password" aria-label="Show password" title="Show password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1">
                                <label class="form-check-label text-muted" style="font-size: 0.9rem;" for="remember_me">Remember me for 7 days</label>
                            </div>
                            <a href="index.php?page=forgot_password" class="text-primary text-decoration-none fw-semibold" style="font-size: 0.9rem;">Forgot Password?</a>
                        </div>

                        <?php if ($next): ?>
                            <input type="hidden" name="next" value="<?php echo htmlspecialchars($next, ENT_QUOTES, 'UTF-8'); ?>" />
                        <?php
endif; ?>

                        <button class="btn-elegant w-100 py-3 fw-semibold" type="submit">
                            <span class="btn-content">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In to Your Account
                            </span>
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-4 border-top-elegant">
                        <p class="text-muted mb-0">
                            Don't have an account? 
                            <a href="index.php?page=register" class="register-link-elegant fw-semibold">
                                Create account
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Professional Features -->
            <div class="features-elegant mt-5">
                <div class="row g-4">
                    <div class="col-4">
                        <div class="feature-item-elegant">
                            <div class="feature-icon-elegant security">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="feature-text-elegant">Secure Access</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="feature-item-elegant">
                            <div class="feature-icon-elegant privacy">
                                <i class="fas fa-user-lock"></i>
                            </div>
                            <div class="feature-text-elegant">Privacy First</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="feature-item-elegant">
                            <div class="feature-icon-elegant seamless">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <div class="feature-text-elegant">Seamless Experience</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Page-local styles disabled; using global theme
:root {
    --primary: #4f46e5;
    --primary-light: #818cf8;
    --primary-dark: #3730a3;
    --secondary: #f59e0b;
    --accent: #10b981;
    --accent-light: #34d399;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-600: #475569;
    --gray-800: #1e293b;
    --gray-900: #0f172a;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

body {
    background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
    min-height: 100vh;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Elegant Icon */
.elegant-icon {
    position: relative;
}

.icon-wrapper {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    box-shadow: var(--shadow-lg);
    position: relative;
}

.icon-wrapper i {
    font-size: 2rem;
    color: white;
}

.icon-wrapper:before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 22px;
    z-index: -1;
    opacity: 0.7;
    filter: blur(8px);
}

/* Login Card */
.login-card {
    border: none;
    border-radius: 20px;
    background: white;
    box-shadow: var(--shadow-xl);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid var(--gray-200);
}

.login-card:hover {
    box-shadow: var(--shadow-xl), 0 0 0 1px rgba(79, 70, 229, 0.1);
}

/* Form Elements */
.form-group-elegant {
    position: relative;
}

.form-label {
    color: var(--gray-800);
    margin-bottom: 0.5rem;
    display: block;
    font-size: 0.9rem;
    font-weight: 600;
}

.input-group-elegant {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 1rem;
    z-index: 2;
    color: var(--gray-600);
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control-elegant {
    width: 100%;
    padding: 1rem 1rem 1rem 3rem;
    border: 2px solid var(--gray-200);
    border-radius: 12px;
    background: white;
    font-size: 1rem;
    transition: all 0.3s ease;
    color: var(--gray-800);
    font-family: inherit;
}

.form-control-elegant:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    background: white;
}

.form-control-elegant:focus + .input-icon {
    color: var(--primary);
}

/* Elegant Button */
.btn-elegant {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    border: none;
    border-radius: 12px;
    color: white;
    font-size: 1rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow);
}

.btn-elegant:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-lg);
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
}

.btn-elegant:active {
    transform: translateY(0);
}

.btn-content {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Alert */
.alert-elegant {
    background: #fef3f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    border-radius: 10px;
    padding: 1rem;
    border-left: 4px solid #ef4444;
    font-size: 0.9rem;
}

/* Register Link */
.register-link-elegant {
    color: var(--primary);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.register-link-elegant:hover {
    color: var(--primary-dark);
}

.border-top-elegant {
    border-top: 1px solid var(--gray-200) !important;
}

/* Elegant Features */
.features-elegant {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.feature-item-elegant {
    text-align: center;
    padding: 0.5rem;
}

.feature-icon-elegant {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.75rem;
    font-size: 1.2rem;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.feature-icon-elegant.security {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: white;
}

.feature-icon-elegant.privacy {
    background: linear-gradient(135deg, var(--accent), var(--accent-light));
    color: white;
}

.feature-icon-elegant.seamless {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
}

.feature-icon-elegant:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.feature-text-elegant {
    font-size: 0.8rem;
    color: var(--gray-600);
    font-weight: 600;
    line-height: 1.4;
}

/* Professional Animations */
@keyframes subtleFadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.login-card {
    animation: subtleFadeIn 0.6s ease-out;
}

.feature-item-elegant {
    animation: subtleFadeIn 0.6s ease-out;
}

.feature-item-elegant:nth-child(1) { animation-delay: 0.1s; }
.feature-item-elegant:nth-child(2) { animation-delay: 0.2s; }
.feature-item-elegant:nth-child(3) { animation-delay: 0.3s; }

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .login-card .card-body {
        padding: 2rem !important;
    }
    
    .icon-wrapper {
        width: 70px;
        height: 70px;
    }
    
    .icon-wrapper i {
        font-size: 1.75rem;
    }
    
    .features-elegant {
        padding: 1.5rem;
    }
}

/* Focus states for accessibility */
.form-control-elegant:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

.btn-elegant:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}
-->

<script>
// Professional micro-interactions
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control-elegant');
    
    inputs.forEach(input => {
        // Add focus/blur effects
        input.addEventListener('focus', function() {
            this.parentElement.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.parentElement.classList.remove('focused');
        });
        
        // Add input validation styling
        input.addEventListener('input', function() {
            if (this.validity.valid) {
                this.style.borderColor = 'var(--accent)';
            } else if (this.value.length > 0) {
                this.style.borderColor = '#ef4444';
            } else {
                this.style.borderColor = 'var(--gray-200)';
            }
        });
    });
    
    // Smooth button hover effects
    const loginBtn = document.querySelector('.btn-elegant');
    
    loginBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-1px)';
    });
    
    loginBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });

    // Password visibility toggle
    const pwdInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password');
    if (pwdInput && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const isHidden = pwdInput.getAttribute('type') === 'password';
            pwdInput.setAttribute('type', isHidden ? 'text' : 'password');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }
            this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
            this.setAttribute('title', isHidden ? 'Hide password' : 'Show password');
        });
    }
});
</script>