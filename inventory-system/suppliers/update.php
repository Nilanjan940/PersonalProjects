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
    $_SESSION['error_message'] = "No supplier specified for update.";
    header('Location: read.php');
    exit();
}

$supplier_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Get supplier details
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE supplier_id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    $_SESSION['error_message'] = "Supplier not found.";
    header('Location: read.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $tax_id = filter_input(INPUT_POST, 'tax_id', FILTER_SANITIZE_STRING);
    $contact_person = filter_input(INPUT_POST, 'contact_person', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $website = filter_input(INPUT_POST, 'website', FILTER_SANITIZE_URL);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

    try {
        $pdo->beginTransaction();
        
        // Update supplier
        $stmt = $pdo->prepare("UPDATE suppliers 
                              SET name = ?, tax_id = ?, contact_person = ?, email = ?, 
                                  phone = ?, website = ?, address = ?, status = ?, type = ?
                              WHERE supplier_id = ?");
        $stmt->execute([$name, $tax_id, $contact_person, $email, $phone, $website, 
                       $address, $status, $type, $supplier_id]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Supplier updated successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error updating supplier: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier - InduStock</title>
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
        
        .supplier-profile {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
        
        .dot-active { background-color: var(--success); }
        .dot-pending { background-color: var(--warning); }
        
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
            .supplier-profile .row > div {
                margin-bottom: 10px;
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
                    <h1><i class="fas fa-edit me-2"></i>Edit Supplier</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Suppliers
                    </a>
                </div>

                <div class="supplier-profile">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <img src="https://via.placeholder.com/120x120?text=<?= substr($supplier['name'], 0, 2) ?>" id="supplierLogo" width="80" class="rounded-circle mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="changeLogoBtn">
                                <i class="fas fa-camera me-1"></i> Change Logo
                            </button>
                            <input type="file" id="logoUpload" accept="image/*" style="display:none">
                        </div>
                        <div class="col-md-10">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3><?= htmlspecialchars($supplier['name']) ?></h3>
                                    <div class="status-badge mb-2">
                                        <span class="status-dot <?= $supplier['status'] === 'active' ? 'dot-active' : ($supplier['status'] === 'pending' ? 'dot-pending' : '') ?>"></span>
                                        <span><?= ucfirst($supplier['status']) ?> Supplier</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge <?= $supplier['type'] === 'vip' ? 'bg-primary' : ($supplier['type'] === 'preferred' ? 'bg-success' : 'bg-secondary') ?>">
                                        <?= ucfirst($supplier['type']) ?> Supplier
                                    </span>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4"><strong>Contact:</strong> <?= htmlspecialchars($supplier['contact_person']) ?></div>
                                <div class="col-md-4"><strong>Email:</strong> <?= htmlspecialchars($supplier['email']) ?></div>
                                <div class="col-md-4"><strong>Since:</strong> <?= date('Y-m-d', strtotime($supplier['created_at'])) ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="POST" action="update.php?id=<?= $supplier_id ?>">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Supplier Name*</label>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($supplier['name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tax ID/VAT Number</label>
                                    <input type="text" name="tax_id" class="form-control" value="<?= htmlspecialchars($supplier['tax_id']) ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Contact Person*</label>
                                    <input type="text" name="contact_person" class="form-control" value="<?= htmlspecialchars($supplier['contact_person']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number*</label>
                                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($supplier['phone']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Email*</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($supplier['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="website" class="form-control" value="<?= htmlspecialchars($supplier['website']) ?>" placeholder="https://">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Full Address*</label>
                                <textarea class="form-control" name="address" rows="3" required><?= htmlspecialchars($supplier['address']) ?></textarea>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="active" <?= $supplier['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                        <option value="pending" <?= $supplier['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="inactive" <?= $supplier['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Supplier Type</label>
                                    <select class="form-select" name="type">
                                        <option value="regular" <?= $supplier['type'] === 'regular' ? 'selected' : '' ?>>Regular</option>
                                        <option value="vip" <?= $supplier['type'] === 'vip' ? 'selected' : '' ?>>VIP</option>
                                        <option value="preferred" <?= $supplier['type'] === 'preferred' ? 'selected' : '' ?>>Preferred</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="delete.php?id=<?= $supplier_id ?>" class="btn btn-danger">
                                    <i class="fas fa-trash me-1"></i> Delete Supplier
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Save Changes
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

        // Logo upload
        document.getElementById('changeLogoBtn').addEventListener('click', function() {
            document.getElementById('logoUpload').click();
        });

        document.getElementById('logoUpload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('supplierLogo').src = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>