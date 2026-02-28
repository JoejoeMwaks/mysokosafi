<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$email = 'josemwaks04@gmail.com';
$name = 'Jose Mwaks';
$password = 'Admin123!'; // A temporary password, can be changed later

// Check if user already exists
$existing_user = get_user_by_email($email);

if ($existing_user) {
    echo "User already exists with ID: " . $existing_user['id'] . "<br>\n";
    $user_id = $existing_user['id'];

    // Force update password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    if ($stmt->execute([$hashed_password, $user_id])) {
        echo "Password successfully updated to: " . htmlspecialchars($password) . "<br>\n";
        echo "Bcrypt Hash: " . htmlspecialchars($hashed_password) . "<br>\n";
    }
    else {
        echo "Failed to update password.<br>\n";
    }

}
else {
    // Create the user
    $user_id = create_user($name, $email, $password);
    if ($user_id) {
        echo "User created successfully with ID: $user_id<br>\n";

        // Print the bcrypt hash that was generated during creation
        $new_user = get_user_by_email($email);
        echo "Password successfully set to: " . htmlspecialchars($password) . "<br>\n";
        echo "Bcrypt Hash: " . htmlspecialchars($new_user['password']) . "<br>\n";
    }
    else {
        echo "Failed to create user.<br>\n";
        exit(1);
    }
}

// Assign admin role
$success = assign_user_role_by_name($user_id, 'admin');

if ($success) {
    echo "Admin role assigned successfully to user ID: $user_id<br>\n";
}
else {
    // Check if the user already has the role
    if (user_has_role($user_id, 'admin')) {
        echo "User already has the admin role.<br>\n";
    }
    else {
        echo "Failed to assign admin role.<br>\n";
        exit(1);
    }
}
