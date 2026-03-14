<?php
// Compute base path so assets resolve from /admin and root
if (!isset($base)) {
    $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $base = $in_admin ? '..' : '.';
}
?>
    <!-- ***** Footer ***** -->
    <footer id="footer" class="glass-footer">
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-4 col-md-6">
                    <div class="mb-4">
                        <a href="<?php echo $base; ?>/index.php" class="d-flex align-items-center gap-2 text-decoration-none">
                            <img src="<?php echo $base; ?>/assets/images/logo.png" alt="logo" style="height: 24px !important; width: auto !important;">
                            <span class="text-white fw-bold" style="font-size: 0.95rem; letter-spacing: 0.02em;">SokoSafi</span>
                        </a>
                    </div>
                    <p class="small mb-4" style="color:rgba(255,255,255,0.55); line-height:1.75;">Your destination for premium products and exceptional shopping experiences. Quality and sophistication redefined.</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;padding:0;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;padding:0;"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;padding:0;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width:36px;height:36px;padding:0;"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="mb-3 text-white fw-bold" style="letter-spacing:0.05em;font-size:0.8rem;text-transform:uppercase;">Collections</h6>
                    <ul class="list-unstyled small" style="line-height:2;">
                        <li><a href="<?php echo $base; ?>/index.php?page=products" class="text-secondary text-decoration-none hover-white">All Products</a></li>
                        <li><a href="<?php echo $base; ?>/index.php?page=featured"  class="text-secondary text-decoration-none hover-white">Featured</a></li>
                        <li><a href="<?php echo $base; ?>/index.php?page=new_arrivals" class="text-secondary text-decoration-none hover-white">New Arrivals</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="mb-3 text-white fw-bold" style="letter-spacing:0.05em;font-size:0.8rem;text-transform:uppercase;">Support</h6>
                    <ul class="list-unstyled small" style="line-height:2;">
                        <li><a href="<?php echo $base; ?>/index.php?page=faq"      class="text-secondary text-decoration-none hover-white">FAQ</a></li>
                        <li><a href="<?php echo $base; ?>/index.php?page=shipping"  class="text-secondary text-decoration-none hover-white">Shipping</a></li>
                        <li><a href="<?php echo $base; ?>/index.php?page=returns"   class="text-secondary text-decoration-none hover-white">Returns</a></li>
                        <li><a href="<?php echo $base; ?>/index.php?page=contact"   class="text-secondary text-decoration-none hover-white">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="mb-3 text-white fw-bold" style="letter-spacing:0.05em;font-size:0.8rem;text-transform:uppercase;">Get in Touch</h6>
                    <ul class="list-unstyled small" style="color:rgba(255,255,255,0.55); line-height:2.1;">
                        <li class="d-flex align-items-start gap-2"><i class="fas fa-map-marker-alt mt-1" style="color:var(--accent);"></i><span>Nairobi, Kenya</span></li>
                        <li class="d-flex align-items-start gap-2"><i class="fas fa-phone mt-1" style="color:var(--accent);"></i><span>0758549123</span></li>
                        <li class="d-flex align-items-start gap-2"><i class="fas fa-envelope mt-1" style="color:var(--accent);"></i><span>support@synora.dev</span></li>
                    </ul>
                </div>
            </div>

            <div class="mt-5 pt-4" style="border-top:1px solid rgba(255,255,255,0.07);">
                <div class="text-center">
                    <p class="small mb-2" style="color:rgba(255,255,255,0.35);">&copy; 2026 Synora Systems. All rights reserved.</p>
                    <img src="<?php echo $base; ?>/assets/images/payment-methods.png" alt="Payment Methods" height="22" onerror="this.style.display='none'">
                </div>
            </div>
        </div>
    </footer>

    <style>
      .hover-white:hover { color: #fff !important; transition: color 0.2s; }
    </style>

    <!-- Vendor scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <!-- App script -->
    <script src="<?php echo $base; ?>/assets/js/main.js"></script>
  </body>
</html>