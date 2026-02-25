<?php
// scripts/export_descriptions.php
require_once __DIR__ . '/../config/db.php';

// Force download of a CSV file
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sokosafi_product_descriptions.csv');

$output = fopen('php://output', 'w');

// Write the CSV headers
fputcsv($output, ['Product ID', 'SKU', 'Product Name', 'Category', 'Description']);

// Fetch all products with their categories
$query = "
    SELECT p.id, p.sku, p.name, c.name as category_name, p.description
    FROM products p
    LEFT JOIN product_category pc ON p.id = pc.product_id
    LEFT JOIN categories c ON pc.category_id = c.id
    ORDER BY c.name, p.name
";
$stmt = $pdo->query($query);

// Write data rows
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['sku'],
        $row['name'],
        $row['category_name'] ?? 'Uncategorized',
        $row['description']
    ]);
}

fclose($output);
exit;
