<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");
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
    <title>Document</title>
</head>
<body>
<nav>
<div class="logo">
<div class="logo-image">
    <!-- Display the company profile image fetched from the database -->
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

    <li class="navList active">
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
        </div>
    </nav>
    
    <section class="dashboard">
        <div class="top">
            <ion-icon class="navToggle" name="menu-outline"></ion-icon>
        </div>

        <div class="content-wrapper">
        <div class="container">
            <div class="row">
            <div class="col-md-12 col-offset-2">
<h2><ion-icon name="reader"></ion-icon>     Inventory Journal</h2>
        <form class="form-inline" method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-5">
                    <label>Date From:</label>
                    <input type="date" class="form-control" placeholder="Start" name="date1"
                        value="<?php echo isset($_POST['date1']) ? $_POST['date1'] : '' ?>" />
                </div>
                
                <div class="col-md-5">
                    <label>To</label>
                    <input type="date" class="form-control" placeholder="End" name="date2"
                        value="<?php echo isset($_POST['date2']) ? $_POST['date2'] : '' ?>" />
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary" name="search" style="margin-top:24px;">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>
                <div class="col-md-1">
                    <a href="Reports.php" type="button" class="btn btn-success" style="margin-top:24px; margin-left:-40px;">
                        <ion-icon name="refresh-outline"></ion-icon>
                    </a>
                </div>
            </div>
        </form>

        <div class="row" id="lightgallery">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="journalTable">
                        <thead class="alert-info">
                            <tr>
                                <th>ProductID</th>
                                <th>ProductName</th>
                                <th>Quantity</th>
                                <th>Action</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php include 'Range.php' ?>
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
        $('#journalTable').DataTable({
            responsive: true,
            searching: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50],
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // ProductID
                { responsivePriority: 2, targets: 1 }, // ProductName
                { responsivePriority: 3, targets: 3 }, // Action
                { responsivePriority: 4, targets: 5 }, // Date
                { responsivePriority: 5, targets: 2 }, // Quantity
                { responsivePriority: 6, targets: 4 }  // Status
            ]
        });
    });
    </script>
</body>
</html>