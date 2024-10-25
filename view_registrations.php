<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// Fetch event details
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: manage_events.php');
    exit();
}

// Handle export with CSV column separation
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    ob_clean();
    
    // Only fetch active registrations
    $stmt = $pdo->prepare("
        SELECT u.name, u.email, r.registration_date, r.status
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.event_id = ? AND r.status = 'active'
        ORDER BY r.registration_date DESC
    ");
    $stmt->execute([$event_id]);
    $registrations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . str_replace(' ', '_', $event['name']) . '_registrations_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Event Information Section
    fputcsv($output, array_pad(['Event Information'], 4, '')); // Pad with empty columns
    fputcsv($output, ['Name', $event['name']]);
    fputcsv($output, ['Date', date('F d, Y', strtotime($event['date']))]);
    fputcsv($output, ['Time', date('h:i A', strtotime($event['time']))]);
    fputcsv($output, ['Location', $event['location']]);
    fputcsv($output, ['Active Registrations', count($registrations)]);
    fputcsv($output, array_pad([], 4, '')); // Empty row

    // Headers
    fputcsv($output, ['Name', 'Email', 'Registration Date', 'Status']);
    
    // Data
    foreach ($registrations as $row) {
        $date = new DateTime($row['registration_date']);
        fputcsv($output, [
            $row['name'],
            $row['email'],
            $date->format('M d, Y h:i A'),
            'Active'
        ]);
    }

    fclose($output);
    exit();
}

// Fetch only active registrations for display
$stmt = $pdo->prepare("
    SELECT u.name, u.email, r.registration_date, r.status, r.id as registration_id
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    WHERE r.event_id = ? AND r.status = 'active'
    ORDER BY r.registration_date DESC
");
$stmt->execute([$event_id]);
$registrations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Registrations - <?php echo htmlspecialchars($event['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <!-- Event Details Header -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($event['name']); ?></h1>
                        <div class="space-y-1">
                            <p class="text-gray-600">
                                Date: <?php echo date('F d, Y', strtotime($event['date'])); ?>
                            </p>
                            <p class="text-gray-600">
                                Time: <?php echo date('h:i A', strtotime($event['time'])); ?>
                            </p>
                            <p class="text-gray-600">
                                Location: <?php echo htmlspecialchars($event['location']); ?>
                            </p>
                            <p class="text-gray-600">
                                Active Registrations: <?php echo count($registrations); ?>
                            </p>
                        </div>
                    </div>
                    <div class="space-x-4">
                        <a href="?event_id=<?php echo $event_id; ?>&export=csv" 
                           class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
                            Export to CSV
                        </a>
                        <a href="manage_events.php" 
                           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                            Back to Events
                        </a>
                    </div>
                </div>
            </div>

            <!-- Active Registrations List -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registration Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($registrations)): ?>
                            <?php foreach ($registrations as $registration): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registration['name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($registration['email']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y h:i A', strtotime($registration['registration_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-sm rounded-full bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No active registrations found for this event.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>