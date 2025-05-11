<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

$pageTitle = "Home";
$recentPosts = getRecentPosts(6);
$categories = getCategories();
?>

<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4">Latest Posts</h1>
        
        <?php if(empty($recentPosts)): ?>
            <div class="alert alert-info">No posts found.</div>
        <?php else: ?>
            <?php foreach($recentPosts as $post): ?>
                <div class="card mb-4">
                    <?php if($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="card-text text-muted">
                            Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?> 
                            <?php if($post['category_id']): ?>
                                in <a href="category.php?id=<?php echo $post['category_id']; ?>"><?php echo htmlspecialchars(getCategoryById($post['category_id'])['name']); ?></a>
                            <?php endif; ?>
                        </p>
                        <p class="card-text"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary">Read More →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Categories</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach($categories as $category): ?>
                        <li>
                            <a href="category.php?id=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>About Me</h5>
            </div>
            <div class="card-body">
                <p>Welcome to my personal blog where I share my thoughts, experiences, and knowledge about various topics.</p>
                <a href="about.php" class="btn btn-outline-secondary">Learn More</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>