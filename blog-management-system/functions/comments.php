<?php
require_once '../config/database.php';

function getCommentsForPost($conn, $postId) {
    $stmt = $conn->prepare("
        SELECT c.*, u.username, u.avatar
        FROM comments c
        JOIN users u ON c.user_id = u.id
        WHERE c.post_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$postId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addComment($conn, $postId, $userId, $body) {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)");
    return $stmt->execute([$postId, $userId, $body]);
}

function deleteComment($conn, $commentId) {
    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
    return $stmt->execute([$commentId]);
}

function getCommentById($conn, $commentId) {
    $stmt = $conn->prepare("SELECT * FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getRecentComments($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT c.*, u.username, p.title AS post_title
        FROM comments c
        JOIN users u ON c.user_id = u.id
        JOIN posts p ON c.post_id = p.id
        ORDER BY c.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function handleCommentAction($action) {
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        session_start();
        require_once '../config/database.php';
        $db = new Database();
        $conn = $db->getConnection();
        
        if (!isset($_SESSION['user_id']) || !isset($_POST['post_id']) || empty($_POST['comment_body'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            exit;
        }
        
        $postId = $_POST['post_id'];
        $userId = $_SESSION['user_id'];
        $body = trim($_POST['comment_body']);
        
        if (addComment($conn, $postId, $userId, $body)) {
            $user = getUserById($conn, $userId);
            echo json_encode([
                'success' => true,
                'username' => htmlspecialchars($user['username']),
                'avatar' => $user['avatar'] ? UPLOAD_DIR . $user['avatar'] : DEFAULT_AVATAR,
                'comment' => htmlspecialchars($body)
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
        }
        exit;
    }
}

if (isset($_GET['action'])) {
    handleCommentAction($_GET['action']);
}

function getUserById($conn, $userId) {
    $stmt = $conn->prepare("SELECT username, avatar FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>