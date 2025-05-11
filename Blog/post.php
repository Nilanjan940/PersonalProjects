<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

if(!isset($_GET['slug']) || empty($_GET['slug'])) {
    header("Location: index.php");
    exit;
}

$post = getPostBySlug($_GET['slug']);

if(!$post) {
    header("Location: index.php");
    exit;
}

$pageTitle = $post['title'];
$comments = getComments($post['id']);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $comment = trim($_POST['comment']);
    
    if(!empty($name) && !empty($email) && !empty($comment) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if(addComment($post['id'], $name, $email, $comment)) {
            $success = "Thank you for your comment! It will be visible after approval.";
            // Refresh comments
            $comments = getComments($post['id']);
        } else {
            $error = "There was an error submitting your comment. Please try again.";
        }
    } else {
        $error = "Please fill all fields with valid information.";
    }
}
?>

<div class="row">
    <div class="col-lg-8">
        <article>
            <header class="mb-4">
                <h1 class="fw-bolder mb-1"><?php echo htmlspecialchars($post['title']); ?></h1>
                <div class="text-muted fst-italic mb-2">
                    Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?> 
                    <?php if($post['category_name']): ?>
                        in <a href="category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialchars($post['category_name']); ?></a>
                    <?php endif; ?>
                    by <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?>
                </div>
                <?php if($post['featured_image']): ?>
                    <img class="img-fluid rounded mb-4" src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" />
                <?php endif; ?>
            </header>
            
            <section class="mb-5">
                <?php echo $post['content']; ?>
            </section>
        </article>
        
        <section class="mb-5">
            <div class="card bg-light">
                <div class="card-body">
                    <h4 class="mb-4">Leave a Comment</h4>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="submit_comment" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </section>
        
        <?php if(!empty($comments)): ?>
            <section class="mb-5">
                <h4 class="mb-4">Comments (<?php echo count($comments); ?>)</h4>
                <?php foreach($comments as $comment): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <img class="rounded-circle" src="https://ui-avatars.com/api/?name=<?php echo urlencode($comment['name']); ?>&background=random" width="50" height="50" alt="...">
                                </div>
                                <div class="ms-3">
                                    <h5 class="mt-0"><?php echo htmlspecialchars($comment['name']); ?></h5>
                                    <p class="text-muted small"><?php echo date('F j, Y \a\t g:i a', strtotime($comment['created_at'])); ?></p>
                                    <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Search</div>
            <div class="card-body">
                <form action="search.php" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="text" name="q" placeholder="Enter search term..." aria-label="Enter search term..." aria-describedby="button-search">
                        <button class="btn btn-primary" id="button-search" type="submit">Go!</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">Categories</div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach(getCategories() as $category): ?>
                        <li>
                            <a href="category.php?id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">Recent Posts</div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <?php foreach(getRecentPosts(5) as $recentPost): ?>
                        <li class="mb-2">
                            <a href="post.php?slug=<?php echo htmlspecialchars($recentPost['slug']); ?>"><?php echo htmlspecialchars($recentPost['title']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>