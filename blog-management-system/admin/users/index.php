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

$pageTitle = "Manage Users";

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $userId = $_GET['id'];
    
    if ($_GET['action'] === 'delete') {
        // Prevent deleting yourself
        if ($userId == $_SESSION['user_id']) {
            header("Location: index.php?error=Cannot+delete+your+own+account");
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$userId])) {
            header("Location: index.php?success=deleted");
            exit;
        } else {
            $errors['general'] = 'Failed to delete user. Please try again.';
        }
    } elseif ($_GET['action'] === 'toggle_role') {
        // Prevent changing your own role
        if ($userId == $_SESSION['user_id']) {
            header("Location: index.php?error=Cannot+change+your+own+role");
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE users SET role = IF(role = 'admin', 'user', 'admin') WHERE id = ?");
        if ($stmt->execute([$userId])) {
            header("Location: index.php?success=role+updated");
            exit;
        } else {
            $errors['general'] = 'Failed to update user role. Please try again.';
        }
    }
}

// Get all users
$users = $conn->query("
    SELECT u.id, u.username, u.email, u.role, u.created_at, 
           COUNT(p.id) AS post_count
    FROM users u
    LEFT JOIN posts p ON u.id = p.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

include '../../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Users</h1>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">User <?php echo $_GET['success']; ?> successfully!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo urldecode($_GET['error']); ?></div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Posts</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $user['role'] === 'admin' ? 'bg-primary' : 'bg-secondary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo $user['post_count']; ?></td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="index.php?action=toggle_role&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-arrow-repeat"></i> Toggle Role
                                </a>
                                <a href="index.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger confirm-action" data-confirm-message="Are you sure you want to delete this user?">
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