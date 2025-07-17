<?php
// run this once to insert admin
include '../function.php';
$db = new Database();

// Use hashed password (NEVER plain text!)
$hashed_password = password_hash('admin123', PASSWORD_BCRYPT);

$db->setData(
  "INSERT INTO admin_users (username, password) VALUES (:username, :password)",
  ['username' => 'admin', 'password' => $hashed_password]
);
echo "Admin user created.";
