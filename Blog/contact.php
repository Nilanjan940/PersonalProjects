<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';

$pageTitle = "Contact Me";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    $errors = [];
    
    if(empty($name)) {
        $errors[] = "Name is required.";
    }
    
    if(empty($email)) {
        $errors[] = "Email is required.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if(empty($subject)) {
        $errors[] = "Subject is required.";
    }
    
    if(empty($message)) {
        $errors[] = "Message is required.";
    }
    
    if(empty($errors)) {
        // In a real application, you would send an email here
        $success = "Thank you for your message! I'll get back to you soon.";
        
        // Reset form fields
        $name = $email = $subject = $message = '';
    }
}
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="card-title">Contact Me</h1>
                <p class="lead">Have questions or want to get in touch? Fill out the form below.</p>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" class="form-control" id="subject" name="subject" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Contact Information</h5>
                <p><i class="bi bi-envelope me-2"></i> email@example.com</p>
                <p><i class="bi bi-phone me-2"></i> (123) 456-7890</p>
                <p><i class="bi bi-geo-alt me-2"></i> 123 Main St, City, Country</p>
                
                <hr>
                
                <h5 class="card-title mt-4">Follow Me</h5>
                <div class="social-links">
                    <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="me-2"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="me-2"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="me-2"><i class="bi bi-linkedin"></i></a>
                    <a href="#" class="me-2"><i class="bi bi-github"></i></a>
                </div>
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