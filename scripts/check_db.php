<?php
require_once __DIR__ . '/../config/db.php';
global $pdo;
$stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = 554');
$stmt->execute();
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
