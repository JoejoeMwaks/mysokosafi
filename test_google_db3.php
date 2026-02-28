<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/db_functions.php';

echo "Testing create_google_user with NEW random email...<br>";

try {
    $email = "new_random_user_" . time() . "@example.com";
    $first_name = "New";
    $last_name = "User";
    $google_id = "test_google_id_" . time();

    // Completely new user to trigger create_google_user
    $new_id = create_google_user($email, $first_name, $last_name, $google_id);
    if ($new_id) {
        echo "SUCCESS! Created user ID: " . $new_id . "<br>";
    }
    else {
        echo "FAILED TO CREATE GOOGLE USER!<br>";

        // Let's run it directly to see the PDOException
        $pdo->beginTransaction();
        $random_password = bin2hex(random_bytes(16));
        $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('INSERT INTO users (email, password, first_name, last_name, google_id) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$email, $hashed_password, $first_name, $last_name, $google_id]);
        $user_id = $pdo->lastInsertId();

        $role_stmt = $pdo->prepare('SELECT id FROM roles WHERE name = "customer" LIMIT 1');
        $role_stmt->execute();
        $role_id = $role_stmt->fetchColumn();

        if ($role_id) {
            $ur_stmt = $pdo->prepare('INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)');
            $ur_stmt->execute([$user_id, $role_id]);
        }

        $pdo->commit();
        echo "Wait, running it directly worked?! Target ID: " . $user_id;
    }

}
catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "PDO EXCEPTION: " . $e->getMessage() . "<br>";
}
catch (Exception $e) {
    echo "GENERAL EXCEPTION: " . $e->getMessage() . "<br>";
}
?>
