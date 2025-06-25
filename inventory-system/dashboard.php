<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once __DIR__ . '/config/database.php';

// Function to fetch dashboard data
function getDashboardData($pdo) {
    $data = [];
    
    // Total Products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $data['total_products'] = $stmt->fetch()['total'];

    // Low Stock Items (below reorder level)
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM inventory i
        JOIN products p ON i.product_id = p.product_id
        WHERE i.quantity <= p.reorder_level AND i.quantity > 0
    ");
    $data['low_stock'] = $stmt->fetch()['total'];

    // Out of Stock
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory WHERE quantity = 0");
    $data['out_of_stock'] = $stmt->fetch()['total'];

    // Active Suppliers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM suppliers");
    $data['active_suppliers'] = $stmt->fetch()['total'];

    // Recent Transactions
    $stmt = $pdo->query("
        SELECT t.*, p.name as product_name, u.username 
        FROM transactions t
        JOIN products p ON t.product_id = p.product_id
        JOIN users u ON t.user_id = u.user_id
        ORDER BY t.transaction_date DESC
        LIMIT 5
    ");
    $data['recent_transactions'] = $stmt->fetchAll();

    // Low Stock Alerts
    $stmt = $pdo->query("
        SELECT p.product_id, p.name, p.reorder_level, i.quantity
        FROM products p
        JOIN inventory i ON p.product_id = i.product_id
        WHERE i.quantity <= p.reorder_level
        ORDER BY i.quantity ASC
        LIMIT 3
    ");
    $data['low_stock_alerts'] = $stmt->fetchAll();

    return $data;
}

// Get initial dashboard data
$stats = getDashboardData($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industrial Inventory Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --danger: #e74c3c;
            --warning: #f39c12;
            --success: #2ecc71;
            --light: #ecf0f1;
            --dark: #343a40;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        /* Dashboard Container */
        .dashboard-container {
            max-width: 1800px;
            margin: 0 auto;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 1rem;
            overflow: hidden;
        }
        
        /* Dashboard Content - Scrollable Area */
        .dashboard-content {
            flex: 1;
            overflow: auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        
        /* Stats Cards - Enhanced Design */
        .stat-card {
            border-left: 4px solid;
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        
        .stat-card-primary { border-left-color: var(--primary); }
        .stat-card-success { border-left-color: var(--success); }
        .stat-card-warning { border-left-color: var(--warning); }
        .stat-card-danger { border-left-color: var(--danger); }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            line-height: 1.2;
            color: var(--dark);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-value {
            transform: scale(1.05);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        
        .stat-trend {
            font-size: 0.8rem;
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
            font-size: 2.5rem;
            opacity: 0.15;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover .stat-icon {
            opacity: 0.25;
            transform: scale(1.1);
        }
        
        /* Alert Cards */
        .alert-card {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            height: 100%;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .alert-card:hover {
            transform: translateX(5px);
        }
        
        /* Tables */
        .recent-table {
            width: 100%;
        }
        
        .recent-table th {
            background-color: var(--primary);
            color: white;
            position: sticky;
            top: 0;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
        }
        
        .recent-table td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
            font-size: 0.85rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s ease;
        }
        
        .recent-table tr:hover td {
            background-color: rgba(0,0,0,0.02);
        }
        
        /* Header */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem 0;
        }
        
        .dashboard-header h2 {
            margin: 0;
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--primary);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .search-bar {
            min-width: 250px;
            flex-grow: 1;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .quick-actions .btn {
            padding: 0.75rem 1.25rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quick-actions .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        /* Card Headers */
        .card-header {
            padding: 1rem 1.25rem;
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            color: var(--primary);
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            grid-template-rows: auto 1fr auto;
            gap: 1.5rem;
            height: calc(100vh - 180px);
        }
        
        .stats-row {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
        
        .transactions-col {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .alerts-col {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .actions-row {
            grid-column: 1 / -1;
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1);
        }
        
        .card-body {
            flex: 1;
            padding: 0;
            overflow: hidden;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1600px) {
            .stat-value {
                font-size: 1.75rem;
            }
            
            .content-grid {
                height: auto;
                min-height: calc(100vh - 180px);
            }
        }
        
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto 1fr auto;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0 !important;
            }
            
            .dashboard-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .search-bar {
                min-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .quick-actions .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .dashboard-header h2 {
                font-size: 1.5rem;
            }
            
            .stat-card {
                padding: 1.25rem;
            }
            
            .stat-icon {
                font-size: 2rem;
            }
        }
        
        /* Animation for real-time updates */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .data-updated {
            animation: pulse 0.5s ease;
        }
    </style>
</head>

<body>
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="d-flex" style="height: 100vh;">
        <!-- Include Sidebar -->
        <div id="sidebar-container">
            <!-- This will be replaced with the sidebar content -->
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-container">
                <!-- Header -->
                <div class="dashboard-header">
                    <h2>Dashboard Overview</h2>
                    <div class="header-actions">
                        <div class="input-group search-bar">
                            <input type="text" class="form-control" placeholder="Search inventory..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light d-flex align-items-center py-1 px-2" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <img src="https://via.placeholder.com/30" class="rounded-circle me-2">
                                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Dashboard Content -->
                <div class="dashboard-content">
                    <!-- Stats Cards -->
                    <div class="stats-row" id="statsContainer">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-value" id="totalProducts"><?= htmlspecialchars($stats['total_products']) ?></div>
                            <div class="stat-label">Total Products</div>
                            <div class="stat-trend text-success">
                                <i class="fas fa-caret-up me-1"></i> In System
                            </div>
                            <i class="fas fa-boxes stat-icon"></i>
                        </div>
                        
                        <div class="stat-card stat-card-warning">
                            <div class="stat-value" id="lowStock"><?= htmlspecialchars($stats['low_stock']) ?></div>
                            <div class="stat-label">Low Stock Items</div>
                            <div class="stat-trend text-warning">
                                <i class="fas fa-exclamation-triangle me-1"></i> Needs attention
                            </div>
                            <i class="fas fa-exclamation-circle stat-icon"></i>
                        </div>
                        
                        <div class="stat-card stat-card-danger">
                            <div class="stat-value" id="outOfStock"><?= htmlspecialchars($stats['out_of_stock']) ?></div>
                            <div class="stat-label">Out of Stock</div>
                            <div class="stat-trend text-danger">
                                <i class="fas fa-times-circle me-1"></i> Re-order needed
                            </div>
                            <i class="fas fa-box-open stat-icon"></i>
                        </div>
                        
                        <div class="stat-card stat-card-success">
                            <div class="stat-value" id="activeSuppliers"><?= htmlspecialchars($stats['active_suppliers']) ?></div>
                            <div class="stat-label">Active Suppliers</div>
                            <div class="stat-trend text-success">
                                <i class="fas fa-check-circle me-1"></i> All operational
                            </div>
                            <i class="fas fa-truck stat-icon"></i>
                        </div>
                    </div>
                    
                    <!-- Main Content Area -->
                    <div class="content-grid mt-2">
                        <!-- Transactions Column -->
                        <div class="transactions-col">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Recent Transactions</h5>
                                    <a href="#" class="btn btn-sm btn-outline-secondary">View All</a>
                                </div>
                                <div class="card-body p-0" style="overflow: auto;">
                                    <table class="table table-hover mb-0 recent-table" id="transactionsTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Item</th>
                                                <th>Type</th>
                                                <th>Qty</th>
                                                <th>Date</th>
                                                <th>User</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($stats['recent_transactions'] as $transaction): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($transaction['transaction_id']) ?></td>
                                                <td><?= htmlspecialchars($transaction['product_name']) ?></td>
                                                <td>
                                                    <?php 
                                                    $badge_class = '';
                                                    switch($transaction['transaction_type']) {
                                                        case 'in': $badge_class = 'bg-success'; break;
                                                        case 'out': $badge_class = 'bg-danger'; break;
                                                        case 'adjustment': $badge_class = 'bg-warning'; break;
                                                        case 'return': $badge_class = 'bg-info'; break;
                                                    }
                                                    ?>
                                                    <span class="badge <?= $badge_class ?>">
                                                        <?= htmlspecialchars(ucfirst($transaction['transaction_type'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($transaction['quantity']) ?></td>
                                                <td><?= date('Y-m-d', strtotime($transaction['transaction_date'])) ?></td>
                                                <td><?= htmlspecialchars($transaction['username']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Alerts Column -->
                        <div class="alerts-col">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Low Stock Alerts</h5>
                                </div>
                                <div class="card-body p-2" style="overflow: auto;" id="alertsContainer">
                                    <?php foreach ($stats['low_stock_alerts'] as $alert): ?>
                                    <div class="alert <?= $alert['quantity'] == 0 ? 'alert-danger' : 'alert-warning' ?> alert-card">
                                        <i class="fas <?= $alert['quantity'] == 0 ? 'fa-exclamation-circle' : 'fa-exclamation-triangle' ?> me-3"></i>
                                        <div>
                                            <strong><?= htmlspecialchars($alert['name']) ?></strong>
                                            <div class="mt-1">
                                                Current: <?= htmlspecialchars($alert['quantity']) ?> 
                                                (Reorder at <?= htmlspecialchars($alert['reorder_level']) ?>)
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="actions-row">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="quick-actions">
                                        <a href="products/create.php" class="btn btn-primary">
                                            <i class="fas fa-plus me-1"></i> Add Product
                                        </a>
                                        <a href="inventory/create.php" class="btn btn-success">
                                            <i class="fas fa-exchange-alt me-1"></i> Stock Adjustment
                                        </a>
                                        <a href="purchases/create.php" class="btn btn-warning">
                                            <i class="fas fa-shopping-cart me-1"></i> New Purchase Order
                                        </a>
                                        <a href="reports/create.php" class="btn btn-info">
                                            <i class="fas fa-chart-pie me-1"></i> Generate Report
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load sidebar content
        fetch('assets/sidebar.html')
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

        // Real-time data updates
        function updateDashboardData() {
            fetch('api/get_dashboard_data.php')
                .then(response => response.json())
                .then(data => {
                    // Update stats cards
                    document.getElementById('totalProducts').textContent = data.total_products;
                    document.getElementById('lowStock').textContent = data.low_stock;
                    document.getElementById('outOfStock').textContent = data.out_of_stock;
                    document.getElementById('activeSuppliers').textContent = data.active_suppliers;
                    
                    // Add animation to show data was updated
                    document.getElementById('statsContainer').classList.add('data-updated');
                    setTimeout(() => {
                        document.getElementById('statsContainer').classList.remove('data-updated');
                    }, 500);
                    
                    // Update transactions table
                    const transactionsTable = document.getElementById('transactionsTable').querySelector('tbody');
                    transactionsTable.innerHTML = '';
                    data.recent_transactions.forEach(transaction => {
                        const row = document.createElement('tr');
                        
                        // Determine badge class based on transaction type
                        let badgeClass = '';
                        switch(transaction.transaction_type) {
                            case 'in': badgeClass = 'bg-success'; break;
                            case 'out': badgeClass = 'bg-danger'; break;
                            case 'adjustment': badgeClass = 'bg-warning'; break;
                            case 'return': badgeClass = 'bg-info'; break;
                        }
                        
                        row.innerHTML = `
                            <td>${transaction.transaction_id}</td>
                            <td>${transaction.product_name}</td>
                            <td><span class="badge ${badgeClass}">${transaction.transaction_type.charAt(0).toUpperCase() + transaction.transaction_type.slice(1)}</span></td>
                            <td>${transaction.quantity}</td>
                            <td>${transaction.transaction_date.split(' ')[0]}</td>
                            <td>${transaction.username}</td>
                        `;
                        transactionsTable.appendChild(row);
                    });
                    
                    // Update alerts
                    const alertsContainer = document.getElementById('alertsContainer');
                    alertsContainer.innerHTML = '';
                    data.low_stock_alerts.forEach(alert => {
                        const isOutOfStock = alert.quantity == 0;
                        const alertDiv = document.createElement('div');
                        alertDiv.className = `alert ${isOutOfStock ? 'alert-danger' : 'alert-warning'} alert-card`;
                        alertDiv.innerHTML = `
                            <i class="fas ${isOutOfStock ? 'fa-exclamation-circle' : 'fa-exclamation-triangle'} me-3"></i>
                            <div>
                                <strong>${alert.name}</strong>
                                <div class="mt-1">
                                    Current: ${alert.quantity} (Reorder at ${alert.reorder_level})
                                </div>
                            </div>
                        `;
                        alertsContainer.appendChild(alertDiv);
                    });
                })
                .catch(error => console.error('Error updating dashboard data:', error));
        }

        // Update data every 30 seconds
        setInterval(updateDashboardData, 30000);
        
        // Initial update after 1 second (to stagger requests if multiple tabs are open)
        setTimeout(updateDashboardData, 1000);

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    // In a real implementation, you would filter the data or make an API call
                    console.log('Searching for:', searchTerm);
                }
            }
        });
    </script>
</body>
</html>