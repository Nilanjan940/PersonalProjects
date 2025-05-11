<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Posts Management";

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['create_post'])) {
        $title = trim($_POST['title']);
        $slug = createSlug($title);
        $content = trim($_POST['content']);
        $excerpt = trim($_POST['excerpt']);
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
        $user_id = $_SESSION['user_id'];
        
        // Handle file upload
        $featured_image = null;
        if(isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/img/uploads/';
            if(!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['featured_image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                $featured_image = 'assets/img/uploads/' . $fileName;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, excerpt, featured_image, category_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if($stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $category_id, $user_id])) {
            $_SESSION['success'] = "Post created successfully!";
            header("Location: posts.php");
            exit;
        } else {
            $error = "Failed to create post.";
        }
    } elseif(isset($_POST['update_post'])) {
        $id = intval($_POST['id']);
        $title = trim($_POST['title']);
        $slug = createSlug($title);
        $content = trim($_POST['content']);
        $excerpt = trim($_POST['excerpt']);
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : null;
        
        // Handle file upload if a new image is provided
        $featured_image = $_POST['current_featured_image']; // Keep current image by default
        if(isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/img/uploads/';
            if(!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['featured_image']['name']);
            $targetPath = $uploadDir . $fileName;
            
            if(move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetPath)) {
                // Delete old image if it exists
                if($featured_image && file_exists('../' . $featured_image)) {
                    unlink('../' . $featured_image);
                }
                $featured_image = 'assets/img/uploads/' . $fileName;
            }
        }
        
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, slug = ?, content = ?, excerpt = ?, featured_image = ?, category_id = ? WHERE id = ?");
        if($stmt->execute([$title, $slug, $content, $excerpt, $featured_image, $category_id, $id])) {
            $_SESSION['success'] = "Post updated successfully!";
            header("Location: posts.php");
            exit;
        } else {
            $error = "Failed to update post.";
        }
    }
}

// Handle delete action
if(isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get post to delete featured image if exists
    $stmt = $pdo->prepare("SELECT featured_image FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($post && $post['featured_image'] && file_exists('../' . $post['featured_image'])) {
        unlink('../' . $post['featured_image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    if($stmt->execute([$id])) {
        $_SESSION['success'] = "Post deleted successfully!";
        header("Location: posts.php");
        exit;
    } else {
        $error = "Failed to delete post.";
    }
}

// Get all posts
$stmt = $pdo->query("SELECT p.*, c.name as category_name FROM posts p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// Function to create slug
function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if(empty($text)) {
        return 'n-a';
    }
    
    return $text;
}
?>

<?php include_once '../includes/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include_once 'sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Posts Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="posts.php?action=create" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Add New Post
                    </a>
                </div>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($_GET['action']) && in_array($_GET['action'], ['create', 'edit'])): ?>
                <!-- Create/Edit Post Form -->
                <?php
                $post = ['title' => '', 'content' => '', 'excerpt' => '', 'category_id' => '', 'featured_image' => ''];
                $formAction = 'posts.php?action=create';
                $formTitle = 'Add New Post';
                $formButton = 'Create Post';
                
                if($_GET['action'] === 'edit' && isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
                    $stmt->execute([$id]);
                    $post = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($post) {
                        $formAction = 'posts.php?action=edit';
                        $formTitle = 'Edit Post';
                        $formButton = 'Update Post';
                    } else {
                        header("Location: posts.php");
                        exit;
                    }
                }
                ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo $formTitle; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data" action="<?php echo $formAction; ?>">
                            <?php if($_GET['action'] === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <input type="hidden" name="current_featured_image" value="<?php echo $post['featured_image']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="content" class="form-label">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="excerpt" class="form-label">Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                                <small class="text-muted">A short summary of your post (optional).</small>
                            </div>
                            
                            <div class="mb-3">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">-- Select Category --</option>
                                    <?php foreach($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $post['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Featured Image</label>
                                <input type="file" class="form-control" id="featured_image" name="featured_image">
                                <?php if($post['featured_image']): ?>
                                    <div class="mt-2">
                                        <img src="../<?php echo $post['featured_image']; ?>" alt="Current Featured Image" style="max-height: 150px;">
                                        <p class="text-muted small mt-1">Current featured image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" name="<?php echo $_GET['action'] === 'create' ? 'create_post' : 'update_post'; ?>" class="btn btn-primary">
                                <?php echo $formButton; ?>
                            </button>
                            <a href="posts.php" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Posts List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($posts)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No posts found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($posts as $post): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                <td><?php echo $post['category_name'] ?? 'Uncategorized'; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                                <td>
                                                    <a href="../post.php?slug=<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                                    <a href="posts.php?action=edit&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                    <a href="posts.php?action=delete&id=<?php echo $post['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>