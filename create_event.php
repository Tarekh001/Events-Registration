<?php
session_start();
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate and sanitize input
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $date = $_POST['date'];
        $time = $_POST['time'];
        $location = trim($_POST['location']);
        $max_participants = (int)$_POST['max_participants'];

        // Basic validation
        if (empty($name) || empty($date) || empty($time) || empty($location) || empty($max_participants)) {
            throw new Exception('All required fields must be filled out');
        }

        // Create uploads directory if it doesn't exist
        $uploadsDir = 'uploads/';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        // Handle file uploads
        $banner_path = '';
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $banner = $_FILES['banner'];
            $extension = strtolower(pathinfo($banner['name'], PATHINFO_EXTENSION));

            // Validate file type
            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF allowed');
            }

            // Generate unique filename
            $banner_path = $uploadsDir . uniqid() . '.' . $extension;

            // Move uploaded file
            if (!move_uploaded_file($banner['tmp_name'], $banner_path)) {
                throw new Exception('Failed to upload banner');
            }
        }

        // Insert event into database
        $stmt = $pdo->prepare("
            INSERT INTO events (name, description, date, time, location, max_participants, banner_path, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, 'open')
        ");

        if ($stmt->execute([$name, $description, $date, $time, $location, $max_participants, $banner_path])) {
            $_SESSION['message'] = "Event created successfully!";
            header('Location: manage_events.php');
            exit();
        } else {
            throw new Exception('Failed to create event');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Create New Event</h1>
            <a href="manage_events.php" class="text-blue-500 hover:text-blue-700">← Back to Events</a>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Event Name *
                </label>
                <input type="text"
                    id="name"
                    name="name"
                    required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <textarea id="description"
                    name="description"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    rows="4"></textarea>
            </div>

            <div class="mb-4 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="date">
                        Date *
                    </label>
                    <input type="date"
                        id="date"
                        name="date"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="time">
                        Time *
                    </label>
                    <input type="time"
                        id="time"
                        name="time"
                        required
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="location">
                    Location *
                </label>
                <input type="text"
                    id="location"
                    name="location"
                    required
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="max_participants">
                    Maximum Participants *
                </label>
                <input type="number"
                    id="max_participants"
                    name="max_participants"
                    required
                    min="1"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="banner">
                    Event Banner
                </label>
                <input type="file"
                    id="banner"
                    name="banner"
                    accept="image/*"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="mt-1 text-sm text-gray-500">
                    Accepted formats: JPG, PNG, GIF. Max size: 5MB
                </p>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Create Event
                </button>
                <a href="manage_events.php"
                    class="text-gray-500 hover:text-gray-700">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        // Preview image before upload
        document.getElementById('banner').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.className = 'mt-2 max-w-xs';

                    // Remove any existing preview
                    const existingPreview = document.querySelector('.banner-preview');
                    if (existingPreview) {
                        existingPreview.remove();
                    }

                    // Add new preview
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'banner-preview';
                    previewDiv.appendChild(img);
                    e.target.parentNode.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>