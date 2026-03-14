<?php
$file = 'c:/xampp/htdocs/sokosafi/pages/product.php';
$content = file_get_contents($file);

// 1. Rename product-image class for the main image
$content = str_replace(
    'class="img-fluid product-image"',
    'class="img-fluid main-product-image"',
    $content
);

// 2. Improve zoom window style
$content = str_replace(
    'id="zoom-window" class="border rounded-3 bg-white" style="position: absolute; left: calc(100% + 1.5rem); top: 0; width: 100%; height: 500px; background-repeat: no-repeat; display: none; z-index: 1050; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); image-rendering: auto;"',
    'id="zoom-window" class="border rounded-3 shadow-lg" style="position: absolute; left: calc(100% + 1.5rem); top: 0; width: 100%; height: 500px; background-color: #fff; background-repeat: no-repeat; display: none; z-index: 2000; pointer-events: none; border: 1px solid rgba(0,0,0,0.1) !important;"',
    $content
);

file_put_contents($file, $content);
echo "Successfully updated product.php\n";
?>
