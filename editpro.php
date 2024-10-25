<?php
// editpro.php
require_once 'includes/header.php';
requireLogin();

$error = '';
$success = '';

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $current_password = trim($_POST['current_password']);
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        // Basic validation
        if (empty($name) || empty($email) || empty($current_password)) {
            throw new Exception('Required fields cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Verify current password
        if (!password_verify($current_password, $user['password'])) {
            throw new Exception('Current password is incorrect');
        }

        // Check if email exists (if changed)
        if ($email !== $user['email']) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            if ($stmt->rowCount() > 0) {
                throw new Exception('Email is already in use');
            }
        }

        // Update query parts
        $updateFields = [];
        $params = [];

        // Always update name and email
        $updateFields[] = "name = ?";
        $updateFields[] = "email = ?";
        $params[] = $name;
        $params[] = $email;

        // If new password is provided, validate and update it
        if (!empty($new_password)) {
            if (strlen($new_password) < 8) {
                throw new Exception('New password must be at least 8 characters long');
            }
            if ($new_password !== $confirm_password) {
                throw new Exception('New passwords do not match');
            }
            $updateFields[] = "password = ?";
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }

        // Add user ID to params
        $params[] = $_SESSION['user_id'];

        // Update user data
        $sql = "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute($params)) {
            // Update session data
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $success = "Profile updated successfully!";
        } else {
            throw new Exception('Failed to update profile');
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Edit Profile</h1>
                    <a href="profile.php" class="text-blue-500 hover:text-blue-700">← Back to Profile</a>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($user['name']); ?>" 
                               required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" 
                               required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>

                    <!-- Current Password Field -->
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               required 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <p class="mt-1 text-sm text-gray-500">Required to make any changes</p>
                    </div>

                    <!-- New Password Field -->
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                        <p class="mt-1 text-sm text-gray-500">Leave blank to keep current password</p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-2">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <a href="profile.php" 
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>