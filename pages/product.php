<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$product = ($id && db_has_connection()) ? get_product_by_id($id) : null;
$cart_error = $cart_error ?? null; // Provided by index.php when POST fails

// Get related products for recommendation
$related_products = ($id && db_has_connection()) ? get_related_products($id, 4) : [];
?>

<section class="container py-5">
    <!-- Elegant Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb elegant-breadcrumb">
            <li class="breadcrumb-item"><a href="index.php?page=home" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="index.php?page=products" class="text-decoration-none">Products</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo $product ? htmlspecialchars($product['name']) : 'Product Details'; ?></li>
        </ol>
    </nav>

    <?php if ($product): ?>
        <div class="row g-5">
            <!-- Product Images -->
            <div class="col-lg-6">
                <div class="product-gallery">
                    <div class="main-image mb-4">
                        <?php $img = resolve_product_image($product); if (!empty($img)): ?>
                            <img src="<?php echo htmlspecialchars($img); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 class="img-fluid rounded-3 w-100 product-image"
                                 onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                 alt="Product" 
                                 class="img-fluid rounded-3 w-100 product-image">
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="col-lg-6">
                <div class="product-details">
                    <?php if (!empty($product['categories'])): $pc = explode(',', $product['categories']); ?>
                        <span class="badge elegant-badge mb-3"><?php echo htmlspecialchars(trim($pc[0])); ?></span>
                    <?php endif; ?>

                    <!-- Product Title -->
                    <h1 class="h2 fw-bold text-dark mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <!-- Rating -->
                    <div class="rating mb-3">
                        <div class="stars">
                            <?php for ($i = 0; $i < 5; $i++) { echo '<i class="far fa-star text-warning"></i>'; } ?>
                        </div>
                        <span class="text-muted ms-2">0 reviews</span>
                    </div>

                    <!-- Price -->
                    <div class="price-section mb-4">
                        <?php if (isset($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                            <div class="d-flex align-items-center gap-3">
                                <span class="h2 fw-bold text-primary"><?php echo format_currency((float)$product['sale_price']); ?></span>
                                <span class="h5 text-muted text-decoration-line-through"><?php echo format_currency((float)$product['price']); ?></span>
                                <span class="badge" style="background-color: var(--accent);">Save <?php echo number_format((($product['price'] - $product['sale_price']) / $product['price']) * 100, 0); ?>%</span>
                            </div>
                        <?php else: ?>
                            <span class="h2 fw-bold text-primary"><?php echo format_currency((float)$product['price']); ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <div class="description mb-4">
                        <p class="text-muted lead"><?php echo htmlspecialchars($product['description'] ?? 'High-quality product designed for exceptional performance and durability.'); ?></p>
                    </div>

                    <!-- Features -->
                    <div class="features mb-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="d-flex align-items-center text-muted">
                    <i class="fas fa-truck text-primary me-2"></i>
                    <small>Delivery Available</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fas fa-undo text-primary me-2"></i>
                                    <small>30-Day Returns</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fas fa-shield-alt text-primary me-2"></i>
                                    <small>2-Year Warranty</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center text-muted">
                                    <i class="fas fa-headset text-primary me-2"></i>
                                    <small>24/7 Support</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add to Cart Form -->
                    <div class="add-to-cart-section">
                        <?php if ($cart_error): ?>
                            <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($cart_error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" action="index.php?page=product&id=<?php echo (int)$id; ?>" class="row g-3 align-items-end">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <input type="hidden" name="product_id" value="<?php echo (int)$id; ?>" />
                            
                            <div class="col-auto">
                                <label for="quantity" class="form-label fw-semibold">Quantity</label>
                                <div class="quantity-selector">
                                    <button type="button" class="quantity-btn minus" onclick="adjustQuantity(-1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <input type="number" 
                                           id="quantity"
                                           name="quantity" 
                                           min="1" 
                                           value="1" 
                                           class="quantity-input">
                                    <button type="button" class="quantity-btn plus" onclick="adjustQuantity(1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col">
                                <button class="btn btn-primary btn-lg w-100 py-3 fw-semibold" type="submit" name="add_to_cart" value="1">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Add to Cart
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Additional Info -->
                    <div class="additional-info mt-4 pt-4 border-top">
                        <div class="row g-4">
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="fas fa-truck text-primary mb-2 fs-4"></i>
                <div class="small text-muted">Delivery fee calculated by distance</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <i class="fas fa-lock text-primary mb-2 fs-4"></i>
                                    <div class="small text-muted">Secure checkout guaranteed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details Tabs -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <ul class="nav nav-tabs nav-underline mb-4" id="productTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                                    <i class="fas fa-info-circle me-2"></i>Product Details
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">
                                    <i class="fas fa-list-alt me-2"></i>Specifications
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                                    <i class="fas fa-star me-2"></i>Customer Reviews
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="productTabsContent">
                            <div class="tab-pane fade show active" id="details" role="tabpanel">
                                <p class="text-muted"><?php echo htmlspecialchars($product['description'] ?? 'This premium product combines exceptional quality with innovative design. Crafted with attention to detail and built to last, it offers outstanding performance and reliability.'); ?></p>
                                <p class="text-muted mb-0">Experience the perfect blend of style and functionality with this carefully designed product.</p>
                            </div>
                            
                            <div class="tab-pane fade" id="specs" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span class="text-muted">Material</span>
                                            <span class="fw-semibold">Premium Quality</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span class="text-muted">Dimensions</span>
                                            <span class="fw-semibold">Standard Size</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span class="text-muted">Weight</span>
                                            <span class="fw-semibold">Lightweight</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between border-bottom py-2">
                                            <span class="text-muted">Warranty</span>
                                            <span class="fw-semibold">2 Years</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="tab-pane fade" id="reviews" role="tabpanel">
                                <div class="text-center py-4">
                                    <i class="fas fa-comments text-muted fs-1 mb-3"></i>
                                    <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                                    <button class="btn btn-outline-primary">Write a Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="h4 fw-bold mb-4">You Might Also Like</h3>
                <div class="row g-4">
                    <?php foreach ($related_products as $related): ?>
                    <div class="col-md-6 col-lg-3">
                        <div class="card h-100 product-card">
                            <?php $rimg = resolve_product_image($related); ?>
                            <img src="<?php echo !empty($rimg) ? htmlspecialchars($rimg) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-semibold"><?php echo htmlspecialchars($related['name']); ?></h5>
                                <p class="card-text text-primary fw-bold mb-auto"><?php echo format_currency((float)($related['sale_price'] ?? $related['price'])); ?></p>
                                <a href="index.php?page=product&id=<?php echo (int)$related['id']; ?>" class="btn btn-outline-primary mt-2">View Details</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- Product Not Found State -->
        <div class="text-center py-5">
            <div class="mb-4">
                <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
            </div>
            <h2 class="h3 fw-bold text-dark mb-3">
                <?php echo $id ? 'Product Not Found' : 'Browse Our Products'; ?>
            </h2>
            <p class="text-muted mb-4">
                <?php echo $id ? 'The product you\'re looking for doesn\'t exist or is no longer available.' : 'Select a product from our home page to view details.'; ?>
            </p>
            <a href="index.php?page=home" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>
                Continue Shopping
            </a>
        </div>
    <?php endif; ?>
</section>

<style>
:root {
    --light: #f8fafc;
    --dark: #1e293b;
    --text: #334155;
    --border: #e2e8f0;
    --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

body {
    background-color: #ffffff;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

/* Elegant Breadcrumb */
.elegant-breadcrumb .breadcrumb-item a {
    color: var(--primary);
    font-weight: 500;
}

.elegant-breadcrumb .breadcrumb-item.active {
    color: var(--text);
}

/* Product Image */
.product-image {
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: transform 0.3s ease;
}

.product-image:hover {
    transform: scale(1.02);
}

/* Elegant Badge */
.elegant-badge {
    background: var(--primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    font-size: 0.875rem;
}

/* Quantity Selector */
.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    width: fit-content;
}

.quantity-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--light);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quantity-btn:hover {
    background: var(--primary);
    color: white;
}

.quantity-input {
    width: 60px;
    border: none;
    text-align: center;
    font-weight: 500;
    background: white;
}

.quantity-input:focus {
    outline: none;
    background: var(--light);
}

/* Buttons */
.btn-primary {
    background-color: var(--primary);
    border-color: var(--primary);
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: var(--primary-light);
    border-color: var(--primary-light);
}

.btn-outline-primary {
    border-color: var(--primary);
    color: var(--primary);
    border-radius: 8px;
    font-weight: 500;
}

.btn-outline-primary:hover {
    background-color: var(--primary);
    border-color: var(--primary);
}

/* Product Cards */
.product-card {
    border: 1px solid var(--border);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

/* Tabs */
.nav-underline .nav-link {
    color: var(--text);
    border: none;
    padding: 1rem 1.5rem;
    font-weight: 500;
}

.nav-underline .nav-link.active {
    color: var(--primary);
    border-bottom: 2px solid var(--primary);
    background: none;
}

/* Alert */
.alert-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
    border-radius: 8px;
}

/* Border */
.border-top {
    border-color: var(--border) !important;
}

/* Stars */
.stars {
    display: inline-flex;
    gap: 2px;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
}
</style>

<script>
function adjustQuantity(change) {
    const input = document.getElementById('quantity');
    let currentValue = parseInt(input.value) || 1;
    let newValue = currentValue + change;
    
    if (newValue < 1) newValue = 1;
    if (newValue > 99) newValue = 99;
    
    input.value = newValue;
}

function validateQuantity(input) {
    let value = parseInt(input.value);
    
    if (isNaN(value) || value < 1) {
        input.value = 1;
    } else if (value > 99) {
        input.value = 99;
    }
}

// Initialize Bootstrap tabs if available
if (typeof bootstrap !== 'undefined') {
    const triggerTabList = [].slice.call(document.querySelectorAll('#productTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
}
</script>
