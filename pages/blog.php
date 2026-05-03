<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

// Pagination settings
$items_per_page = 9;
$current_page = max(1, isset($_GET['p']) ? (int)$_GET['p'] : 1);
$offset = ($current_page - 1) * $items_per_page;

$blogs = [];
$total_items = 0;

try {
    if (db_has_connection()) {
        $blogs = get_all_blogs($items_per_page, $offset);
        
        // Quick query to get total blog posts count
        global $pdo;
        $stmt = $pdo->query("SELECT COUNT(*) FROM blog_posts");
        $total_items = (int)$stmt->fetchColumn();
    }
} catch (Exception $e) {
    error_log('Error loading blogs: ' . $e->getMessage());
}

$total_pages = ceil($total_items / $items_per_page);
?>

<section class="container py-5">
  <div class="text-center mb-5">
    <h1 class="display-4 fw-bold text-dark">Our Blog</h1>
    <p class="lead text-muted">Latest news, guides, and updates from Sokosafi.</p>
  </div>

  <?php if (empty($blogs)): ?>
    <div class="text-center py-5 my-5">
        <i class="fa fa-newspaper fa-3x text-muted mb-3 opacity-50"></i>
        <h4 class="text-muted">No articles published yet.</h4>
        <p class="text-muted small">Check back soon for exciting updates!</p>
    </div>
  <?php else: ?>
    <div class="row g-4 mb-5">
      <?php foreach ($blogs as $b): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden blog-card" style="transition: transform 0.3s ease, box-shadow 0.3s ease;">
            <a href="index.php?page=blog_post&slug=<?php echo urlencode($b['slug']); ?>" class="d-block position-relative" style="height: 220px;">
                <?php if (!empty($b['image_url'])): ?>
                    <img src="<?php echo htmlspecialchars($b['image_url']); ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" class="w-100 h-100" style="object-fit: cover;">
                <?php else: ?>
                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted">
                        <i class="fa fa-image fa-3x opacity-25"></i>
                    </div>
                <?php endif; ?>
                <!-- Optional Video Icon Overlay -->
                <?php if (!empty($b['video_url'])): ?>
                    <div class="position-absolute top-50 start-50 translate-middle bg-dark bg-opacity-75 rounded-circle d-flex align-items-center justify-content-center text-white shadow" style="width: 50px; height: 50px;">
                        <i class="fa fa-play ms-1"></i>
                    </div>
                <?php endif; ?>
            </a>
            
            <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-primary fw-bold text-uppercase" style="letter-spacing: 1px;">
                        <?php echo date('M d, Y', strtotime($b['created_at'])); ?>
                    </span>
                </div>
                
                <h5 class="card-title mb-3">
                    <a href="index.php?page=blog_post&slug=<?php echo urlencode($b['slug']); ?>" class="text-dark text-decoration-none fw-bold" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo htmlspecialchars($b['title']); ?>
                    </a>
                </h5>
                
                <p class="card-text text-muted small mb-4" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                    <?php echo htmlspecialchars(strip_tags($b['content'])); ?>
                </p>
                
                <div class="mt-auto d-flex align-items-center justify-content-between border-top pt-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px; font-size: 14px;">
                            <?php 
                                $author_name = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? '')) ?: 'Admin';
                                echo strtoupper(substr($author_name, 0, 1));
                            ?>
                        </div>
                        <span class="small text-muted fw-semibold"><?php echo htmlspecialchars($author_name); ?></span>
                    </div>
                    <a href="index.php?page=blog_post&slug=<?php echo urlencode($b['slug']); ?>" class="text-primary text-decoration-none small fw-bold">Read More <i class="fa fa-arrow-right ms-1"></i></a>
                </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    
    <style>
        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
        }
    </style>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-5 border-top pt-4">
      <ul class="pagination justify-content-center pagination-lg">
        
        <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
          <a class="page-link shadow-none" href="<?php echo $current_page > 1 ? 'index.php?page=blog&p=' . ($current_page - 1) : '#'; ?>" tabindex="-1">Previous</a>
        </li>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
              <a class="page-link shadow-none" href="index.php?page=blog&p=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
          <a class="page-link shadow-none" href="<?php echo $current_page < $total_pages ? 'index.php?page=blog&p=' . ($current_page + 1) : '#'; ?>">Next</a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>

  <?php endif; ?>
</section>
