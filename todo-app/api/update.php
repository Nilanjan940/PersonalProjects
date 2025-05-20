<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['completed'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE tasks SET completed = ? WHERE id = ?");
    $stmt->execute([$data['completed'] ? 1 : 0, $data['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Task updated successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update task',
        'error' => $e->getMessage()
    ]);
}
?>