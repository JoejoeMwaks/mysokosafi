<?php
require_once __DIR__ . '/../config/db.php';
$stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ?');
$stmt->execute([559]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($rows);
