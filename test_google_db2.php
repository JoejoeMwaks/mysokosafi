<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/db_functions.php';

echo "Testing create_google_user with mwakaijoseph@gmail.com...<br>";

try {
    $email = "mwakaijoseph@gmail.com";
    $first_name = "Joseph";
    $last_name = "Mwakai";
    $google_id = "test_google_id_" . time(); // fake id just to see if insert works

    // Let's first check if the user exists
    $existing = get_user_by_email($email);
    if ($existing) {
        echo "USER ALREADY EXISTS WITH EMAIL: " . $email . " | ID: " . $existing['id'] . " | GOOGLE ID: " . ($existing['google_id'] ?? 'NULL') . "<br>";

        // Try linking
        $link_res = link_google_id_to_user($existing['id'], $google_id);
        echo "Link result: " . ($link_res ? "SUCCESS" : "FAILED") . "<br>";

        // Let's print out what get_user_by_google_id returns
        $re_fetch = get_user_by_google_id($google_id);
        if (!$re_fetch) {
            echo "RE-FETCH FAILED! The user might be inactive. is_active=" . ($existing['is_active'] ?? 'UNKNOWN') . "<br>";
        }
        else {
            echo "RE-FETCH SUCCESS! User ID: " . $re_fetch['id'] . "<br>";
        }
    }
    else {
        echo "User does NOT exist by email. Trying to create...<br>";

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
        echo "SUCCESS! Created user ID: " . $user_id . "<br>";
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
