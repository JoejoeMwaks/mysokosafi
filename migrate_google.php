<?php
require_once __DIR__ . '/config/db.php';
try {
    // Add google_id
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'google_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN google_id VARCHAR(255) DEFAULT NULL");
        echo "Added google_id column.<br>";
    }
    else {
        echo "google_id column already exists.<br>";
    }

    echo "<b>Migration Complete! You can now use Google Login!</b>";
}
catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
