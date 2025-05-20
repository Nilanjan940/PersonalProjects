<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['title'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid data or missing title']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO tasks (title, description) VALUES (?, ?)");
    $stmt->execute([trim($data['title']), trim($data['description'] ?? '')]);
    
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId(),
        'message' => 'Task added successfully'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add task',
        'error' => $e->getMessage()
    ]);
}
?>