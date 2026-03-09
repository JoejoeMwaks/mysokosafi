<?php
$file = __DIR__ . '/../admin/add_product.php';
$content = file_get_contents($file);

$search1 = <<<'EOT'
            // Auto-generate slug if empty
            if ($slug === '') {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            
            try {
EOT;

$replace1 = <<<'EOT'
            // Auto-generate slug if empty
            if ($slug === '') {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
            }
            
            // Ensure unique slug
            $original_slug = $slug;
            $counter = 1;
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
            while (true) {
                $stmtCheck->execute([$slug]);
                if ($stmtCheck->fetchColumn() > 0) {
                    $slug = $original_slug . '-' . $counter;
                    $counter++;
                } else {
                    break;
                }
            }
            
            try {
EOT;

if (strpos($content, 'Ensure unique slug') === false) {
    if (strpos($content, $search1) !== false) {
        $content = str_replace($search1, $replace1, $content);
        file_put_contents($file, $content);
        echo "add_product.php patched successfully.\n";
    }
    else {
        $search2 = str_replace("\r\n", "\n", $search1);
        if (strpos($content, $search2) !== false) {
            $content = str_replace($search2, $replace1, $content);
            file_put_contents($file, $content);
            echo "add_product.php patched successfully (LF).\n";
        }
        else {
            echo "Search block not found in add_product.php.\n";
        }
    }
}
else {
    echo "add_product.php already patched.\n";
}

$file2 = __DIR__ . '/../admin/edit_product.php';
$content2 = file_get_contents($file2);

$search_edit = <<<'EOT'
    $description = trim($_POST['description'] ?? '');
    try {
EOT;

$replace_edit = <<<'EOT'
    $description = trim($_POST['description'] ?? '');
    
    // Ensure unique slug
    if ($slug === '') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
    $original_slug = $slug;
    $counter = 1;
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?");
    while (true) {
        $stmtCheck->execute([$slug, $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        } else {
            break;
        }
    }
    
    try {
EOT;

if (strpos($content2, 'Ensure unique slug') === false) {
    if (strpos($content2, $search_edit) !== false) {
        $content2 = str_replace($search_edit, $replace_edit, $content2);
        file_put_contents($file2, $content2);
        echo "edit_product.php patched successfully.\n";
    }
    else {
        $search_edit2 = str_replace("\r\n", "\n", $search_edit);
        if (strpos($content2, $search_edit2) !== false) {
            $content2 = str_replace($search_edit2, $replace_edit, $content2);
            file_put_contents($file2, $content2);
            echo "edit_product.php patched successfully (LF).\n";
        }
        else {
            echo "Search block not found in edit_product.php.\n";
        }
    }
}
else {
    echo "edit_product.php already patched.\n";
}
