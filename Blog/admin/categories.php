<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = "Categories Management";

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['create_category'])) {
        $name = trim($_POST['name']);
        $slug = createSlug($name);
        $description = trim($_POST['description']);
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        if($stmt->execute([$name, $slug, $description])) {
            $_SESSION['success'] = "Category created successfully!";
            header("Location: categories.php");
            exit;
        } else {
            $error = "Failed to create category.";
        }
    } elseif(isset($_POST['update_category'])) {
        $id = intval($_POST['id']);
        $name = trim($_POST['name']);
        $slug = createSlug($name);
        $description = trim($_POST['description']);
        
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?");
        if($stmt->execute([$name, $slug, $description, $id])) {
            $_SESSION['success'] = "Category updated successfully!";
            header("Location: categories.php");
            exit;
        } else {
            $error = "Failed to update category.";
        }
    }
}

// Handle delete action
if(isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Check if category has posts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
    $stmt->execute([$id]);
    $postCount = $stmt->fetchColumn();
    
    if($postCount > 0) {
        $_SESSION['error'] = "Cannot delete category with posts. Please reassign or delete the posts first.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if($stmt->execute([$id])) {
            $_SESSION['success'] = "Category deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete category.";
        }
    }
    
    header("Location: categories.php");
    exit;
}

// Get all categories
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
                <h1 class="h2">Categories Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="categories.php?action=create" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-circle"></i> Add New Category
                    </a>
                </div>
            </div>

            <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($_GET['action']) && in_array($_GET['action'], ['create', 'edit'])): ?>
                <!-- Create/Edit Category Form -->
                <?php
                $category = ['name' => '', 'description' => ''];
                $formAction = 'categories.php?action=create';
                $formTitle = 'Add New Category';
                $formButton = 'Create Category';
                
                if($_GET['action'] === 'edit' && isset($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $category = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($category) {
                        $formAction = 'categories.php?action=edit';
                        $formTitle = 'Edit Category';
                        $formButton = 'Update Category';
                    } else {
                        header("Location: categories.php");
                        exit;
                    }
                }
                ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?php echo $formTitle; ?></h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="<?php echo $formAction; ?>">
                            <?php if($_GET['action'] === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                            </div>
                            
                            <button type="submit" name="<?php echo $_GET['action'] === 'create' ? 'create_category' : 'update_category'; ?>" class="btn btn-primary">
                                <?php echo $formButton; ?>
                            </button>
                            <a href="categories.php" class="btn btn-outline-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <!-- Categories List -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Slug</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($categories)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No categories found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach($categories as $category): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                <td>
                                                    <a href="categories.php?action=edit&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                                    <a href="categories.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
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