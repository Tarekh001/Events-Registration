<?php
// admin_dashboard.php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch statistics with only active registrations
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM events) as total_events,
        (SELECT COUNT(*) 
         FROM registrations 
         WHERE status = 'active') as total_registrations,
        (SELECT COUNT(*) 
         FROM users 
         WHERE role = 'user') as total_users
")->fetch();

// Fetch events with active registration counts
$stmt = $pdo->query("
    SELECT e.*, 
           COUNT(DISTINCT CASE WHEN r.status = 'active' THEN r.id END) as registration_count
    FROM events e 
    LEFT JOIN registrations r ON e.id = r.event_id
    WHERE e.date >= CURDATE()
    GROUP BY e.id
    ORDER BY e.date ASC
");
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-gray-800">Admin Dashboard</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="create_event.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                        Create Event
                    </a>
                    <a href="manage_events.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Manage Events
                    </a>
                    <a href="create_admin.php" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg">
                        Create Admin
                    </a>
                    <a href="manage_users.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
                        Manage Users
                    </a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Statistics Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700">Total Events</h3>
                <p class="text-3xl font-bold text-blue-600"><?php echo $stats['total_events']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700">Total Registrations</h3>
                <p class="text-3xl font-bold text-green-600"><?php echo $stats['total_registrations']; ?></p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-700">Total Users</h3>
                <p class="text-3xl font-bold text-purple-600"><?php echo $stats['total_users']; ?></p>
            </div>
        </div>

        <!-- Events List -->
        <h2 class="text-2xl font-bold mb-6">Upcoming Events</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php if ($event['banner_path']): ?>
                        <img src="<?php echo htmlspecialchars($event['banner_path']); ?>" 
                             alt="<?php echo htmlspecialchars($event['name']); ?>"
                             class="w-full h-48 object-cover">
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl font-bold"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <span class="px-2 py-1 text-sm rounded-full <?php 
                                echo $event['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                    ($event['status'] === 'closed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Date:</span> 
                                <?php echo date('F d, Y', strtotime($event['date'])); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Time:</span>
                                <?php echo date('h:i A', strtotime($event['time'])); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Location:</span>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-semibold">Registrations:</span>
                                <?php echo $event['registration_count']; ?>/<?php echo $event['max_participants']; ?>
                            </p>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                               class="text-blue-500 hover:text-blue-700">
                                Edit Event
                            </a>
                            <a href="view_registrations.php?event_id=<?php echo $event['id']; ?>" 
                               class="text-green-500 hover:text-green-700">
                                View Registrations
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($events)): ?>
            <div class="text-center py-8">
                <p class="text-gray-500">No events found.</p>
                <a href="create_event.php" class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                    Create your first event
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="bg-white mt-12 py-6">
        <div class="max-w-7xl mx-auto px-4">
            <p class="text-center text-gray-600">
                &copy; <?php echo date('Y'); ?> JMK48
            </p>
        </div>
    </footer>
</body>
</html>