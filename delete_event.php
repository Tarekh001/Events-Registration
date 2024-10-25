<?php
// delete_event.php
require_once 'includes/header.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    $event_id = (int)$_POST['event_id'];
    
    // Start transaction
    $pdo->beginTransaction();
    
    // First, delete all registrations for this event
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ?");
    $stmt->execute([$event_id]);
    
    // Then, delete the event
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    
    // Delete associated files if they exist
    $stmt = $pdo->prepare("SELECT banner_path, image_path FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();
    
    if ($event) {
        if (!empty($event['banner_path']) && file_exists($event['banner_path'])) {
            unlink($event['banner_path']);
        }
        if (!empty($event['image_path']) && file_exists($event['image_path'])) {
            unlink($event['image_path']);
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>