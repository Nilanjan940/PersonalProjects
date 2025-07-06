<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

$companyId = 1; 
$query = "SELECT * FROM companyprofile WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $companyId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$companyProfile = mysqli_fetch_assoc($result);
$companyProfileImagePath = $companyProfile['profilepicture'];
$companyName = $companyProfile['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
    <title>Audit Trails</title>
</head>
<body>
<nav>
<div class="logo">
<div class="logo-image">
    <?php
    if (!empty($companyProfileImagePath)) {
        echo '<img src="' . $companyProfileImagePath . '" alt="Company Logo" class="logoimage">';
    } else {
        echo '<img src="path/to/placeholder/image.jpg" alt="No Image" style="">';
    }
    ?>
</div>

    <div class="logo-name">
    <?php echo $companyName; ?>
    </div>
</div>

        <div class="menu-items">
            <ul class="navLinks">
            <li class="navList">
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

    <li class="navList active">
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
        </div>
    </nav>
    
    <section class="dashboard">
        <div class="top">
            <ion-icon class="navToggle" name="menu-outline"></ion-icon>
        </div>

        <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2><ion-icon name="receipt"></ion-icon> Audit Trails</h2>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search audit trails...">
                        </div>
                        <div class="col-md-6">
                            <select name="userFilter" id="userFilter" class="form-control">
                                <option value="">All Users</option>
                                <?php
                                $usersQuery = mysqli_query($conn, "SELECT DISTINCT username FROM audittrails");
                                while ($user = mysqli_fetch_assoc($usersQuery)) {
                                    echo "<option value='".$user['username']."'>".$user['username']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="auditTable">
                                    <thead class="alert-info">
                                        <tr>
                                            <th>#</th>
                                            <th>DateTime</th>
                                            <th>Username</th>
                                            <th>Action Made</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    // Fetch data from the database and populate the table
                                    include "config.php";

                                    $auditTrailQuery = "SELECT * FROM audittrails ORDER BY datetime DESC";
                                    $result = mysqli_query($conn, $auditTrailQuery);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>". $row['id'] ."</td>";
                                        echo "<td>" . $row['datetime'] . "</td>";
                                        echo "<td>" . $row['username'] . "</td>";
                                        echo "<td>" . $row['action'] . "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
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
        var table = $('#auditTable').DataTable({
            responsive: true,
            searching: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            order: [[1, 'desc']], // Default sort by datetime descending
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // ID
                { responsivePriority: 2, targets: 1 }, // DateTime
                { responsivePriority: 3, targets: 3 }, // Action
                { responsivePriority: 4, targets: 2 }  // Username
            ]
        });

        // Custom search functionality
        $('#searchInput').keyup(function(){
            table.search($(this).val()).draw();
        });

        // Filter by user
        $('#userFilter').change(function(){
            var user = $(this).val();
            table.column(2).search(user).draw();
        });
    });
    </script>
</body>
</html>