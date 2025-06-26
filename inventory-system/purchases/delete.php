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
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $other_reason = filter_input(INPUT_POST, 'other_reason', FILTER_SANITIZE_STRING);
    
    try {
        $pdo->beginTransaction();
        
        // Update PO status to cancelled
        $stmt = $pdo->prepare("UPDATE purchase_orders SET status = 'cancelled', notes = CONCAT(notes, '\nCancellation Reason: ', ?) 
                              WHERE po_id = ?");
        $stmt->execute([$reason === 'Other' ? $other_reason : $reason, $po_id]);
        
        // Remove received items from inventory
        foreach ($po_items as $item) {
            if ($item['received_quantity'] > 0) {
                $stmt = $pdo->prepare("UPDATE inventory SET quantity = quantity - ? 
                                      WHERE product_id = ?");
                $stmt->execute([$item['received_quantity'], $item['product_id']]);
            }
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Purchase order cancelled successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error cancelling purchase order: " . $e->getMessage();
    }
}

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
    <title>Cancel Purchase Order - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --danger: #dc3545;
            --warning: #ffc107;
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
        
        .confirmation-panel {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .po-details {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .impact-list {
            list-style-type: none;
            padding-left: 0;
        }
        
        .impact-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .impact-list li:last-child {
            border-bottom: none;
        }
        
        .btn-delete {
            transition: all 0.3s;
        }
        
        .btn-delete:disabled {
            opacity: 0.65;
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
                    <h1><i class="fas fa-times-circle me-2 text-danger"></i>Cancel Purchase Order</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>

                <div class="confirmation-panel">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Warning: Order Cancellation</h4>
                        <p class="mb-0">You are about to cancel this purchase order and reverse any received inventory.</p>
                    </div>

                    <div class="po-details">
                        <div class="text-center mb-3">
                            <h3>PO-<?= htmlspecialchars($purchase_order['po_id']) ?></h3>
                            <p class="text-muted">
                                Issued: <?= date('Y-m-d', strtotime($purchase_order['order_date'])) ?> | 
                                Supplier: <?= htmlspecialchars($purchase_order['supplier_name']) ?>
                            </p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
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
                                </p>
                                <p><strong>Total Value:</strong> $<?= number_format($total_amount, 2) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Items:</strong> <?= count($po_items) ?></p>
                                <p><strong>Last Updated:</strong> <?= date('Y-m-d', strtotime($purchase_order['updated_at'])) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5><i class="fas fa-exclamation-circle text-warning me-2"></i>This will affect:</h5>
                        <ul class="impact-list">
                            <?php 
                            $received_items = array_filter($po_items, function($item) {
                                return $item['received_quantity'] > 0;
                            });
                            ?>
                            <?php if (count($received_items) > 0): ?>
                                <?php foreach ($received_items as $item): ?>
                                    <li><i class="fas fa-undo text-danger me-2"></i> 
                                        <?= $item['received_quantity'] ?> <?= htmlspecialchars($item['product_name']) ?> 
                                        will be removed from inventory
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li><i class="fas fa-info-circle text-primary me-2"></i> No items have been received yet</li>
                            <?php endif; ?>
                            <li><i class="fas fa-file-invoice text-danger me-2"></i> Accounting records will be updated</li>
                            <li><i class="fas fa-envelope text-danger me-2"></i> Supplier will be notified automatically</li>
                        </ul>
                    </div>

                    <form method="POST" action="cancel.php?id=<?= $po_id ?>">
                        <div class="mb-4">
                            <label class="form-label">Cancellation Reason*</label>
                            <select class="form-select" id="cancelReason" name="reason" required>
                                <option value="">Select reason</option>
                                <option value="No longer needed">No longer needed</option>
                                <option value="Found better price">Found better price</option>
                                <option value="Supplier issue">Supplier issue</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                        </div>

                        <div class="mb-4" id="otherReasonContainer" style="display: none;">
                            <label class="form-label">Please specify reason*</label>
                            <textarea class="form-control" rows="3" name="other_reason" id="otherReason"></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmCancel" required>
                            <label class="form-check-label" for="confirmCancel">
                                I understand this action cannot be undone
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="read.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button class="btn btn-danger btn-delete" id="confirmBtn" disabled>
                                <i class="fas fa-trash-alt me-1"></i> Confirm Cancellation
                            </button>
                        </div>
                    </form>
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
            const confirmCheckbox = document.getElementById('confirmCancel');
            const confirmBtn = document.getElementById('confirmBtn');
            const cancelReason = document.getElementById('cancelReason');
            const otherReasonContainer = document.getElementById('otherReasonContainer');

            // Show/hide other reason field
            cancelReason.addEventListener('change', function() {
                otherReasonContainer.style.display = this.value === 'Other' ? 'block' : 'none';
                updateConfirmButton();
            });

            // Enable/disable confirm button
            confirmCheckbox.addEventListener('change', updateConfirmButton);
            document.getElementById('otherReason')?.addEventListener('input', updateConfirmButton);
            
            function updateConfirmButton() {
                const reasonValid = cancelReason.value && 
                                  (cancelReason.value !== 'Other' || 
                                   document.getElementById('otherReason').value.trim());
                confirmBtn.disabled = !(reasonValid && confirmCheckbox.checked);
            }

            // Form submission
            confirmBtn.addEventListener('click', function(e) {
                if (!confirm('Final confirmation: Cancel this purchase order and reverse all transactions?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>