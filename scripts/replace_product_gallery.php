<?php
$file = 'c:/xampp/htdocs/sokosafi/pages/product.php';
$content = file_get_contents($file);

$target1 = <<<EOT
                <div class="product-gallery">
                    <div class="main-image mb-4">
                        <?php \$img = resolve_product_image(\$product); if (!empty(\$img)): ?>
                            <img src="<?php echo htmlspecialchars(\$img); ?>" 
                                 alt="<?php echo htmlspecialchars(\$product['name']); ?>"
                                 class="img-fluid rounded-3 w-100 product-image"
                                 onerror="this.src='https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'">
                        <?php else: ?>
                            <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" 
                                 alt="Product" 
                                 class="img-fluid rounded-3 w-100 product-image">
                        <?php endif; ?>
                    </div>
                </div>
EOT;

$rep1 = <<<EOT
                <div class="product-gallery d-flex flex-column gap-3">
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
                    <?php if (!empty(\$product_images) && count(\$product_images) > 1): ?>
                    <div class="thumbnails d-flex gap-2 overflow-auto py-2" style="white-space: nowrap;">
                        <?php foreach (\$product_images as \$index => \$image): ?>
                            <div class="thumbnail-wrapper border rounded p-1 cursor-pointer <?php echo \$index === 0 ? 'border-primary' : 'border-secondary'; ?>" 
                                 onclick="changeMainImage('<?php echo htmlspecialchars(\$image['file_path']); ?>', this)" 
                                 style="width: 80px; height: 80px; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center; background: white;">
                                <img src="<?php echo htmlspecialchars(\$image['file_path']); ?>" 
                                     alt="Thumbnail" 
                                     style="max-width: 100%; max-height: 100%; object-fit: contain;"
                                     onerror="this.src='https://dummyimage.com/80x80/e0e0e0/636363.jpg&text=No+Image'">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
EOT;

$target2 = <<<EOT
// Initialize Bootstrap tabs if available
if (typeof bootstrap !== 'undefined') {
    const triggerTabList = [].slice.call(document.querySelectorAll('#productTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
}
</script>
EOT;

$rep2 = <<<EOT
// Initialize Bootstrap tabs if available
if (typeof bootstrap !== 'undefined') {
    const triggerTabList = [].slice.call(document.querySelectorAll('#productTabs button'));
    triggerTabList.forEach(function (triggerEl) {
        var tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });
}

function changeMainImage(url, thumnailEl) {
    document.getElementById('mainProductImage').src = url;
    document.querySelectorAll('.thumbnail-wrapper').forEach(el => {
        el.classList.remove('border-primary');
        el.classList.add('border-secondary');
    });
    thumnailEl.classList.remove('border-secondary');
    thumnailEl.classList.add('border-primary');
}

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
</script>
EOT;

// Normalize line endings
$content = str_replace("\r\n", "\n", $content);
$target1 = str_replace("\r\n", "\n", $target1);
$rep1 = str_replace("\r\n", "\n", $rep1);
$target2 = str_replace("\r\n", "\n", $target2);
$rep2 = str_replace("\r\n", "\n", $rep2);

if (strpos($content, $target1) !== false) {
    echo "Found target 1\n";
    $content = str_replace($target1, $rep1, $content);
}
else {
    echo "NO TARGET 1 FOUND\n";
}

if (strpos($content, $target2) !== false) {
    echo "Found target 2\n";
    $content = str_replace($target2, $rep2, $content);
}
else {
    echo "NO TARGET 2 FOUND\n";
}

file_put_contents($file, $content);
echo "Done replacing.";
?>
