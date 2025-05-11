<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

if(!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$category_id = intval($_GET['id']);
$category = getCategoryById($category_id);
$posts = getPostsByCategory($category_id);

if(!$category) {
    header("Location: index.php");
    exit;
}

$pageTitle = "Category: " . htmlspecialchars($category['name']);
?>

<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4">Posts in <?php echo htmlspecialchars($category['name']); ?></h1>
        
        <?php if(empty($posts)): ?>
            <div class="alert alert-info">No posts found in this category.</div>
        <?php else: ?>
            <?php foreach($posts as $post): ?>
                <div class="card mb-4">
                    <?php if($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="card-text text-muted">
                            Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
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
                <h5>About This Category</h5>
            </div>
            <div class="card-body">
                <p><?php echo htmlspecialchars($category['description'] ?? 'No description available.'); ?></p>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Other Categories</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <?php foreach(getCategories() as $cat): ?>
                        <?php if($cat['id'] != $category_id): ?>
                            <li>
                                <a href="category.php?id=<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>