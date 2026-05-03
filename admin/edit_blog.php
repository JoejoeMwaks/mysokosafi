<?php
require_once __DIR__ . '/../includes/admin_guard.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$message = null;
$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($blog_id <= 0) {
    die('Invalid blog ID.');
}

$blog = get_blog_by_id($blog_id);
if (!$blog) {
    die('Blog post not found.');
}

// Fetch products for dropdown
$products = [];
if (db_has_connection()) {
    try {
        $stmt = $pdo->query("SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Security check failed. Please refresh the page.');
    }

    if (db_has_connection()) {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $video_url = trim($_POST['video_url'] ?? '');
        $linked_product_id = !empty($_POST['linked_product_id']) ? (int)$_POST['linked_product_id'] : null;
        
        if ($title === '' || $content === '') {
            $message = 'Error: Title and Content are required.';
        } else {
            // Validate or auto-generate slug
            if ($slug === '') {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }
            
            // Ensure unique slug (ignoring current blog's slug)
            $original_slug = $slug;
            $counter = 1;
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM blog_posts WHERE slug = ? AND id != ?");
            while (true) {
                $stmtCheck->execute([$slug, $blog_id]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                } else {
                    break;
                }
            }

            $image_url = $blog['image_url']; // keep existing by default
            $uploaded_video_url = $blog['uploaded_video_url']; // keep existing by default
            $cloudinary_failed = false;

            // Handle Image Upload if new image provided
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                require_once __DIR__ . '/../config/cloudinary.php';
                $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
                
                $tmpPath = $_FILES['image']['tmp_name'];
                $fileName = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES['image']['name']);
                $uploadDir = __DIR__ . '/../uploads/blogs/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $localPath = $uploadDir . $fileName;
                $dbLocalPath = 'uploads/blogs/' . $fileName;
                
                $moved = @move_uploaded_file($tmpPath, $localPath);
                $uploadSource = $moved ? $localPath : $tmpPath;
                
                try {
                    $response = $uploadApi->upload($uploadSource, [
                        'folder' => 'sokosafi/blogs',
                        'transformation' => [
                            'quality' => 'auto',
                            'fetch_format' => 'auto',
                            'width' => 1200,
                            'crop' => 'limit'
                        ]
                    ]);
                    $image_url = $response['secure_url'];
                } catch (\Exception $e) {
                    error_log("Cloudinary upload failed: " . $e->getMessage());
                    $cloudinary_failed = true;
                    if ($moved) {
                        $image_url = $dbLocalPath;
                    }
                }
            }

            // Handle Video Upload if new video provided
            if (isset($_FILES['uploaded_video']) && $_FILES['uploaded_video']['error'] === UPLOAD_ERR_OK) {
                require_once __DIR__ . '/../config/cloudinary.php';
                $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
                
                $tmpPath = $_FILES['uploaded_video']['tmp_name'];
                $fileName = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES['uploaded_video']['name']);
                $uploadDir = __DIR__ . '/../uploads/videos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $localPath = $uploadDir . $fileName;
                $dbLocalPath = 'uploads/videos/' . $fileName;
                
                $moved = @move_uploaded_file($tmpPath, $localPath);
                $uploadSource = $moved ? $localPath : $tmpPath;
                
                try {
                    $response = $uploadApi->upload($uploadSource, [
                        'folder' => 'sokosafi/videos',
                        'resource_type' => 'video'
                    ]);
                    $uploaded_video_url = $response['secure_url'];
                } catch (\Exception $e) {
                    error_log("Cloudinary video upload failed: " . $e->getMessage());
                    $cloudinary_failed = true;
                    if ($moved) {
                        $uploaded_video_url = $dbLocalPath;
                    }
                }
            }

            try {
                if (update_blog($blog_id, $title, $slug, $content, $image_url, $video_url, $uploaded_video_url, $linked_product_id)) {
                    $message = 'Blog post updated successfully.';
                    if ($cloudinary_failed) {
                        $message .= ' Cloudinary upload failed, but media saved locally.';
                    }
                    // Refresh blog data
                    $blog = get_blog_by_id($blog_id);
                } else {
                    $message = 'Error: Failed to update blog post.';
                }
            } catch (Throwable $e) {
                $message = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<!-- Include Quill Stylesheet -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<section class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Edit Blog Post</h2>
        <a href="manage_blogs.php" class="btn btn-outline-secondary">Back to Manage Blogs</a>
    </div>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" class="d-grid gap-3" style="max-width: 800px;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div>
            <label class="form-label">Blog Title</label>
            <input type="text" name="title" class="form-control" required value="<?php echo htmlspecialchars($_POST['title'] ?? $blog['title']); ?>">
        </div>

        <div>
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control" required value="<?php echo htmlspecialchars($_POST['slug'] ?? $blog['slug']); ?>">
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Featured Image (Optional)</label>
                <?php if (!empty($blog['image_url'])): ?>
                    <div class="mb-2">
                        <img src="<?php echo htmlspecialchars($blog['image_url']); ?>" alt="Current Image" style="max-height: 150px; border-radius: 8px;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" class="form-control" accept="image/jpeg, image/png">
                <small class="text-muted">Leave empty to keep current.</small>
            </div>
            <div class="col-md-6">
                <label class="form-label">Link a Product (Optional)</label>
                <select name="linked_product_id" class="form-select">
                    <option value="">-- None --</option>
                    <?php 
                    $selected_product_id = $_POST['linked_product_id'] ?? $blog['linked_product_id'];
                    foreach ($products as $p): ?>
                        <option value="<?php echo (int)$p['id']; ?>" <?php echo ($selected_product_id == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Select a product to feature.</small>
            </div>
        </div>

        <div class="row border rounded p-3 bg-light m-0">
            <h6 class="mb-3">Video Integration (Optional)</h6>
            <div class="col-md-6">
                <label class="form-label">YouTube/Vimeo Embed URL</label>
                <input type="url" name="video_url" class="form-control" placeholder="https://www.youtube.com/watch?v=..." value="<?php echo htmlspecialchars($_POST['video_url'] ?? $blog['video_url']); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">OR Upload Video File</label>
                <?php if (!empty($blog['uploaded_video_url'])): ?>
                    <div class="mb-2">
                        <a href="<?php echo htmlspecialchars($blog['uploaded_video_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fa fa-play"></i> View Current Video</a>
                    </div>
                <?php endif; ?>
                <input type="file" name="uploaded_video" class="form-control" accept="video/mp4, video/webm">
                <small class="text-muted">Leave empty to keep current.</small>
            </div>
        </div>
        
        <div>
            <label class="form-label">Content</label>
            <!-- Hidden textarea for form submission -->
            <textarea name="content" id="content-textarea" style="display:none;"><?php echo htmlspecialchars($_POST['content'] ?? $blog['content']); ?></textarea>
            <!-- Editor container -->
            <div id="editor-container" style="height: 400px; background: #fff; color: #000;">
                <?php echo $_POST['content'] ?? $blog['content']; ?>
            </div>
        </div>
        
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg fw-bold" id="submit-btn">Update Blog Post</button>
        </div>
    </form>
</section>

<!-- Include Quill Library -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Write your blog post here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    // Populate the hidden textarea before form submission
    document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('content-textarea').value = quill.root.innerHTML;
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
