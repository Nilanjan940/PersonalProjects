<?php
require_once '../config/database.php';

function getAllTags($conn) {
    $stmt = $conn->prepare("SELECT * FROM tags ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTagById($conn, $tagId) {
    $stmt = $conn->prepare("SELECT * FROM tags WHERE id = ?");
    $stmt->execute([$tagId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function createTag($conn, $name) {
    // Check if tag already exists
    $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        return false; // Tag already exists
    }
    
    $stmt = $conn->prepare("INSERT INTO tags (name) VALUES (?)");
    return $stmt->execute([$name]);
}

function updateTag($conn, $tagId, $name) {
    // Check if tag already exists with different ID
    $stmt = $conn->prepare("SELECT id FROM tags WHERE name = ? AND id != ?");
    $stmt->execute([$name, $tagId]);
    if ($stmt->fetch()) {
        return false; // Tag with this name already exists
    }
    
    $stmt = $conn->prepare("UPDATE tags SET name = ? WHERE id = ?");
    return $stmt->execute([$name, $tagId]);
}

function deleteTag($conn, $tagId) {
    try {
        $conn->beginTransaction();
        
        // Delete from post_tags
        $conn->prepare("DELETE FROM post_tags WHERE tag_id = ?")->execute([$tagId]);
        
        // Delete tag
        $stmt = $conn->prepare("DELETE FROM tags WHERE id = ?");
        $success = $stmt->execute([$tagId]);
        
        $conn->commit();
        return $success;
    } catch (Exception $e) {
        $conn->rollBack();
        return false;
    }
}

function getPopularTags($conn, $limit = 10) {
    $stmt = $conn->prepare("
        SELECT t.id, t.name, COUNT(pt.post_id) AS post_count
        FROM tags t
        LEFT JOIN post_tags pt ON t.id = pt.tag_id
        LEFT JOIN posts p ON pt.post_id = p.id AND p.published = TRUE
        GROUP BY t.id
        ORDER BY post_count DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTagsForPost($conn, $postId) {
    $stmt = $conn->prepare("
        SELECT t.*
        FROM tags t
        JOIN post_tags pt ON t.id = pt.tag_id
        WHERE pt.post_id = ?
    ");
    $stmt->execute([$postId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>