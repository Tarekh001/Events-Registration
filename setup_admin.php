<?php
// setup_admin.php
require_once 'config.php';

try {
    // First check if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() == 0) {
        // Create users table
        $pdo->exec("CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "Users table created successfully<br>";
    }

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute(['admin@admin.com']);
    
    if ($stmt->rowCount() == 0) {
        // Create admin account
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            'Admin',
            'admin@admin.com',
            password_hash('admin123', PASSWORD_DEFAULT),
            'admin'
        ]);
        echo "Admin account created successfully<br>";
    } else {
        echo "Admin account already exists<br>";
    }

    // Show all users
    $users = $pdo->query("SELECT id, email, role FROM users")->fetchAll();
    echo "<br>Current users in database:<br>";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Email: {$user['email']}, Role: {$user['role']}<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>