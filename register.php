<?php
// register.php with debug info
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_SESSION['admin_id'])) {
    header('Location: admin_dashboard.php');
    exit();
}

require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<div style='background-color: #f0f0f0; padding: 10px; margin: 10px;'>";
    echo "<h3>Debug Information:</h3>";
    
    // Sanitize and get input
    $name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    echo "Received data:<br>";
    echo "Name: " . $name . "<br>";
    echo "Email: " . $email . "<br>";
    echo "Password length: " . strlen($password) . "<br>";
    
    // Validate input
    if (empty($name)) {
        $error = "Name is required";
        echo "Error: Name is empty<br>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
        echo "Error: Invalid email format<br>";
    } elseif (empty($password)) {
        $error = "Password is required";
        echo "Error: Password is empty<br>";
    } else {
        try {
            echo "Attempting database operations...<br>";
            
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
            echo "Email check query prepared<br>";
            
            $stmt->execute([$email]);
            echo "Email check query executed<br>";
            
            $count = $stmt->fetchColumn();
            echo "Existing email count: " . $count . "<br>";
            
            if ($count > 0) {
                $error = "Email already registered";
                echo "Error: Email already exists<br>";
            } else {
                // Create new admin account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                echo "Password hashed successfully<br>";
                
                $stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
                echo "Insert statement prepared<br>";
                
                try {
                    $result = $stmt->execute([$name, $email, $hashed_password]);
                    echo "Execute result: " . ($result ? "true" : "false") . "<br>";
                    
                    if ($result) {
                        $success = "Registration successful! You can now login.";
                        echo "Success: User registered<br>";
                    } else {
                        $error = "Failed to create account. Please try again.";
                        echo "Error info:<br>";
                        print_r($stmt->errorInfo());
                    }
                } catch (PDOException $ex) {
                    echo "Execute error: " . $ex->getMessage() . "<br>";
                    $error = "Database error during insert";
                }
            }
        } catch (PDOException $e) {
            $error = "Registration failed. Please try again.";
            echo "Database error: " . $e->getMessage() . "<br>";
            echo "Error code: " . $e->getCode() . "<br>";
        }
    }
    echo "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration - Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h1 class="text-2xl font-bold mb-6 text-center">Admin Registration</h1>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                    <p class="mt-2 text-sm">
                        <a href="index.php" class="text-blue-500 hover:text-blue-700">Click here to login</a>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="name"
                           type="text"
                           name="name"
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                           required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="email"
                           type="email"
                           name="email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           id="password"
                           type="password"
                           name="password"
                           required>
                </div>
                
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full mb-4"
                        type="submit">
                    Register
                </button>
            </form>
            
            <div class="text-center text-sm">
                <span class="text-gray-600">Already have an account?</span>
                <a href="index.php" class="text-blue-500 hover:text-blue-700 ml-1">Login here</a>
            </div>
        </div>
    </div>
</body>
</html>