<?php
// reset_admin.php
require_once 'config.php';

try {
    // Delete existing admin account
    $stmt = $pdo->prepare("DELETE FROM users WHERE email = ?");
    $stmt->execute(['admin@admin.com']);
    
    // Create new admin password hash
    $password = 'admin123';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new admin account
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        'Admin',
        'admin@admin.com',
        $hash,
        'admin'
    ]);
    
    echo "Admin account reset successfully<br>";
    echo "Email: admin@admin.com<br>";
    echo "Password: admin123<br>";
    echo "Generated hash: " . $hash . "<br>";
    
    // Verify the hash works
    if (password_verify('admin123', $hash)) {
        echo "Password verification test successful<br>";
    } else {
        echo "Password verification test failed<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>