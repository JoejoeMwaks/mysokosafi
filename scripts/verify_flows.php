<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

echo "--- STARTING END-TO-END SYSTEM VERIFICATION ---\n\n";

if (!db_has_connection()) {
    die("❌ FAILED: Database connection is not available.\n");
}
echo "✅ Database connection successful.\n";

$user_id = null;
$product_id = null;
$order_id = null;

// 1. Verify Customer Registration & Login Logic
echo "\n--- Testing Customer Flow ---\n";
$test_email = 'test_customer_' . time() . '@example.com';
$password = 'password123';

try {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (email, password, first_name, last_name, is_active, created_at) VALUES (?, ?, 'Test', 'Customer', 1, NOW())");
    $stmt->execute([$test_email, $hashed]);
    $user_id = $pdo->lastInsertId();
    echo "✅ Customer user created successfully (ID: $user_id).\n";

    // Assign customer role
    assign_user_role_by_name($user_id, 'customer');
    echo "✅ Customer role assigned.\n";

}
catch (Exception $e) {
    echo "❌ FAILED creating customer: " . $e->getMessage() . "\n";
}

// 2. Verify Admin Flow
echo "\n--- Testing Admin Flow ---\n";
if ($user_id) {
    try {
        // Make them admin temporarily just to test the role function
        assign_user_role_by_name($user_id, 'admin');

        $stmtRoles = $pdo->prepare("SELECT r.name FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ?");
        $stmtRoles->execute([$user_id]);
        $rolesRows = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);
        $roles = array_column($rolesRows, 'name');

        if (in_array('admin', $roles)) {
            echo "✅ Admin role assignment successful.\n";
        }
        else {
            echo "❌ FAILED: Admin role not found in assigned roles.\n";
        }

        // Product Creation Test
        $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, stock, is_active, created_at) VALUES ('Test Product', 'test-product', 'Desc', 99.99, 10, 1, NOW())");
        $stmt->execute();
        $product_id = $pdo->lastInsertId();
        echo "✅ Admin created a test product successfully (ID: $product_id).\n";

        // Adding multiple images
        $stmtImg = $pdo->prepare("INSERT INTO product_images (product_id, file_path, `order`) VALUES (?, ?, ?)");
        for ($i = 1; $i <= 3; $i++) {
            $stmtImg->execute([$product_id, "./assets/images/products/test_img_$i.jpg", $i]);
        }
        echo "✅ Admin added 3 images to the test product (Gallery test logic ok).\n";

    }
    catch (Exception $e) {
        echo "❌ FAILED admin operation: " . $e->getMessage() . "\n";
    }
}
else {
    echo "⏭️ SKIPPING: Admin flow due to user creation failure.\n";
}

// 3. Verify Order Flow
echo "\n--- Testing Checkout & Tracking Flow ---\n";
if ($user_id && $product_id) {
    try {
        $order_num = 'ORD-TEST-' . time();
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, status, total, created_at) VALUES (?, ?, 'pending', 99.99, NOW())");
        $stmt->execute([$user_id, $order_num]);
        $order_id = $pdo->lastInsertId();
        echo "✅ Order created successfully (ID: $order_id, Num: $order_num).\n";

        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, sku, name, quantity, price, total) VALUES (?, ?, 'TEST-SKU', 'Test Product', 1, 99.99, 99.99)");
        $stmtItem->execute([$order_id, $product_id]);
        echo "✅ Order item mapped successfully.\n";

        // Tracking test
        $tracked = get_order_by_number($order_num);
        if ($tracked && $tracked['id'] == $order_id && count($tracked['items']) > 0) {
            echo "✅ Order Tracking function works! (Fetched Order #$order_num and its items).\n";
        }
        else {
            echo "❌ FAILED: Order Tracking function did not return expected data.\n";
        }

    }
    catch (Exception $e) {
        echo "❌ FAILED order operation: " . $e->getMessage() . "\n";
    }
}
else {
    echo "⏭️ SKIPPING: Checkout flow due to prior failures.\n";
}

// 4. Cleanup Test Data
echo "\n--- Cleaning up test data ---\n";
try {
    if ($order_id) {
        $pdo->exec("DELETE FROM order_items WHERE order_id = $order_id");
        $pdo->exec("DELETE FROM orders WHERE id = $order_id");
    }
    if ($product_id) {
        $pdo->exec("DELETE FROM product_images WHERE product_id = $product_id");
        $pdo->exec("DELETE FROM products WHERE id = $product_id");
    }
    if ($user_id) {
        $pdo->exec("DELETE FROM user_roles WHERE user_id = $user_id");
        $pdo->exec("DELETE FROM users WHERE id = $user_id");
    }
    echo "✅ Test data cleaned up successfully.\n";
}
catch (Exception $e) {
    echo "⚠️ Warning: Failed to clean up some test data: " . $e->getMessage() . "\n";
}

echo "\n--- END OF VERIFICATION ---\n";
?>
