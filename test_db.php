<?php
// test_db.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Test connection
    $pdo->query("SELECT 1");
    echo "Database connection successful!<br>";

    // Show database name
    $stmt = $pdo->query("SELECT DATABASE()");
    $dbname = $stmt->fetchColumn();
    echo "Current database: " . $dbname . "<br>";

    // Check admins table
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() > 0) {
        echo "Admins table exists!<br>";
        
        // Show table structure
        $stmt = $pdo->query("DESCRIBE admins");
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        while ($row = $stmt->fetch()) {
            print_r($row);
        }
        echo "</pre>";
    } else {
        echo "Admins table does not exist!<br>";
        
        // Create the table
        $sql = "CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $pdo->exec($sql);
        echo "Admins table created successfully!<br>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}
?>