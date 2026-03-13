<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

// Improved error handling
try {
    $items = db_has_connection() ? get_products(12) : [];
}
catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    $items = [];
}
// Use shared format_currency from includes/db_functions.php
?>

<!-- Loading Indicator -->
<div id="loading-indicator" class="text-center py-5" style="display:none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<!-- ***** Hero Section Start ***** -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="hero-content">
                    <span class="hero-badge">New Collection</span>
                    <h1 class="hero-title">Timeless Elegance, Modern Sophistication</h1>
                    <p class="hero-description">Discover our curated collection of premium products designed for the discerning individual. Experience unparalleled quality and craftsmanship.</p>
                    <a href="index.php?page=products" class="hero-btn">
                        Explore Collection <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <img src="assets/images/hero-assortment.png" 
                     alt="Premium Collection" 
                     class="img-fluid text-center w-100"
                     loading="lazy"
                     style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
            </div>
        </div>
    </div>
</section>
<!-- ***** Hero Section End ***** -->

<!-- ***** Featured Products Section ***** -->
<section class="container py-5 my-5">
    <h2 class="section-title">Curated Selection</h2>
    <p class="section-subtitle">Discover our carefully curated collection of premium products</p>

    <div class="row g-4">
        <?php if (empty($items)): ?>
            <div class="col-12 text-center py-5">
                <div class="alert alert-info d-inline-block">No products currently available. Please check back later.</div>
            </div>
        <?php else: ?>
            <?php foreach ($items as $p): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <?php 
                        $img = resolve_product_image($p); 
                        ?>
                        <div class="position-relative overflow-hidden group">
                           <?php if (!empty($img)): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" 
                                 class="card-img-top transition-transform duration-300 hover:scale-105" 
                                 alt="<?php echo htmlspecialchars($p['name']); ?>"
                                 loading="lazy"
                                 style="height: 250px; object-fit: cover;"
                                 onerror="this.onerror=null;this.src='https://dummyimage.com/400x400/e0e0e0/636363.jpg&text=No+Image';">
                           <?php endif; ?>
                            <?php if (isset($p['is_new']) && $p['is_new']): ?>
                                <span class="position-absolute top-0 start-0 m-2 badge bg-primary">New</span>
                            <?php endif; ?>
                             <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                <span class="position-absolute top-0 end-0 m-2 badge bg-danger">Sale</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">
                                <a href="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="text-decoration-none text-dark fw-bold stretched-link-custom">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </a>
                            </h5>
                            <?php if (!empty($p['category_name'])): ?>
                                <p class="text-muted small mb-2"><?php echo htmlspecialchars($p['category_name']); ?></p>
                            <?php endif; ?>
                            
                            <div class="mt-auto">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <span class="fs-5 fw-bold text-primary"><?php echo format_currency((float)($p['sale_price'] ?? $p['price'])); ?></span>
                                    <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                        <span class="text-muted text-decoration-line-through small"><?php echo format_currency((float)$p['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="d-grid">
                                    <form method="post" action="index.php?page=cart_add" class="add-to-cart-form">
                                        <input type="hidden" name="add_to_cart" value="1">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="button" class="btn btn-outline-dark w-100 add-to-cart" data-product-name="<?php echo htmlspecialchars($p['name']); ?>">
                                            <i class="fas fa-shopping-bag me-2"></i> Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="text-center mt-5">
        <a href="index.php?page=products" class="btn btn-primary btn-lg px-5">View All Products</a>
    </div>
</section>
<!-- ***** Featured Products End ***** -->

<!-- ***** Categories Section ***** -->
<section id="categories" class="container-fluid py-5 my-5 bg-light">
    <div class="container">
        <h2 class="section-title">Collections</h2>
        <p class="section-subtitle">Explore our distinguished product categories</p>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=electronics" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Electronics collection" loading="lazy" decoding="async" fetchpriority="low">
                    <div class="category-content">
                        <h4 class="fw-bold">Electronics</h4>
                        <p class="mb-0">Cutting-edge technology</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=home-living" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Home & Living collection" loading="lazy" decoding="async" fetchpriority="low">
                    <div class="category-content">
                        <h4 class="fw-bold">Home & Living</h4>
                        <p class="mb-0">Elevate your space</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=fashion" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Fashion collection" loading="lazy" decoding="async" fetchpriority="low">
                    <div class="category-content">
                        <h4 class="fw-bold">Fashion</h4>
                        <p class="mb-0">Timeless style</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=beauty" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Beauty products collection" loading="lazy">
                    <div class="category-content">
                        <h4 class="fw-bold">Beauty</h4>
                        <p class="mb-0">Care and cosmetics</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=accessories" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&q=70"
                         alt="Accessories (Jewelry) collection"
                         loading="lazy"
                         decoding="async" fetchpriority="low"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/accessories-jewelry/800/600';">
                    <div class="category-content">
                        <h4 class="fw-bold">Accessories</h4>
                        <p class="mb-0">Complete your look</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=shoes" class="category-card d-block text-decoration-none">
                    <img src="https://images.pexels.com/photos/298863/pexels-photo-298863.jpeg?auto=compress&cs=tinysrgb&w=800&dpr=1&q=60"
                         alt="Shoes collection"
                         loading="lazy" decoding="async" fetchpriority="low"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/shoes-collection/800/600';">
                    <div class="category-content">
                        <h4 class="fw-bold">Shoes</h4>
                        <p class="mb-0">Footwear for every occasion</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=gaming" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1511512578047-dfb367046420?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                         alt="Gaming collection"
                         loading="lazy" decoding="async" fetchpriority="low"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/gaming-collection/800/600';">
                    <div class="category-content">
                        <h4 class="fw-bold">Gaming</h4>
                        <p class="mb-0">Next-level entertainment</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ***** Features Section ***** -->
<section id="features" class="container py-5 my-5">
    <h2 class="section-title">Our Services</h2>
    <p class="section-subtitle">Experience the difference with our premium services</p>

    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h4 class="fw-bold">Complimentary Shipping</h4>
                <p class="text-muted">Free express shipping on all orders over $200. Delivered directly to your doorstep with care.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h4 class="fw-bold">Hassle-Free Returns</h4>
                <p class="text-muted">30-day return policy for all items. Your satisfaction is our priority.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h4 class="fw-bold">Secure Transactions</h4>
                <p class="text-muted">Your payment information is protected with enterprise-grade encryption technology.</p>
            </div>
        </div>
    </div>
</section>

<!-- ***** Newsletter Section ***** -->
<section class="newsletter-section">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h2 class="fw-bold mb-3">Stay Informed</h2>
                <p class="mb-4 opacity-90">Subscribe to our newsletter for exclusive offers and new collection previews</p>
                <form id="newsletter-form" method="POST" class="row g-3 justify-content-center">
                    <input type="hidden" name="add_to_cart" value="1">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="col-lg-8">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                    </div>
                    <div class="col-lg-4">
                        <button class="btn btn-light btn-lg w-100" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    // Sticky Header logic (throttled)
    (function() {
        const header = document.querySelector('.header-area');
        if (!header) return;
        let lastY = 0;
        let ticking = false;
        function updateHeader(y) {
            const shouldStick = y > 100;
            const hasClass = header.classList.contains('header-sticky');
            if (shouldStick && !hasClass) header.classList.add('header-sticky');
            else if (!shouldStick && hasClass) header.classList.remove('header-sticky');
        }
        window.addEventListener('scroll', function() {
            lastY = window.scrollY || window.pageYOffset;
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    updateHeader(lastY);
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
        updateHeader(window.scrollY || window.pageYOffset);
    })();

    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            const form = this.closest('form');
            const productName = this.getAttribute('data-product-name');
            const originalText = this.innerHTML;
            if (form) {
                e.preventDefault();
                const fd = new FormData(form);
                fd.set('add_to_cart', '1');
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
                this.disabled = true;
                fetch('index.php?page=cart_add', {
                    method: 'POST',
                    body: fd,
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data && data.ok) {
                        const cartBadge = document.querySelector('.badge.bg-primary') || createCartBadge();
                        cartBadge.textContent = parseInt(data.cart_count ?? '0');
                        showToast(`${productName} added to cart!`);
                    } else if (data && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showToast(data?.error || 'Unable to add to cart.');
                    }
                }).catch(() => {
                    showToast('Network error. Please try again.');
                }).finally(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                });
            }
        });
    });

    function createCartBadge() {
        const badge = document.createElement('span');
        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary';
        document.querySelector('a[href*="cart"]').appendChild(badge);
        return badge;
    }

    function showToast(message) {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        const wrapper = document.createElement('div');
        wrapper.innerHTML = toastHtml;
        const toastEl = wrapper.firstElementChild;
        toastContainer.appendChild(toastEl);
        
        const bsToast = new bootstrap.Toast(toastEl, { delay: 3000 });
        bsToast.show();
        
        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }

    // Newsletter handling
    document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const emailInput = this.querySelector('input[type="email"]');
        const button = this.querySelector('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        setTimeout(() => {
            showToast('Thank you for subscribing!');
            emailInput.value = '';
            button.innerHTML = originalText;
            button.disabled = false;
        }, 1000);
    });
</script>
