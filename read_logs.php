<?php
$log_file = __DIR__ . '/logs/error.log';
if (file_exists($log_file)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($log_file)) . "</pre>";
}
else {
    echo "No log file found.";
}
?>
