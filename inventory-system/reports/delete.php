<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit();
}

// Check if report ID is provided
if (!isset($_GET['id'])) {
    header('Location: read.php');
    exit();
}

$report_id = $_GET['id'];

// Fetch report data
$stmt = $pdo->prepare("SELECT r.*, u.username as created_by 
                      FROM reports r
                      LEFT JOIN users u ON r.created_by = u.user_id
                      WHERE r.report_id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) {
    $_SESSION['error_message'] = "Report not found";
    header('Location: read.php');
    exit();
}

// Fetch report formats
$stmt = $pdo->prepare("SELECT format FROM report_formats WHERE report_id = ?");
$stmt->execute([$report_id]);
$formats = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $other_reason = filter_input(INPUT_POST, 'other_reason', FILTER_SANITIZE_STRING);
    
    try {
        $pdo->beginTransaction();
        
        // Delete report formats
        $stmt = $pdo->prepare("DELETE FROM report_formats WHERE report_id = ?");
        $stmt->execute([$report_id]);
        
        // Delete report
        $stmt = $pdo->prepare("DELETE FROM reports WHERE report_id = ?");
        $stmt->execute([$report_id]);
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Report deleted successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error deleting report: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Report - InduStock</title>
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
            max-width: 600px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .report-details {
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
                    <h1><i class="fas fa-trash-alt me-2 text-danger"></i>Delete Report</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reports
                    </a>
                </div>

                <div class="confirmation-panel">
                    <div class="alert alert-danger">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i>Permanent Deletion Warning</h4>
                        <p class="mb-0">This action cannot be undone - all report data will be permanently removed.</p>
                    </div>

                    <div class="report-details">
                        <div class="text-center mb-3">
                            <h3><?= htmlspecialchars($report['name']) ?></h3>
                            <p class="text-muted">Report ID: <?= $report_id ?></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Type:</strong> <?= ucfirst($report['type']) ?></p>
                                <p><strong>Created:</strong> <?= date('Y-m-d', strtotime($report['created_at'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Created By:</strong> <?= htmlspecialchars($report['created_by']) ?></p>
                                <p><strong>Formats:</strong> <?= implode(", ", array_map('strtoupper', $formats)) ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5><i class="fas fa-exclamation-circle text-warning me-2"></i>This will permanently delete:</h5>
                        <ul class="impact-list">
                            <li>
                                <span class="impact-icon"><i class="fas fa-file-pdf"></i></span>
                                PDF version of this report
                            </li>
                            <li>
                                <span class="impact-icon"><i class="fas fa-file-excel"></i></span>
                                Excel spreadsheet export
                            </li>
                            <li>
                                <span class="impact-icon"><i class="fas fa-database"></i></span>
                                Report configuration and history
                            </li>
                        </ul>
                    </div>

                    <form method="POST" action="delete.php?id=<?= $report_id ?>">
                        <div class="mb-4">
                            <label class="form-label">Reason for Deletion*</label>
                            <select class="form-select" name="reason" id="deleteReason" required>
                                <option value="">Select a reason</option>
                                <option value="no_longer_needed">Report no longer needed</option>
                                <option value="outdated">Data is outdated</option>
                                <option value="duplicate">Duplicate report</option>
                                <option value="other">Other (please specify)</option>
                            </select>
                        </div>

                        <div class="mb-4" id="otherReasonContainer" style="display: none;">
                            <label class="form-label">Please specify reason</label>
                            <textarea class="form-control" rows="2" name="other_reason" id="otherReason"></textarea>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirmDelete" required>
                            <label class="form-check-label" for="confirmDelete">
                                I understand this action is permanent and cannot be undone
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="read.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            <button class="btn btn-danger" id="deleteBtn" disabled>
                                <i class="fas fa-trash-alt me-1"></i> Permanently Delete Report
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

        // Delete confirmation functionality
        document.addEventListener('DOMContentLoaded', function() {
            const deleteReason = document.getElementById('deleteReason');
            const otherReasonContainer = document.getElementById('otherReasonContainer');
            const confirmCheckbox = document.getElementById('confirmDelete');
            const deleteBtn = document.getElementById('deleteBtn');

            // Show/hide other reason field
            deleteReason.addEventListener('change', function() {
                otherReasonContainer.style.display = this.value === 'other' ? 'block' : 'none';
                updateDeleteButton();
            });

            // Enable/disable delete button
            confirmCheckbox.addEventListener('change', updateDeleteButton);
            
            function updateDeleteButton() {
                const reasonValid = deleteReason.value && 
                                  (deleteReason.value !== 'other' || 
                                   document.getElementById('otherReason').value.trim());
                deleteBtn.disabled = !(reasonValid && confirmCheckbox.checked);
            }

            // Validate other reason field if shown
            document.getElementById('otherReason')?.addEventListener('input', updateDeleteButton);
        });
    </script>
</body>
</html>