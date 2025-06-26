<?php
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'functions/posts.php';

$db = new Database();
$conn = $db->getConnection();

$pageTitle = "Search";
$searchResults = [];
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

if (!empty($searchQuery)) {
    $pageTitle = "Search Results for '{$searchQuery}'";
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $postsPerPage = 6;
    $searchResults = searchPosts($conn, $searchQuery, $currentPage, $postsPerPage);
    $totalResults = getTotalSearchResults($conn, $searchQuery);
    $totalPages = ceil($totalResults / $postsPerPage);
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><?php echo $pageTitle; ?></h1>
    
    <!-- Search Form -->
    <div class="card mb-5">
        <div class="card-body">
            <form action="search.php" method="GET">
                <div class="input-group">
                    <input type="text" class="form-control form-control-lg" name="q" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search for posts..." required>
                    <button class="btn btn-primary" type="submit">Search</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Search Results -->
    <?php if (!empty($searchQuery)): ?>
        <?php if (!empty($searchResults)): ?>
            <div class="row">
                <?php foreach ($searchResults as $post): ?>
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
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?q=<?php echo urlencode($searchQuery); ?>&page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                No results found for "<?php echo htmlspecialchars($searchQuery); ?>".
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
include 'includes/footer.php';
?>