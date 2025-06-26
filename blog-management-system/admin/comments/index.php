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

$pageTitle = "Manage Comments";

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $commentId = $_GET['id'];
    
    if ($_GET['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        if ($stmt->execute([$commentId])) {
            header("Location: index.php?success=deleted");
            exit;
        } else {
            $errors['general'] = 'Failed to delete comment. Please try again.';
        }
    } elseif ($_GET['action'] === 'approve') {
        // In this simple system, all comments are approved when created
        // This is just for demonstration
        header("Location: index.php?success=approved");
        exit;
    }
}

// Get all comments
$comments = $conn->query("
    SELECT c.*, u.username, u.avatar, p.title AS post_title, p.slug AS post_slug
    FROM comments c
    JOIN users u ON c.user_id = u.id
    JOIN posts p ON c.post_id = p.id
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Comments</h1>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Comment <?php echo $_GET['success']; ?> successfully!</div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Comment</th>
                            <th>Author</th>
                            <th>Post</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(substr($comment['body'], 0, 50)); ?>...</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $comment['avatar'] ? UPLOAD_DIR . $comment['avatar'] : DEFAULT_AVATAR; ?>" class="rounded-circle me-2" width="30" height="30">
                                    <?php echo htmlspecialchars($comment['username']); ?>
                                </div>
                            </td>
                            <td>
                                <a href="../../post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank">
                                    <?php echo htmlspecialchars($comment['post_title']); ?>
                                </a>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($comment['created_at'])); ?></td>
                            <td>
                                <a href="index.php?action=approve&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-outline-success">
                                    <i class="bi bi-check-circle"></i> Approve
                                </a>
                                <a href="index.php?action=delete&id=<?php echo $comment['id']; ?>" class="btn btn-sm btn-outline-danger confirm-action" data-confirm-message="Are you sure you want to delete this comment?">
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