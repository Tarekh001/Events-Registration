<?php
// user_events.php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_id']) || !isset($_GET['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: manage_users.php');
    exit();
}

// Get user's registered events
$stmt = $pdo->prepare("
    SELECT e.*, r.registration_date 
    FROM events e 
    JOIN registrations r ON e.id = r.event_id 
    WHERE r.user_id = ?
    ORDER BY e.date DESC
");
$stmt->execute([$user_id]);
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Events - <?php echo htmlspecialchars($user['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Events for <?php echo htmlspecialchars($user['name']); ?></h1>
            <a href="manage_users.php" class="text-gray-500 hover:text-gray-700">Back to Users</a>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($events as $event): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($event['name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo date('M d, Y', strtotime($event['date'])); ?>
                            <br>
                            <?php echo date('h:i A', strtotime($event['time'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo htmlspecialchars($event['location']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php echo date('M d, Y H:i', strtotime($event['registration_date'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $event['status'] === 'open' ? 'bg-green-100 text-green-800' : 
                                    ($event['status'] === 'closed' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'); ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
