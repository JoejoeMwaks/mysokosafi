<?php
// Simple PDO configuration. Update credentials to match your local MySQL.

if (file_exists(__DIR__ . '/config.local.php')) {
    include_once __DIR__ . '/config.local.php';
}

if (!defined('DB_HOST'))
    define('DB_HOST', getenv('MYSQLHOST') ?: 'localhost');
if (!defined('DB_NAME'))
    define('DB_NAME', getenv('MYSQLDATABASE') ?: 'ecommerce_db');
if (!defined('DB_USER'))
    define('DB_USER', getenv('MYSQLUSER') ?: 'root');
if (!defined('DB_PASS'))
    // Hosted environments might use an empty or zero password, so we check carefully
    define('DB_PASS', getenv('MYSQLPASSWORD') !== false ? getenv('MYSQLPASSWORD') : '');
if (!defined('DB_PORT'))
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');

$dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // Fallback quickly if connection hangs
    ];
    
    // Only add SSL attributes if we're not on localhost and the certificate path exists
    // This prevents connection failures on local Windows XAMPP environments
    $cert_path = '/etc/ssl/certs/ca-certificates.crt';
    if (DB_HOST !== 'localhost' && DB_HOST !== '127.0.0.1' && file_exists($cert_path)) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $cert_path;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
}
catch (Throwable $e) {
    // In dev, show a friendly message; in prod, log this instead.
    $pdo = null;
    error_log('DB connection failed: ' . $e->getMessage());
    // For local debugging, we can optionally echo the error if on localhost
    if (DB_HOST === 'localhost') {
        echo '<!-- DB Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
    }
}