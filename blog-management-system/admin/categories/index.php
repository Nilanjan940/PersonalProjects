<?php
require_once '../../../config/constants.php';
require_once '../../../config/database.php';
require_once '../../../functions/auth.php';

$db = new Database();
$conn = $db->getConnection();

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$pageTitle = "Manage Categories";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $errors['name'] = 'Category name is required.';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            if ($stmt->execute([$name, $description])) {
                header("Location: index.php?success=added");
                exit;
            } else {
                $errors['general'] = 'Failed to add category. Please try again.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (empty($name)) {
            $errors['name'] = 'Category name is required.';
        } else {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$name, $description, $id])) {
                header("Location: index.php?success=updated");
                exit;
            } else {
                $errors['general'] = 'Failed to update category. Please try again.';
            }
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        
        // Check if category has posts
        $stmt = $conn->prepare("SELECT COUNT(*) FROM posts WHERE category_id = ?");
        $stmt->execute([$id]);
        $postCount = $stmt->fetchColumn();
        
        if ($postCount > 0) {
            header("Location: index.php?error=Category+has+posts+and+cannot+be+deleted");
            exit;
        }
        
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            header("Location: index.php?success=deleted");
            exit;
        } else {
            $errors['general'] = 'Failed to delete category. Please try again.';
        }
    }
}

// Get all categories
$categories = getAllCategories($conn);

include '../../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manage Categories</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-circle"></i> Add New
                    </button>
                </div>
            </div>
            
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">Category <?php echo $_GET['success']; ?> successfully!</div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo urldecode($_GET['error']); ?></div>
            <?php endif; ?>
            
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Posts</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description'] ?: '—'); ?></td>
                            <td><?php echo $category['post_count']; ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-category" 
                                        data-id="<?php echo $category['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                        data-description="<?php echo htmlspecialchars($category['description']); ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="index.php?action=delete&id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-danger confirm-action" data-confirm-message="Are you sure you want to delete this category?">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="edit_name" name="name" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit category button clicks
    document.querySelectorAll('.edit-category').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const description = this.dataset.description;
            
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            
            const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
            modal.show();
        });
    });
});
</script>

<?php
include '../../../includes/footer.php';
?>