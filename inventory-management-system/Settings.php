<?php
session_start();
isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");
require_once "config.php";
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
    <title>Settings</title>
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

    <li class="navList active">
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
               
                <div class="box">
                    
    <h2><ion-icon name="cog"></ion-icon>     Settings</h2>

    <h4>Account</h4>
    <p>Manage account here, view, add and delete account.</p>
    <div class="accountbox">
        <div class="settingTitle">
            <p><strong>View account</strong></p>
        </div>
        <div class="d-flex align-items-center">
            <p class="mb-0">View all the account available that use to manage inventory by pressing the view account button.</p>
            <a href="viewaccount.php" class="btn btn-primary ms-auto">
                <ion-icon name="eye-outline"></ion-icon> View
            </a>
        </div>
        <hr>
        
        <div class="settingTitle">
            <p><strong>Add account</strong></p>
        </div>
        <div class="d-flex align-items-center">
            <p class="mb-0">Press add account button to add another account.</p>
            <a href="AddAccount.php" class="btn btn-primary ms-auto">
                <ion-icon name="person-add-outline"></ion-icon> Add
            </a>
        </div>
    </div>
    
    <h3 class="mt-4">Help & Support</h3>
    <div class="accountbox">
        <div class="settingTitle">
            <p><strong>Contact support</strong></p>
        </div>
        <div class="d-flex align-items-center">
            <p class="mb-0">Help and Support here, you can provide feedback to us, we will reply as fast as we can.</p>
            <a href="Contact.php" class="btn btn-primary ms-auto">
                <ion-icon name="chatbubble-ellipses-outline"></ion-icon> Contact
            </a>
        </div>
    </div>

    <h3 class="mt-4">Company Profile</h3>
    <div class="accountbox">
        <div class="settingTitle">
            <p><strong>Edit profile</strong></p>
        </div>
        <div class="d-flex align-items-center">
            <p class="mb-0">Edit company logo and company name here.</p>
            <a href="company_profile.php" class="btn btn-primary ms-auto">
                <ion-icon name="create-outline"></ion-icon> Edit
            </a>
        </div>
    </div>

    <h3 class="mt-4">Location</h3>
    <div class="accountbox">
        <div class="settingTitle">
            <p><strong>View location</strong></p>
        </div>
        <div class="d-flex align-items-center">
            <p class="mb-0">View all the location to include for product transfer.</p>
            <a href="viewlocation.php" class="btn btn-primary ms-auto">
                <ion-icon name="eye-outline"></ion-icon> View
            </a>
        </div>
        <hr>
        
        <div class="settingTitle">
            <p><strong>Add location</strong></p>
        </div>
        <div class="d-flex align-items-center">
            <p class="mb-0">Add new location for product transfer.</p>
            <a href="addlocation.php" class="btn btn-primary ms-auto">
                <ion-icon name="add-outline"></ion-icon> Add
            </a>
        </div>
    </div>
    <br>
    <br>
         
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