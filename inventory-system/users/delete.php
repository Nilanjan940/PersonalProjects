<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: read.php');
    exit();
}

$userId = $_GET['id'];
$currentUserId = $_SESSION['user_id'];
$user = [];
$error = '';

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        header('Location: read.php');
        exit();
    }
    
    // Check if trying to delete own account
    if ($user['user_id'] == $currentUserId) {
        $error = "You cannot delete your own account while logged in";
    }
} catch (PDOException $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) {
    $reason = trim($_POST['reason']);
    $otherReason = trim($_POST['other_reason'] ?? '');
    $finalReason = $reason === 'Other' ? $otherReason : $reason;
    
    if (empty($reason)) {
        $error = "Please select a reason for deletion";
    } elseif ($reason === 'Other' && empty($otherReason)) {
        $error = "Please specify the reason for deletion";
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // 1. Find an admin user to reassign records to (excluding current user)
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE role = 'admin' AND user_id != ? LIMIT 1");
            $stmt->execute([$userId]);
            $adminUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$adminUser) {
                throw new Exception("Cannot delete user - no other admin exists to reassign records to");
            }
            
            $reassignTo = $adminUser['user_id'];
            $affectedTables = [];
            
            // 2. Reassign transactions
            $stmt = $pdo->prepare("UPDATE transactions SET user_id = ? WHERE user_id = ?");
            $stmt->execute([$reassignTo, $userId]);
            if ($stmt->rowCount() > 0) $affectedTables[] = 'transactions';
            
            // 3. Reassign purchase orders
            $stmt = $pdo->prepare("UPDATE purchase_orders SET created_by = ? WHERE created_by = ?");
            $stmt->execute([$reassignTo, $userId]);
            if ($stmt->rowCount() > 0) $affectedTables[] = 'purchase_orders';
            
            // 4. Reassign reports
            $stmt = $pdo->prepare("UPDATE reports SET created_by = ? WHERE created_by = ?");
            $stmt->execute([$reassignTo, $userId]);
            if ($stmt->rowCount() > 0) $affectedTables[] = 'reports';
            
            // 5. Reassign report logs
            $stmt = $pdo->prepare("UPDATE report_logs SET user_id = ? WHERE user_id = ?");
            $stmt->execute([$reassignTo, $userId]);
            if ($stmt->rowCount() > 0) $affectedTables[] = 'report_logs';
            
            // 6. Create audit log before deletion
            $stmt = $pdo->prepare("INSERT INTO delete_logs (
                deleted_user_id, 
                deleted_username, 
                deleted_by, 
                deletion_reason, 
                reassigned_to, 
                affected_tables
            ) VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $userId,
                $user['username'],
                $currentUserId,
                $finalReason,
                $reassignTo,
                implode(', ', $affectedTables) ?: 'None'
            ]);
            
            // 7. Now delete the user
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            // Commit transaction
            $pdo->commit();
            
            $_SESSION['success_message'] = sprintf(
                "User %s deleted successfully! %d records reassigned to admin ID %d",
                htmlspecialchars($user['username']),
                count($affectedTables),
                $reassignTo
            );
            header('Location: read.php');
            exit();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error deleting user: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --danger: #dc3545;
            --warning: #ffc107;
            --primary: #2c3e50;
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
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .user-details {
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
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            display: block;
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
            
            .confirmation-panel {
                padding: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .confirmation-panel {
                padding: 15px;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            .user-details .row > div {
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-user-slash me-2 text-danger"></i>Delete User</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Users
                    </a>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <div class="confirmation-panel">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Warning: Permanent User Deletion</h4>
                        <p class="mb-0">This will permanently remove the user account and associated data.</p>
                    </div>

                    <div class="user-details">
                        <img src="https://via.placeholder.com/100x100?text=<?= strtoupper(substr($user['full_name'], 0, 1)) ?>" class="user-avatar">
                        <div class="text-center mb-3">
                            <h3 id="userName"><?= htmlspecialchars($user['full_name']) ?></h3>
                            <p class="text-muted" id="userRole">User ID: <?= htmlspecialchars($user['user_id']) ?> | <?= ucfirst($user['role']) ?></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Email:</strong> <span id="userEmail"><?= htmlspecialchars($user['email']) ?></span></p>
                                <p><strong>Last Login:</strong> <span id="lastLogin"><?= $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never' ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Account Created:</strong> <span id="accountCreated"><?= date('Y-m-d', strtotime($user['created_at'])) ?></span></p>
                                <p><strong>Status:</strong> <span class="badge bg-success" id="userStatus">Active</span></p>
                            </div>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="mb-4">
                            <h5><i class="fas fa-exclamation-circle text-warning me-2"></i>This deletion will:</h5>
                            <ul class="impact-list">
                                <li>
                                    <span class="impact-icon"><i class="fas fa-user-times"></i></span>
                                    Permanently remove this user account
                                </li>
                                <li>
                                    <span class="impact-icon"><i class="fas fa-exchange-alt"></i></span>
                                    Reassign all records to another admin user
                                </li>
                                <li>
                                    <span class="impact-icon"><i class="fas fa-history"></i></span>
                                    Create a permanent audit log of this action
                                </li>
                                <li>
                                    <span class="impact-icon"><i class="fas fa-envelope"></i></span>
                                    Preserve all historical data with new ownership
                                </li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Reason for Deletion*</label>
                            <select class="form-select" name="reason" id="deleteReason" required>
                                <option value="">Select a reason</option>
                                <option value="Employee left">Employee left the company</option>
                                <option value="Account not needed">Account no longer needed</option>
                                <option value="Security concern">Security concern</option>
                                <option value="Other">Other (please specify)</option>
                            </select>
                        </div>

                        <div class="mb-4" id="otherReasonContainer" style="display: none;">
                            <label class="form-label">Please specify reason*</label>
                            <textarea class="form-control" rows="2" name="other_reason" id="otherReason"></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" name="confirm" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this action is permanent and cannot be undone
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="read.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-danger btn-delete" id="deleteBtn">
                                <i class="fas fa-trash-alt me-1"></i> Permanently Delete User
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

        // User deletion functionality
        document.addEventListener('DOMContentLoaded', function() {
            const deleteReason = document.getElementById('deleteReason');
            const otherReasonContainer = document.getElementById('otherReasonContainer');
            const confirmCheckbox = document.getElementById('confirmDelete');
            const deleteBtn = document.getElementById('deleteBtn');

            // Show/hide other reason field
            deleteReason.addEventListener('change', function() {
                otherReasonContainer.style.display = this.value === 'Other' ? 'block' : 'none';
            });

            // Confirm before deletion
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!confirm('Final confirmation: Permanently delete this user account?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>