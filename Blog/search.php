<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$pageTitle = "Search Results for: " . htmlspecialchars($query);

if(empty($query)) {
    header("Location: index.php");
    exit;
}

$results = searchPosts($query);
?>

<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4">Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
        
        <?php if(empty($results)): ?>
            <div class="alert alert-info">No results found for your search query.</div>
        <?php else: ?>
            <?php foreach($results as $post): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="card-text text-muted">
                            Posted on <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                        </p>
                        <p class="card-text"><?php echo substr(htmlspecialchars(strip_tags($post['content'])), 0, 200); ?>...</p>
                        <a href="post.php?slug=<?php echo htmlspecialchars($post['slug']); ?>" class="btn btn-primary">Read More →</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">Search Again</div>
            <div class="card-body">
                <form action="search.php" method="GET">
                    <div class="input-group">
                        <input class="form-control" type="text" name="q" value="<?php echo htmlspecialchars($query); ?>" placeholder="Enter search term..." aria-label="Enter search term..." aria-describedby="button-search">
                        <button class="btn btn-primary" id="button-search" type="submit">Search</button>
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
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>