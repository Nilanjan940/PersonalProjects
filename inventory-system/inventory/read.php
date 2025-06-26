<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Get inventory items with product info
$query = "SELECT i.*, p.name as product_name, p.sku, p.unit_price, p.reorder_level 
          FROM inventory i
          JOIN products p ON i.product_id = p.product_id";

// Add search filter if provided
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $query .= " WHERE p.name LIKE :search OR p.sku LIKE :search";
}

$query .= " ORDER BY p.name";

$stmt = $pdo->prepare($query);

if (!empty($search)) {
    $search_term = "%$search%";
    $stmt->bindParam(':search', $search_term);
}

$stmt->execute();
$inventory_items = $stmt->fetchAll();

// Get counts for dashboard cards
$total_items = $pdo->query("SELECT COUNT(*) FROM inventory")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM inventory i JOIN products p ON i.product_id = p.product_id WHERE i.quantity <= p.reorder_level")->fetchColumn();
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM inventory WHERE quantity = 0")->fetchColumn();
$recently_added = $pdo->query("SELECT COUNT(*) FROM inventory WHERE last_updated >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

// Check for success message
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Overview - InduStock</title>
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
        
        .inventory-card {
            transition: all 0.3s;
        }
        
        .inventory-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .badge-low { 
            background-color: var(--warning); 
            color: #000; 
        }
        
        .badge-out { 
            background-color: var(--danger); 
            color: white;
        }
        
        .search-box {
            max-width: 400px;
        }
        
        .table th {
            background-color: var(--primary);
            color: white;
        }
        
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
            
            .inventory-cards .col-md-3 {
                margin-bottom: 15px;
            }
            
            .table-responsive {
                overflow-x: auto;
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
                    <h1><i class="fas fa-warehouse me-2"></i>Inventory Overview</h1>
                    <div>
                        <a href="create.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-1"></i> Add Item
                        </a>
                        <button class="btn btn-success">
                            <i class="fas fa-file-export me-1"></i> Export
                        </button>
                    </div>
                </div>

                <div class="row mb-4 inventory-cards">
                    <div class="col-md-3">
                        <div class="card inventory-card border-start border-primary border-4 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Total Items</h5>
                                <h3><?= number_format($total_items) ?></h3>
                                <p class="text-muted mb-0"><small>Across all locations</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card inventory-card border-start border-warning border-4 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Low Stock</h5>
                                <h3><?= number_format($low_stock) ?></h3>
                                <p class="text-muted mb-0"><small>Below reorder level</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card inventory-card border-start border-danger border-4 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Out of Stock</h5>
                                <h3><?= number_format($out_of_stock) ?></h3>
                                <p class="text-muted mb-0"><small>Urgent replenishment</small></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card inventory-card border-start border-success border-4 h-100">
                            <div class="card-body">
                                <h5 class="card-title">Recently Added</h5>
                                <h3><?= number_format($recently_added) ?></h3>
                                <p class="text-muted mb-0"><small>Last 7 days</small></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center">
                        <form method="GET" action="read.php" class="input-group search-box mb-2 mb-md-0">
                            <input type="text" class="form-control" name="search" placeholder="Search inventory..." value="<?= htmlspecialchars($search) ?>">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                        <div class="btn-group">
                            <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-filter me-1"></i> Filters
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="read.php">All Items</a></li>
                                <li><a class="dropdown-item" href="read.php?filter=low">Low Stock</a></li>
                                <li><a class="dropdown-item" href="read.php?filter=out">Out of Stock</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="read.php?filter=warehouse">By Warehouse</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>SKU</th>
                                        <th>Location</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inventory_items as $item): ?>
                                        <?php
                                        // Determine stock status
                                        $status = '';
                                        $badge_class = '';
                                        if ($item['quantity'] <= 0) {
                                            $status = 'Out of Stock';
                                            $badge_class = 'bg-danger';
                                        } elseif ($item['quantity'] <= $item['reorder_level']) {
                                            $status = 'Low Stock';
                                            $badge_class = 'bg-warning';
                                        } else {
                                            $status = 'In Stock';
                                            $badge_class = 'bg-success';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td><?= htmlspecialchars($item['sku']) ?></td>
                                            <td><?= htmlspecialchars($item['location']) ?></td>
                                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                                            <td><span class="badge <?= $badge_class ?>"><?= $status ?></span></td>
                                            <td><?= date('Y-m-d', strtotime($item['last_updated'])) ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="update.php?id=<?= $item['inventory_id'] ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $item['inventory_id'] ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                                
                                                <!-- Delete Modal for each item -->
                                                <div class="modal fade" id="deleteModal<?= $item['inventory_id'] ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-danger text-white">
                                                                <h5 class="modal-title">Confirm Deletion</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>Are you sure you want to delete "<?= htmlspecialchars($item['product_name']) ?>" from inventory?</p>
                                                                <div class="alert alert-warning">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                    This will permanently remove the inventory record.
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <a href="delete.php?id=<?= $item['inventory_id'] ?>" class="btn btn-danger">Delete Item</a>
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
                        <nav aria-label="Inventory pagination">
                            <ul class="pagination justify-content-center mb-0">
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