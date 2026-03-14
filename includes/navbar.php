<?php
// Navbar — PHP logic unchanged, HTML updated with glass classes
$is_logged_in = isset($_SESSION['user']);
$user_name = $is_logged_in ? ($_SESSION['user']['name'] ?? 'Account') : null;
$roles = $is_logged_in ? ($_SESSION['user']['roles'] ?? []) : [];
$is_admin = is_array($roles) && in_array('admin', $roles, true);
if (!isset($base)) {
    $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $base = $in_admin ? '..' : '.';
}
$current_page = $_GET['page'] ?? 'home';
$is_category = isset($_GET['category']);
?>
<nav class="nav flex-column flex-lg-row align-items-lg-center gap-1">

  <a class="nav-link nav-link-custom <?php echo ($current_page === 'home') ? 'active' : ''; ?>" href="<?php echo $base; ?>/index.php?page=home">
    <i class="fa fa-home d-lg-none"></i> Home
  </a>

  <a class="nav-link nav-link-custom <?php echo ($current_page === 'products' && !$is_category) ? 'active' : ''; ?>" href="<?php echo $base; ?>/index.php?page=products">
    <i class="fa fa-th-large d-lg-none"></i> Shop
  </a>

  <div class="dropdown">
    <a class="nav-link nav-link-custom dropdown-toggle <?php echo ($current_page === 'products' && $is_category) ? 'active' : ''; ?>" href="#" id="navCategories" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa fa-list d-lg-none"></i> Categories
    </a>
    <ul class="dropdown-menu glass-dropdown shadow" aria-labelledby="navCategories">
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=electronics"><i class="fas fa-microchip me-2 text-muted fa-sm"></i>Electronics</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=fashion"><i class="fas fa-tshirt me-2 text-muted fa-sm"></i>Fashion</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=beauty"><i class="fas fa-spa me-2 text-muted fa-sm"></i>Beauty</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=home-living"><i class="fas fa-couch me-2 text-muted fa-sm"></i>Home &amp; Living</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=accessories"><i class="fas fa-gem me-2 text-muted fa-sm"></i>Accessories</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=shoes"><i class="fas fa-shoe-prints me-2 text-muted fa-sm"></i>Shoes</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=gaming"><i class="fas fa-gamepad me-2 text-muted fa-sm"></i>Gaming</a></li>
    </ul>
  </div>

  <a class="nav-link nav-link-custom <?php echo ($current_page === 'track_order') ? 'active' : ''; ?>" href="<?php echo $base; ?>/index.php?page=track_order">
    <i class="fa fa-map-marker-alt d-lg-none"></i> Track Order
  </a>

  <a class="nav-link nav-link-custom position-relative" href="<?php echo $base; ?>/index.php?page=cart">
    <i class="fa fa-shopping-cart"></i>
    <span class="d-lg-none ms-2">Cart</span>
    <span class="cart-badge" id="cart-count" style="display:none;">0</span>
  </a>

  <?php if ($is_logged_in): ?>
    <div class="dropdown">
      <a class="nav-link nav-link-custom dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="user-avatar">
          <?php echo strtoupper(substr($user_name, 0, 1)); ?>
        </div>
        <span class="d-none d-lg-inline"><?php echo htmlspecialchars($user_name); ?></span>
      </a>
      <ul class="dropdown-menu dropdown-menu-end glass-dropdown shadow">
        <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=profile"><i class="fa fa-user me-2 text-muted fa-sm"></i>My Profile</a></li>
        <?php if ($is_admin): ?>
          <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/dashboard.php"><i class="fa fa-cog me-2 text-muted fa-sm"></i>Admin Panel</a></li>
          <li><hr class="dropdown-divider"></li>
        <?php endif; ?>
        <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=logout"><i class="fa fa-sign-out me-2 text-muted fa-sm"></i>Logout</a></li>
      </ul>
    </div>
  <?php else: ?>
    <div class="d-flex gap-2 ms-lg-1 mt-2 mt-lg-0">
      <a class="btn btn-sm btn-outline-primary" href="<?php echo $base; ?>/index.php?page=login">Login</a>
      <a class="btn btn-sm btn-primary" href="<?php echo $base; ?>/index.php?page=register">Register</a>
    </div>
  <?php endif; ?>

</nav>