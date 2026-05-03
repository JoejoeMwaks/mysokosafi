<?php
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

// Handle blog deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Security check failed. Please refresh the page.');
    }
    
    $blog_id = isset($_POST['blog_id']) ? (int)$_POST['blog_id'] : 0;
    if ($blog_id > 0) {
        if (delete_blog($blog_id)) {
            $_SESSION['flash'] = 'Blog post deleted successfully.';
        } else {
            $_SESSION['flash'] = 'Failed to delete blog post.';
        }
    }
    header('Location: manage_blogs.php');
    exit;
}

$blogs = get_all_blogs();

include __DIR__ . '/../includes/header.php';
?>

<section class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Blogs</h2>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary me-2">Back to Dashboard</a>
            <a href="add_blog.php" class="btn btn-primary">Add New Blog Post</a>
        </div>
    </div>
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="alert alert-info">
            <?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Date Published</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($blogs)): ?>
                    <tr>
                        <td colspan="6" class="text-center">No blog posts found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($blogs as $b): ?>
                        <tr>
                            <td><?php echo (int)$b['id']; ?></td>
                            <td>
                                <?php if (!empty($b['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($b['image_url']); ?>" alt="Blog Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($b['title']); ?></td>
                            <td>
                                <?php 
                                    $author_name = trim(($b['first_name'] ?? '') . ' ' . ($b['last_name'] ?? ''));
                                    echo htmlspecialchars($author_name ?: 'Admin'); 
                                ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($b['created_at'])); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="edit_blog.php?id=<?php echo (int)$b['id']; ?>" class="btn btn-sm btn-outline-light">Edit</a>
                                    
                                    <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="blog_id" value="<?php echo (int)$b['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
