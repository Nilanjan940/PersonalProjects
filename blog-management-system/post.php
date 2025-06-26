<?php
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'functions/posts.php';
require_once 'functions/comments.php';

$db = new Database();
$conn = $db->getConnection();

// Get post by slug
$post = null;
if (isset($_GET['slug'])) {
    $post = getPostBySlug($conn, $_GET['slug']);
}

if (!$post) {
    header("Location: index.php");
    exit;
}

$pageTitle = $post['title'];
$relatedPosts = getRelatedPosts($conn, $post['id'], $post['category_id']);
$comments = getCommentsForPost($conn, $post['id']);

// Increment view count
incrementPostViews($conn, $post['id']);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <!-- Post Content -->
            <article>
                <h1 class="mb-3"><?php echo htmlspecialchars($post['title']); ?></h1>
                
                <div class="d-flex align-items-center mb-4">
                    <img src="<?php echo $post['avatar'] ? UPLOAD_DIR . $post['avatar'] : DEFAULT_AVATAR; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>" class="rounded-circle me-3" width="60" height="60">
                    <div>
                        <h5 class="mb-0"><?php echo htmlspecialchars($post['username']); ?></h5>
                        <small class="text-muted">
                            <?php echo date('F j, Y', strtotime($post['created_at'])); ?> • 
                            <?php echo $post['views']; ?> views • 
                            <span id="comment-count"><?php echo count($comments); ?></span> comments
                        </small>
                    </div>
                </div>
                
                <img src="<?php echo UPLOAD_DIR . $post['thumbnail']; ?>" class="post-thumbnail img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($post['title']); ?>">
                
                <div class="post-content mb-5">
                    <?php echo $post['body']; ?>
                </div>
                
                <div class="post-meta mb-5">
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="badge bg-primary me-2 mb-2"><?php echo htmlspecialchars($post['category_name']); ?></span>
                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                            <span class="badge bg-secondary me-2 mb-2"><?php echo htmlspecialchars(trim($tag)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="post-share mb-5">
                    <h5>Share this post:</h5>
                    <div class="d-flex">
                        <a href="#" class="btn btn-outline-primary me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-primary me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="btn btn-outline-primary me-2"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="btn btn-outline-primary"><i class="bi bi-link-45deg"></i></a>
                    </div>
                </div>
            </article>
            
            <!-- Author Bio -->
            <div class="card mb-5">
                <div class="card-body">
                    <div class="d-flex">
                        <img src="<?php echo $post['avatar'] ? UPLOAD_DIR . $post['avatar'] : DEFAULT_AVATAR; ?>" alt="<?php echo htmlspecialchars($post['username']); ?>" class="rounded-circle me-3" width="80" height="80">
                        <div>
                            <h5>About <?php echo htmlspecialchars($post['username']); ?></h5>
                            <p><?php echo htmlspecialchars($post['bio'] ?: 'This author hasn\'t written a bio yet.'); ?></p>
                            <a href="author.php?id=<?php echo $post['user_id']; ?>" class="btn btn-outline-primary btn-sm">View all posts</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Posts -->
            <div class="mb-5">
                <h4 class="mb-4">Related Posts</h4>
                <div class="row">
                    <?php foreach ($relatedPosts as $relatedPost): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <img src="<?php echo UPLOAD_DIR . $relatedPost['thumbnail']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($relatedPost['title']); ?>">
                            <div class="card-body">
                                <h6 class="card-title"><?php echo htmlspecialchars($relatedPost['title']); ?></h6>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($relatedPost['created_at'])); ?></small>
                            </div>
                            <div class="card-footer bg-transparent">
                                <a href="post.php?slug=<?php echo $relatedPost['slug']; ?>" class="btn btn-outline-primary btn-sm">Read More</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Comments Section -->
            <div class="mb-5">
                <h4 class="mb-4">Comments (<span id="comment-count-display"><?php echo count($comments); ?></span>)</h4>
                
                <div class="comments-container mb-4">
                    <?php if (empty($comments)): ?>
                        <div class="alert alert-info">No comments yet. Be the first to comment!</div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex">
                                    <img src="<?php echo $comment['avatar'] ? UPLOAD_DIR . $comment['avatar'] : DEFAULT_AVATAR; ?>" alt="<?php echo htmlspecialchars($comment['username']); ?>" class="comment-avatar me-3">
                                    <div>
                                        <h6 class="card-title"><?php echo htmlspecialchars($comment['username']); ?></h6>
                                        <small class="text-muted"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></small>
                                        <p class="card-text mt-2"><?php echo htmlspecialchars($comment['body']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Comment Form -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Leave a Comment</h5>
                        <form id="comment-form" action="functions/comments.php?action=create" method="POST">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <div class="mb-3">
                                <textarea class="form-control" name="comment_body" rows="3" placeholder="Write your comment here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="login.php" class="alert-link">login</a> to leave a comment.
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <?php include 'includes/sidebar.php'; ?>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>