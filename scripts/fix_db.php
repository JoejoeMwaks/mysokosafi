<?php
require_once __DIR__ . '/../config/db.php';

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

    echo "Database check completed.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
