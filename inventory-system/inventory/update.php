<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if ID is provided
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = "No inventory item specified for update.";
    header('Location: read.php');
    exit();
}

$inventory_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Get inventory item details
$stmt = $pdo->prepare("SELECT i.*, p.name as product_name, p.sku, p.unit_price, p.reorder_level 
                      FROM inventory i
                      JOIN products p ON i.product_id = p.product_id
                      WHERE i.inventory_id = ?");
$stmt->execute([$inventory_id]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error_message'] = "Inventory item not found.";
    header('Location: read.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adjustment_type = filter_input(INPUT_POST, 'adjustment_type', FILTER_SANITIZE_STRING);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $shelf = filter_input(INPUT_POST, 'shelf', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    try {
        $pdo->beginTransaction();
        
        // Calculate new quantity based on adjustment type
        $new_quantity = $item['quantity'];
        switch ($adjustment_type) {
            case 'add':
                $new_quantity += $quantity;
                break;
            case 'remove':
                $new_quantity -= $quantity;
                break;
            case 'set':
                $new_quantity = $quantity;
                break;
        }
        
        // Update inventory
        $stmt = $pdo->prepare("UPDATE inventory 
                              SET quantity = ?, location = ?, last_updated = NOW() 
                              WHERE inventory_id = ?");
        $stmt->execute([$new_quantity, $location, $inventory_id]);
        
        // Log transaction
        $transaction_type = $adjustment_type === 'add' ? 'in' : ($adjustment_type === 'remove' ? 'out' : 'adjustment');
        $transaction_quantity = abs($new_quantity - $item['quantity']);
        
        $stmt = $pdo->prepare("INSERT INTO transactions (product_id, user_id, transaction_type, quantity, notes) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$item['product_id'], $_SESSION['user_id'], $transaction_type, $transaction_quantity, 
                        "Inventory adjustment. Reason: $reason. Notes: $notes"]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Inventory item updated successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error updating inventory item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Inventory - InduStock</title>
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
        
        .inventory-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .adjustment-card {
            border-left: 4px solid var(--warning);
            padding-left: 15px;
            margin-bottom: 20px;
        }
        
        .qty-controls .btn {
            width: 40px;
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
            
            .inventory-details .row > div {
                margin-bottom: 10px;
            }
            
            .inventory-details .row > div:last-child {
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
                    <h1><i class="fas fa-edit me-2"></i>Edit Inventory Item</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                    </a>
                </div>

                <div class="inventory-details">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="https://via.placeholder.com/100?text=<?= substr($item['product_name'], 0, 2) ?>" class="img-fluid rounded mb-2">
                        </div>
                        <div class="col-md-10">
                            <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                            <div class="row">
                                <div class="col-md-3"><strong>SKU:</strong> <?= htmlspecialchars($item['sku']) ?></div>
                                <div class="col-md-3"><strong>Current Stock:</strong> <?= htmlspecialchars($item['quantity']) ?></div>
                                <div class="col-md-3"><strong>Location:</strong> <?= htmlspecialchars($item['location']) ?></div>
                                <div class="col-md-3"><strong>Status:</strong> 
                                    <span class="badge <?= $item['quantity'] <= 0 ? 'bg-danger' : ($item['quantity'] <= $item['reorder_level'] ? 'bg-warning' : 'bg-success') ?>">
                                        <?= $item['quantity'] <= 0 ? 'Out of Stock' : ($item['quantity'] <= $item['reorder_level'] ? 'Low Stock' : 'In Stock') ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="update.php?id=<?= $inventory_id ?>">
                            <div class="adjustment-card">
                                <h5><i class="fas fa-exchange-alt me-2"></i>Stock Adjustment</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Adjustment Type*</label>
                                        <select class="form-select" name="adjustment_type" id="adjustmentType" required>
                                            <option value="add">Add to Stock</option>
                                            <option value="remove">Remove from Stock</option>
                                            <option value="set">Set Exact Quantity</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity*</label>
                                        <div class="input-group qty-controls">
                                            <button type="button" class="btn btn-outline-secondary" id="decrementQty">-</button>
                                            <input type="number" class="form-control text-center" name="quantity" id="adjustQty" value="1" min="1" required>
                                            <button type="button" class="btn btn-outline-secondary" id="incrementQty">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason*</label>
                                    <select class="form-select" name="reason" required>
                                        <option value="">Select reason</option>
                                        <option>Received shipment</option>
                                        <option>Damaged goods</option>
                                        <option>Inventory correction</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Location*</label>
                                    <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($item['location']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Shelf/Bin</label>
                                    <input type="text" class="form-control" name="shelf" value="<?= htmlspecialchars($item['shelf'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($item['notes'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="delete.php?id=<?= $inventory_id ?>" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> Delete Item
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Inventory
                                </button>
                            </div>
                        </form>
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

        document.addEventListener('DOMContentLoaded', function() {
            // Quantity controls
            const qtyInput = document.getElementById('adjustQty');
            document.getElementById('incrementQty').addEventListener('click', () => {
                qtyInput.value = parseInt(qtyInput.value) + 1;
            });
            document.getElementById('decrementQty').addEventListener('click', () => {
                if (qtyInput.value > 1) qtyInput.value -= 1;
            });

            // Update quantity based on adjustment type
            document.getElementById('adjustmentType').addEventListener('change', function() {
                if (this.value === 'set') {
                    qtyInput.value = <?= $item['quantity'] ?>;
                }
            });
        });
    </script>
</body>
</html>