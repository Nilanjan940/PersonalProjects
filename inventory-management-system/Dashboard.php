<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");



$c_category = count_by_id('category');
$c_inventory = count_by_id('inventory');
$c_users = count_by_id('users');
$c_location= count_by_id('location');

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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <title>Admin Dashboard</title>
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
        </div>
    </nav>

    <section class="dashboard">
        <div class="top">
            <ion-icon class="navToggle" name="menu-outline"></ion-icon>       
        </div>
        <div class="content-wrapper">
        <div class="container">
        <div class="row">
                <div class="col-lg-12 col-offset-2">
            <h2><ion-icon name="stats-chart"></ion-icon>   Dashboard</h2>
     <div style="display:flex;">
     <div class="col-md-3">
     <a href="viewaccount.php" class="boxdash" style="background: linear-gradient(90deg, #00d2ff 0%, #3a47d5 100%);">  
    <ion-icon name="person-circle-outline" style="font-size: 5em;"></ion-icon>
    <div>
        <h2 class="margin-top"><?php echo $c_users; ?></h2>
        <p class="text-muted">Users</p>
    </div>
</a>
</div>

<div class="col-md-3">
	<a href="Category.php" class="boxdash" style="background: linear-gradient(90deg, #00C9FF 0%, #92FE9D 100%);">
    
    <ion-icon name="grid-outline" style="font-size: 5em;"></ion-icon>   
    <div>  
          <h2 class="margin-top"> <?php  echo $c_category; ?> </h2>
          <p class="text-muted">Categories</p>
</div>
	</a>
</div>

<div class="col-md-3">
	<a href="Inventory.php" class="boxdash" style="background: linear-gradient(90deg, #FC466B 0%, #3F5EFB 100%);">
    <ion-icon name="file-tray-full-outline" style="font-size: 5em;"></ion-icon> 
    <div>
          <h2 class="margin-top"> <?php  echo $c_inventory; ?> </h2>
          <p class="text-muted">Products</p>
</div>
	</a>
</div>

<div class="col-md-3">
    <a href="viewlocation.php" class="boxdash" style="background: linear-gradient(90deg, #f8ff00 0%, #3ad59f 100%); margin-left:auto;">
    <ion-icon name="location-outline" style="font-size: 5em;"></ion-icon> 
    <div>
          <h2 class="margin-top"> <?php  echo $c_location; ?> </h2>
          <p class="text-muted">Location</p>
</div>
	</a>
</div>

</div>

<br>
<div class="row" id="lightgallery">
    <div class="col-md-6">
        <h3>Stock's quantity that lower than 10</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="alert-info">
                                <tr>
                                <th class="fixed-column">ProductID</th>
                                    <th class="fixed-column">ProductName</th>
                                    <th class="fixed-column">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                              <?php include 'lowstock.php' ?>
                            </tbody>
                        </table>
                    </div>
                    <ul class="pagination justify-content-end mt-3">
        <!-- Pagination links go here -->
    </ul>
                </div>
                <div class="col-md-6">
                <h3>Recently added for past 5 days</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="alert-info">
                                <tr>
                                <th class="fixed-column">ProductID</th>
                                    <th class="fixed-column">ProductName</th>
                                    <th class="fixed-column">Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php include 'recentlyadded.php' ?>
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
</body>

</html>