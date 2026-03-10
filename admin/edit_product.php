<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php require_once __DIR__ . '/../config/db.php'; ?>
<?php require_once __DIR__ . '/../includes/db_functions.php'; ?>
<?php
$message = null;
$edited = null;
// Load top-level categories for selection in forms
if (db_has_connection()) {
  // Seed core categories if missing so admin can select them
  ensure_core_categories_seeded();
}
$allCategories = db_has_connection() ? get_categories(null) : [];

// Minimal update/delete handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && db_has_connection()) {
  $action = $_POST['action'] ?? '';
  $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
  if ($action === 'update' && $id > 0) {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $sale_price = $_POST['sale_price'] !== '' ? (float)$_POST['sale_price'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    // Ensure unique slug
    if ($slug === '') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
    $original_slug = $slug;
    $counter = 1;
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
    while (true) {
        $stmtCheck->execute([$slug, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        } else {
            break;
        }
    }
    
    try {
      // Simple inline update query (no separate helper yet)
      $stmt = $pdo->prepare("UPDATE products SET name=:name, slug=:slug, description=:description, price=:price, sale_price=:sale_price, stock=:stock WHERE id=:id");
      $stmt->execute([
        ':name' => $name,
        ':slug' => $slug,
        ':description' => $description,
        ':price' => $price,
        ':sale_price' => $sale_price,
        ':stock' => $stock,
        ':id' => $id,
      ]);
      $message = 'Product updated.';
      $edited = $id;

      // Update category associations
      $catIds = isset($_POST['category_ids']) ? (array)$_POST['category_ids'] : [];
      set_product_categories($id, $catIds);

      // Handle new images upload directly to Cloudinary
      if (isset($_FILES['new_images']) && is_array($_FILES['new_images']['name'])) {
          require_once __DIR__ . '/../config/cloudinary.php';
          $uploadApi = new \Cloudinary\Api\Upload\UploadApi();
          
          $fileCount = count($_FILES['new_images']['name']);
          $limit = min(10, $fileCount);
          
          // Get current max order
          $stmtMax = $pdo->prepare("SELECT MAX(`order`) as max_ord FROM product_images WHERE product_id = ?");
          $stmtMax->execute([$id]);
          $maxRes = $stmtMax->fetch();
          $order_idx = ($maxRes && $maxRes['max_ord']) ? (int)$maxRes['max_ord'] + 1 : 1;
          
          $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, file_path, `order`) VALUES (?, ?, ?)");
          
          $uploadDir = __DIR__ . '/../uploads/products/';
          if (!is_dir($uploadDir)) {
              mkdir($uploadDir, 0777, true);
          }
          
          for ($i = 0; $i < $limit; $i++) {
              if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                  $tmpPath = $_FILES['new_images']['tmp_name'][$i];
                  $fileName = time() . '_' . rand(1000, 9999) . '_' . basename($_FILES['new_images']['name'][$i]);
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
                      if ($moved) {
                          $newUrl = $dbLocalPath;
                      }
                  }
                  
                  if ($newUrl) {
                      $stmtImg->execute([$id, $newUrl, $order_idx]);
                      
                      // Update primary image path if it's the first image
                      if ($order_idx === 1) {
                          $pdo->prepare("UPDATE products SET image_path = ? WHERE id = ?")->execute([$newUrl, $id]);
                      }
                      $order_idx++;
                  }
              }
          }
      }
    } catch (Throwable $e) {
      $message = 'Error: ' . $e->getMessage();
    }
  } elseif ($action === 'delete' && $id > 0) {
    try {
      $stmt = $pdo->prepare("DELETE FROM products WHERE id=:id");
      $stmt->execute([':id' => $id]);
      $message = 'Product deleted.';
    } catch (Throwable $e) {
      $message = 'Error: ' . $e->getMessage();
    }
  }
}
$products = db_has_connection() ? get_products(null, null) : [];
?>

<?php include __DIR__ . '/../includes/header.php'; ?>
<section class="container">
  
  <?php if ($message): ?>
    <div class="alert alert-info mt-3"><?php echo htmlspecialchars($message); ?><?php if(isset($warn_msg)) echo ' <strong class="text-warning">'.$warn_msg.'</strong>'; ?></div>
  <?php endif; ?>

  <?php if (!isset($_GET['id'])): ?>
    <!-- LIST VIEW (JIJI STYLE CARDS) -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Products</h2>
        <a href="admin/add_product.php" class="btn btn-success">+ Add Product</a>
    </div>

    <div class="d-flex flex-column gap-3">
        <?php foreach ($products as $p): 
            $images = get_product_images($p['id']);
            $primaryImage = !empty($images) ? $images[0]['file_path'] : 'assets/images/placeholder.jpg';
        ?>
            <div class="card shadow-sm">
                <div class="card-body d-flex flex-column flex-md-row gap-4">
                    <!-- Product Image -->
                    <div style="width: 150px; height: 150px; flex-shrink: 0;" class="bg-light rounded overflow-hidden">
                        <?php if (str_starts_with($primaryImage, 'http')): ?>
                            <img src="<?php echo htmlspecialchars($primaryImage); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Product">
                        <?php else: ?>
                            <img src="<?php echo htmlspecialchars('/sokosafi/' . $primaryImage); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="Product">
                        <?php endif; ?>
                    </div>
                    
                    <!-- Details -->
                    <div class="flex-grow-1 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start">
                                <h4 class="mb-1 text-dark fw-bold">KSh <?php echo number_format($p['price'], 2); ?></h4>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <h5 class="card-title text-dark mb-1"><?php echo htmlspecialchars($p['name']); ?></h5>
                            <p class="text-muted small mb-2">Stock: <?php echo (int)$p['stock']; ?> units available</p>
                        </div>
                        
                        <!-- Actions -->
                        <div class="d-flex gap-3 align-items-center border-top pt-3 mt-2">
                            <a href="admin/edit_product.php?id=<?php echo $p['id']; ?>" class="text-success text-decoration-none fw-bold"><i class="bi bi-pencil"></i> Edit</a>
                            
                            <form method="post" class="m-0 p-0" onsubmit="return confirm('Are you sure you want to completely delete this product?');">
                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                <button type="submit" name="action" value="delete" class="btn btn-link text-danger text-decoration-none fw-bold p-0"><i class="bi bi-trash"></i> Close</button>
                            </form>
                            
                            <div class="ms-auto">
                                <span class="badge border border-warning text-warning rounded-pill">Top Ad</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($products)): ?>
            <div class="text-center py-5 text-muted">
                <p>You haven't added any products yet.</p>
                <a href="admin/add_product.php" class="btn btn-primary">Add Your First Product</a>
            </div>
        <?php endif; ?>
    </div>
    
  <?php else: ?>
    <!-- EDIT VIEW (SPECIFIC PRODUCT) -->
    <?php 
        $edit_id = (int)$_GET['id'];
        $product = null;
        foreach($products as $p) {
            if ($p['id'] == $edit_id) {
                $product = $p;
                break;
            }
        }
        
        if (!$product):
    ?>
        <div class="alert alert-danger">Product not found. <a href="admin/edit_product.php">Go back</a></div>
    <?php else: 
        $existing_images = get_product_images($product['id']);
    ?>
    <div class="mb-4">
        <a href="admin/edit_product.php" class="text-decoration-none text-muted mb-3 d-inline-block">← Back to My Products</a>
        <h2>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h2>
    </div>

    <form method="post" enctype="multipart/form-data" class="d-grid gap-4 bg-white p-4 rounded shadow-sm border" style="max-width: 800px;">
        <input type="hidden" name="id" value="<?php echo (int)$product['id']; ?>" />
        
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Title*</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" class="form-control form-control-lg border-success" required />
            </div>
            
            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Slug (Leave blank to auto-generate)</label>
                <input type="text" name="slug" value="<?php echo htmlspecialchars($product['slug']); ?>" class="form-control border-success" />
            </div>
            
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Price*</label>
                <input type="number" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" class="form-control form-control-lg border-success" required />
            </div>
            
            <div class="col-md-6">
                <label class="form-label text-muted small fw-bold">Sale Price</label>
                <input type="number" step="0.01" min="0" name="sale_price" value="<?php echo htmlspecialchars($product['sale_price'] ?? ''); ?>" class="form-control border-success" />
            </div>
            
            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Stock*</label>
                <input type="number" step="1" min="0" name="stock" value="<?php echo (int)$product['stock']; ?>" class="form-control border-success" required />
            </div>
            
            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Description</label>
                <textarea name="description" rows="5" class="form-control border-success"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            
            <div class="col-md-12">
                <label class="form-label text-muted small fw-bold">Category*</label>
                <div class="border border-success rounded p-3 bg-light">
                    <?php 
                    $selectedCats = db_has_connection() ? get_product_category_ids((int)$product['id']) : []; 
                    if (!empty($allCategories)): ?>
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            <?php foreach ($allCategories as $cat): ?>
                                <div class="col">
                                    <label class="form-check">
                                        <input class="form-check-input" type="checkbox" name="category_ids[]" value="<?php echo (int)$cat['id']; ?>" <?php echo in_array((int)$cat['id'], $selectedCats, true) ? 'checked' : ''; ?> />
                                        <span class="form-check-label"><?php echo htmlspecialchars($cat['name']); ?></span>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-muted">No categories found.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-12 mt-4">
                <h5 class="mb-1 text-dark">Add at least 1 photo</h5>
                <p class="text-success small mb-3">First picture is the title picture. Supported formats are *.jpg and *.png</p>
                
                <div id="image-gallery-container" class="d-flex flex-wrap gap-2 mb-2">
                    <!-- EXISTING IMAGES -->
                    <?php foreach ($existing_images as $img): ?>
                        <div class="position-relative border rounded overflow-hidden existing-image-box" style="width: 100px; height: 100px;" data-id="<?php echo $img['id']; ?>">
                            <?php if (str_starts_with($img['file_path'], 'http')): ?>
                                <img src="<?php echo htmlspecialchars($img['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <img src="<?php echo htmlspecialchars('/sokosafi/' . $img['file_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php endif; ?>
                            <div class="position-absolute top-0 end-0 bg-dark text-white rounded-circle d-flex align-items-center justify-content-center delete-existing-btn" style="width: 20px; height: 20px; cursor: pointer; margin: 4px; font-size: 12px; z-index: 10;">
                                ✕
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- ADD BUTTON -->
                    <div id="add-image-btn" class="border rounded d-flex align-items-center justify-content-center bg-light" style="width: 100px; height: 100px; cursor: pointer; border-style: dashed !important; border-width: 2px !important;">
                        <span class="fs-1 text-success">+</span>
                    </div>
                </div>
                
                <div id="hidden-inputs-container">
                    <!-- New hidden file inputs go here -->
                </div>
                <!-- Hidden inputs for tracking deleted existing images -->
                <div id="deleted-images-container"></div>
            </div>
        </div>

        <div class="d-grid mt-4">
            <button class="btn btn-success btn-lg py-3 fw-bold fs-5" type="submit" name="action" value="update">Next</button>
        </div>
    </form>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle Existing Image Deletion
        const existingDeleteBtns = document.querySelectorAll('.delete-existing-btn');
        const deletedImagesContainer = document.getElementById('deleted-images-container');
        let currentImageCount = <?php echo count($existing_images); ?>;
        
        existingDeleteBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const box = this.closest('.existing-image-box');
                const imgId = box.dataset.id;
                
                // Add hidden input to tell backend to delete this image
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'deleted_images[]';
                hiddenInput.value = imgId;
                deletedImagesContainer.appendChild(hiddenInput);
                
                box.remove();
                currentImageCount--;
                checkImageLimit();
            });
        });

        // Handle New Image Addition
        const addBtn = document.getElementById('add-image-btn');
        const galleryContainer = document.getElementById('image-gallery-container');
        const inputsContainer = document.getElementById('hidden-inputs-container');
        const maxImages = 10;

        function checkImageLimit() {
            if (currentImageCount >= maxImages) {
                addBtn.style.display = 'none';
            } else {
                addBtn.style.display = 'flex';
            }
        }
        
        checkImageLimit();

        addBtn.addEventListener('click', function() {
            if (currentImageCount >= maxImages) {
                alert('You can only upload up to 10 images.');
                return;
            }

            const input = document.createElement('input');
            input.type = 'file';
            input.name = 'new_images[]';
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
                    this.remove();
                }
            });
            input.click();
        });
    });
    </script>
    <?php endif; ?>

</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>