<?php
session_start();
require_once 'config.php';



// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch available events (status is open and not full)
$stmt = $pdo->query("
    SELECT e.*, 
           COUNT(DISTINCT r.id) as registration_count,
           (SELECT COUNT(*) 
            FROM registrations r2 
            WHERE r2.event_id = e.id 
            AND r2.user_id = " . $_SESSION['user_id'] . " 
            AND r2.status = 'active') as user_registered
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'active'
    WHERE e.status = 'open' 
    AND e.date >= CURDATE()
    GROUP BY e.id
    HAVING registration_count < max_participants
    ORDER BY e.date ASC
");
$events = $stmt->fetchAll();

// Check for registration success message
$registrationMessage = '';
if (isset($_SESSION['registration_success'])) {
    $registrationMessage = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-lg">
        <div class="max-w-6xl mx-auto px-4">
            <div class="flex justify-between">
                <div class="flex items-center py-4">
                    <span class="text-2xl font-bold text-gray-800">Event Registration</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="regevent.php" class="py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600">My Events</a>
                    <a href="profile.php" class="py-2 px-4 bg-green-500 text-white rounded hover:bg-green-600">Profile</a>
                    <a href="logout.php" class="py-2 px-4 bg-red-500 text-white rounded hover:bg-red-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <?php if ($registrationMessage): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($registrationMessage); ?>
            </div>
        <?php endif; ?>

        <h2 class="text-2xl font-bold mb-6">Available Events</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($events)): ?>
                <?php foreach ($events as $event): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <?php if ($event['banner_path']): ?>
                            <img src="<?php echo htmlspecialchars($event['banner_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($event['name']); ?>"
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($event['description'], 0, 150)) . '...'; ?></p>
                            
                            <div class="space-y-2 mb-4">
                                <p class="text-sm text-gray-600">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <?php echo date('F d, Y', strtotime($event['date'])); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <?php echo date('h:i A', strtotime($event['time'])); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <?php echo $event['registration_count']; ?>/<?php echo $event['max_participants']; ?> registered
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <?php if ($event['user_registered']): ?>
                                    <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg">
                                        ✓ Already Registered
                                    </span>
                                <?php else: ?>
                                    <a href="event.php?id=<?php echo $event['id']; ?>" 
                                       class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500">No events available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>