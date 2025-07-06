<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

// Count functions
$c_category = count_by_id('category');
$c_inventory = count_by_id('inventory');
$c_users = count_by_id('users');
$c_location = count_by_id('location');

// Get company profile
$companyId = 1; 
$query = "SELECT * FROM companyprofile WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $companyId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$companyProfile = mysqli_fetch_assoc($result);
$companyProfileImagePath = $companyProfile['profilepicture'];
$companyName = $companyProfile['name'];

// Get recently added products (last 5 days)
$recentProducts = [];
$recentQuery = "SELECT id, name, quantity FROM inventory WHERE DATE(addeddate) >= CURDATE() - INTERVAL 5 DAY";
$recentResult = mysqli_query($conn, $recentQuery);
if ($recentResult && mysqli_num_rows($recentResult) > 0) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentProducts[] = $row;
    }
}

// Get low stock products (quantity < 10)
$lowStockProducts = [];
$lowStockQuery = "SELECT id, name, quantity FROM inventory WHERE quantity < 10";
$lowStockResult = mysqli_query($conn, $lowStockQuery);
if ($lowStockResult && mysqli_num_rows($lowStockResult) > 0) {
    while ($row = mysqli_fetch_assoc($lowStockResult)) {
        $lowStockProducts[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <title>Admin Dashboard</title>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
    <style>
        /* Custom styles for dashboard cards */
        .dashboard-card {
            border-radius: 10px;
            color: white;
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            min-height: 150px;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
            text-decoration: none;
            color: white;
        }
        
        .dashboard-card ion-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .dashboard-card h2 {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .dashboard-card p {
            margin-bottom: 0;
            opacity: 0.9;
            font-size: 0.9rem;
        }
        
        /* Specific card colors */
        .users-card {
            background: linear-gradient(135deg, #3a47d5 0%, #00d2ff 100%);
        }
        
        .categories-card {
            background: linear-gradient(135deg, #00C9FF 0%, #92FE9D 100%);
        }
        
        .products-card {
            background: linear-gradient(135deg, #FC466B 0%, #3F5EFB 100%);
        }
        
        .location-card {
            background: linear-gradient(135deg, #3ad59f 0%, #f8ff00 100%);
        }
        
        /* Responsive adjustments */
        @media (max-width: 767px) {
            .dashboard-card {
                margin-bottom: 15px;
            }
            
            .dashboard-card ion-icon {
                font-size: 2rem;
            }
            
            .dashboard-card h2 {
                font-size: 1.5rem;
            }
        }
        
        /* Center no data message */
        .no-data {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #666;
        }
    </style>
</head>

<body>
<nav>
    <div class="logo">
        <div class="logo-image">
            <?php
            if (!empty($companyProfileImagePath)) {
                echo '<img src="' . $companyProfileImagePath . '" alt="Company Logo" class="logoimage">';
            } else {
                echo '<img src="path/to/placeholder/image.jpg" alt="No Image">';
            }
            ?>
        </div>
        <div class="logo-name">
            <?php echo $companyName; ?>
        </div>
    </div>

    <div class="menu-items">
        <ul class="navLinks">
            <li class="navList active">
                <a href="Dashboard.php">
                    <ion-icon name="stats-chart"></ion-icon>
                    <span class="links">Dashboard</span>
                </a>
            </li>
            <li class="navList">
                <a href="Inventory.php">
                    <ion-icon name="file-tray-full"></ion-icon> 
                    <span class="links">Inventory</span>
                </a>
            </li>
            <li class="navList">
                <a href="Product.php">
                    <ion-icon name="add-circle"></ion-icon> 
                    <span class="links">Add Product</span>
                </a>
            </li>
            <li class="navList">
                <a href="Category.php">
                    <ion-icon name="grid"></ion-icon> 
                    <span class="links">Category</span>
                </a>
            </li>  
            <li class="navList">
                <a href="Order.php">
                    <ion-icon name="swap-horizontal"></ion-icon> 
                    <span class="links">Product Transfer</span>
                </a>
            </li> 
            <li class="navList">
                <a href="Reports.php">
                    <ion-icon name="reader"></ion-icon> 
                    <span class="links">Inventory Journal</span>
                </a>
            </li> 
            <li class="navList">
                <a href="audittrails.php">
                    <ion-icon name="receipt"></ion-icon>
                    <span class="links">Audit trails</span>
                </a>
            </li> 
            <li class="navList">
                <a href="Settings.php">
                    <ion-icon name="cog"></ion-icon> 
                    <span class="links">Settings</span>
                </a>
            </li>          
        </ul>
        <ul class="bottom-link">
            <li>
                <a href="logout.php">
                    <ion-icon name="log-out"></ion-icon>
                    <span class="links">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<section class="dashboard">
    <div class="top">
        <ion-icon class="navToggle" name="menu-outline"></ion-icon>       
    </div>
    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2><ion-icon name="stats-chart"></ion-icon> Dashboard</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <a href="viewaccount.php" class="dashboard-card users-card">
                        <ion-icon name="person-circle-outline"></ion-icon>
                        <div>
                            <h2><?php echo $c_users; ?></h2>
                            <p>Users</p>
                        </div>
                    </a>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <a href="Category.php" class="dashboard-card categories-card">
                        <ion-icon name="grid-outline"></ion-icon>   
                        <div>  
                            <h2><?php echo $c_category; ?></h2>
                            <p>Categories</p>
                        </div>
                    </a>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <a href="Inventory.php" class="dashboard-card products-card">
                        <ion-icon name="file-tray-full-outline"></ion-icon> 
                        <div>
                            <h2><?php echo $c_inventory; ?></h2>
                            <p>Products</p>
                        </div>
                    </a>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3 mb-4">
                    <a href="viewlocation.php" class="dashboard-card location-card">
                        <ion-icon name="location-outline"></ion-icon> 
                        <div>
                            <h2><?php echo $c_location; ?></h2>
                            <p>Locations</p>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-12 col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Stock's quantity that lower than 10</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="lowStockTable">
                                    <thead class="alert-info">
                                        <tr>
                                            <th>ProductID</th>
                                            <th>ProductName</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($lowStockProducts)): ?>
                                            <?php foreach ($lowStockProducts as $product): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="no-data">No products with low stock</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="mb-0">Recently added for past 5 days</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="recentTable">
                                    <thead class="alert-info">
                                        <tr>
                                            <th>ProductID</th>
                                            <th>ProductName</th>
                                            <th>Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($recentProducts)): ?>
                                            <?php foreach ($recentProducts as $product): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="3" class="no-data">No recently added products in the past 5 days</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script src="./index.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function () {
    // Initialize DataTables with proper error handling
    try {
        $('#lowStockTable').DataTable({
            responsive: true,
            searching: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            columns: [
                { title: "ProductID" },
                { title: "ProductName" },
                { title: "Quantity" }
            ]
        });
        
        $('#recentTable').DataTable({
            responsive: true,
            searching: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            columns: [
                { title: "ProductID" },
                { title: "ProductName" },
                { title: "Quantity" }
            ]
        });
    } catch (e) {
        console.error('DataTables initialization error:', e);
    }
});
</script>
</body>
</html>