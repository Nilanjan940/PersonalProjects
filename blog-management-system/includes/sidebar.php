<div class="sidebar">
    <!-- About Widget -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">About</h5>
            <p class="card-text">Welcome to <?php echo SITE_NAME; ?>, your go-to source for quality content on various topics. Join our community today!</p>
        </div>
    </div>
    
    <!-- Categories Widget -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Categories</h5>
            <ul class="list-group list-group-flush">
                <?php
                $categories = getPopularCategories($conn, 5);
                foreach ($categories as $category): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="category.php?id=<?php echo $category['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($category['name']); ?></a>
                    <span class="badge bg-primary rounded-pill"><?php echo $category['post_count']; ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <!-- Popular Posts Widget -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Popular Posts</h5>
            <?php
            $popularPosts = getPopularPosts($conn, 3);
            foreach ($popularPosts as $post): ?>
            <div class="mb-3">
                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none">
                    <div class="d-flex">
                        <img src="<?php echo UPLOAD_DIR . $post['thumbnail']; ?>" class="rounded me-2" width="60" height="60" style="object-fit: cover;">
                        <div>
                            <h6 class="mb-0"><?php echo htmlspecialchars($post['title']); ?></h6>
                            <small class="text-muted"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Tags Widget -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Tags</h5>
            <div class="d-flex flex-wrap">
                <?php
                $tags = getPopularTags($conn, 10);
                foreach ($tags as $tag): ?>
                <a href="search.php?q=<?php echo urlencode($tag['name']); ?>" class="badge bg-secondary text-decoration-none me-1 mb-1"><?php echo htmlspecialchars($tag['name']); ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Newsletter Widget -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Newsletter</h5>
            <p class="card-text">Subscribe to our newsletter to get the latest updates.</p>
            <form>
                <div class="mb-3">
                    <input type="email" class="form-control" placeholder="Your email">
                </div>
                <button type="submit" class="btn btn-primary w-100">Subscribe</button>
            </form>
        </div>
    </div>
</div>