<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php require_once __DIR__ . '/../includes/db_functions.php'; ?>
<?php
$message = null;

// Load categories
if (db_has_connection()) {
    ensure_core_categories_seeded();
}
$allCategories = db_has_connection() ? get_categories(null) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Security check failed. Please refresh the page.');
    }

    if (db_has_connection()) {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $sale_price = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
        $stock = (int)($_POST['stock'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        
        if ($name === '') {
            $message = 'Error: Product name is required.';
        } else {
            // Auto-generate slug if empty
            if ($slug === '') {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            
            // Ensure unique slug
            $original_slug = $slug;
            $counter = 1;
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
            while (true) {
                $stmtCheck->execute([$slug]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                } else {
                    break;
                }
            }
            
            try {
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, sale_price, stock, created_at, is_active) VALUES (:name, :slug, :description, :price, :sale_price, :stock, NOW(), 1)");
                $stmt->execute([
                    ':name' => $name,
                    ':slug' => $slug,
                    ':description' => $description,
                    ':price' => $price,
                    ':sale_price' => $sale_price,
                    ':stock' => $stock
                ]);
                $product_id = $pdo->lastInsertId();
                
                // Handle Categories
                $catIds = isset($_POST['category_ids']) ? (array)$_POST['category_ids'] : [];
                set_product_categories($product_id, $catIds);

                // Handle Image Uploads directly to Cloudinary
                if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                    require_once __DIR__ . '/../config/cloudinary.php';
                    $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
                    
                    $fileCount = count($_FILES['images']['name']);
                    $limit = min(10, $fileCount);
                    
                    $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, file_path, `order`) VALUES (?, ?, ?)");
                    $order_idx = 1;
                    
                    $uploadDir = __DIR__ . '/../uploads/products/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $cloudinary_failed = false;
                    for ($i = 0; $i < $limit; $i++) {
                        if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                            $tmpPath = $_FILES['images']['tmp_name'][$i];
                            $fileName = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES['images']['name'][$i]);
                            $localPath = $uploadDir . $fileName;
                            $dbLocalPath = 'uploads/products/' . $fileName;
                            
                            $moved = @move_uploaded_file($tmpPath, $localPath);
                            $uploadSource = $moved ? $localPath : $tmpPath;
                            $newUrl = null;
                            
                            try {
                                $response = $uploadApi->upload($uploadSource, [
                                    'folder' => 'sokosafi/products',
                                    'transformation' => [
                                        'quality' => 'auto',
                                        'fetch_format' => 'auto',
                                        'width' => 800,
                                        'crop' => 'limit'
                                    ]
                                ]);
                                $newUrl = $response['secure_url'];
                            } catch (\Exception $e) {
                                error_log("Cloudinary upload failed: " . $e->getMessage());
                                $cloudinary_failed = true;
                                if ($moved) {
                                    $newUrl = $dbLocalPath;
                                }
                            }
                            
                            if ($newUrl) {
                                $stmtImg->execute([$product_id, $newUrl, $order_idx]);
                                
                                // Update primary image path if it's the first image
                                if ($order_idx === 1) {
                                    $pdo->prepare("UPDATE products SET image_path = ? WHERE id = ?")->execute([$newUrl, $product_id]);
                                }
                                $order_idx++;
                            }
                        }
                    }
                }

                $message = 'Product added successfully.';
                if (isset($cloudinary_failed) && $cloudinary_failed) {
                    $warn_msg = ' However, Cloudinary upload failed. Images were saved locally on the server space instead. Please check your Cloudinary configuration.';
                }
                // Clear form?
                $_POST = []; 
            } catch (Throwable $e) {
                $message = 'Error: ' . $e->getMessage();
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<section class="container">
    <h2>Add New Product</h2>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?><?php if(isset($warn_msg)) echo ' <strong class="text-warning">'.$warn_msg.'</strong>'; ?></div>
    <?php endif; ?>
    
    <form method="post" enctype="multipart/form-data" class="d-grid gap-3" style="max-width: 600px;">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div>
            <label class="form-label">Product Name</label>
            <input type="text" name="name" class="form-control" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>
        
        <div>
            <label class="form-label">Slug (Optional)</label>
            <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>">
        </div>
        
        <div class="row">
            <div class="col">
                <label class="form-label">Price</label>
                <input type="number" step="0.01" name="price" class="form-control" required value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
            </div>
            <div class="col">
                <label class="form-label">Sale Price</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>">
            </div>
        </div>
        
        <div>
            <label class="form-label">Stock Quantity</label>
            <input type="number" name="stock" class="form-control" value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
        </div>
        
        <div>
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        
        <div>
            <label class="form-label">Categories</label>
            <div class="card p-2">
                <?php foreach ($allCategories as $cat): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="category_ids[]" value="<?php echo (int)$cat['id']; ?>" id="cat_<?php echo (int)$cat['id']; ?>">
                        <label class="form-check-label" for="cat_<?php echo (int)$cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="card p-3 mb-4">
            <h5 class="mb-1 text-dark">Product Images (up to 10)</h5>
            <p class="text-success small mb-3">First picture is the title picture. Supported formats are *.jpg and *.png</p>
            
            <div id="image-gallery-container" class="d-flex flex-wrap gap-2 mb-2">
                <!-- ADD BUTTON -->
                <div id="add-image-btn" class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 100px; height: 100px; cursor: pointer; border-style: dashed !important; border-width: 2px !important;">
                    <span class="fs-1 text-success">+</span>
                </div>
            </div>
            
            <div id="hidden-inputs-container">
                <!-- Hidden file inputs go here -->
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.getElementById('add-image-btn');
            const galleryContainer = document.getElementById('image-gallery-container');
            const inputsContainer = document.getElementById('hidden-inputs-container');
            let currentImageCount = 0;
            const maxImages = 10;

            function checkImageLimit() {
                if (currentImageCount >= maxImages) {
                    addBtn.style.display = 'none';
                } else {
                    addBtn.style.display = 'flex';
                }
            }

            addBtn.addEventListener('click', function() {
                if (currentImageCount >= maxImages) {
                    alert('You can only upload up to 10 images.');
                    return;
                }

                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'images[]';
                input.accept = 'image/jpeg, image/png';
                input.className = 'd-none hidden-file-input';
                
                input.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const file = this.files[0];
                        if (!file.type.match('image/jpeg') && !file.type.match('image/png')) {
                            alert('Only JPG and PNG formats are supported.');
                            this.remove();
                            return;
                        }

                        const previewDiv = document.createElement('div');
                        previewDiv.className = 'position-relative border rounded overflow-hidden preview-box';
                        previewDiv.style.width = '100px';
                        previewDiv.style.height = '100px';

                        const img = document.createElement('img');
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        
                        const reader = new FileReader();
                        reader.onload = function(e) { img.src = e.target.result; };
                        reader.readAsDataURL(file);

                        const removeBtn = document.createElement('div');
                        removeBtn.className = 'position-absolute top-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center';
                        removeBtn.style.width = '20px';
                        removeBtn.style.height = '20px';
                        removeBtn.style.cursor = 'pointer';
                        removeBtn.style.margin = '4px';
                        removeBtn.style.fontSize = '12px';
                        removeBtn.innerHTML = '✕';
                        
                        removeBtn.addEventListener('click', function(evt) {
                            evt.stopPropagation();
                            previewDiv.remove();
                            input.remove();
                            currentImageCount--;
                            checkImageLimit();
                        });

                        previewDiv.appendChild(img);
                        previewDiv.appendChild(removeBtn);
                        
                        galleryContainer.insertBefore(previewDiv, addBtn);
                        inputsContainer.appendChild(input);
                        
                        currentImageCount++;
                        checkImageLimit();
                    } else {
                        // User canceled selection
                        this.remove();
                    }
                });
                input.click();
            });
        });
        </script>
        
        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-success btn-lg py-3 fw-bold fs-5">Add Product</button>
        </div>
    </form>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>