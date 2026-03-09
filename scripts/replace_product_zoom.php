<?php
$product_file = __DIR__ . '/../pages/product.php';
$content = file_get_contents($product_file);

// 1. Replace the HTML
$html_old = <<<EOT
                    <div id="image-zoom-container" class="main-image position-relative border rounded-3 p-2 bg-white" style="height: 400px; display: flex; align-items: center; justify-content: center; overflow: hidden; cursor: crosshair;">
                        <?php 
                        \$main_img_url = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                        if (!empty(\$product_images)) {
                            \$main_img_url = htmlspecialchars(\$product_images[0]['file_path']);
                        } elseif (\$img = resolve_product_image(\$product)) {
                            \$main_img_url = htmlspecialchars(\$img);
                        }
                        ?>
                        <img src="<?php echo \$main_img_url; ?>" 
                             alt="<?php echo htmlspecialchars(\$product['name']); ?>"
                             id="mainProductImage"
                             class="img-fluid product-image zoom-image"
                             style="max-height: 100%; object-fit: contain; width: 100%; border-radius: 8px; transition: transform 0.3s ease-out, transform-origin 0.3s ease-out;"
                             onerror="this.src='https://dummyimage.com/800x800/e0e0e0/636363.jpg&text=No+Image'">
                    </div>
EOT;

$html_new = <<<EOT
                    <div id="image-zoom-container" class="main-image position-relative border rounded-3 p-2 bg-white" style="height: 400px; display: flex; align-items: center; justify-content: center; cursor: crosshair; user-select: none;">
                        <?php 
                        \$main_img_url = 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80';
                        if (!empty(\$product_images)) {
                            \$main_img_url = htmlspecialchars(\$product_images[0]['file_path']);
                        } elseif (\$img = resolve_product_image(\$product)) {
                            \$main_img_url = htmlspecialchars(\$img);
                        }
                        ?>
                        <div id="zoom-lens" style="position: absolute; border: 1px solid #d4d4d4; background: rgba(255, 255, 255, 0.4); display: none; pointer-events: none; z-index: 10;"></div>
                        <img src="<?php echo \$main_img_url; ?>" 
                             alt="<?php echo htmlspecialchars(\$product['name']); ?>"
                             id="mainProductImage"
                             class="img-fluid product-image"
                             style="max-height: 100%; object-fit: contain; width: 100%; border-radius: 8px;"
                             onerror="this.src='https://dummyimage.com/800x800/e0e0e0/636363.jpg&text=No+Image'">
                        <div id="zoom-window" class="border rounded-3 bg-white" style="position: absolute; left: calc(100% + 1.5rem); top: 0; width: 100%; height: 500px; background-repeat: no-repeat; display: none; z-index: 1050; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);"></div>
                    </div>
EOT;

// 2. Replace CSS
$css_old = <<<EOT
.product-image:hover {
    transform: scale(1.02);
}
EOT;

$css_new = <<<EOT
EOT;

// 3. Replace JS
$js_old = <<<EOT
// Hover zoom effect for the main product image
const zoomContainer = document.getElementById('image-zoom-container');
const zoomImage = document.getElementById('mainProductImage');

if (zoomContainer && zoomImage) {
    // Only apply on desktop
    zoomContainer.addEventListener('mousemove', function(e) {
        if (window.innerWidth >= 768) {
            const bounds = zoomContainer.getBoundingClientRect();
            let x = e.clientX - bounds.left;
            let y = e.clientY - bounds.top;
            
            // Calculate percentage
            let xPercent = (x / bounds.width) * 100;
            let yPercent = (y / bounds.height) * 100;
            
            // Apply zoom scaling and origin
            zoomImage.style.transformOrigin = `\${xPercent}% \${yPercent}%`;
            zoomImage.style.transform = 'scale(2.5)';
        }
    });
    
    zoomContainer.addEventListener('mouseleave', function() {
        if (window.innerWidth >= 768) {
            zoomImage.style.transformOrigin = 'center center';
            zoomImage.style.transform = 'scale(1)';
        }
    });

    // Reset zoom on resize
    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            zoomImage.style.transformOrigin = 'center center';
            zoomImage.style.transform = 'scale(1)';
        }
    });
}
EOT;

$js_new = <<<EOT
// Hover zoom effect for the main product image (Amazon style)
const zoomContainer = document.getElementById('image-zoom-container');
const zoomImage = document.getElementById('mainProductImage');
const zoomLens = document.getElementById('zoom-lens');
const zoomWindow = document.getElementById('zoom-window');

if (zoomContainer && zoomImage && zoomLens && zoomWindow) {
    const ratio = 2.0; // Zoom magnification ratio
    let isHovering = false;
    
    zoomContainer.addEventListener('mouseenter', function() {
        if (window.innerWidth >= 992) {
            isHovering = true;
            zoomLens.style.display = 'block';
            zoomWindow.style.display = 'block';
            zoomWindow.style.backgroundImage = `url('\${zoomImage.src}')`;
            
            const imgBounds = zoomImage.getBoundingClientRect();
            
            // Lens size relates to the original image dimensions vs zoom window dimensions
            const lensWidth = zoomWindow.offsetWidth / ratio;
            const lensHeight = zoomWindow.offsetHeight / ratio;
            
            zoomLens.style.width = lensWidth + 'px';
            zoomLens.style.height = lensHeight + 'px';
            
            const realImgWidth = imgBounds.width;
            const realImgHeight = imgBounds.height;
            zoomWindow.style.backgroundSize = `\${realImgWidth * ratio}px \${realImgHeight * ratio}px`;
        }
    });

    zoomContainer.addEventListener('mousemove', function(e) {
        if (isHovering && window.innerWidth >= 992) {
            const bounds = zoomContainer.getBoundingClientRect();
            const imgBounds = zoomImage.getBoundingClientRect();
            
            let x = e.clientX - imgBounds.left;
            let y = e.clientY - imgBounds.top;
            
            let lensX = x - (zoomLens.offsetWidth / 2);
            let lensY = y - (zoomLens.offsetHeight / 2);
            
            // Clamp lens
            if (lensX < 0) lensX = 0;
            if (lensY < 0) lensY = 0;
            if (lensX > imgBounds.width - zoomLens.offsetWidth) lensX = imgBounds.width - zoomLens.offsetWidth;
            if (lensY > imgBounds.height - zoomLens.offsetHeight) lensY = imgBounds.height - zoomLens.offsetHeight;
            
            // The lens position inside the container vs the image position inside the container
            let offsetX = imgBounds.left - bounds.left;
            let offsetY = imgBounds.top - bounds.top;
            
            zoomLens.style.left = (lensX + offsetX) + 'px';
            zoomLens.style.top = (lensY + offsetY) + 'px';
            
            // Pan zoom window
            zoomWindow.style.backgroundPosition = `-\${lensX * ratio}px -\${lensY * ratio}px`;
        }
    });
    
    zoomContainer.addEventListener('mouseleave', function() {
        isHovering = false;
        zoomLens.style.display = 'none';
        zoomWindow.style.display = 'none';
    });
}
EOT;

// Replace using normal whitespace normalization to avoid trailing space mismatch
$content = str_replace(str_replace("\r\n", "\n", $html_old), $html_new, $content);
$content = str_replace($html_old, $html_new, $content);
$content = str_replace(str_replace("\r\n", "\n", $css_old), $css_new, $content);
$content = str_replace($css_old, $css_new, $content);
$content = str_replace(str_replace("\r\n", "\n", $js_old), $js_new, $content);
$content = str_replace($js_old, $js_new, $content);

file_put_contents($product_file, $content);
echo "Patch applied!\n";
?>
