<?php
// regevent.php
require_once 'includes/header.php';
requireLogin();

// Fetch user's registered events from database
$stmt = $pdo->prepare("
    SELECT e.*, r.registration_date, r.status as registration_status
    FROM events e
    INNER JOIN registrations r ON e.id = r.event_id
    WHERE r.user_id = ? AND r.status = 'active'
    ORDER BY e.date ASC
");
$stmt->execute([$_SESSION['user_id']]);
$registeredEvents = $stmt->fetchAll();

// Handle event cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_event'])) {
    try {
        $eventId = $_POST['event_id'];
        
        // Update registration status to cancelled
        $stmt = $pdo->prepare("
            UPDATE registrations 
            SET status = 'cancelled' 
            WHERE user_id = ? AND event_id = ?
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $eventId])) {
            redirectWith('regevent.php', 'Event registration cancelled successfully.', 'success');
        } else {
            throw new Exception('Failed to cancel registration');
        }
    } catch (Exception $e) {
        redirectWith('regevent.php', 'Error cancelling registration: ' . $e->getMessage(), 'error');
    }
}
?>

<div class="container mx-auto px-4">
    <h1 class="text-2xl font-bold mb-6">Your Registered Events</h1>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (!empty($registeredEvents)): ?>
            <?php foreach ($registeredEvents as $event): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <?php if ($event['banner_path']): ?>
                        <img src="<?php echo htmlspecialchars($event['banner_path']); ?>" 
                             alt="<?php echo htmlspecialchars($event['name']); ?>"
                             class="w-full h-48 object-cover">
                    <?php endif; ?>
                    
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($event['name']); ?></h3>
                        <div class="space-y-2 mb-4">
                            <p class="text-gray-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <?php echo date('M d, Y', strtotime($event['date'])); ?>
                            </p>
                            <p class="text-gray-600">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                          d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <a href="event.php?id=<?php echo $event['id']; ?>" 
                               class="text-blue-500 hover:text-blue-700">
                                View Details
                            </a>
                            <form action="" method="POST" class="inline">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                <button type="submit" 
                                        name="cancel_event" 
                                        onclick="return confirm('Are you sure you want to cancel this registration?');"
                                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">You haven't registered for any events yet.</p>
                <a href="test2.php" class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                    Browse Available Events
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>