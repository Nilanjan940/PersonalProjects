<?php
require_once '../config/database.php';

function getAllCategories($conn) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id AND p.published = TRUE
        GROUP BY c.id
        ORDER BY c.name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategoryById($conn, $categoryId) {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) AS post_count
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id AND p.published = TRUE
        WHERE c.id = ?
        GROUP BY c.id
    ");
    $stmt->execute([$categoryId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createCategory($conn, $name, $description = null) {
    $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    return $stmt->execute([$name, $description]);
}

function updateCategory($conn, $categoryId, $name, $description = null) {
    $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
    return $stmt->execute([$name, $description, $categoryId]);
}

function deleteCategory($conn, $categoryId) {
    // Check if category has posts
    $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
    $stmt->execute([$categoryId]);
    $postCount = $stmt->fetchColumn();
    
    if ($postCount > 0) {
        return false; // Can't delete category with posts
    }
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    return $stmt->execute([$categoryId]);
}

function getPopularCategories($conn, $limit = 5) {
    $stmt = $conn->prepare("
        SELECT c.id, c.name, c.description, COUNT(p.id) AS post_count
        FROM categories c
        LEFT JOIN posts p ON c.id = p.category_id AND p.published = TRUE
        GROUP BY c.id
        ORDER BY post_count DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>