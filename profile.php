<?php
// profile.php
require_once 'includes/header.php';
requireLogin();

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch user's registration history
$stmt = $pdo->prepare("
    SELECT e.*, r.registration_date, r.status as registration_status
    FROM events e
    INNER JOIN registrations r ON e.id = r.event_id
    WHERE r.user_id = ?
    ORDER BY r.registration_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$registrationHistory = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Event Registration System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Profile Information -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">My Profile</h1>
                    <a href="editpro.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Edit Profile
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-gray-600">
                            <span class="font-semibold">Name:</span>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </p>
                        <p class="text-gray-600">
                            <span class="font-semibold">Email:</span>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>
                        <p class="text-gray-600">
                            <span class="font-semibold">Member Since:</span>
                            <?php echo date('F d, Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Registration History -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-bold mb-4">Registration History</h2>
                
                <?php if (!empty($registrationHistory)): ?>
                    <div class="space-y-4">
                        <?php foreach ($registrationHistory as $registration): ?>
                            <div class="border rounded-lg p-4 <?php echo $registration['registration_status'] === 'active' ? 'border-green-200' : 'border-red-200'; ?>">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="font-semibold text-lg">
                                            <?php echo htmlspecialchars($registration['name']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            <?php echo date('F d, Y', strtotime($registration['date'])); ?> at 
                                            <?php echo date('h:i A', strtotime($registration['time'])); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($registration['location']); ?>
                                        </p>
                                    </div>
                                    <span class="px-2 py-1 text-sm rounded-full <?php 
                                        echo $registration['registration_status'] === 'active' 
                                            ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'; 
                                    ?>">
                                        <?php echo ucfirst($registration['registration_status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">You haven't registered for any events yet.</p>
                    <a href="test2.php" class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                        Browse Available Events
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>