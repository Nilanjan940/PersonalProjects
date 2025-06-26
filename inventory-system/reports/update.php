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
$stmt = $pdo->prepare("SELECT r.*, GROUP_CONCAT(rf.format) as formats 
                      FROM reports r
                      LEFT JOIN report_formats rf ON r.report_id = rf.report_id
                      WHERE r.report_id = ?
                      GROUP BY r.report_id");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) {
    $_SESSION['error_message'] = "Report not found";
    header('Location: read.php');
    exit();
}

// Get categories and suppliers for filters
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM suppliers")->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $time_period = filter_input(INPUT_POST, 'time_period', FILTER_SANITIZE_STRING);
    $categories = $_POST['categories'] ?? [];
    $suppliers = $_POST['suppliers'] ?? [];
    $chart_type = filter_input(INPUT_POST, 'chart_type', FILTER_SANITIZE_STRING);
    $data_points = $_POST['data_points'] ?? [];
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    $formats = $_POST['formats'] ?? [];

    // Update in database
    try {
        $pdo->beginTransaction();
        
        // Update report
        $stmt = $pdo->prepare("UPDATE reports 
                              SET name = ?, type = ?, time_period = ?, chart_type = ?, notes = ?
                              WHERE report_id = ?");
        $stmt->execute([$name, $type, $time_period, $chart_type, $notes, $report_id]);
        
        // Delete existing formats
        $stmt = $pdo->prepare("DELETE FROM report_formats WHERE report_id = ?");
        $stmt->execute([$report_id]);
        
        // Insert new formats
        foreach ($formats as $format) {
            $stmt = $pdo->prepare("INSERT INTO report_formats (report_id, format) VALUES (?, ?)");
            $stmt->execute([$report_id, $format]);
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Report updated successfully!";
        header('Location: read.php');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error_message = "Error updating report: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Report - InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --info: #17a2b8;
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
        
        .report-builder {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .report-section {
            border-left: 4px solid var(--info);
            padding-left: 15px;
            margin: 25px 0;
        }
        
        .preview-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            min-height: 300px;
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
            .report-builder {
                padding: 15px;
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
                    <h1><i class="fas fa-edit me-2"></i>Edit Report</h1>
                    <a href="read.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Reports
                    </a>
                </div>

                <div class="report-builder">
                    <form method="POST" action="update.php?id=<?= $report_id ?>" id="reportForm">
                        <div class="mb-4">
                            <label class="form-label">Report Name*</label>
                            <input type="text" class="form-control" name="name" 
                                   value="<?= htmlspecialchars($report['name']) ?>" required>
                        </div>

                        <div class="report-section">
                            <h4><i class="fas fa-filter me-2"></i>Filters</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Report Type*</label>
                                    <select class="form-select" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="inventory" <?= $report['type'] === 'inventory' ? 'selected' : '' ?>>Inventory Summary</option>
                                        <option value="purchases" <?= $report['type'] === 'purchases' ? 'selected' : '' ?>>Purchase History</option>
                                        <option value="suppliers" <?= $report['type'] === 'suppliers' ? 'selected' : '' ?>>Supplier Analysis</option>
                                        <option value="custom" <?= $report['type'] === 'custom' ? 'selected' : '' ?>>Custom</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Time Period*</label>
                                    <select class="form-select" name="time_period" required>
                                        <option value="7" <?= $report['time_period'] === '7' ? 'selected' : '' ?>>Last 7 Days</option>
                                        <option value="30" <?= $report['time_period'] === '30' ? 'selected' : '' ?>>Last 30 Days</option>
                                        <option value="90" <?= $report['time_period'] === '90' ? 'selected' : '' ?>>Last 90 Days</option>
                                        <option value="custom" <?= $report['time_period'] === 'custom' ? 'selected' : '' ?>>Custom Range</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Category Filter</label>
                                    <select class="form-select" name="categories[]" multiple>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['category_id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Supplier Filter</label>
                                    <select class="form-select" name="suppliers[]" multiple>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?= $supplier['supplier_id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="report-section">
                            <h4><i class="fas fa-chart-pie me-2"></i>Visualization</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Chart Type</label>
                                    <select class="form-select" name="chart_type">
                                        <option value="">None</option>
                                        <option value="bar" <?= $report['chart_type'] === 'bar' ? 'selected' : '' ?>>Bar Chart</option>
                                        <option value="pie" <?= $report['chart_type'] === 'pie' ? 'selected' : '' ?>>Pie Chart</option>
                                        <option value="line" <?= $report['chart_type'] === 'line' ? 'selected' : '' ?>>Line Graph</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Data Points</label>
                                    <select class="form-select" name="data_points[]" multiple>
                                        <option value="quantity" selected>Quantity</option>
                                        <option value="value" selected>Value</option>
                                        <option value="category">Category</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="report-section">
                            <h4><i class="fas fa-file-export me-2"></i>Output Formats</h4>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <input type="checkbox" class="btn-check" name="formats[]" id="formatPdf" value="pdf" autocomplete="off" 
                                    <?= strpos($report['formats'], 'pdf') !== false ? 'checked' : '' ?>>
                                <label class="btn btn-outline-primary" for="formatPdf"><i class="fas fa-file-pdf me-1"></i>PDF</label>
                                
                                <input type="checkbox" class="btn-check" name="formats[]" id="formatExcel" value="excel" autocomplete="off"
                                    <?= strpos($report['formats'], 'excel') !== false ? 'checked' : '' ?>>
                                <label class="btn btn-outline-success" for="formatExcel"><i class="fas fa-file-excel me-1"></i>Excel</label>
                                
                                <input type="checkbox" class="btn-check" name="formats[]" id="formatHtml" value="html" autocomplete="off"
                                    <?= strpos($report['formats'], 'html') !== false ? 'checked' : '' ?>>
                                <label class="btn btn-outline-secondary" for="formatHtml"><i class="fas fa-code me-1"></i>HTML</label>
                            </div>
                        </div>

                        <div class="report-section">
                            <h4><i class="fas fa-eye me-2"></i>Preview</h4>
                            <div class="preview-container" id="reportPreview">
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-chart-pie fa-3x mb-3"></i>
                                    <p>Report preview will appear here</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"><?= htmlspecialchars($report['notes']) ?></textarea>
                        </div>

                        <div class="d-grid d-md-flex justify-content-md-end gap-2">
                            <a href="delete.php?id=<?= $report_id ?>" class="btn btn-outline-danger">
                                <i class="fas fa-trash me-1"></i> Delete
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Report
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

        // Update preview when form changes
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('reportForm');
            const preview = document.getElementById('reportPreview');
            
            form.addEventListener('change', function() {
                // In a real app, you would update the preview based on form values
                const reportName = form.elements['name'].value;
                if (reportName) {
                    preview.innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Preview for "${reportName}"
                        </div>
                        <div class="text-center py-4">
                            <i class="fas fa-chart-pie fa-3x text-info mb-3"></i>
                            <p>Report preview will be generated when saved</p>
                        </div>
                    `;
                }
            });
        });
    </script>
</body>
</html>