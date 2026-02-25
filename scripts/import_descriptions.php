<?php
// scripts/import_descriptions.php
require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];

    if (is_uploaded_file($file)) {
        $handle = fopen($file, "r");
        if ($handle !== FALSE) {
            // Read the first row (headers) to ignore it
            fgetcsv($handle);

            $stmt = $pdo->prepare("UPDATE products SET description = ? WHERE id = ?");
            $updated_count = 0;

            $pdo->beginTransaction();
            try {
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    // Based on our export format: column 0 is ID, column 4 is Description
                    $product_id = isset($data[0]) ? trim($data[0]) : '';
                    $description = isset($data[4]) ? trim($data[4]) : '';

                    if (is_numeric($product_id) && $description !== '') {
                        $stmt->execute([$description, $product_id]);
                        if ($stmt->rowCount() > 0) {
                            $updated_count++;
                        }
                    }
                }
                $pdo->commit();
                $message = "Successfully updated {$updated_count} product descriptions!";
            }
            catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database Error: " . $e->getMessage();
            }
            fclose($handle);
        }
        else {
            $error = "Could not read the uploaded file.";
        }
    }
    else {
        $error = "No file uploaded or file upload failed.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Product Descriptions | SokoSafi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 600px;">
        <div class="text-center mb-4">
            <h2 class="fw-bold">Bulk Description Updater</h2>
            <p class="text-muted">Upload your edited `sokosafi_product_descriptions.csv` file.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success fw-medium"><?php echo htmlspecialchars($message); ?></div>
        <?php
endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger fw-medium"><?php echo htmlspecialchars($error); ?></div>
        <?php
endif; ?>

        <div class="card p-4">
            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-semibold">Select CSV File</label>
                    <input class="form-control" type="file" name="csv_file" accept=".csv" required>
                    <div class="form-text mt-2">Ensure you use the exact format exported from `export_descriptions.php`. Do not re-order columns.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Update Descriptions</button>
            </form>
        </div>
        
        <div class="text-center mt-4">
            <a href="../index.php?page=home" class="text-decoration-none text-muted">&larr; Back to Website</a>
        </div>
    </div>
</body>
</html>
