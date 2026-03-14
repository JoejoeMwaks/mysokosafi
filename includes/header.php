<?php
// Shared header integrating FoodMart template assets while keeping our app routing
?>
<!doctype html>
<html lang="en" data-theme="light">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>SokoSafi</title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'SokoSafi E-Commerce Store'); ?>">
    <?php
// Determine base path so includes work from /admin and root pages
$in_admin = (basename(getcwd()) === 'admin');
$base = $in_admin ? '..' : '.';
?>
    <link rel="icon" type="image/png" href="https://res.cloudinary.com/dmnbjskbz/image/upload/v1771605281/sokosafi/favicon.jpg">
    <!-- Global font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Vendor styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Font Awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Local styles -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/vendor.css">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/theme-light.css?v=10">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/header-custom.css?v=6">
    <!-- Glassmorphism design system (loaded last) -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/glass.css?v=1">
    <?php if ($in_admin): ?>
      <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/admin.css">
    <?php
endif; ?>
    <!-- Apply saved theme before paint (prevents flash) -->
    <script>
      (function(){
        try {
          var t = localStorage.getItem('sokosafi-theme') || 'light';
          document.documentElement.setAttribute('data-theme', t);
        } catch(e){}
      })();
    </script>
  </head>
  <body>
    <header class="header-area">
      <div class="container-fluid px-3 px-lg-4 h-100 w-100">
        <div class="d-flex align-items-center justify-content-between h-100 gap-3">

          <!-- Logo -->
          <div class="main-logo flex-shrink-0">
            <a href="<?php echo $base; ?>/index.php?page=home" class="text-decoration-none d-flex align-items-center gap-2">
              <img src="<?php echo $base; ?>/assets/images/logo.png" alt="logo" onerror="this.style.display='none'">
              <span class="fw-bold">SokoSafi</span>
            </a>
          </div>

          <!-- Search bar (desktop) -->
          <div class="flex-grow-1 d-none d-lg-block" style="max-width:500px;">
            <form id="search-form" method="get" action="<?php echo $base; ?>/index.php">
              <input type="hidden" name="page" value="products">
              <div class="input-group">
                <span class="input-group-text"><i class="fa fa-search"></i></span>
                <input type="text" name="q" class="form-control" placeholder="Search for products..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
                <button class="btn btn-primary" type="submit">Search</button>
              </div>
            </form>
          </div>

          <!-- Right actions -->
          <div class="d-flex align-items-center gap-2">
            <!-- Desktop nav -->
            <div class="d-none d-lg-flex align-items-center gap-1">
              <?php include __DIR__ . '/navbar.php'; ?>
            </div>

            <!-- Theme toggle -->
            <button class="theme-toggle" id="theme-toggle" title="Toggle dark mode" aria-label="Toggle dark mode">
              <i class="fas fa-moon" id="theme-icon"></i>
            </button>

            <!-- Hamburger (mobile) -->
            <button class="navbar-toggler d-lg-none border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
              <i class="fa fa-bars fa-lg"></i>
            </button>
          </div>

        </div>
      </div>
    </header>

    <!-- Mobile Menu Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
      <div class="offcanvas-header">
        <div class="d-flex align-items-center gap-2">
          <img src="<?php echo $base; ?>/assets/images/logo.png" alt="logo" class="offcanvas-logo" onerror="this.style.display='none'">
          <h5 class="offcanvas-title mb-0 fw-bold" id="mobileMenuLabel">SokoSafi</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <!-- Mobile search -->
        <form class="mb-4" method="get" action="<?php echo $base; ?>/index.php">
          <input type="hidden" name="page" value="products">
          <div class="input-group">
            <input type="text" name="q" class="form-control" placeholder="Search products..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
          </div>
        </form>
        <!-- Mobile nav -->
        <?php include __DIR__ . '/navbar.php'; ?>
      </div>
    </div>

    <?php if (!empty($_SESSION['flash'])): ?>
      <div class="container mt-3">
        <div class="alert alert-primary">
          <?php echo htmlspecialchars($_SESSION['flash']);
  unset($_SESSION['flash']); ?>
        </div>
      </div>
    <?php
endif; ?>