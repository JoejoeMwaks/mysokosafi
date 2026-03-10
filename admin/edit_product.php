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
  <h2>Edit Product</h2>
  <?php if ($message): ?>
    <div style="margin:.75rem 0;padding:.5rem;border:1px solid #1f2937;border-radius:8px;background:#0b1220;">
      <?php echo htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($products as $p): ?>
      <div class="col-12 col-md-6">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title mb-2"><?php echo htmlspecialchars($p['name']); ?> (ID <?php echo (int)$p['id']; ?>)</h5>
            <form method="post" enctype="multipart/form-data" class="d-grid gap-2">
              <input type="hidden" name="id" value="<?php echo (int)$p['id']; ?>" />
              <label>Name <input type="text" name="name" value="<?php echo htmlspecialchars($p['name']); ?>" class="form-control" /></label>
              <label>Slug <input type="text" name="slug" value="<?php echo htmlspecialchars($p['slug']); ?>" class="form-control" /></label>
              <label>Price <input type="number" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($p['price']); ?>" class="form-control" /></label>
              <label>Sale Price <input type="number" step="0.01" min="0" name="sale_price" value="<?php echo htmlspecialchars($p['sale_price'] ?? ''); ?>" class="form-control" /></label>
              <label>Stock <input type="number" step="1" min="0" name="stock" value="<?php echo (int)$p['stock']; ?>" class="form-control" /></label>
              <label>Description <textarea name="description" rows="3" class="form-control"><?php echo htmlspecialchars($p['description'] ?? ''); ?></textarea></label>
              <?php $selectedCats = db_has_connection() ? get_product_category_ids((int)$p['id']) : []; ?>
              <fieldset class="border rounded p-2">
                <legend class="float-none w-auto px-2">Categories</legend>
                <?php if (!empty($allCategories)): ?>
                  <div class="row row-cols-2 g-2">
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
              </fieldset>
                            <label>Add Additional Images 
                <input type="file" name="new_images[]" class="form-control" accept="image/*" multiple max="10" />
                <small class="text-muted d-block">Uploading here will ADD to existing images (max 10 total usually).</small>
              </label>
              <div class="d-flex gap-2">
                <button class="btn btn-dark" type="submit" name="action" value="update">Update</button>
                <button class="btn btn-outline-danger" type="submit" name="action" value="delete" onclick="return confirm('Delete this product?');">Delete</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>