<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    $_SESSION['flash'] = 'Blog post not found.';
    header('Location: index.php?page=blog');
    exit;
}

$blog = get_blog_by_slug($slug);

if (!$blog) {
    $_SESSION['flash'] = 'Blog post not found.';
    header('Location: index.php?page=blog');
    exit;
}

// Simple YouTube embed helper
function get_embed_url($url) {
    if (strpos($url, 'youtube.com/watch') !== false) {
        parse_str(parse_url($url, PHP_URL_QUERY), $vars);
        if (isset($vars['v'])) {
            return 'https://www.youtube.com/embed/' . $vars['v'];
        }
    } elseif (strpos($url, 'youtu.be/') !== false) {
        $path = parse_url($url, PHP_URL_PATH);
        return 'https://www.youtube.com/embed' . $path;
    } elseif (strpos($url, 'vimeo.com/') !== false) {
        $path = parse_url($url, PHP_URL_PATH);
        return 'https://player.vimeo.com/video' . $path;
    }
    return false;
}
?>

<div class="blog-hero position-relative bg-dark text-white" style="min-height: 400px; display: flex; align-items: flex-end;">
    <?php if (!empty($blog['image_url'])): ?>
        <img src="<?php echo htmlspecialchars($blog['image_url']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" class="w-100 h-100 position-absolute top-0 start-0" style="object-fit: cover; opacity: 0.6;">
    <?php endif; ?>
    
    <div class="container position-relative z-index-1 pb-5 pt-5">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=home" class="text-white text-decoration-none opacity-75">Home</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=blog" class="text-white text-decoration-none opacity-75">Blog</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($blog['title']); ?></li>
            </ol>
        </nav>
        
        <h1 class="display-4 fw-bold mb-3 text-shadow"><?php echo htmlspecialchars($blog['title']); ?></h1>
        
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow" style="width: 48px; height: 48px; font-size: 20px;">
                <?php 
                    $author_name = trim(($blog['first_name'] ?? '') . ' ' . ($blog['last_name'] ?? '')) ?: 'Admin';
                    echo strtoupper(substr($author_name, 0, 1));
                ?>
            </div>
            <div>
                <div class="fw-semibold text-white"><?php echo htmlspecialchars($author_name); ?></div>
                <div class="small text-white opacity-75"><?php echo date('F j, Y', strtotime($blog['created_at'])); ?></div>
            </div>
        </div>
    </div>
</div>

<section class="container py-5 my-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php 
            $embed = !empty($blog['video_url']) ? get_embed_url($blog['video_url']) : false;
            if (!empty($blog['uploaded_video_url'])): 
            ?>
                <div class="ratio ratio-16x9 mb-5 rounded shadow overflow-hidden bg-dark">
                    <video controls controlsList="nodownload">
                        <source src="<?php echo htmlspecialchars($blog['uploaded_video_url']); ?>" type="video/mp4">
                        Your browser does not support HTML video.
                    </video>
                </div>
            <?php elseif ($embed): ?>
                <div class="ratio ratio-16x9 mb-5 rounded shadow overflow-hidden">
                    <iframe src="<?php echo htmlspecialchars($embed); ?>" title="Video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
            <?php elseif (!empty($blog['video_url'])): ?>
                <div class="alert alert-info mb-5">
                    <i class="fa fa-video me-2"></i> Watch the related video here: <a href="<?php echo htmlspecialchars($blog['video_url']); ?>" target="_blank" class="alert-link"><?php echo htmlspecialchars($blog['video_url']); ?></a>
                </div>
            <?php endif; ?>

            <div class="blog-content fs-5" style="line-height: 1.8;">
                <!-- Quill generates clean HTML, so we can output it directly -->
                <?php echo $blog['content']; ?>
            </div>
            
            <?php if (!empty($blog['linked_product_id'])): ?>
                <div class="card mt-5 mb-4 border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef);">
                    <div class="row g-0">
                        <div class="col-md-4 d-flex align-items-center justify-content-center bg-white p-3">
                            <?php if (!empty($blog['product_image'])): ?>
                                <img src="<?php echo htmlspecialchars($blog['product_image']); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($blog['product_name']); ?>" style="max-height: 200px; object-fit: contain;">
                            <?php else: ?>
                                <div class="text-muted"><i class="fa fa-box fa-3x opacity-25"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <div class="card-body p-4 d-flex flex-column h-100">
                                <span class="badge bg-primary mb-2 align-self-start">Featured Product</span>
                                <h5 class="card-title fw-bold fs-4"><?php echo htmlspecialchars($blog['product_name']); ?></h5>
                                <div class="mt-auto d-flex align-items-center justify-content-between pt-3">
                                    <div>
                                        <?php if (!empty($blog['product_sale_price'])): ?>
                                            <span class="fs-4 fw-bold text-dark"><?php echo format_currency((float)$blog['product_sale_price']); ?></span>
                                            <span class="text-muted text-decoration-line-through small ms-2"><?php echo format_currency((float)$blog['product_price']); ?></span>
                                        <?php else: ?>
                                            <span class="fs-4 fw-bold text-dark"><?php echo format_currency((float)$blog['product_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?page=product&id=<?php echo (int)$blog['linked_product_id']; ?>" class="btn btn-dark rounded-pill px-4">View Product</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <hr class="my-5">
            
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php?page=blog" class="btn btn-outline-secondary rounded-pill px-4"><i class="fa fa-arrow-left me-2"></i> Back to Blog</a>
                
                <div class="d-flex gap-2">
                    <span class="text-muted d-flex align-items-center me-2">Share:</span>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($blog['title']); ?>" target="_blank" class="btn btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://wa.me/?text=<?php echo urlencode($blog['title'] . ' ' . 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" target="_blank" class="btn btn-outline-dark rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
        </div>
    </div>
</section>

<style>
    .text-shadow {
        text-shadow: 0 2px 4px rgba(0,0,0,0.5);
    }
    .blog-content img {
        max-width: 100%;
        height: auto;
        border-radius: 8px;
        margin: 1.5rem 0;
    }
    .blog-content h2, .blog-content h3 {
        margin-top: 2rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }
    .blog-content p {
        margin-bottom: 1.5rem;
    }
    .blog-content blockquote {
        border-left: 4px solid #0d6efd;
        padding-left: 1rem;
        color: #6c757d;
        font-style: italic;
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0 8px 8px 0;
    }
</style>
