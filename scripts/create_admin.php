<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/db_functions.php';

$email = 'josemwaks04@gmail.com';
$name = 'Jose Mwaks';
$password = 'Admin123!'; // A temporary password, can be changed later

// Check if user already exists
$existing_user = get_user_by_email($email);

if ($existing_user) {
    echo "User already exists with ID: " . $existing_user['id'] . "\n";
    $user_id = $existing_user['id'];
}
else {
    // Create the user
    $user_id = create_user($name, $email, $password);
    if ($user_id) {
        echo "User created successfully with ID: $user_id\n";
    }
    else {
        echo "Failed to create user.\n";
        exit(1);
    }
}

// Assign admin role
$success = assign_user_role_by_name($user_id, 'admin');

if ($success) {
    echo "Admin role assigned successfully to user ID: $user_id\n";
}
else {
    // Check if the user already has the role
    if (user_has_role($user_id, 'admin')) {
        echo "User already has the admin role.\n";
    }
    else {
        echo "Failed to assign admin role.\n";
        exit(1);
    }
}
