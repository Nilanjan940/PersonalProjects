<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Build base query with joins
$baseQuery = "SELECT po.*, s.name as supplier_name, 
              COUNT(poi.po_item_id) as item_count, 
              SUM(poi.quantity * poi.unit_price) as total_amount
              FROM purchase_orders po
              LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
              LEFT JOIN po_items poi ON po.po_id = poi.po_id";

// Initialize variables for filtering
$whereClauses = [];
$params = [];

// Handle search filter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $whereClauses[] = "(po.po_id LIKE :search OR s.name LIKE :search)";
    $params[':search'] = "%$search%";
}

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
if (!empty($status_filter)) {
    $whereClauses[] = "po.status = :status";
    $params[':status'] = $status_filter;
}

// Handle date filter
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
if (!empty($date_filter)) {
    $whereClauses[] = "po.order_date = :order_date";
    $params[':order_date'] = $date_filter;
}

// Build final query
$query = $baseQuery;
if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
}
$query .= " GROUP BY po.po_id ORDER BY po.order_date DESC";

// Execute purchase orders query
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$purchase_orders = $stmt->fetchAll();

// Check for success message
$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - InduStock</title>
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
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-draft { background-color: #6c757d; color: white; }
        .badge-ordered { background-color: var(--primary); color: white; }
        .badge-received { background-color: var(--success); color: white; }
        .badge-cancelled { background-color: var(--danger); color: white; }
        .badge-partial { background-color: var(--warning); color: black; }
        
        .po-table th {
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
        }
        
        @media (max-width: 768px) {
            h1 {
                font-size: 1.75rem;
            }
            
            .po-table th, .po-table td {
                padding: 0.5rem;
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
                    <h1><i class="fas fa-shopping-cart me-2"></i>Purchase Orders</h1>
                    <div>
                        <a href="create.php" class="btn btn-primary me-2">
                            <i class="fas fa-plus me-1"></i> New Order
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
                                <div class="col-md-4 mb-2">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Search orders..." value="<?= htmlspecialchars($search) ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <select class="form-select" name="status" id="statusFilter">
                                        <option value="">All Statuses</option>
                                        <option value="draft" <?= $status_filter === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="ordered" <?= $status_filter === 'ordered' ? 'selected' : '' ?>>Ordered</option>
                                        <option value="received" <?= $status_filter === 'received' ? 'selected' : '' ?>>Received</option>
                                        <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        <option value="partial" <?= $status_filter === 'partial' ? 'selected' : '' ?>>Partially Received</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <input type="date" class="form-control" name="date" 
                                           value="<?= htmlspecialchars($date_filter) ?>">
                                </div>
                                <div class="col-md-1 mb-2 text-end">
                                    <a href="read.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover po-table mb-0">
                                <thead>
                                    <tr>
                                        <th>PO #</th>
                                        <th>Supplier</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($purchase_orders as $po): ?>
                                        <?php
                                        // Determine status badge
                                        $badge_class = '';
                                        switch ($po['status']) {
                                            case 'draft': $badge_class = 'badge-draft'; break;
                                            case 'ordered': $badge_class = 'badge-ordered'; break;
                                            case 'received': $badge_class = 'badge-received'; break;
                                            case 'cancelled': $badge_class = 'badge-cancelled'; break;
                                            case 'partial': $badge_class = 'badge-partial'; break;
                                        }
                                        ?>
                                        <tr>
                                            <td>PO-<?= htmlspecialchars($po['po_id']) ?></td>
                                            <td><?= htmlspecialchars($po['supplier_name']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($po['order_date'])) ?></td>
                                            <td><?= htmlspecialchars($po['item_count']) ?></td>
                                            <td>$<?= number_format($po['total_amount'], 2) ?></td>
                                            <td><span class="status-badge <?= $badge_class ?>"><?= ucfirst($po['status']) ?></span></td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="update.php?id=<?= $po['po_id'] ?>" class="btn btn-outline-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($po['status'] === 'draft' || $po['status'] === 'ordered'): ?>
                                                        <button class="btn btn-outline-danger" data-bs-toggle="modal" 
                                                                data-bs-target="#cancelModal<?= $po['po_id'] ?>">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if ($po['status'] === 'received'): ?>
                                                        <a href="#" class="btn btn-outline-success">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Cancel Modal for each PO -->
                                                <?php if ($po['status'] === 'draft' || $po['status'] === 'ordered'): ?>
                                                    <div class="modal fade" id="cancelModal<?= $po['po_id'] ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-warning text-dark">
                                                                    <h5 class="modal-title">Cancel Purchase Order</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>Are you sure you want to cancel PO-<?= $po['po_id'] ?>?</p>
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Cancellation Reason:</label>
                                                                        <select class="form-select">
                                                                            <option value="">Select reason</option>
                                                                            <option>No longer needed</option>
                                                                            <option>Found better price</option>
                                                                            <option>Supplier issue</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                    <a href="cancel.php?id=<?= $po['po_id'] ?>" class="btn btn-warning">Confirm Cancellation</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <nav aria-label="Page navigation">
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