<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if PO ID is provided
if (!isset($_GET['id'])) {
    header('Location: read.php');
    exit();
}

$po_id = (int)$_GET['id'];

// Get PO data
$stmt = $pdo->prepare("SELECT po.*, s.name as supplier_name 
                      FROM purchase_orders po
                      LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
                      WHERE po.po_id = ?");
$stmt->execute([$po_id]);
$purchase_order = $stmt->fetch();

if (!$purchase_order) {
    header('Location: read.php');
    exit();
}

// Get PO items
$stmt = $pdo->prepare("SELECT poi.*, p.name as product_name, p.sku 
                      FROM po_items poi
                      LEFT JOIN products p ON poi.product_id = p.product_id
                      WHERE poi.po_id = ?");
$stmt->execute([$po_id]);
$po_items = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $expected_delivery = filter_input(INPUT_POST, 'expected_delivery', FILTER_SANITIZE_STRING);
    $shipping_method = filter_input(INPUT_POST, 'shipping_method', FILTER_SANITIZE_STRING);
    $payment_terms = filter_input(INPUT_POST, 'payment_terms', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $items = $_POST['items'] ?? [];

    try {
        $pdo->beginTransaction();
        
        // Update purchase order
        $stmt = $pdo->prepare("UPDATE purchase_orders SET 
                              expected_delivery = ?, shipping_method = ?, 
                              payment_terms = ?, notes = ?, status = ?
                              WHERE po_id = ?");
        $stmt->execute([$expected_delivery, $shipping_method, $payment_terms, 
                       $notes, $status, $po_id]);
        
        // Update received quantities
        foreach ($items as $item_id => $item) {
            $received_qty = (int)$item['received_qty'];
            $stmt = $pdo->prepare("UPDATE po_items SET received_quantity = ? 
                                  WHERE po_item_id = ?");
            $stmt->execute([$received_qty, $item_id]);
            
            // If item is received, update inventory
            if ($received_qty > 0 && $status === 'received') {
                $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity + ? 
                                      WHERE product_id = (SELECT product_id FROM po_items WHERE po_item_id = ?)");
                $stmt->execute([$received_qty, $item_id]);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Purchase order updated successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error updating purchase order: " . $e->getMessage();
    }
}

// Get suppliers and products for dropdowns
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();
$products = $pdo->query("SELECT p.product_id, p.name, p.sku, p.unit_price 
                        FROM products p")->fetchAll();

// Calculate total amount
$total_amount = 0;
foreach ($po_items as $item) {
    $total_amount += $item['quantity'] * $item['unit_price'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Purchase Order - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --warning: #ffc107;
            --success: #28a745;
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
        
        .po-header {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .po-item-card {
            border-left: 4px solid var(--warning);
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 4px;
            position: relative;
        }
        
        .received-check {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .total-display {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary);
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
            
            .po-item-card .row > div {
                margin-bottom: 10px;
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
                    <h1><i class="fas fa-edit me-2"></i>Edit Purchase Order</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>

                <div class="po-header">
                    <div class="d-flex justify-content-between">
                        <h3>PO-<?= htmlspecialchars($purchase_order['po_id']) ?></h3>
                        <span class="badge bg-primary">
                            Last Updated: <?= date('Y-m-d', strtotime($purchase_order['updated_at'])) ?>
                        </span>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <strong>Supplier:</strong> <?= htmlspecialchars($purchase_order['supplier_name']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Order Date:</strong> <?= date('Y-m-d', strtotime($purchase_order['order_date'])) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Status:</strong> 
                            <?php 
                            $badge_class = '';
                            switch ($purchase_order['status']) {
                                case 'draft': $badge_class = 'badge-draft'; break;
                                case 'ordered': $badge_class = 'badge-ordered'; break;
                                case 'received': $badge_class = 'badge-received'; break;
                                case 'cancelled': $badge_class = 'badge-cancelled'; break;
                                case 'partial': $badge_class = 'badge-partial'; break;
                            }
                            ?>
                            <span class="status-badge <?= $badge_class ?>">
                                <?= ucfirst($purchase_order['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="update.php?id=<?= $po_id ?>">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Supplier*</label>
                                    <select class="form-select" required disabled>
                                        <option selected><?= htmlspecialchars($purchase_order['supplier_name']) ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expected Delivery Date*</label>
                                    <input type="date" class="form-control" name="expected_delivery" 
                                           value="<?= htmlspecialchars($purchase_order['expected_delivery']) ?>" required>
                                </div>
                            </div>

                            <h4 class="mb-3"><i class="fas fa-boxes me-2"></i>Order Items</h4>
                            <div id="orderItemsContainer">
                                <?php foreach ($po_items as $item): ?>
                                    <div class="po-item-card">
                                        <div class="form-check form-switch received-check">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="received<?= $item['po_item_id'] ?>" 
                                                   <?= $item['received_quantity'] > 0 ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="received<?= $item['po_item_id'] ?>">
                                                Received
                                            </label>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label">Product*</label>
                                                <select class="form-select" required disabled>
                                                    <option selected>
                                                        <?= htmlspecialchars($item['product_name']) ?> (<?= htmlspecialchars($item['sku']) ?>)
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Qty*</label>
                                                <input type="number" class="form-control" 
                                                       value="<?= htmlspecialchars($item['quantity']) ?>" min="1" disabled>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label">Received Qty*</label>
                                                <input type="number" class="form-control" 
                                                       name="items[<?= $item['po_item_id'] ?>][received_qty]" 
                                                       value="<?= htmlspecialchars($item['received_quantity']) ?>" 
                                                       min="0" max="<?= htmlspecialchars($item['quantity']) ?>" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Unit Price*</label>
                                                <div class="input-group">
                                                    <span class="input-group-text">$</span>
                                                    <input type="number" class="form-control" 
                                                           value="<?= htmlspecialchars($item['unit_price']) ?>" step="0.01" disabled>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="d-flex justify-content-end mb-4">
                                <div class="total-display me-3">
                                    Total: $<?= number_format($total_amount, 2) ?>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Shipping Method</label>
                                    <select class="form-select" name="shipping_method">
                                        <option value="Standard" <?= $purchase_order['shipping_method'] === 'Standard' ? 'selected' : '' ?>>Standard (5-7 days)</option>
                                        <option value="Express" <?= $purchase_order['shipping_method'] === 'Express' ? 'selected' : '' ?>>Express (2-3 days)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status*</label>
                                    <select class="form-select" name="status" required>
                                        <option value="draft" <?= $purchase_order['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                        <option value="ordered" <?= $purchase_order['status'] === 'ordered' ? 'selected' : '' ?>>Ordered</option>
                                        <option value="partial" <?= $purchase_order['status'] === 'partial' ? 'selected' : '' ?>>Partially Received</option>
                                        <option value="received" <?= $purchase_order['status'] === 'received' ? 'selected' : '' ?>>Received</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($purchase_order['notes']) ?></textarea>
                            </div>

                            <div class="d-grid d-md-flex justify-content-md-end gap-2">
                                <?php if ($purchase_order['status'] !== 'cancelled'): ?>
                                    <button type="button" class="btn btn-danger me-md-2" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                        <i class="fas fa-times me-1"></i> Cancel Order
                                    </button>
                                <?php endif; ?>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-1"></i> Update Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Order Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">Cancel Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel PO-<?= $purchase_order['po_id'] ?>?</p>
                    <div class="mb-3">
                        <label class="form-label">Cancellation Reason*</label>
                        <select class="form-select" required>
                            <option value="">Select reason</option>
                            <option>No longer needed</option>
                            <option>Found better price</option>
                            <option>Supplier issue</option>
                        </select>
                    </div>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. All received items will be removed from inventory.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="cancel.php?id=<?= $po_id ?>" class="btn btn-warning">Confirm Cancellation</a>
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

        // Toggle received status
        document.querySelectorAll('.form-check-input').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const receivedQty = this.closest('.po-item-card').querySelector('input[name*="received_qty"]');
                if (this.checked) {
                    receivedQty.value = receivedQty.max;
                } else {
                    receivedQty.value = 0;
                }
            });
        });
    </script>
</body>
</html>