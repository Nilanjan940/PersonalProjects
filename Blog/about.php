<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

$pageTitle = "About Me";
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="card-title">About Me</h1>
                <p class="lead">Hello! I'm a passionate blogger sharing my thoughts and experiences with the world.</p>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <img src="https://via.placeholder.com/300" class="img-fluid rounded-circle" alt="Profile Picture">
                    </div>
                    <div class="col-md-8">
                        <p>Welcome to my personal blog! I started this journey to share my knowledge, experiences, and thoughts on various topics that interest me.</p>
                        <p>With a background in [your field], I bring a unique perspective to [your blog topics]. My goal is to create content that is informative, engaging, and valuable to my readers.</p>
                        <p>When I'm not blogging, you can find me [your hobbies/interests]. I believe in continuous learning and sharing knowledge with others.</p>
                    </div>
                </div>
                
                <h2 class="mt-5">My Mission</h2>
                <p>To provide high-quality, authentic content that helps, inspires, and entertains my readers. I strive to create a community where ideas can be shared and discussed openly.</p>
                
                <h2 class="mt-5">Contact Information</h2>
                <p>Feel free to reach out to me through the <a href="contact.php">contact page</a> or connect with me on social media.</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Popular Posts</div>
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