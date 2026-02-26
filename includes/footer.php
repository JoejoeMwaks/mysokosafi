<?php
// Compute base path so assets resolve from /admin and root
if (!isset($base)) {
    $in_admin = isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false;
    $base = $in_admin ? '..' : '.';
}
?>
    <!-- ***** Footer ***** -->
    <footer id="footer" style="background: #0b1220; color: #e5e7eb; padding: 4rem 0 2rem; border-top: 1px solid #1f2937;">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4 col-md-6">
                    <div class="mb-3">
                        <a href="<?php echo $base; ?>/index.php" class="d-flex align-items-center text-decoration-none">
                             <img src="https://res.cloudinary.com/dmnbjskbz/image/upload/v1771605277/sokosafi/logo.png" alt="logo" height="38" class="me-2">
                             <span class="footer-brand h5 fw-bold text-white mb-0">SokoSafi</span>
                        </a>
                    </div>
                    <p class="small text-secondary mb-4">Your destination for premium products and exceptional shopping experiences. Quality and sophistication redefined.</p>
                    <div class="d-flex gap-2">
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle" style="width: 32px; height: 32px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="text-white fw-bold mb-3">Collections</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=products" class="text-secondary text-decoration-none hover-white">All Products</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=featured" class="text-secondary text-decoration-none hover-white">Featured</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=new_arrivals" class="text-secondary text-decoration-none hover-white">New Arrivals</a></li>
                    </ul>
                </div>

                <div class="col-lg-2 col-md-3 col-6">
                    <h6 class="text-white fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=faq" class="text-secondary text-decoration-none hover-white">FAQ</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=shipping" class="text-secondary text-decoration-none hover-white">Shipping</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=returns" class="text-secondary text-decoration-none hover-white">Returns</a></li>
                        <li class="mb-2"><a href="<?php echo $base; ?>/index.php?page=contact" class="text-secondary text-decoration-none hover-white">Contact Us</a></li>
                    </ul>
                </div>

                <div class="col-lg-4 col-md-6">
                    <h6 class="text-white fw-bold mb-3">Get in Touch</h6>
                    <ul class="list-unstyled small text-secondary">
                        <li class="mb-2 d-flex"><i class="fas fa-map-marker-alt mt-1 me-2 text-primary"></i> <span>Nairobi, Kenya</span></li>
                        <li class="mb-2 d-flex"><i class="fas fa-phone mt-1 me-2 text-primary"></i> <span>0758549124</span></li>
                        <li class="mb-2 d-flex"><i class="fas fa-envelope mt-1 me-2 text-primary"></i> <span>support@synora.dev</span></li>
                    </ul>
                </div>
            </div>

            <div class="border-top border-secondary mt-4 pt-4">
                <div class="row align-items-center">
                    <div class="col-12 text-center">
                        <p class="small text-secondary mb-2">&copy; 2026 Synora Systems. All rights reserved.</p>
                        <img src="<?php echo $base; ?>/assets/images/payment-methods.png" alt="Payment Methods" height="24" onerror="this.style.display='none'">
                    </div>
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