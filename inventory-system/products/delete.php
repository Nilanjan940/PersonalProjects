<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if product ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "Invalid product ID";
    header('Location: read.php');
    exit();
}

$product_id = (int)$_GET['id'];

// Get product data with additional checks
try {
    $stmt = $pdo->prepare("SELECT p.*, i.quantity, c.name as category_name,
                          (SELECT COUNT(*) FROM order_items WHERE product_id = p.product_id) as order_count
                          FROM products p
                          LEFT JOIN inventory i ON p.product_id = i.product_id
                          LEFT JOIN categories c ON p.category_id = c.category_id
                          WHERE p.product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = "Product not found";
        header('Location: read.php');
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    header('Location: read.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $other_reason = filter_input(INPUT_POST, 'other_reason', FILTER_SANITIZE_STRING);
    $force_delete = isset($_POST['force_delete']) && $_POST['force_delete'] === '1';

    try {
        $pdo->beginTransaction();

        // Check if product has associated orders
        if ($product['order_count'] > 0 && !$force_delete) {
            throw new Exception("This product has {$product['order_count']} associated order(s). Check 'Force Delete' to proceed.");
        }

        // Delete from inventory first
        $stmt = $pdo->prepare("DELETE FROM inventory WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Delete product
        $stmt = $pdo->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);

        // Log the deletion in products table (alternative since we don't have deletion_logs)
        // We'll add a deleted_by and deletion_reason column to products table temporarily
        $log_reason = ($reason === 'Other') ? $other_reason : $reason;
        
        $pdo->commit();

        $_SESSION['success_message'] = "Product '{$product['name']}' deleted successfully!";
        header('Location: read.php');
        exit();
    } catch (Exception $e) {
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
        
        .confirmation-panel {
            max-width: 700px;
        }
        
        .impact-warning {
            border-left: 4px solid var(--danger);
            padding-left: 15px;
        }
        
        .product-preview img {
            max-width: 100px;
            height: auto;
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

                <div class="confirmation-panel mx-auto">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Warning!</h4>
                        <p>You are about to permanently delete this product.</p>
                    </div>

                    <div class="product-preview text-center p-4 bg-light rounded mb-4">
                        <img src="https://via.placeholder.com/120x120?text=<?= urlencode(substr($product['name'], 0, 1)) ?>" 
                             class="rounded-circle mb-3" alt="<?= htmlspecialchars($product['name']) ?>">
                        <h3><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <p><strong>SKU:</strong> <?= htmlspecialchars($product['sku']) ?></p>
                                <p><strong>Category:</strong> <?= htmlspecialchars($product['category_name']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Stock:</strong> <?= htmlspecialchars($product['quantity']) ?></p>
                                <p><strong>Price:</strong> $<?= number_format($product['unit_price'], 2) ?></p>
                            </div>
                        </div>
                    </div>

                    <?php if ($product['order_count'] > 0): ?>
                    <div class="alert alert-warning impact-warning mb-4">
                        <h5><i class="fas fa-exclamation-circle me-2"></i>Important Notice</h5>
                        <p>This product has <?= $product['order_count'] ?> associated order(s).</p>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="forceDelete" name="force_delete" value="1">
                            <label class="form-check-label" for="forceDelete">
                                Force delete (will break order history links)
                            </label>
                        </div>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="delete.php?id=<?= $product_id ?>">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Reason for Deletion*</label>
                            <select class="form-select" id="deleteReason" name="reason" required>
                                <option value="">Select reason</option>
                                <option>Product discontinued</option>
                                <option>No longer stocked</option>
                                <option>Duplicate entry</option>
                                <option>Data correction</option>
                                <option value="Other">Other (specify below)</option>
                            </select>
                        </div>

                        <div class="mb-4" id="otherReasonContainer" style="display: none;">
                            <label class="form-label fw-bold">Please specify reason*</label>
                            <textarea class="form-control" rows="3" id="otherReason" name="other_reason" required></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this action cannot be undone
                            </label>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="read.php" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger" id="confirmBtn" disabled>
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
        // Sidebar initialization (same as original)
        fetch('../assets/sidebar.html')
            .then(response => response.text())
            .then(data => {
                document.getElementById('sidebar-container').innerHTML = data;
                initializeSidebar();
            });

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

        // Delete confirmation logic
        document.addEventListener('DOMContentLoaded', function() {
            const confirmCheckbox = document.getElementById('confirmDelete');
            const confirmBtn = document.getElementById('confirmBtn');
            const deleteReason = document.getElementById('deleteReason');
            const otherReasonContainer = document.getElementById('otherReasonContainer');
            const forceDeleteCheckbox = document.getElementById('forceDelete');

            // Toggle other reason field
            deleteReason.addEventListener('change', function() {
                otherReasonContainer.style.display = this.value === 'Other' ? 'block' : 'none';
                updateConfirmButton();
            });

            // Update confirm button state
            function updateConfirmButton() {
                const reasonValid = deleteReason.value && 
                                  (deleteReason.value !== 'Other' || 
                                   document.getElementById('otherReason').value.trim());
                const forceDeleteValid = !(<?= $product['order_count'] ?> > 0) || forceDeleteCheckbox?.checked;
                confirmBtn.disabled = !(reasonValid && confirmCheckbox.checked && forceDeleteValid);
            }

            confirmCheckbox.addEventListener('change', updateConfirmButton);
            document.getElementById('otherReason')?.addEventListener('input', updateConfirmButton);
            if (forceDeleteCheckbox) {
                forceDeleteCheckbox.addEventListener('change', updateConfirmButton);
            }

            // Final confirmation
            confirmBtn.addEventListener('click', function(e) {
                if (!confirm('Are you absolutely sure you want to permanently delete this product?')) {
                    e.preventDefault();
                } else {
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';
                    this.disabled = true;
                }
            });
        });
    </script>
</body>
</html>