<?php
session_start();
require_once __DIR__ . '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $unit_price = filter_input(INPUT_POST, 'unit_price', FILTER_VALIDATE_FLOAT);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $shelf = filter_input(INPUT_POST, 'shelf', FILTER_SANITIZE_STRING);
    $reorder_level = filter_input(INPUT_POST, 'reorder_level', FILTER_VALIDATE_INT);
    $expiry_date = filter_input(INPUT_POST, 'expiry_date', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);

    try {
        $pdo->beginTransaction();
        
        // Insert into inventory
        $stmt = $pdo->prepare("INSERT INTO inventory (product_id, quantity, location, last_updated) 
                              VALUES (?, ?, ?, NOW())");
        $stmt->execute([$product_id, $quantity, $location]);
        
        // Update product reorder level if provided
        if ($reorder_level) {
            $stmt = $pdo->prepare("UPDATE products SET reorder_level = ? WHERE product_id = ?");
            $stmt->execute([$reorder_level, $product_id]);
        }
        
        // Log transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (product_id, user_id, transaction_type, quantity, notes) 
                              VALUES (?, ?, 'in', ?, ?)");
        $stmt->execute([$product_id, $_SESSION['user_id'], $quantity, $notes]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Inventory item added successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error adding inventory item: " . $e->getMessage();
    }
}

// Get products for dropdown
$products = $pdo->query("SELECT product_id, name, sku FROM products ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #28a745;
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
        
        .inventory-form {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .form-section {
            border-left: 4px solid var(--success);
            padding-left: 15px;
            margin-bottom: 25px;
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
            
            .form-section {
                padding-left: 10px;
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
                    <h1><i class="fas fa-boxes me-2"></i>Add Inventory Item</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                    </a>
                </div>

                <div class="inventory-form">
                    <form method="POST" action="create.php" enctype="multipart/form-data">
                        <div class="form-section">
                            <h4><i class="fas fa-info-circle me-2"></i>Item Information</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Product*</label>
                                    <select class="form-select" name="product_id" required>
                                        <option value="">Select Product</option>
                                        <?php foreach ($products as $product): ?>
                                            <option value="<?= $product['product_id'] ?>">
                                                <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantity*</label>
                                    <input type="number" name="quantity" class="form-control" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Unit Price*</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" name="unit_price" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-map-marker-alt me-2"></i>Location Details</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Warehouse*</label>
                                    <select class="form-select" name="location" required>
                                        <option value="">Select Warehouse</option>
                                        <option value="Warehouse A">Warehouse A</option>
                                        <option value="Warehouse B">Warehouse B</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Shelf/Bin Location</label>
                                    <input type="text" name="shelf" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-section">
                            <h4><i class="fas fa-cog me-2"></i>Additional Settings</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Reorder Level</label>
                                    <input type="number" name="reorder_level" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="expiry_date" class="form-control">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" name="notes" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="d-grid d-md-flex justify-content-md-end gap-2">
                            <button type="reset" class="btn btn-outline-danger">
                                <i class="fas fa-undo me-1"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Add to Inventory
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
    </script>
</body>
</html>