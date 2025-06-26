<?php
require_once __DIR__ . '/../config/database.php';

class Post {
    private $conn;
    
    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getFeaturedPost() {
        $stmt = $this->conn->prepare("
            SELECT p.*, u.username, u.avatar, c.name AS category_name, 
                   GROUP_CONCAT(t.name SEPARATOR ',') AS tags
            FROM posts p
            JOIN users u ON p.user_id = u.id
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.published = TRUE
            GROUP BY p.id
            ORDER BY p.views DESC, p.created_at DESC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Add all other post-related methods here...
    // getLatestPosts(), getPostBySlug(), etc.
}