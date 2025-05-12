<?php
require_once 'db.php';

function getRecentPosts($limit = 5) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM posts ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Log the error or handle it appropriately
        error_log("Database error: " . $e->getMessage());
        return []; // Return empty array on error
    }
}

function getCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPostBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, u.full_name as author_name 
                          FROM posts p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          LEFT JOIN users u ON p.user_id = u.id 
                          WHERE p.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getPostsByCategory($category_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE category_id = ? ORDER BY created_at DESC");
    $stmt->execute([$category_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function searchPosts($query) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$query%", "%$query%"]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function addComment($post_id, $name, $email, $comment) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, name, email, comment) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$post_id, $name, $email, $comment]);
}

function getComments($post_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>