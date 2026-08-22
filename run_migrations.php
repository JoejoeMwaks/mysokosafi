<?php
require_once __DIR__ . '/config/db.php';

echo "<h2>Production Database Migration Tool</h2>";

if (!isset($pdo) || $pdo === null) {
    die("<h3 style='color:red;'>❌ Database connection failed. Cannot run migrations.</h3>");
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
