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
    $supplier_id = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
    $order_date = filter_input(INPUT_POST, 'order_date', FILTER_SANITIZE_STRING);
    $expected_delivery = filter_input(INPUT_POST, 'expected_delivery', FILTER_SANITIZE_STRING);
    $shipping_method = filter_input(INPUT_POST, 'shipping_method', FILTER_SANITIZE_STRING);
    $payment_terms = filter_input(INPUT_POST, 'payment_terms', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    $items = $_POST['items'] ?? [];

    // Insert into database
    try {
        $pdo->beginTransaction();
        
        // Insert purchase order
        $stmt = $pdo->prepare("INSERT INTO purchase_orders (supplier_id, order_date, expected_delivery, 
                              shipping_method, payment_terms, notes, status, created_by) 
                              VALUES (?, ?, ?, ?, ?, ?, 'draft', ?)");
        $stmt->execute([$supplier_id, $order_date, $expected_delivery, $shipping_method, 
                       $payment_terms, $notes, $_SESSION['user_id']]);
        $po_id = $pdo->lastInsertId();
        
        // Insert PO items
        foreach ($items as $item) {
            $stmt = $pdo->prepare("INSERT INTO po_items (po_id, product_id, quantity, unit_price) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$po_id, $item['product_id'], $item['quantity'], $item['unit_price']]);
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Purchase order created successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error creating purchase order: " . $e->getMessage();
    }
}

// Get suppliers and products for dropdowns
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();
$products = $pdo->query("SELECT p.product_id, p.name, p.sku, p.unit_price 
                        FROM products p")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Purchase Order - InduStock</title>
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
        
        .po-form {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .po-item-card {
            border-left: 4px solid var(--success);
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 4px;
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
                    <h1><i class="fas fa-file-invoice-dollar me-2"></i>New Purchase Order</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>

                <div class="po-form">
                    <form method="POST" action="create.php" id="purchaseOrderForm">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Supplier*</label>
                                <select class="form-select" name="supplier_id" id="supplierSelect" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?= $supplier['supplier_id'] ?>">
                                            <?= htmlspecialchars($supplier['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Order Date*</label>
                                <input type="date" class="form-control" name="order_date" 
                                       value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Expected Delivery Date*</label>
                                <input type="date" class="form-control" name="expected_delivery" 
                                       min="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Shipping Method</label>
                                <select class="form-select" name="shipping_method">
                                    <option value="Standard">Standard (5-7 days)</option>
                                    <option value="Express">Express (2-3 days)</option>
                                </select>
                            </div>
                        </div>

                        <h4 class="mb-3"><i class="fas fa-boxes me-2"></i>Order Items</h4>
                        <div id="orderItemsContainer">
                            <!-- PO Item Template -->
                            <div class="po-item-card" data-item-index="0">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label">Product*</label>
                                        <select class="form-select item-product" name="items[0][product_id]" required>
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?= $product['product_id'] ?>" 
                                                    data-price="<?= $product['unit_price'] ?>">
                                                    <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Qty*</label>
                                        <input type="number" class="form-control item-qty" 
                                               name="items[0][quantity]" value="1" min="1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Unit Price*</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control item-price" 
                                                   name="items[0][unit_price]" step="0.01" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-item-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-4">
                            <div class="total-display me-3">
                                Total: $<span id="orderTotal">0.00</span>
                            </div>
                            <button type="button" class="btn btn-outline-primary" id="addItemBtn">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Payment Terms*</label>
                                <select class="form-select" name="payment_terms" required>
                                    <option value="Net 30">Net 30</option>
                                    <option value="Net 60">Net 60</option>
                                    <option value="Due on Receipt" selected>Due on Receipt</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"></textarea>
                        </div>

                        <div class="d-grid d-md-flex justify-content-md-end gap-2">
                            <button type="reset" class="btn btn-outline-danger">
                                <i class="fas fa-undo me-1"></i> Reset Form
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i> Submit Order
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
            let itemCounter = 0;
            
            // Calculate total
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.po-item-card').forEach(card => {
                    const qty = parseFloat(card.querySelector('.item-qty').value) || 0;
                    const price = parseFloat(card.querySelector('.item-price').value) || 0;
                    total += qty * price;
                });
                document.getElementById('orderTotal').textContent = total.toFixed(2);
            }
            
            // Add new item row
            document.getElementById('addItemBtn').addEventListener('click', function() {
                itemCounter++;
                const newItem = document.createElement('div');
                newItem.className = 'po-item-card';
                newItem.setAttribute('data-item-index', itemCounter);
                newItem.innerHTML = `
                    <div class="row g-3">
                        <div class="col-md-5">
                            <select class="form-select item-product" name="items[${itemCounter}][product_id]" required>
                                <option value="">Select Product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?= $product['product_id'] ?>" 
                                        data-price="<?= $product['unit_price'] ?>">
                                        <?= htmlspecialchars($product['name']) ?> (<?= htmlspecialchars($product['sku']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control item-qty" name="items[${itemCounter}][quantity]" 
                                   value="1" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control item-price" 
                                       name="items[${itemCounter}][unit_price]" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-danger w-100 remove-item-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                document.getElementById('orderItemsContainer').appendChild(newItem);
                
                // Add event listeners to new item
                newItem.querySelector('.item-product').addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    if (price) {
                        newItem.querySelector('.item-price').value = price;
                        calculateTotal();
                    }
                });
                
                newItem.querySelector('.item-qty').addEventListener('input', calculateTotal);
                newItem.querySelector('.item-price').addEventListener('input', calculateTotal);
            });
            
            // Remove item row
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-item-btn')) {
                    const itemCard = e.target.closest('.po-item-card');
                    if (document.querySelectorAll('.po-item-card').length > 1) {
                        itemCard.remove();
                        calculateTotal();
                    } else {
                        alert('At least one item is required');
                    }
                }
            });
            
            // Product change event
            document.querySelectorAll('.item-product').forEach(select => {
                select.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    if (price) {
                        this.closest('.po-item-card').querySelector('.item-price').value = price;
                        calculateTotal();
                    }
                });
            });
            
            // Quantity/price change events
            document.querySelectorAll('.item-qty, .item-price').forEach(input => {
                input.addEventListener('input', calculateTotal);
            });
            
            // Initialize total calculation
            calculateTotal();
        });
    </script>
</body>
</html>