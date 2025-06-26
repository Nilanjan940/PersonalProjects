<?php
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../functions/auth.php';

$db = new Database();
$conn = $db->getConnection();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$pageTitle = "Admin Dashboard";

// Get stats
$stmt = $conn->query("SELECT COUNT(*) FROM users");
$totalUsers = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM posts");
$totalPosts = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM comments");
$totalComments = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM categories");
$totalCategories = $stmt->fetchColumn();

// Get latest users
$latestUsers = $conn->query("SELECT username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Get latest posts
$latestPosts = $conn->query("
    SELECT p.title, p.slug, u.username, p.created_at 
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="bi bi-speedometer2 me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="posts/index.php">
                            <i class="bi bi-file-earmark-post me-2"></i>
                            Posts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories/index.php">
                            <i class="bi bi-bookmarks me-2"></i>
                            Categories
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="comments/index.php">
                            <i class="bi bi-chat-left-text me-2"></i>
                            Comments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users/index.php">
                            <i class="bi bi-people me-2"></i>
                            Users
                        </a>
                    </li>
                </ul>
                
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Account</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link" href="../../profile.php">
                            <i class="bi bi-person me-2"></i>
                            Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../../logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                        <i class="bi bi-calendar"></i>
                        This week
                    </button>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Users</h5>
                                    <h2 class="mb-0"><?php echo $totalUsers; ?></h2>
                                </div>
                                <i class="bi bi-people fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Posts</h5>
                                    <h2 class="mb-0"><?php echo $totalPosts; ?></h2>
                                </div>
                                <i class="bi bi-file-earmark-post fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Comments</h5>
                                    <h2 class="mb-0"><?php echo $totalComments; ?></h2>
                                </div>
                                <i class="bi bi-chat-left-text fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Categories</h5>
                                    <h2 class="mb-0"><?php echo $totalCategories; ?></h2>
                                </div>
                                <i class="bi bi-bookmarks fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Latest Users and Posts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Latest Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($latestUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Latest Posts</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($latestPosts as $post): ?>
                                        <tr>
                                            <td>
                                                <a href="../../post.php?slug=<?php echo $post['slug']; ?>" target="_blank">
                                                    <?php echo htmlspecialchars($post['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($post['username']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
include '../../includes/footer.php';
?>