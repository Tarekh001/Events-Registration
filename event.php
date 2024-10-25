<?php
// event.php
require_once 'includes/header.php';
requireLogin();

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch event details with registration count
$stmt = $pdo->prepare("
    SELECT e.*, 
           COUNT(r.id) as registration_count,
           (SELECT COUNT(*) FROM registrations r2 
            WHERE r2.event_id = e.id 
            AND r2.user_id = ? 
            AND r2.status = 'active') as user_registered
    FROM events e
    LEFT JOIN registrations r ON e.id = r.event_id AND r.status = 'active'
    WHERE e.id = ?
    GROUP BY e.id
");
$stmt->execute([$_SESSION['user_id'], $eventId]);
$event = $stmt->fetch();

if (!$event) {
    redirectWith('test2.php', 'Event not found.', 'error');
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_event'])) {
    try {
        // Check if event is full
        if ($event['registration_count'] >= $event['max_participants']) {
            throw new Exception('This event is already full');
        }

        // Check if user is already registered
        if ($event['user_registered'] > 0) {
            throw new Exception('You are already registered for this event');
        }

        // Register user for the event
        $stmt = $pdo->prepare("
            INSERT INTO registrations (user_id, event_id, status) 
            VALUES (?, ?, 'active')
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $eventId])) {
            redirectWith('regevent.php', 'Successfully registered for the event!', 'success');
        } else {
            throw new Exception('Failed to register for the event');
        }
    } catch (Exception $e) {
        redirectWith("event.php?id=$eventId", $e->getMessage(), 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['name']); ?> - Event Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Event Banner -->
            <?php if ($event['banner_path']): ?>
                <div class="w-full h-64 md:h-96 mb-6 rounded-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($event['banner_path']); ?>" 
                         alt="<?php echo htmlspecialchars($event['name']); ?>"
                         class="w-full h-full object-cover">
                </div>
            <?php endif; ?>

            <!-- Event Details -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h1 class="text-3xl font-bold mb-4"><?php echo htmlspecialchars($event['name']); ?></h1>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="space-y-4">
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span><?php echo date('F d, Y', strtotime($event['date'])); ?></span>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span><?php echo date('h:i A', strtotime($event['time'])); ?></span>
                        </div>
                        
                        <div class="flex items-center text-gray-600">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                      d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span><?php echo htmlspecialchars($event['location']); ?></span>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="text-gray-600">
                            <span class="font-semibold">Registration Status:</span>
                            <span class="ml-2 px-2 py-1 rounded-full <?php 
                                echo $event['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                    ($event['status'] === 'closed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); 
                                ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </div>
                        
                        <div class="text-gray-600">
                            <span class="font-semibold">Capacity:</span>
                            <span class="ml-2"><?php echo $event['registration_count']; ?>/<?php echo $event['max_participants']; ?> registered</span>
                        </div>
                    </div>
                </div>

                <!-- Event Description -->
                <div class="prose max-w-none mb-6">
                    <h2 class="text-xl font-semibold mb-2">About This Event</h2>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>

                <!-- Registration Button -->
                <?php if ($event['status'] === 'open' && $event['registration_count'] < $event['max_participants']): ?>
                    <?php if ($event['user_registered'] == 0): ?>
                        <form method="POST" class="mt-6">
                            <input type="hidden" name="register_event" value="1">
                            <button type="submit" 
                                    class="w-full md:w-auto bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors">
                                Register for Event
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="mt-6">
                            <span class="bg-green-100 text-green-800 px-4 py-2 rounded-lg">
                                ✓ You're registered for this event
                            </span>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="mt-6">
                        <span class="bg-red-100 text-red-800 px-4 py-2 rounded-lg">
                            <?php echo $event['status'] === 'open' ? 'Event is full' : 'Registration is closed'; ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>