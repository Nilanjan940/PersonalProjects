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
$stmt = $pdo->prepare("SELECT p.*, i.quantity, c.name as category_name
                      FROM products p
                      LEFT JOIN inventory i ON p.product_id = i.product_id
                      LEFT JOIN categories c ON p.category_id = c.category_id
                      WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: read.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $other_reason = filter_input(INPUT_POST, 'other_reason', FILTER_SANITIZE_STRING);
    
    try {
        $pdo->beginTransaction();
        
        // Delete inventory record
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // Log the deletion (in a real app, you'd have an audit table)
        // $stmt = $pdo->prepare("INSERT INTO deletion_log (...) VALUES (...)");
        // $stmt->execute([...]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Product deleted successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error deleting product: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - InduStock</title>
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
        
        /* Confirmation Panel */
        .confirmation-panel {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .product-preview {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .btn-delete {
            transition: all 0.3s;
        }
        
        .btn-delete:disabled {
            opacity: 0.65;
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
            .confirmation-panel {
                padding: 20px;
            }
            
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
                    <h1><i class="fas fa-trash-alt me-2"></i>Delete Product</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Products
                    </a>
                </div>

                <div class="confirmation-panel">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Warning!</h4>
                        <p class="mb-0">You are about to permanently delete this product.</p>
                    </div>

                    <div class="product-preview">
                        <div class="text-center mb-3">
                            <img src="https://via.placeholder.com/120x120?text=<?= urlencode(substr($product['name'], 0, 1)) ?>" width="80" class="rounded">
                        </div>
                        <div class="text-center">
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>SKU:</strong> <?= htmlspecialchars($product['sku']) ?></p>
                                    <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Current Stock:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                                    <p class="mb-1"><strong>Price:</strong> $<?= number_format($product['unit_price'], 2) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i> This action cannot be undone. All inventory records for this product will be permanently deleted.
                    </div>

                    <form method="POST" action="delete.php?id=<?= $product_id ?>">
                        <div class="mb-4">
                            <label class="form-label">Reason for Deletion*</label>
                            <select class="form-select" id="deleteReason" name="reason" required>
                                <option value="">Select reason</option>
                                <option>Product discontinued</option>
                                <option>No longer in inventory</option>
                                <option>Other (specify below)</option>
                            </select>
                        </div>

                        <div class="mb-4" id="otherReasonContainer" style="display: none;">
                            <label class="form-label">Please specify reason</label>
                            <textarea class="form-control" rows="3" id="otherReason" name="other_reason"></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this action cannot be undone
                            </label>
                        </div>

                        <div class="text-end">
                            <a href="read.php" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button class="btn btn-danger btn-delete" id="confirmBtn" disabled>
                                <i class="fas fa-trash-alt me-1"></i> Confirm Delete
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
            const confirmCheckbox = document.getElementById('confirmDelete');
            const confirmBtn = document.getElementById('confirmBtn');
            const deleteReason = document.getElementById('deleteReason');
            const otherReasonContainer = document.getElementById('otherReasonContainer');

            // Show/hide other reason field
            deleteReason.addEventListener('change', function() {
                otherReasonContainer.style.display = this.value === 'Other (specify below)' ? 'block' : 'none';
                updateConfirmButton();
            });

            // Enable/disable confirm button
            confirmCheckbox.addEventListener('change', updateConfirmButton);
            document.getElementById('otherReason')?.addEventListener('input', updateConfirmButton);
            
            function updateConfirmButton() {
                const reasonValid = deleteReason.value && 
                                  (deleteReason.value !== 'Other (specify below)' || 
                                   document.getElementById('otherReason').value.trim());
                confirmBtn.disabled = !(reasonValid && confirmCheckbox.checked);
            }

            // Delete confirmation
            confirmBtn.addEventListener('click', function(e) {
                if (!confirm('Final confirmation: Permanently delete this product and all associated data?')) {
                    e.preventDefault();
                } else {
                    // Show loading state
                    const originalText = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...';
                    this.disabled = true;
                }
            });
        });
    </script>
</body>
</html>