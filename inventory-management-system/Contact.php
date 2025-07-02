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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <title>Contact</title>
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
        <ion-icon name="file-tray-full"></ion-icon> <!-- Change to the desired icon name, e.g., "apps-outline" for inventory -->
        <span class="links">Inventory</span>
    </a>
</li>
<li class="navList">
    <a href="Product.php">
        <ion-icon name="add-circle"></ion-icon> <!-- Change to the desired icon name, e.g., "bag-outline" for product -->
        <span class="links">Add Product</span>
    </a>
</li>

    <li class="navList">
        <a href="Category.php">
            <ion-icon name="grid"></ion-icon> <!-- Change to the desired icon name, e.g., "person-outline" for account -->
            <span class="links">Category</span>
        </a>
    </li>  

    <li class="navList">
        <a href="Order.php">
        <ion-icon name="swap-horizontal"></ion-icon> <!-- Change to the desired icon name, e.g., "person-outline" for account -->
            <span class="links">Product Transfer</span>
        </a>
    </li> 

    <li class="navList">
        <a href="Reports.php">
            <ion-icon name="reader"></ion-icon> <!-- Change to the desired icon name, e.g., "person-outline" for account -->
            <span class="links">Inventory Journal</span>
        </a>
    </li> 

    <li class="navList">
        <a href="audittrails.php">
        <ion-icon name="receipt"></ion-icon> <!-- Change to the desired icon name, e.g., "person-outline" for account -->
            <span class="links">Audit trails</span>
        </a>
    </li> 

    <li class="navList active">
        <a href="Settings.php">
            <ion-icon name="cog"></ion-icon> <!-- Change to the desired icon name, e.g., "person-outline" for account -->
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
                <a href="Settings.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
<form method="POST" action="process_feedback.php" enctype="multipart/form-data" class="loginform">
<p class="loginform-title">Contact Support</p>
<div class="input-container">
    <input type="email" name="user_email" required placeholder="Enter company email" class="form-control">
</div>
    <div class="input-container">
    <textarea name="feedback" rows="4" required placeholder="Enter feedback" class="form-control"></textarea>
</div>

    <div class="input-container">
    <input type="file" name="image" placeholder="Submit image" class="form-control">
</div>
    <button type="submit" class="submit">Submit Feedback</button>
</form>
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