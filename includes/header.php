<?php
// Shared header integrating FoodMart template assets while keeping our app routing
?>
<!doctype html>
<html lang="en">
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
    <!-- Global font to match homepage -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Template vendor styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <!-- Font Awesome icons (global) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Local template styles (mapped to our assets folder) -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/vendor.css">
    <!-- link rel="stylesheet" href="<?php echo $base; ?>/assets/css/style.css" -->
    <!-- Light theme to align all pages with homepage look -->
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/theme-light.css?v=7">
    <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/header-custom.css?v=5">
    <?php if ($in_admin): ?>
      <link rel="stylesheet" href="<?php echo $base; ?>/assets/css/admin.css">
    <?php
endif; ?>
  </head>
  <body>
    <header class="header-area">
      <div class="container-fluid px-4 h-100">
        <div class="d-flex align-items-center justify-content-between h-100">
          <div class="main-logo">
            <a href="<?php echo $base; ?>/index.php?page=home" class="text-decoration-none d-flex align-items-center">
              <img src="https://res.cloudinary.com/dmnbjskbz/image/upload/v1771605277/sokosafi/logo.png" alt="logo" onerror="this.style.display='none'">
              <span class="fw-bold ms-2">SokoSafi</span>
            </a>
          </div>
          
          <div class="flex-grow-1 d-none d-lg-block px-5">
            <form id="search-form" class="input-group" method="get" action="<?php echo $base; ?>/index.php">
              <input type="hidden" name="page" value="products">
              <span class="input-group-text"><i class="fa fa-search"></i></span>
              <input type="text" name="q" class="form-control" placeholder="Search for products..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
              <button class="btn btn-primary" type="submit">Search</button>
            </form>
          </div>

          <div class="d-flex align-items-center gap-3">
             <div class="d-none d-lg-block">
                <?php include __DIR__ . '/navbar.php'; ?>
             </div>
             
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" style="border-color: rgba(0,0,0,.1);">
              <i class="fa fa-bars fa-lg text-dark"></i>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Menu Offcanvas -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="mobileMenuLabel">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
         <form class="input-group mb-4 d-lg-none" method="get" action="<?php echo $base; ?>/index.php">
            <input type="hidden" name="page" value="products">
            <input type="text" name="q" class="form-control" placeholder="Search..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>" />
            <button class="btn btn-primary" type="submit"><i class="fa fa-search"></i></button>
         </form>
         <?php
// Re-include navbar for mobile context if needed, or just standard links
include __DIR__ . '/navbar.php';
?>
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