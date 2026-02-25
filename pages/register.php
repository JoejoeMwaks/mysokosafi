<?php
// Show any server-side auth errors set by index.php
$error = $auth_error ?? null;
?>

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <!-- Elegant Header -->
            <div class="text-center mb-5">
                <div class="elegant-icon mb-4">
                    <div class="icon-wrapper">
                        <img src="https://res.cloudinary.com/dmnbjskbz/image/upload/v1771605277/sokosafi/logo.png" alt="Logo" style="width: 60%; height: auto;">
                    </div>
                </div>
                <h1 class="h2 fw-bold text-dark mb-3">Create Your Account</h1>
                <p class="text-muted">Join us and start your shopping journey</p>
            </div>

            <!-- Registration Form -->
            <div class="elegant-card">
                <div class="card-body p-5">
                    <?php if ($error): ?>
                        <div class="alert alert-elegant d-flex align-items-center mb-4" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php
endif; ?>

                    <form method="post" action="index.php?page=register">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="form-group-elegant mb-4">
                            <label for="name" class="form-label fw-semibold text-dark">Full Name</label>
                            <div class="input-group-elegant">
                                <span class="input-icon">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" 
                                       class="form-control-elegant" 
                                       id="name" 
                                       name="name" 
                                       placeholder="Enter your full name" 
                                       required>
                            </div>
                        </div>

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
                                       placeholder="your@email.com" 
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
                                       placeholder="Create a secure password" 
                                       required>
                            </div>
                            <div class="form-text text-muted mt-2">
                                <small>Use 8 or more characters with a mix of letters, numbers & symbols</small>
                            </div>
                        </div>

                        <button class="btn-elegant w-100 py-3 fw-semibold mb-4" type="submit">
                            <i class="fas fa-user-plus me-2"></i>
                            Create Account
                        </button>
                    </form>

                    <div class="position-relative mt-2 mb-4">
                        <hr class="text-muted opacity-25">
                        <div class="position-absolute top-50 start-50 translate-middle px-3 bg-white text-muted small fw-semibold">
                            OR
                        </div>
                    </div>

                    <a href="index.php?page=google_oauth" class="btn btn-outline-dark w-100 py-3 fw-semibold d-flex align-items-center justify-content-center mb-4" style="border-width: 2px; border-radius: 8px; transition: all 0.3s ease;">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="24" height="24" class="me-2">
                        Sign Up with Google
                    </a>

                    <div class="text-center">
                        <p class="text-muted mb-0">
                            Already have an account? 
                            <a href="index.php?page=login" class="login-link fw-semibold">
                                Sign in here
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Trust Indicators -->
            <div class="trust-indicators mt-4">
                <div class="row g-4 text-center">
                    <div class="col-4">
                        <div class="trust-item">
                            <i class="fas fa-shield-alt text-success"></i>
                            <div class="trust-text">Secure</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="trust-item">
                            <i class="fas fa-lock text-primary"></i>
                            <div class="trust-text">Private</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="trust-item">
                            <i class="fas fa-bolt text-warning"></i>
                            <div class="trust-text">Fast</div>
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
    --accent: #10b981;
    --gray-50: #f8fafc;
    --gray-100: #f1f5f9;
    --gray-200: #e2e8f0;
    --gray-600: #475569;
    --gray-800: #1e293b;
    --gray-900: #0f172a;
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
    opacity: 0.3;
    filter: blur(8px);
}

/* Elegant Card */
.elegant-card {
    border: none;
    border-radius: 20px;
    background: white;
    box-shadow: var(--shadow-xl);
    transition: all 0.3s ease;
    border: 1px solid var(--gray-200);
}

.elegant-card:hover {
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

.form-text {
    font-size: 0.875rem;
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
    transition: all 0.3s ease;
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

/* Login Link */
.login-link {
    color: var(--primary);
    text-decoration: none;
    transition: all 0.3s ease;
}

.login-link:hover {
    color: var(--primary-dark);
}

/* Trust Indicators */
.trust-indicators {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: var(--shadow);
    border: 1px solid var(--gray-200);
}

.trust-item {
    padding: 0.5rem;
}

.trust-item i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    display: block;
}

.trust-text {
    font-size: 0.8rem;
    color: var(--gray-600);
    font-weight: 600;
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

.elegant-card {
    animation: subtleFadeIn 0.6s ease-out;
}

.trust-item {
    animation: subtleFadeIn 0.6s ease-out;
}

.trust-item:nth-child(1) { animation-delay: 0.1s; }
.trust-item:nth-child(2) { animation-delay: 0.2s; }
.trust-item:nth-child(3) { animation-delay: 0.3s; }

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .elegant-card .card-body {
        padding: 2rem !important;
    }
    
    .icon-wrapper {
        width: 70px;
        height: 70px;
    }
    
    .icon-wrapper i {
        font-size: 1.75rem;
    }
    
    .trust-indicators {
        padding: 1rem;
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

/* Password strength indicator */
.password-strength {
    height: 4px;
    background: var(--gray-200);
    border-radius: 2px;
    margin-top: 0.5rem;
    overflow: hidden;
}

.strength-bar {
    height: 100%;
    width: 0%;
    background: var(--accent);
    transition: all 0.3s ease;
    border-radius: 2px;
}
-->

<script>
// Professional micro-interactions
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.form-control-elegant');
    const passwordInput = document.getElementById('password');
    
    // Add focus/blur effects
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.parentElement.classList.remove('focused');
        });
    });
    
    // Password strength indicator (basic)
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const strengthBar = document.querySelector('.strength-bar');
            if (strengthBar) {
                const strength = Math.min(this.value.length * 10, 100);
                strengthBar.style.width = strength + '%';
                
                // Change color based on strength
                if (strength < 40) {
                    strengthBar.style.background = '#ef4444';
                } else if (strength < 70) {
                    strengthBar.style.background = '#f59e0b';
                } else {
                    strengthBar.style.background = '#10b981';
                }
            }
        });
    }
    
    // Smooth button hover effects
    const registerBtn = document.querySelector('.btn-elegant');
    
    registerBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-1px)';
    });
    
    registerBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
    
    // Form validation styling
    inputs.forEach(input => {
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
});

// Add password strength indicator to the form
document.addEventListener('DOMContentLoaded', function() {
    const passwordGroup = document.querySelector('#password').closest('.form-group-elegant');
    if (passwordGroup) {
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        strengthIndicator.innerHTML = '<div class="strength-bar"></div>';
        passwordGroup.appendChild(strengthIndicator);
    }
});
</script>