<?php
// Navbar adapted to bootstrap classes and our routing/auth
$is_logged_in = isset($_SESSION['user']);
$user_name = $is_logged_in ? ($_SESSION['user']['name'] ?? 'Account') : null;
$roles = $is_logged_in ? ($_SESSION['user']['roles'] ?? []) : [];
$is_admin = is_array($roles) && in_array('admin', $roles, true);
// Base path provided by header, compute fallback if not set
if (!isset($base)) {
  $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
  $base = $in_admin ? '..' : '.';
}
?>
<nav class="nav flex-column flex-lg-row align-items-lg-center gap-1 gap-lg-2">
  <a class="nav-link nav-link-custom" href="<?php echo $base; ?>/index.php?page=home">
    <i class="fa fa-home d-lg-none"></i> Home
  </a>
  <a class="nav-link nav-link-custom" href="<?php echo $base; ?>/index.php?page=products">
    <i class="fa fa-th-large d-lg-none"></i> Shop
  </a>
  
  <div class="dropdown">
    <a class="nav-link nav-link-custom dropdown-toggle" href="#" id="navCategories" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa fa-list d-lg-none"></i> Categories
    </a>
    <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="navCategories">
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=electronics">Electronics</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=fashion">Fashion</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=beauty">Beauty</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=home-living">Home & Living</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=accessories">Accessories</a></li>
      <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=products&category=shoes">Shoes</a></li>
    </ul>
  </div>

  <a class="nav-link nav-link-custom" href="<?php echo $base; ?>/index.php?page=track_order">
    <i class="fa fa-map-marker-alt d-lg-none"></i> Track Order
  </a>

  <a class="nav-link nav-link-custom position-relative" href="<?php echo $base; ?>/index.php?page=cart">
    <i class="fa fa-shopping-cart"></i> 
    <span class="d-lg-none ms-2">Cart</span>
  </a>

  <?php if ($is_logged_in): ?>
    <div class="dropdown ms-lg-3">
        <a class="nav-link nav-link-custom dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-circle me-2" style="width: 32px; height: 32px;">
                <?php echo strtoupper(substr($user_name, 0, 1)); ?>
            </div>
            <span class="d-none d-lg-inline"><?php echo htmlspecialchars($user_name); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
            <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=profile"><i class="fa fa-user me-2"></i> My Profile</a></li>
            <?php if ($is_admin): ?>
                <li><a class="dropdown-item" href="<?php echo $base; ?>/admin/dashboard.php"><i class="fa fa-cog me-2"></i> Admin Panel</a></li>
                <li><hr class="dropdown-divider"></li>
            <?php
  endif; ?>
            <li><a class="dropdown-item" href="<?php echo $base; ?>/index.php?page=logout"><i class="fa fa-sign-out me-2"></i> Logout</a></li>
        </ul>
    </div>
  <?php
else: ?>
    <div class="d-flex gap-2 ms-lg-3 mt-2 mt-lg-0">
        <a class="btn btn-sm btn-outline-primary px-3 rounded-pill" href="<?php echo $base; ?>/index.php?page=login">Login</a>
        <a class="btn btn-sm btn-primary px-3 rounded-pill" href="<?php echo $base; ?>/index.php?page=register">Register</a>
    </div>
  <?php
endif; ?>
</nav>