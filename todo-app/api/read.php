<?php
include 'config.php';

$filter = $_GET['filter'] ?? 'all';

try {
    switch ($filter) {
        case 'active':
            $stmt = $pdo->query("SELECT * FROM tasks WHERE completed = FALSE ORDER BY created_at DESC");
            break;
        case 'completed':
            $stmt = $pdo->query("SELECT * FROM tasks WHERE completed = TRUE ORDER BY created_at DESC");
            break;
        default:
            $stmt = $pdo->query("SELECT * FROM tasks ORDER BY 
                                CASE WHEN completed THEN 1 ELSE 0 END, 
                                created_at DESC");
    }

    $tasks = $stmt->fetchAll();
    echo json_encode($tasks);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch tasks',
        'error' => $e->getMessage()
    ]);
}
?>