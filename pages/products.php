<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

// Pagination settings
$items_per_page = 12;
$current_page = max(1, isset($_GET['p']) ? (int)$_GET['p'] : 1);
$offset = ($current_page - 1) * $items_per_page;

// Collect filter parameters from GET request
$params = [];

// Search Query
$search_q = isset($_GET['q']) ? trim($_GET['q']) : null;
if (!empty($search_q)) {
    $params['q'] = $search_q;
}

// Categories
$selected_categories = [];
// Handle single category from old links or multiple categories from new checkboxes
$cat_input = isset($_GET['category']) ? $_GET['category'] : [];
if (!is_array($cat_input) && !empty($cat_input)) {
    // If it's a string (e.g., from homepage link ?category=electronics)
    $cat = get_category_by_slug($cat_input);
    if ($cat) {
        $selected_categories[] = $cat['id'];
    }
} elseif (is_array($cat_input)) {
    // Array of IDs from filter checkboxes
    $selected_categories = array_map('intval', $cat_input);
}
if (!empty($selected_categories)) {
    $params['categories'] = $selected_categories;
}

// Price Range
$min_price = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
if ($min_price !== null) $params['min_price'] = $min_price;
if ($max_price !== null) $params['max_price'] = $max_price;

// Sorting
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$params['sort'] = $sort;

// Fetch filtered products and total count
try {
    if (db_has_connection()) {
        $items = get_products_filtered($params, $items_per_page, $offset);
        $total_items = get_products_filtered_count($params);
    } else {
        $items = [];
        $total_items = 0;
    }
} catch (Exception $e) {
    error_log('Error loading filtered products: ' . $e->getMessage());
    $items = [];
    $total_items = 0;
}

$total_pages = ceil($total_items / $items_per_page);
$all_categories = get_categories(null); // Fetch all parent categories for filter sidebar

// Helper to build URL with current query parameters but modified values
function build_filter_url($updates) {
    $query = $_GET;
    foreach ($updates as $key => $value) {
        if ($value === null) {
            unset($query[$key]);
        } else {
            $query[$key] = $value;
        }
    }
    return 'index.php?' . http_build_query($query);
}
?>

<section class="container py-5">
  <nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="index.php?page=home" class="text-decoration-none">Home</a></li>
      <li class="breadcrumb-item active" aria-current="page">Shop</li>
    </ol>
  </nav>

  <div class="row">
    <!-- FILTER SIDEBAR -->
    <aside class="col-lg-3 mb-5 mb-lg-0">
      <div class="filter-sidebar sticky-top" style="top: 85px; z-index: 10;">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h4 class="h5 fw-bold mb-0"><i class="fa fa-filter me-2 text-primary"></i>Filters</h4>
          <a href="index.php?page=products" class="btn btn-sm btn-outline-secondary rounded-pill px-3" style="font-size: 0.75rem;">Clear All</a>
        </div>
        
        <form method="get" action="index.php" id="filterForm" class="card shadow-sm border-0 bg-white">
          <input type="hidden" name="page" value="products">
          <div class="card-body p-4">
              
            <!-- Sorting -->
            <div class="mb-4 pb-4 border-bottom">
              <h6 class="fw-bold mb-3">Sort By</h6>
              <select name="sort" class="form-select form-select-sm shadow-none" onchange="this.form.submit()">
                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest Arrivals</option>
                <option value="price_low_high" <?php echo $sort === 'price_low_high' ? 'selected' : ''; ?>>Price: Low to High</option>
                <option value="price_high_low" <?php echo $sort === 'price_high_low' ? 'selected' : ''; ?>>Price: High to Low</option>
                <option value="name_a_z" <?php echo $sort === 'name_a_z' ? 'selected' : ''; ?>>Name: A to Z</option>
                <option value="name_z_a" <?php echo $sort === 'name_z_a' ? 'selected' : ''; ?>>Name: Z to A</option>
              </select>
            </div>

            <!-- Search -->
            <div class="mb-4 pb-4 border-bottom">
              <h6 class="fw-bold mb-3">Search</h6>
              <div class="input-group input-group-sm">
                <input type="text" name="q" class="form-control shadow-none" placeholder="Keywords..." value="<?php echo htmlspecialchars($search_q ?? ''); ?>">
                <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
              </div>
            </div>

            <!-- Categories -->
            <div class="mb-4 pb-4 border-bottom">
              <h6 class="fw-bold mb-3">Categories</h6>
              <div class="d-flex flex-column gap-2" style="max-height: 250px; overflow-y: auto;">
                <?php foreach ($all_categories as $cat): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="category[]" value="<?php echo $cat['id']; ?>" id="cat_<?php echo $cat['id']; ?>" <?php echo in_array($cat['id'], $selected_categories) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    <label class="form-check-label text-muted" for="cat_<?php echo $cat['id']; ?>">
                      <?php echo htmlspecialchars($cat['name']); ?>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Price Range -->
            <div class="mb-4">
              <h6 class="fw-bold mb-3">Price Range</h6>
              <div class="row g-2 align-items-center mb-3">
                <div class="col-5">
                  <input type="number" name="min_price" class="form-control form-control-sm text-center shadow-none" placeholder="Min" min="0" value="<?php echo $min_price !== null ? $min_price : ''; ?>">
                </div>
                <div class="col-2 text-center text-muted">-</div>
                <div class="col-5">
                  <input type="number" name="max_price" class="form-control form-control-sm text-center shadow-none" placeholder="Max" min="0" value="<?php echo $max_price !== null ? $max_price : ''; ?>">
                </div>
              </div>
              <button type="submit" class="btn btn-primary w-100 btn-sm text-uppercase fw-bold letter-spacing-1">Apply Price filter</button>
            </div>

          </div>
        </form>
      </div>
    </aside>

    <!-- PRODUCT GRID -->
    <div class="col-lg-9">
      
      <!-- Top Bar -->
      <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 pb-3 border-bottom">
        <h1 class="h3 mb-0 fw-bold">Shop Collection</h1>
        <div class="text-muted small">
          Showing <?php echo $total_items > 0 ? $offset + 1 : 0; ?>-<?php echo min($offset + $items_per_page, $total_items); ?> of <?php echo $total_items; ?> results
        </div>
      </div>

      <!-- Active Filters Display -->
      <?php if (!empty($params)): ?>
      <div class="d-flex flex-wrap gap-2 mb-4">
        <?php if (!empty($search_q)): ?>
            <span class="badge bg-light text-dark border px-3 py-2 fw-normal d-flex align-items-center gap-2">
                "<?php echo htmlspecialchars($search_q); ?>"
                <a href="<?php echo build_filter_url(['q' => null, 'p' => 1]); ?>" class="text-dark"><i class="fa fa-times"></i></a>
            </span>
        <?php endif; ?>
        
        <?php if ($min_price !== null || $max_price !== null): ?>
            <span class="badge bg-light text-dark border px-3 py-2 fw-normal d-flex align-items-center gap-2">
                Price: <?php echo $min_price !== null ? 'KES ' . $min_price : '0'; ?> - <?php echo $max_price !== null ? 'KES ' . $max_price : 'Max'; ?>
                <a href="<?php echo build_filter_url(['min_price' => null, 'max_price' => null, 'p' => 1]); ?>" class="text-dark"><i class="fa fa-times"></i></a>
            </span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Grid -->
      <?php if (empty($items)): ?>
        <div class="text-center py-5 my-5">
            <i class="fa fa-box-open fa-3x text-muted mb-3 opacity-50"></i>
            <h4 class="text-muted">No products found</h4>
            <p class="text-muted small">Try adjusting your filters or clearing your search.</p>
            <a href="index.php?page=products" class="btn btn-primary mt-3 px-4 rounded-pill">Clear All Filters</a>
        </div>
      <?php else: ?>
        <div class="row g-4 mb-5">
          <?php foreach ($items as $p): ?>
            <div class="col-6 col-md-4">
              <div class="card product-card shadow-sm h-100 border-0 rounded-3">
                <?php if ($p['sale_price']): ?>
                    <div class="discount-badge rounded-end shadow-sm">SALE</div>
                <?php endif; ?>
                
                <a href="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="d-block overflow-hidden p-3 bg-white" style="border-radius: 12px 12px 0 0;">
                    <?php $img = resolve_product_image($p); if (!empty($img)): ?>
                      <img src="<?php echo htmlspecialchars($img); ?>" class="card-img-top product-image" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.style.display='none'">
                    <?php else: ?>
                      <div class="card-img-top product-image bg-light d-flex align-items-center justify-content-center text-muted">No Image</div>
                    <?php endif; ?>
                </a>

                <div class="card-body d-flex flex-column p-4 border-top">
                  <?php if (!empty($p['categories'])): ?>
                    <div class="small text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.7rem; letter-spacing: 1px;"><?php echo htmlspecialchars($p['categories']); ?></div>
                  <?php endif; ?>
                  
                  <h5 class="card-title mb-3 fs-6">
                    <a href="index.php?page=product&id=<?php echo (int)$p['id']; ?>" class="text-decoration-none text-dark fw-bold"><?php echo htmlspecialchars($p['name']); ?></a>
                  </h5>
                  
                  <div class="mt-auto d-flex align-items-end justify-content-between">
                    <div>
                        <div class="price-tag fs-5 mb-0"><?php echo format_currency((float)($p['sale_price'] ?? $p['price'])); ?></div>
                        <?php if ($p['sale_price']): ?>
                            <div class="sale-price small text-muted text-decoration-line-through"><?php echo format_currency((float)$p['price']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="post" action="index.php?page=products" class="m-0 p-0">
                      <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                      <input type="hidden" name="product_id" value="<?php echo (int)$p['id']; ?>">
                      <input type="hidden" name="quantity" value="1">
                      <button type="submit" name="add_to_cart" class="btn btn-primary rounded-circle shadow-sm" style="width: 40px; height: 40px; padding: 0;" title="Add to Cart">
                          <i class="fa fa-cart-plus"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-5 border-top pt-4">
          <ul class="pagination justify-content-center pagination-lg">
            
            <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
              <a class="page-link shadow-none" href="<?php echo $current_page > 1 ? build_filter_url(['p' => $current_page - 1]) : '#'; ?>" tabindex="-1">Previous</a>
            </li>
            
            <?php 
                // Display up to 5 page links around the current page
                $start_page = max(1, $current_page - 2);
                $end_page = min($total_pages, $current_page + 2);
                
                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link shadow-none" href="' . build_filter_url(['p' => 1]) . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link border-0">...</span></li>';
                    }
                }
                
                for ($i = $start_page; $i <= $end_page; $i++): 
            ?>
                <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                  <a class="page-link shadow-none" href="<?php echo build_filter_url(['p' => $i]); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            
            <?php
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link border-0">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link shadow-none" href="' . build_filter_url(['p' => $total_pages]) . '">' . $total_pages . '</a></li>';
                }
            ?>

            <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
              <a class="page-link shadow-none" href="<?php echo $current_page < $total_pages ? build_filter_url(['p' => $current_page + 1]) : '#'; ?>">Next</a>
            </li>
          </ul>
        </nav>
        <?php endif; ?>

      <?php endif; ?>
    </div>
  </div>
</section>
