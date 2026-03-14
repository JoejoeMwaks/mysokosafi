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
    <div class="container position-relative" style="z-index:3;">
        <div class="row align-items-center gy-4">
            <div class="col-lg-6 col-md-8">
                <div class="hero-glass-panel">
                    <span class="hero-badge"><i class="fas fa-sparkles fa-sm me-1"></i> New Collection</span>
                    <h1 class="hero-title">Timeless Elegance,<br>Modern Sophistication</h1>
                    <p class="hero-description">Discover our curated collection of premium products designed for the discerning individual. Experience unparalleled quality and craftsmanship.</p>
                    <a href="index.php?page=products" class="hero-btn">
                        Explore Collection <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block text-center">
                <img src="assets/images/hero-assortment.png"
                     alt="Premium Collection"
                     class="img-fluid"
                     loading="lazy"
                     style="border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.35); max-height: 480px; object-fit: cover;"
                     onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>
<!-- ***** Hero Section End ***** -->

<!-- ***** Featured Products Section ***** -->
<section class="container py-5 my-4">
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
                    <div class="glass-card product-card h-100">
                        <?php
                        $img = resolve_product_image($p);
                        ?>
                        <div class="product-image-wrapper">
                            <?php if (!empty($img)): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>"
                                 class="card-img-top product-image"
                                 alt="<?php echo htmlspecialchars($p['name']); ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='https://dummyimage.com/400x400/e0e0e0/636363.jpg&text=No+Image';">
                            <?php endif; ?>
                            <div class="product-badge-group">
                                <?php if (isset($p['is_new']) && $p['is_new']): ?>
                                    <span class="glass-badge new-badge">New</span>
                                <?php else: ?>
                                    <span></span>
                                <?php endif; ?>
                                <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                    <span class="glass-badge sale-badge">Sale</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-1">
                                <a href="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($p['name']); ?>
                                </a>
                            </h5>
                            <?php if (!empty($p['category_name'])): ?>
                                <p class="product-category-label"><?php echo htmlspecialchars($p['category_name']); ?></p>
                            <?php endif; ?>

                            <div class="mt-auto">
                                <div class="product-price-row">
                                    <span class="price-current"><?php echo format_currency((float)($p['sale_price'] ?? $p['price'])); ?></span>
                                    <?php if (isset($p['sale_price']) && $p['sale_price'] < $p['price']): ?>
                                        <span class="price-original"><?php echo format_currency((float)$p['price']); ?></span>
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
<section id="categories" class="container-fluid py-5 my-4 glass-surface-alt">
    <div class="container">
        <h2 class="section-title">Collections</h2>
        <p class="section-subtitle">Explore our distinguished product categories</p>

        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=electronics" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1498049794561-7780e7231661?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Electronics collection" loading="lazy" decoding="async">
                    <div class="category-content">
                        <h4>Electronics</h4>
                        <p>Cutting-edge technology</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=home-living" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Home &amp; Living collection" loading="lazy" decoding="async">
                    <div class="category-content">
                        <h4>Home &amp; Living</h4>
                        <p>Elevate your space</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=fashion" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=70" alt="Fashion collection" loading="lazy" decoding="async">
                    <div class="category-content">
                        <h4>Fashion</h4>
                        <p>Timeless style</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=beauty" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Beauty products collection" loading="lazy">
                    <div class="category-content">
                        <h4>Beauty</h4>
                        <p>Care and cosmetics</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=accessories" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1522312346375-d1a52e2b99b3?auto=format&fit=crop&w=800&q=70"
                         alt="Accessories collection"
                         loading="lazy" decoding="async"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/accessories-jewelry/800/600';">
                    <div class="category-content">
                        <h4>Accessories</h4>
                        <p>Complete your look</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=shoes" class="category-card d-block text-decoration-none">
                    <img src="https://images.pexels.com/photos/298863/pexels-photo-298863.jpeg?auto=compress&cs=tinysrgb&w=800&dpr=1&q=60"
                         alt="Shoes collection"
                         loading="lazy" decoding="async"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/shoes-collection/800/600';">
                    <div class="category-content">
                        <h4>Shoes</h4>
                        <p>Footwear for every occasion</p>
                    </div>
                </a>
            </div>
            <div class="col-lg-4 col-md-6">
                <a href="index.php?page=products&category=gaming" class="category-card d-block text-decoration-none">
                    <img src="https://images.unsplash.com/photo-1511512578047-dfb367046420?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                         alt="Gaming collection"
                         loading="lazy" decoding="async"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/gaming-collection/800/600';">
                    <div class="category-content">
                        <h4>Gaming</h4>
                        <p>Next-level entertainment</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ***** Features Section ***** -->
<section id="features" class="container py-5 my-4">
    <h2 class="section-title">Our Services</h2>
    <p class="section-subtitle">Experience the difference with our premium services</p>

    <div class="row g-4">
        <div class="col-lg-4 col-md-6">
            <div class="feature-card glass-card">
                <div class="feature-icon">
                    <i class="fas fa-shipping-fast"></i>
                </div>
                <h4>Complimentary Shipping</h4>
                <p>Free express shipping on all orders over Ksh 5,000. Delivered directly to your doorstep with care.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="feature-card glass-card">
                <div class="feature-icon">
                    <i class="fas fa-undo"></i>
                </div>
                <h4>Hassle-Free Returns</h4>
                <p>30-day return policy for all items. Your satisfaction is our top priority.</p>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="feature-card glass-card">
                <div class="feature-icon">
                    <i class="fas fa-lock"></i>
                </div>
                <h4>Secure Transactions</h4>
                <p>Your payment information is protected with enterprise-grade encryption technology.</p>
            </div>
        </div>
    </div>
</section>

<!-- ***** Newsletter Section ***** -->
<section class="newsletter-section">
    <div class="container position-relative" style="z-index:2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-7">
                <h2 class="fw-bold mb-3">Stay Informed</h2>
                <p class="mb-4">Subscribe to our newsletter for exclusive offers and new collection previews</p>
                <form id="newsletter-form" method="POST" class="row g-3 justify-content-center">
                    <input type="hidden" name="add_to_cart" value="1">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <div class="col-lg-7 col-sm-8">
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Enter your email address" required>
                    </div>
                    <div class="col-lg-4 col-sm-4">
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
        let lastY = 0, ticking = false;
        function updateHeader(y) {
            const shouldStick = y > 60;
            const hasClass = header.classList.contains('header-sticky');
            if (shouldStick && !hasClass) header.classList.add('header-sticky');
            else if (!shouldStick && hasClass) header.classList.remove('header-sticky');
        }
        window.addEventListener('scroll', function() {
            lastY = window.scrollY || window.pageYOffset;
            if (!ticking) {
                window.requestAnimationFrame(function() { updateHeader(lastY); ticking = false; });
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
                    method: 'POST', body: fd,
                    headers: { 'Accept': 'application/json' }
                }).then(r => r.json()).then(data => {
                    if (data && data.ok) {
                        const badge = document.getElementById('cart-count');
                        if (badge) { badge.textContent = data.cart_count ?? '0'; badge.style.display = 'flex'; }
                        showToast(`${productName} added to cart!`);
                    } else if (data && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showToast(data?.error || 'Unable to add to cart.');
                    }
                }).catch(() => showToast('Network error. Please try again.'))
                .finally(() => { this.innerHTML = originalText; this.disabled = false; });
            }
        });
    });

    function showToast(message) {
        let tc = document.querySelector('.toast-container');
        if (!tc) {
            tc = document.createElement('div');
            tc.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            tc.style.zIndex = '9999';
            document.body.appendChild(tc);
        }
        const id = 'toast-' + Date.now();
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `
            <div id="${id}" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"><i class="fas fa-check-circle me-2"></i>${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        const toastEl = wrapper.firstElementChild;
        tc.appendChild(toastEl);
        new bootstrap.Toast(toastEl, { delay: 3000 }).show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }

    // Newsletter
    document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input[type="email"]');
        const btn = this.querySelector('button');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        setTimeout(() => {
            showToast('Thank you for subscribing!');
            email.value = '';
            btn.innerHTML = orig;
            btn.disabled = false;
        }, 1000);
    });
</script>
