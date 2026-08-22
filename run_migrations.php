<?php
require_once __DIR__ . '/config/db.php';

echo "<h2>Production Database Migration Tool</h2>";

if (!isset($pdo) || $pdo === null) {
    echo "<h3 style='color:red;'>❌ Database connection failed. Cannot run migrations.</h3>";
    
    // Attempt manual connection to grab the exact error
    try {
        $host = getenv('MYSQLHOST');
        $port = getenv('MYSQLPORT');
        $db = getenv('MYSQLDATABASE');
        $user = getenv('MYSQLUSER');
        $pass = getenv('MYSQLPASSWORD');
        
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5];
        
        if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }
        
        new PDO($dsn, $user, $pass, $options);
    } catch (Throwable $e) {
        echo "<div style='background:#fee; padding:15px; border:1px solid #f99; margin:20px 0;'>";
        echo "<strong>Detailed Error:</strong> " . htmlspecialchars($e->getMessage()) . "<br><br>";
        echo "<strong>Check your Render Environment Variables:</strong><br>";
        echo "MYSQLHOST: " . htmlspecialchars($host ?: '(empty)') . "<br>";
        echo "MYSQLDATABASE: " . htmlspecialchars($db ?: '(empty)') . "<br>";
        echo "MYSQLUSER: " . htmlspecialchars($user ?: '(empty)') . "<br>";
        echo "MYSQLPORT: " . htmlspecialchars($port ?: '(empty)') . "<br>";
        echo "</div>";
    }
    die();
}

try {
    // 1. Run Schema
    $schemaFile = __DIR__ . '/config/schema.sql';
    if (file_exists($schemaFile)) {
        echo "<p>Running schema.sql...</p>";
        $schemaSql = file_get_contents($schemaFile);
        $pdo->exec($schemaSql);
        echo "<p style='color:green;'>✅ Schema created successfully!</p>";
    } else {
        echo "<p style='color:red;'>❌ schema.sql not found in config folder.</p>";
    }

    // 2. Run Seed
    $seedFile = __DIR__ . '/config/seed.sql';
    if (file_exists($seedFile)) {
        echo "<p>Running seed.sql...</p>";
        $seedSql = file_get_contents($seedFile);
        $pdo->exec($seedSql);
        echo "<p style='color:green;'>✅ Data seeded successfully!</p>";
    } else {
        echo "<p style='color:red;'>❌ seed.sql not found in config folder.</p>";
    }

    echo "<h3>🎉 Migration Complete!</h3>";
    echo "<p><a href='/'>Go to Home Page</a></p>";

    // Optional: Delete this file for security
    // unlink(__FILE__);
} catch (Exception $e) {
    echo "<h3 style='color:red;'>⚠️ Migration failed!</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
