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
    $_SESSION['error_message'] = "No inventory item specified for deletion.";
    header('Location: read.php');
    exit();
}

$inventory_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Get inventory item details
$stmt = $pdo->prepare("SELECT i.*, p.name as product_name, p.sku 
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
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    try {
        $pdo->beginTransaction();
        
        // Delete from inventory
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE inventory_id = ?");
        $stmt->execute([$inventory_id]);
        
        // Log transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (product_id, user_id, transaction_type, quantity, notes) 
                              VALUES (?, ?, 'out', ?, ?)");
        $stmt->execute([$item['product_id'], $_SESSION['user_id'], $item['quantity'], 
                        "Deleted from inventory. Reason: $reason. Notes: $notes"]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Inventory item deleted successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error deleting inventory item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Inventory - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --danger: #dc3545;
            --warning: #ffc107;
            --primary: #2c3e50;
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
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .item-details {
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
            display: flex;
            align-items: center;
        }
        
        .impact-list li:last-child {
            border-bottom: none;
        }
        
        .impact-icon {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            color: var(--danger);
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
                    <h1><i class="fas fa-trash-alt me-2 text-danger"></i>Delete Inventory Item</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                    </a>
                </div>

                <div class="confirmation-panel">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Permanent Deletion</h4>
                        <p class="mb-0">This will permanently remove this item from inventory records.</p>
                    </div>

                    <div class="item-details">
                        <div class="text-center mb-3">
                            <img src="https://via.placeholder.com/100?text=<?= substr($item['product_name'], 0, 2) ?>" width="80" class="rounded mb-2">
                            <h3><?= htmlspecialchars($item['product_name']) ?></h3>
                            <p class="text-muted">SKU: <?= htmlspecialchars($item['sku']) ?> | Location: <?= htmlspecialchars($item['location']) ?></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Current Stock:</strong> <?= htmlspecialchars($item['quantity']) ?></p>
                                <p><strong>Last Updated:</strong> <?= date('Y-m-d', strtotime($item['last_updated'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    <span class="badge <?= $item['quantity'] <= 0 ? 'bg-danger' : ($item['quantity'] <= 10 ? 'bg-warning' : 'bg-success') ?>">
                                        <?= $item['quantity'] <= 0 ? 'Out of Stock' : ($item['quantity'] <= 10 ? 'Low Stock' : 'In Stock') ?>
                                    </span>
                                </p>
                                <p><strong>Value:</strong> $<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="delete.php?id=<?= $inventory_id ?>">
                        <div class="mb-4">
                            <h5><i class="fas fa-exclamation-circle text-warning me-2"></i>This will:</h5>
                            <ul class="impact-list">
                                <li>
                                    <span class="impact-icon"><i class="fas fa-box-open"></i></span>
                                    Permanently remove <?= htmlspecialchars($item['quantity']) ?> units from inventory
                                </li>
                                <li>
                                    <span class="impact-icon"><i class="fas fa-file-invoice"></i></span>
                                    Create an audit record of this deletion
                                </li>
                                <li>
                                    <span class="impact-icon"><i class="fas fa-ban"></i></span>
                                    Prevent future transactions for this item
                                </li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Reason for Deletion*</label>
                            <select class="form-select" name="reason" id="deleteReason" required>
                                <option value="">Select reason</option>
                                <option>Discontinued item</option>
                                <option>Inventory correction</option>
                                <option>Damaged beyond repair</option>
                                <option>Other (specify below)</option>
                            </select>
                        </div>

                        <div class="mb-4" id="otherReasonContainer" style="display: none;">
                            <label class="form-label">Please specify*</label>
                            <textarea class="form-control" rows="2" name="notes" id="otherReason"></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this will permanently remove all records of this inventory item
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="read.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button class="btn btn-danger btn-delete" id="deleteBtn" disabled>
                                <i class="fas fa-trash-alt me-1"></i> Permanently Delete
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
            const deleteReason = document.getElementById('deleteReason');
            const otherReasonContainer = document.getElementById('otherReasonContainer');
            const confirmCheckbox = document.getElementById('confirmDelete');
            const deleteBtn = document.getElementById('deleteBtn');

            // Show/hide other reason field
            deleteReason.addEventListener('change', function() {
                otherReasonContainer.style.display = this.value === 'Other (specify below)' ? 'block' : 'none';
                updateDeleteButton();
            });

            // Enable/disable delete button
            confirmCheckbox.addEventListener('change', updateDeleteButton);
            
            function updateDeleteButton() {
                const reasonValid = deleteReason.value && 
                                  (deleteReason.value !== 'Other (specify below)' || 
                                   document.getElementById('otherReason').value.trim());
                deleteBtn.disabled = !(reasonValid && confirmCheckbox.checked);
            }

            // Validate other reason field if shown
            document.getElementById('otherReason')?.addEventListener('input', updateDeleteButton);
        });
    </script>
</body>
</html>
