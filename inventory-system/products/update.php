<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header('Location: read.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Get product data
$stmt = $pdo->prepare("SELECT p.*, i.quantity 
                      FROM products p
                      LEFT JOIN inventory i ON p.product_id = i.product_id
                      WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: read.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $sku = filter_input(INPUT_POST, 'sku', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
    $unit_price = filter_input(INPUT_POST, 'unit_price', FILTER_VALIDATE_FLOAT);
    $reorder_level = filter_input(INPUT_POST, 'reorder_level', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    try {
        $pdo->beginTransaction();
        
        // Update product
        $stmt = $pdo->prepare("UPDATE products SET 
                              name = ?, sku = ?, description = ?, 
                              category_id = ?, supplier_id = ?, 
                              unit_price = ?, reorder_level = ?
                              WHERE product_id = ?");
        $stmt->execute([$name, $sku, $description, $category_id, $supplier_id, $unit_price, $reorder_level, $product_id]);
        
        // Update inventory
        $stmt = $pdo->prepare("UPDATE inventory SET quantity = ? WHERE product_id = ?");
        $stmt->execute([$quantity, $product_id]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Product updated successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error updating product: " . $e->getMessage();
    }
}

// Get categories and suppliers for dropdowns
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --warning: #ffc107;
            --danger: #dc3545;
            --light: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            transition: all 0.3s;
            min-height: 100vh;
            width: calc(100% - 250px);
        }
        
        .sidebar.collapsed + .main-content {
            margin-left: 70px;
            width: calc(100% - 70px);
        }
        
        /* Toggle Button */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Product Header */
        .product-header {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .product-thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0 !important;
                width: 100%;
                padding: 1rem;
            }
            
            .sidebar-toggle {
                display: block;
            }
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }
            
            .product-header .row > div {
                text-align: center;
                margin-bottom: 15px;
            }
            
            .product-header .row > div:last-child {
                margin-bottom: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar Container -->
        <div id="sidebar-container"></div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container py-5">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-edit me-2"></i>Edit Product</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                </div>

                <div class="product-header">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="https://via.placeholder.com/100x100?text=<?= urlencode(substr($product['name'], 0, 1)) ?>" class="product-thumbnail mb-2">
                            <button class="btn btn-sm btn-outline-primary btn-block">
                                <i class="fas fa-camera me-1"></i> Change
                            </button>
                        </div>
                        <div class="col-md-10">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <strong>SKU:</strong> <?= htmlspecialchars($product['sku']) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Category:</strong> 
                                    <?php 
                                    $category_name = 'Uncategorized';
                                    foreach ($categories as $cat) {
                                        if ($cat['category_id'] == $product['category_id']) {
                                            $category_name = $cat['name'];
                                            break;
                                        }
                                    }
                                    echo htmlspecialchars($category_name);
                                    ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Current Stock:</strong> <?= htmlspecialchars($product['quantity']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="update.php?id=<?= $product_id ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product Name*</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">SKU Code*</label>
                                    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Category*</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">Select Supplier</option>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?= $supplier['supplier_id'] ?>" <?= $supplier['supplier_id'] == $product['supplier_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($supplier['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Current Stock*</label>
                                    <input type="number" name="quantity" class="form-control" value="<?= htmlspecialchars($product['quantity']) ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Unit Price*</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="unit_price" class="form-control" value="<?= htmlspecialchars($product['unit_price']) ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Reorder Level</label>
                                    <input type="number" name="reorder_level" class="form-control" value="<?= htmlspecialchars($product['reorder_level']) ?>">
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete "<?= htmlspecialchars($product['name']) ?>"?</p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This will permanently remove the product record and all associated data.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="delete.php?id=<?= $product_id ?>" class="btn btn-danger">Delete Product</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load sidebar content
        fetch('../assets/sidebar.html')
            .then(response => response.text())
            .then(data => {
                document.getElementById('sidebar-container').innerHTML = data;
                initializeSidebar();
            })
            .catch(error => console.error('Error loading sidebar:', error));

        function initializeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.sidebar-toggle');
            
            // Mobile toggle
            mobileToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
            
            // Collapse/expand functionality
            const collapseBtn = document.getElementById('collapseToggle');
            if (collapseBtn) {
                collapseBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('collapsed');
                    localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                    
                    // Update icon
                    const icon = this.querySelector('i');
                    if (sidebar.classList.contains('collapsed')) {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-chevron-right');
                    } else {
                        icon.classList.remove('fa-chevron-right');
                        icon.classList.add('fa-chevron-left');
                    }
                });
            }
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992 && 
                    !sidebar.contains(e.target) && 
                    e.target !== mobileToggle) {
                    sidebar.classList.remove('active');
                }
            });
            
            // Load saved sidebar state
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                sidebar.classList.add('collapsed');
                const icon = document.getElementById('collapseToggle')?.querySelector('i');
                if (icon) {
                    icon.classList.remove('fa-chevron-left');
                    icon.classList.add('fa-chevron-right');
                }
            }
        }
    </script>
</body>
</html>