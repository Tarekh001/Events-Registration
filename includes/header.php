<?php
// includes/header.php
session_start();
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Event Registration System'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="bg-<?php echo $_SESSION['message_type'] === 'error' ? 'red' : 'green'; ?>-100 border border-<?php echo $_SESSION['message_type'] === 'error' ? 'red' : 'green'; ?>-400 text-<?php echo $_SESSION['message_type'] === 'error' ? 'red' : 'green'; ?>-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['message']; ?></span>
        </div>
    <?php
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    endif; ?>

    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex space-x-7">
                    <div class="flex items-center py-4">
                        <span class="font-semibold text-gray-500 text-lg">
                            <?php echo isAdmin() ? 'Admin Dashboard' : 'Event Registration'; ?>
                        </span>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <?php if (isAdmin()): ?>
                        <a href="manage_events.php" class="py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">Manage Events</a>
                        <a href="manage_users.php" class="py-2 px-4 bg-green-500 text-white rounded hover:bg-green-600">Manage Users</a>
                        <a href="create_admin.php" class="py-2 px-4 bg-purple-500 text-white rounded hover:bg-purple-600">Create Admin</a>
                    <?php else: ?>
                        <a href="test2.php" class="py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">Available Events</a>
                        <a href="regevent.php" class="py-2 px-4 bg-green-500 text-white rounded hover:bg-green-600">My Events</a>
                        <a href="profile.php" class="py-2 px-4 bg-purple-500 text-white rounded hover:bg-purple-600">Profile</a>
                    <?php endif; ?>
                    <a href="login.php?logout=1" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>
</body>
</html>