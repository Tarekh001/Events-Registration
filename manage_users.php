<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch users with their active registration counts and event names
$stmt = $pdo->prepare("
    SELECT 
        u.*,
        COUNT(DISTINCT r.id) as registration_count,
        GROUP_CONCAT(DISTINCT e.name) as registered_events
    FROM users u
    LEFT JOIN registrations r ON u.id = r.user_id AND r.status = 'active'  -- Only count active registrations
    LEFT JOIN events e ON r.event_id = e.id
    WHERE u.id != ?
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$users = $stmt->fetchAll();

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $userId = (int)$_POST['user_id'];
    try {
        $pdo->beginTransaction();
        
        // Delete user's registrations first
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Then delete the user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->execute([$userId]);
        
        $pdo->commit();
        header('Location: manage_users.php');
        exit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div class="flex items-center">
                <a href="admin_dashboard.php" class="text-gray-600 hover:text-gray-900">← Back to Dashboard</a>
            </div>
            <a href="create_admin.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Create New Admin
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold">Manage Users</h2>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($user['registration_count'] > 0): ?>
                                    <div>
                                        <?php echo $user['registration_count']; ?> events
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($user['registered_events']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500">0 events</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" 
                                                name="delete_user" 
                                                class="text-red-600 hover:text-red-900">
                                            Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>