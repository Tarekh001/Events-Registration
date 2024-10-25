<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch all events
$stmt = $pdo->query("
    SELECT e.*, 
           COUNT(DISTINCT r.id) as registration_count
    FROM events e 
    LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'active'
    GROUP BY e.id
    ORDER BY e.date DESC
");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg mb-8">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex items-center py-4">
                    <a href="admin_dashboard.php" class="text-gray-500 hover:text-gray-700">← Back to Dashboard</a>
                </div>
                <div class="flex items-center">
                    <a href="create_event.php" class="py-2 px-4 bg-green-500 text-white rounded hover:bg-green-600">Create New Event</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Manage Events</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrations</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <?php if ($event['banner_path']): ?>
                                            <img class="h-10 w-10 rounded-full object-cover mr-3" 
                                                 src="<?php echo htmlspecialchars($event['banner_path']); ?>" 
                                                 alt="">
                                        <?php endif; ?>
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($event['name']); ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo date('M d, Y', strtotime($event['date'])); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo date('h:i A', strtotime($event['time'])); ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $event['registration_count']; ?>/<?php echo $event['max_participants']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $event['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                            ($event['status'] === 'closed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                                       class="text-blue-600 hover:text-blue-900 mr-4">Edit</a>
                                    <a href="view_registrations.php?event_id=<?php echo $event['id']; ?>" 
                                       class="text-green-600 hover:text-green-900 mr-4">View Registrations</a>
                                    <button onclick="deleteEvent(<?php echo $event['id']; ?>)" 
                                            class="text-red-600 hover:text-red-900">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                No events found. <a href="create_event.php" class="text-blue-500 hover:text-blue-700">Create one now</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteEvent(eventId) {
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
            fetch('delete_event.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'event_id=' + eventId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error deleting event: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
        }
    }
    </script>
</body>
</html>