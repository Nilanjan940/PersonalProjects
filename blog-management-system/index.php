<?php
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'functions/posts.php';

$db = new Database();
$conn = $db->getConnection();

$pageTitle = "Home";
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$postsPerPage = 6;

// Get featured post
$featuredPost = getFeaturedPost($conn);

// Get latest posts
$latestPosts = getLatestPosts($conn, $currentPage, $postsPerPage);
$totalPosts = getTotalPublishedPosts($conn);
$totalPages = ceil($totalPosts / $postsPerPage);

// Get popular categories
$popularCategories = getPopularCategories($conn, 5);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Welcome to <?php echo SITE_NAME; ?></h1>
        <p class="lead">Discover amazing content and share your thoughts with the community</p>
        <a href="#featured" class="btn btn-primary btn-lg mt-3">Explore</a>
    </div>
</section>

<!-- Featured Post -->
<div class="container mb-5" id="featured">
    <?php if ($featuredPost): ?>
    <div class="card mb-4">
        <div class="row g-0">
            <div class="col-md-6">
                <img src="<?php echo UPLOAD_DIR . $featuredPost['thumbnail']; ?>" class="img-fluid rounded-start" alt="<?php echo htmlspecialchars($featuredPost['title']); ?>">
            </div>
            <div class="col-md-6">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo $featuredPost['avatar'] ? UPLOAD_DIR . $featuredPost['avatar'] : DEFAULT_AVATAR; ?>" alt="<?php echo htmlspecialchars($featuredPost['username']); ?>" class="rounded-circle me-2" width="40" height="40">
                        <div>
                            <small class="text-muted">By <?php echo htmlspecialchars($featuredPost['username']); ?></small><br>
                            <small class="text-muted"><?php echo date('F j, Y', strtotime($featuredPost['created_at'])); ?></small>
                        </div>
                    </div>
                    <h2 class="card-title"><?php echo htmlspecialchars($featuredPost['title']); ?></h2>
                    <p class="card-text"><?php echo substr(strip_tags($featuredPost['body']), 0, 200); ?>...</p>
                    <a href="post.php?slug=<?php echo $featuredPost['slug']; ?>" class="btn btn-primary">Read More</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Latest Posts -->
<div class="container mb-5">
    <h2 class="mb-4">Latest Posts</h2>
    <div class="row">
        <?php foreach ($latestPosts as $post): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <img src="<?php echo UPLOAD_DIR . $post['thumbnail']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?php echo $post['avatar'] ? UPLOAD_DIR . $post['avatar'] : DEFAULT_AVATAR; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>" class="rounded-circle me-2" width="30" height="30">
                        <div>
                            <small class="text-muted">By <?php echo htmlspecialchars($post['username']); ?></small><br>
                            <small class="text-muted"><?php echo date('F j, Y', strtotime($post['created_at'])); ?></small>
                        </div>
                    </div>
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
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <?php if ($currentPage < $totalPages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<!-- Popular Categories -->
<div class="container mb-5">
    <h2 class="mb-4">Popular Categories</h2>
    <div class="row">
        <?php foreach ($popularCategories as $category): ?>
        <div class="col-md-2 col-4 mb-3">
            <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <small class="text-muted"><?php echo $category['post_count']; ?> posts</small>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
include 'includes/footer.php';
?>