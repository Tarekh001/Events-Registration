<?php
// test_upload.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$upload_dirs = [
    'uploads/',
    'uploads/images/',
    'uploads/banners/'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "Created directory: " . $dir . "<br>";
        } else {
            echo "Failed to create directory: " . $dir . "<br>";
        }
    } else {
        echo "Directory exists: " . $dir . "<br>";
        echo "Permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . "<br>";
    }
    
    // Test if directory is writable
    if (is_writable($dir)) {
        echo "Directory is writable: " . $dir . "<br>";
    } else {
        echo "Directory is NOT writable: " . $dir . "<br>";
    }
    echo "<hr>";
}
?>