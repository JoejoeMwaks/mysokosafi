<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$category_slug = isset($_GET['category']) ? trim($_GET['category']) : null;
$search_q = isset($_GET['q']) ? trim($_GET['q']) : null;
$category = ($category_slug && db_has_connection()) ? get_category_by_slug($category_slug) : null;
$category_id = $category ? (int)$category['id'] : null;

// Fetch products, filtered by category when available; apply search when `q` is present
try {
    if (db_has_connection()) {
        if ($search_q !== null && $search_q !== '') {
            $items = get_products_search($search_q, $category_id);
        } else {
            $items = get_products(null, $category_id);
        }
    } else {
        $items = [];
    }
} catch (Exception $e) {
    error_log('Error loading products: ' . $e->getMessage());
    $items = [];
}
?>

<section class="container py-5">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php?page=home" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Products<?php echo $category ? ' · ' . htmlspecialchars($category['name']) : ''; ?><?php echo ($search_q) ? ' · Search: ' . htmlspecialchars($search_q) : ''; ?></li>
    </ol>
  </nav>

  <div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0">Products<?php if ($category) { echo ' — ' . htmlspecialchars($category['name']); } ?><?php if ($search_q) { echo ' — Search for "' . htmlspecialchars($search_q) . '"'; } ?></h1>
    <?php if ($category): ?>
      <span class="badge bg-secondary">Category: <?php echo htmlspecialchars($category['name']); ?></span>
    <?php endif; ?>
  </div>

  <div class="d-flex flex-wrap gap-2 mb-4">
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products">All</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=electronics">Electronics</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=fashion">Fashion</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=beauty">Beauty</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=home-living">Home & Living</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=accessories">Accessories</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=shoes">Shoes</a>
    <a class="btn btn-outline-secondary btn-sm" href="index.php?page=products&category=gaming">Gaming</a>
  </div>

  <?php if ($category_slug && !$category): ?>
    <div class="alert alert-info">No such category “<?php echo htmlspecialchars($category_slug); ?>”. Showing all products.</div>
  <?php endif; ?>

  <?php if (empty($items)): ?>
    <div class="alert alert-warning">No products found<?php echo $category ? ' in ' . htmlspecialchars($category['name']) : ''; ?>.</div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($items as $p): ?>
        <div class="col-6 col-md-4 col-lg-3">
          <div class="card h-100 shadow-sm">
            <?php $img = resolve_product_image($p); if (!empty($img)): ?>
              <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.style.display='none'">
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title mb-2">
                <a href="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($p['name']); ?></a>
              </h5>
              <?php if (!empty($p['categories'])): ?>
                <div class="small text-muted mb-2"><?php echo htmlspecialchars($p['categories']); ?></div>
              <?php endif; ?>
              <div class="mt-auto">
                <div class="fw-bold text-primary mb-2"><?php echo format_currency((float)($p['sale_price'] ?? $p['price'])); ?></div>
                <div class="d-flex flex-column gap-2 w-100">
                  <form method="post" action="index.php?page=products" class="m-0 p-0 w-100">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" name="add_to_cart" class="btn btn-primary w-100 m-0">Add to Cart</button>
                  </form>
                  <a href="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="btn btn-outline-primary w-100 m-0">View Details</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
