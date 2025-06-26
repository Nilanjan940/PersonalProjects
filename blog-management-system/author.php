<?php
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'functions/users.php';
require_once 'functions/posts.php';

$db = new Database();
$conn = $db->getConnection();

$author = null;
$posts = [];
$pageTitle = "Authors";

if (isset($_GET['id'])) {
    $author = getUserById($conn, $_GET['id']);
    if ($author) {
        $pageTitle = $author['username'];
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $postsPerPage = 6;
        $posts = getPostsByAuthor($conn, $author['id'], $currentPage, $postsPerPage);
        $totalPosts = getTotalPostsByAuthor($conn, $author['id']);
        $totalPages = ceil($totalPosts / $postsPerPage);
    }
}

if (!$author) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <!-- Author Profile -->
    <div class="text-center mb-5">
        <img src="<?php echo $author['avatar'] ? UPLOAD_DIR . $author['avatar'] : DEFAULT_AVATAR; ?>" alt="<?php echo htmlspecialchars($author['username']); ?>" class="author-avatar mb-3">
        <h1><?php echo htmlspecialchars($author['username']); ?></h1>
        <p class="lead"><?php echo htmlspecialchars($author['bio'] ?: 'This author hasn\'t written a bio yet.'); ?></p>
        <div class="d-flex justify-content-center gap-3">
            <a href="#" class="text-dark"><i class="bi bi-twitter"></i></a>
            <a href="#" class="text-dark"><i class="bi bi-facebook"></i></a>
            <a href="#" class="text-dark"><i class="bi bi-instagram"></i></a>
            <a href="#" class="text-dark"><i class="bi bi-globe"></i></a>
        </div>
    </div>
    
    <!-- Author Posts -->
    <h2 class="mb-4">Latest Posts</h2>
    <div class="row">
        <?php foreach ($posts as $post): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="<?php echo UPLOAD_DIR . $post['thumbnail']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                    <p class="card-text"><?php echo substr(strip_tags($post['body']), 0, 100); ?>...</p>
                    <a href="post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-outline-primary btn-sm">Read More</a>
                </div>
                <div class="card-footer bg-transparent">
                    <small class="text-muted">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($post['category_name']); ?></span>
                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?id=<?php echo $author['id']; ?>&page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="?id=<?php echo $author['id']; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?id=<?php echo $author['id']; ?>&page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>