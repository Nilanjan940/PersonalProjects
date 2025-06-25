<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Get all products with inventory and category info
$query = "SELECT p.*, c.name as category_name, i.quantity 
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.category_id
          LEFT JOIN inventory i ON p.product_id = i.product_id";

// Add search filter if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $query .= " WHERE p.name LIKE :search OR p.sku LIKE :search";
}

// Add category filter if provided
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
if ($category_filter > 0) {
    $query .= (strpos($query, 'WHERE') === false) ? " WHERE" : " AND";
    $query .= " p.category_id = :category_id";
}

$query .= " ORDER BY p.name ASC";

$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $search_term = "%$search%";
    $stmt->bindParam(':search', $search_term);
}

if ($category_filter > 0) {
    $stmt->bindParam(':category_id', $category_filter);
}

$stmt->execute();
$products = $stmt->fetchAll();

// Get categories for filter dropdown
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Check for success message
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Products - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #28a745;
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
        
        /* Product Table Styles */
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .low-stock {
            color: var(--warning);
            font-weight: bold;
        }
        
        .out-of-stock {
            color: var(--danger);
            font-weight: bold;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
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
            
            .card-header .row > div {
                margin-bottom: 10px;
            }
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .table th, .table td {
                white-space: nowrap;
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
            <div class="container-fluid p-4">
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-boxes me-2"></i>Product Inventory</h1>
                    <div>
                        <a href="create.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-1"></i> Add Product
                        </a>
                        <button class="btn btn-success">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <form method="GET" action="read.php">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <div class="form-floating">
                                        <select class="form-select" id="categoryFilter" name="category">
                                            <option value="0">All Categories</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['category_id'] ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['category_id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label for="categoryFilter">Filter by Category</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-3 text-end">
                                    <a href="read.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-sync-alt me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 80px">Image</th>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th style="width: 120px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <?php
                                        // Determine stock status
                                        $status = '';
                                        $badge_class = '';
                                        if ($product['quantity'] <= 0) {
                                            $status = 'Out of Stock';
                                            $badge_class = 'bg-danger';
                                        } elseif ($product['quantity'] <= $product['reorder_level']) {
                                            $status = 'Low Stock';
                                            $badge_class = 'bg-warning';
                                        } else {
                                            $status = 'In Stock';
                                            $badge_class = 'bg-success';
                                        }
                                        ?>
                                        <tr>
                                            <td><img src="https://via.placeholder.com/60x60?text=<?= urlencode(substr($product['name'], 0, 1)) ?>" class="product-image"></td>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['sku']) ?></td>
                                            <td><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></td>
                                            <td><?= htmlspecialchars($product['quantity']) ?></td>
                                            <td>$<?= number_format($product['unit_price'], 2) ?></td>
                                            <td><span class="badge <?= $badge_class ?>"><?= $status ?></span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="update.php?id=<?= $product['product_id'] ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $product['product_id'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal for each product -->
                                                <div class="modal fade" id="deleteModal<?= $product['product_id'] ?>" tabindex="-1">
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
                                                                    This will permanently remove the product record.
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="delete.php?id=<?= $product['product_id'] ?>" class="btn btn-danger">Delete Product</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                                <li class="page-item"><a class="page-link" href="#">2</a></li>
                                <li class="page-item"><a class="page-link" href="#">3</a></li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
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