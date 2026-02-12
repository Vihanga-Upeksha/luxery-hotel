<?php
require 'db_connect.php';
header('Content-Type: application/json');

$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
if (!$room_id) {
    echo json_encode(['success' => false, 'message' => 'Missing room_id']);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT total_rooms, available_rooms FROM rooms WHERE id = ?');
    $stmt->execute([$room_id]);
    $room = $stmt->fetch();

    if (!$room) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }

    echo json_encode(['success' => true, 'total_rooms' => (int)$room['total_rooms'], 'available_rooms' => (int)$room['available_rooms']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>