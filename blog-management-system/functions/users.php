<?php
require_once '../config/database.php';

function getAllUsers($conn) {
    $stmt = $conn->prepare("
        SELECT u.*, COUNT(p.id) AS post_count
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateUserRole($conn, $userId, $role) {
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    return $stmt->execute([$role, $userId]);
}

function deleteUser($conn, $userId) {
    try {
        $conn->beginTransaction();
        
        // Get user to delete avatar
        $user = getUserById($conn, $userId);
        
        // Delete user's posts and related data
        $posts = $conn->prepare("SELECT id FROM posts WHERE user_id = ?")->execute([$userId]);
        while ($post = $posts->fetch(PDO::FETCH_ASSOC)) {
            deletePost($conn, $post['id']);
        }
        
        // Delete user's comments
        $conn->prepare("DELETE FROM comments WHERE user_id = ?")->execute([$userId]);
        
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $success = $stmt->execute([$userId]);
        
        // Delete avatar file if it's not the default
        if ($user && $user['avatar'] && $user['avatar'] !== 'default-avatar.png') {
            @unlink(UPLOAD_DIR . $user['avatar']);
        }
        
        $conn->commit();
        return $success;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}

function searchUsers($conn, $query) {
    $searchTerm = "%$query%";
    $stmt = $conn->prepare("
        SELECT u.*, COUNT(p.id) AS post_count
        FROM users u
        LEFT JOIN posts p ON u.id = p.user_id
        WHERE u.username LIKE ? OR u.email LIKE ?
        GROUP BY u.id
        ORDER BY u.username
    ");
    $stmt->execute([$searchTerm, $searchTerm]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>