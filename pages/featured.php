<?php
// Featured Products Page
$products = get_featured_products(12); // Fetch top 12 featured items
?>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold">Featured Collections</h1>
        <p class="text-muted">Handpicked items just for you.</p>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info text-center">No featured products found at the moment. Check back later!</div>
    <?php
else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($products as $product): ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm product-card">
                        <div class="position-relative">
                            <a href="<?php echo $base; ?>/index.php?page=product&id=<?php echo $product['id']; ?>">
                                <?php
        $img = resolve_product_image($product);
        $img_src = $img ? (strpos($img, 'http') === 0 ? $img : $base . '/' . ltrim($img, './')) : $base . '/assets/images/placeholder.jpg';
?>
                                <img src="<?php echo htmlspecialchars($img_src); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="height: 250px; object-fit: contain;" loading="lazy">
                            </a>
                            <?php if ($product['display_price'] < $product['price']): ?>
                                <span class="position-absolute top-0 start-0 bg-danger text-white px-2 py-1 m-2 rounded small">SALE</span>
                            <?php
        endif; ?>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title text-truncate">
                                <a href="<?php echo $base; ?>/index.php?page=product&id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($product['categories'] ?? ''); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary">KES <?php echo number_format($product['display_price']); ?></span>
                                    <?php if ($product['display_price'] < $product['price']): ?>
                                        <small class="text-muted text-decoration-line-through ms-1"><?php echo number_format($product['price']); ?></small>
                                    <?php
        endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0">
                             <form method="post" action="<?php echo $base; ?>/index.php?page=featured" class="m-0 p-0 w-100">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" name="add_to_cart" class="btn btn-outline-primary w-100 m-0 rounded-pill">
                                    <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php
    endforeach; ?>
        </div>
    <?php
endif; ?>
</div>
