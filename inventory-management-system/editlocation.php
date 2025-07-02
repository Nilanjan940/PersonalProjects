<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");

$error = '';

// Check if editid is set in the URL
if (isset($_GET['editid'])) {
    $editid = $_GET['editid'];

    // Fetch location details for editing
    $editQuery = "SELECT * FROM location WHERE id = '$editid'";
    $editResult = mysqli_query($conn, $editQuery);

    if (mysqli_num_rows($editResult) > 0) {
        $locationData = mysqli_fetch_assoc($editResult);

        // Set initial values for the form fields
        $locationName = $locationData['name'];
        $address = $locationData['address'];
    } else {
        // Redirect to viewlocation.php or display an error message
        header("Location: viewlocation.php");
        exit();
    }
} else {
    // Redirect to viewlocation.php or display an error message
    header("Location: viewlocation.php");
    exit();
}

// Handle form submission for updating location
if (isset($_POST['updateLocation'])) {
    // Retrieve values from the form
    $locationName = mysqli_real_escape_string($conn, $_POST['locationName']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Update data in the location table
    $updateQuery = "UPDATE location SET name = '$locationName', address = '$address' WHERE id = '$editid'";
    $updateResult = mysqli_query($conn, $updateQuery);

    if ($updateResult) {
        header("Location: viewlocation.php?success=1");
        exit();
    } else {
        $error = "Error occurred while updating location. Please try again.";
    }
}
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
    <title>Document</title>
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
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
                    <form class="loginform" method="POST">
    <p class="loginform-title">Edit Location</p>
    <?php if ($error) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <div class="input-container">
        <input required="" type="text" placeholder="Enter location name" class="form-control" name="locationName" value="<?php echo $locationName; ?>">
    </div>

    <div class="input-container">
        <textarea name="address" rows="4" required placeholder="Enter address" class="form-control"><?php echo $address; ?></textarea>
    </div>
    <button class="submit" name="updateLocation">Update Location</button>
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