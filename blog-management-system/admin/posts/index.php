<?php
require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../functions/auth.php';

$db = new Database();
$conn = $db->getConnection();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$pageTitle = "Manage Posts";

// Get all posts
$stmt = $conn->prepare("
    SELECT p.id, p.title, p.slug, p.published, p.created_at, u.username, c.name AS category_name
    FROM posts p
    JOIN users u ON p.user_id = u.id
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Posts</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="edit.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add New Post
                    </a>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Post <?php echo $_GET['success']; ?> successfully!</div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td><?php echo htmlspecialchars($post['username']); ?></td>
                            <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                            <td>
                                <span class="badge <?php echo $post['published'] ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo $post['published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="edit.php?action=delete&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger confirm-action" data-confirm-message="Are you sure you want to delete this post?">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<?php
include '../../../includes/footer.php';
?>