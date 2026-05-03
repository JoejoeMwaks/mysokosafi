<?php require_once __DIR__ . '/../includes/admin_guard.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<main class="container">
  <div class="admin-header">
    <h2 class="mb-0">Admin Dashboard</h2>
    <a class="btn btn-outline-secondary btn-sm" href="../index.php?page=home">Back to Store</a>
  </div>

  <div class="admin-grid">
    <div class="admin-card">
      <div class="title">Add Product</div>
      <div class="desc">Create a new product listing.</div>
      <a class="btn btn-dark" href="add_product.php">Open</a>
    </div>

    <div class="admin-card">
      <div class="title">Edit Product</div>
      <div class="desc">Update existing product details.</div>
      <a class="btn btn-dark" href="edit_product.php">Open</a>
    </div>

    <div class="admin-card">
      <div class="title">View Orders</div>
      <div class="desc">Review and manage customer orders.</div>
      <a class="btn btn-dark" href="view_orders.php">Open</a>
    </div>

    <div class="admin-card">
      <div class="title">Manage Users</div>
      <div class="desc">Assign roles and manage accounts.</div>
      <a class="btn btn-dark" href="manage_users.php">Open</a>
    </div>

    <div class="admin-card">
      <div class="title">Manage Blogs</div>
      <div class="desc">Create, edit, or remove blog posts.</div>
      <a class="btn btn-dark" href="manage_blogs.php">Open</a>
    </div>
  </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>