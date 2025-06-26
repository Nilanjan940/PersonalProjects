<?php
require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../functions/auth.php';
require_once '../../../functions/posts.php';

$db = new Database();
$conn = $db->getConnection();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$pageTitle = "Add New Post";
$post = null;
$categories = getAllCategories($conn);
$tags = getPopularTags($conn, 100);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $postId = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    if ($stmt->execute([$postId])) {
        header("Location: index.php?success=deleted");
        exit;
    }
}

// Get post data if editing
if (isset($_GET['id'])) {
    $postId = $_GET['id'];
    $stmt = $conn->prepare("
        SELECT p.*, GROUP_CONCAT(t.id SEPARATOR ',') AS tag_ids
        FROM posts p
        LEFT JOIN post_tags pt ON p.id = pt.post_id
        LEFT JOIN tags t ON pt.tag_id = t.id
        WHERE p.id = ?
        GROUP BY p.id
    ");
    $stmt->execute([$postId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($post) {
        $pageTitle = "Edit Post";
    }
}

// Handle form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $body = trim($_POST['body']);
    $categoryId = (int)$_POST['category_id'];
    $published = isset($_POST['published']) ? 1 : 0;
    $selectedTags = isset($_POST['tags']) ? $_POST['tags'] : [];
    
    // Validate inputs
    if (empty($title)) {
        $errors['title'] = 'Title is required.';
    }
    
    if (empty($slug)) {
        $errors['slug'] = 'Slug is required.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors['slug'] = 'Slug can only contain lowercase letters, numbers and hyphens.';
    }
    
    if (empty($body)) {
        $errors['body'] = 'Content is required.';
    }
    
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a category.';
    }
    
    // Handle file upload
    $thumbnail = $post['thumbnail'] ?? '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['thumbnail'];
        
        // Validate file
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors['thumbnail'] = 'File size must be less than ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.';
        } elseif (!in_array($file['type'], ALLOWED_TYPES)) {
            $errors['thumbnail'] = 'Only JPG, PNG, and GIF images are allowed.';
        } else {
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $thumbnail = uniqid() . '.' . $ext;
            $destination = UPLOAD_DIR . $thumbnail;
            
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                $errors['thumbnail'] = 'Failed to upload file.';
            } else {
                // Delete old thumbnail if it exists
                if (isset($post['thumbnail']) && $post['thumbnail']) {
                    @unlink(UPLOAD_DIR . $post['thumbnail']);
                }
            }
        }
    }
    
    // If no errors, save post
    if (empty($errors)) {
        if ($post) {
            // Update existing post
            $stmt = $conn->prepare("
                UPDATE posts 
                SET title = ?, slug = ?, body = ?, category_id = ?, thumbnail = ?, published = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $success = $stmt->execute([$title, $slug, $body, $categoryId, $thumbnail, $published, $post['id']]);
            
            // Update tags
            $conn->prepare("DELETE FROM post_tags WHERE post_id = ?")->execute([$post['id']]);
        } else {
            // Create new post
            $stmt = $conn->prepare("
                INSERT INTO posts (title, slug, body, category_id, thumbnail, published, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $success = $stmt->execute([$title, $slug, $body, $categoryId, $thumbnail, $published, $_SESSION['user_id']]);
            $postId = $conn->lastInsertId();
        }
        
        // Add tags
        $postId = $post ? $post['id'] : $postId;
        if ($success && !empty($selectedTags)) {
            $stmt = $conn->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)");
            foreach ($selectedTags as $tagId) {
                $stmt->execute([$postId, $tagId]);
            }
        }
        
        if ($success) {
            header("Location: index.php?success=" . ($post ? 'updated' : 'created'));
            exit;
        } else {
            $errors['general'] = 'Failed to save post. Please try again.';
        }
    }
}

include '../../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo $pageTitle; ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Posts
                    </a>
                </div>
            </div>
            
            <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ($post ? htmlspecialchars($post['title']) : ''); ?>" required>
                                    <?php if (isset($errors['title'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['title']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug</label>
                                    <input type="text" class="form-control <?php echo isset($errors['slug']) ? 'is-invalid' : ''; ?>" id="slug" name="slug" value="<?php echo isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : ($post ? htmlspecialchars($post['slug']) : ''); ?>" required>
                                    <?php if (isset($errors['slug'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['slug']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="body" class="form-label">Content</label>
                                    <textarea class="form-control <?php echo isset($errors['body']) ? 'is-invalid' : ''; ?>" id="body" name="body" rows="10" required><?php echo isset($_POST['body']) ? htmlspecialchars($_POST['body']) : ($post ? htmlspecialchars($post['body']) : ''); ?></textarea>
                                    <?php if (isset($errors['body'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['body']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Publish</h5>
                                
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="published">
                                        <option value="1" <?php echo (isset($_POST['published']) && $_POST['published']) || (isset($post['published']) && $post['published']) ? 'selected' : ''; ?>>Published</option>
                                        <option value="0" <?php echo (isset($_POST['published']) && !$_POST['published']) || (isset($post['published']) && !$post['published']) ? 'selected' : ''; ?>>Draft</option>
                                    </select>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Save Post</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Featured Image</h5>
                                
                                <div class="mb-3">
                                    <?php if ($post && $post['thumbnail']): ?>
                                        <img id="thumbnail-preview" src="<?php echo UPLOAD_DIR . $post['thumbnail']; ?>" class="img-fluid mb-2">
                                    <?php else: ?>
                                        <img id="thumbnail-preview" src="https://via.placeholder.com/600x400?text=No+Image" class="img-fluid mb-2" style="display: none;">
                                    <?php endif; ?>
                                    
                                    <input type="file" class="form-control image-upload <?php echo isset($errors['thumbnail']) ? 'is-invalid' : ''; ?>" id="thumbnail" name="thumbnail" accept="image/*" data-preview-id="thumbnail-preview">
                                    <?php if (isset($errors['thumbnail'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['thumbnail']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Categories</h5>
                                
                                <div class="mb-3">
                                    <select class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>" id="category_id" name="category_id" required>
                                        <option value="">Select a category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) || (isset($post['category_id']) && $post['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['category_id'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['category_id']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Tags</h5>
                                
                                <div class="mb-3">
                                    <select class="form-select" id="tags" name="tags[]" multiple>
                                        <?php foreach ($tags as $tag): ?>
                                            <option value="<?php echo $tag['id']; ?>" <?php echo (isset($_POST['tags']) && in_array($tag['id'], $_POST['tags'])) || (isset($post['tag_ids']) && in_array($tag['id'], explode(',', $post['tag_ids']))) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tag['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for tags
    $('#tags').select2({
        placeholder: 'Select tags',
        tags: true,
        tokenSeparators: [',', ' ']
    });
    
    // Initialize CKEditor for content
    ClassicEditor
        .create(document.querySelector('#body'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', 'undo', 'redo', '|',
                    'codeBlock', 'imageUpload'
                ]
            },
            language: 'en',
            licenseKey: '',
        })
        .then(editor => {
            window.editor = editor;
        })
        .catch(error => {
            console.error('Oops, something went wrong!');
            console.error('Please, report the following error on https://github.com/ckeditor/ckeditor5/issues with the build id and the error stack trace:');
            console.warn('Build id: 4tbxnwj5v8mp-8qus9u5x9x6w');
            console.error(error);
        });
});
</script>

<?php
include '../../../includes/footer.php';
?>