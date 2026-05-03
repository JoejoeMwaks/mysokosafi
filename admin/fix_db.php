<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

if (!db_has_connection()) {
    die("Database connection failed.");
}

try {
    // Check if warranty column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'warranty'");
    $exists = $stmt->fetch();

    if (!$exists) {
        echo "Adding 'warranty' column to 'products' table...<br>";
        $pdo->exec("ALTER TABLE products ADD COLUMN warranty VARCHAR(255) DEFAULT NULL AFTER stock");
        echo "Column 'warranty' added successfully.<br>";
    } else {
        echo "Column 'warranty' already exists.<br>";
    }

    // Also check for sale_price just in case
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'sale_price'");
    $exists = $stmt->fetch();
    if (!$exists) {
        echo "Adding 'sale_price' column to 'products' table...<br>";
        $pdo->exec("ALTER TABLE products ADD COLUMN sale_price DECIMAL(10,2) DEFAULT NULL AFTER price");
        echo "Column 'sale_price' added successfully.<br>";
    }

    // Check for is_active
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'is_active'");
    $exists = $stmt->fetch();
    if (!$exists) {
        echo "Adding 'is_active' column to 'products' table...<br>";
        $pdo->exec("ALTER TABLE products ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER created_at");
        echo "Column 'is_active' added successfully.<br>";
    }

    // Check if blog_posts table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'blog_posts'");
    $exists = $stmt->fetch();
    if (!$exists) {
        echo "Creating 'blog_posts' table...<br>";
        $pdo->exec("
            CREATE TABLE blog_posts (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                slug VARCHAR(255) NOT NULL UNIQUE,
                content LONGTEXT NOT NULL,
                image_url VARCHAR(255) DEFAULT NULL,
                video_url VARCHAR(255) DEFAULT NULL,
                uploaded_video_url VARCHAR(255) DEFAULT NULL,
                author_id INT UNSIGNED DEFAULT NULL,
                linked_product_id INT UNSIGNED DEFAULT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
                FOREIGN KEY (linked_product_id) REFERENCES products(id) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "Table 'blog_posts' created successfully.<br>";
    } else {
        echo "Table 'blog_posts' already exists.<br>";
        
        // Add new columns if they don't exist
        $stmt = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'linked_product_id'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE blog_posts ADD COLUMN linked_product_id INT UNSIGNED DEFAULT NULL AFTER author_id");
            $pdo->exec("ALTER TABLE blog_posts ADD CONSTRAINT fk_blog_linked_product FOREIGN KEY (linked_product_id) REFERENCES products(id) ON DELETE SET NULL");
            echo "Column 'linked_product_id' added to blog_posts.<br>";
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM blog_posts LIKE 'uploaded_video_url'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE blog_posts ADD COLUMN uploaded_video_url VARCHAR(255) DEFAULT NULL AFTER video_url");
            echo "Column 'uploaded_video_url' added to blog_posts.<br>";
        }
    }

    echo "Database check completed.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
