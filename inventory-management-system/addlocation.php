<?php
session_start();
require_once "config.php";
isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");

// Initialize variables
$errorOccurred = false;
$errorMessage = "";
$success = false;
$successMessage = "";

if (isset($_POST['addlocation'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $locationName = validate($_POST['locationName']);
    $address = validate($_POST['address']);

    // Check if the location name already exists
    $checkQuery = mysqli_query($conn, "SELECT * FROM location WHERE name = '$locationName'");
    
    if (mysqli_num_rows($checkQuery) > 0) {
        $errorOccurred = true;
        $errorMessage = "Location name already exists. Please choose a different name.";
    } else {
        // Insert data into the location table
        $insertQuery = "INSERT INTO location (name, address) VALUES ('$locationName', '$address')";
        $insertResult = mysqli_query($conn, $insertQuery);

        if ($insertResult) {
            $success = true;
            $successMessage = "Location added successfully!";
            
            // Add to audit trail
            $username = $_SESSION['username'];
            $addAction = "Add Location: $locationName";
            $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, '$username', '$addAction')";
            mysqli_query($conn, $auditTrailQuery);
        } else {
            $errorOccurred = true;
            $errorMessage = "Error occurred while adding location. Please try again.";
        }
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
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="testin3.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.15.0/font/bootstrap-icons.css" rel="stylesheet">
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </symbol>
        <symbol id="info-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
        </symbol>
        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </symbol>
    </svg>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Add Location</title>
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
                <div class="col-lg-12 col-offset-2">
                    <div class="page-header">
                        <a href="Settings.php" style="font-size: 24px; text-decoration:none; color:black;">
                            <ion-icon name="arrow-back-outline"></ion-icon>
                        </a>
                        <h2><ion-icon name="location-outline"></ion-icon> Add Location</h2>
                        
                        <?php if ($errorOccurred): ?>
                            <div class="alert alert-danger d-flex align-items-center alert-dismissible" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
                                <div>
                                    <?php echo $errorMessage; ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success d-flex align-items-center alert-dismissible" role="alert">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
                                <div>
                                    <?php echo $successMessage; ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="margin-left:auto;"></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <form class="row g-3" method="POST" id="locationForm">
                        <div class="col-md-6">
                            <div style="display:flex;">
                                <label for="locationName" class="form-label">Location Name</label>
                            </div>
                            <input type="text" name="locationName" id="locationName" class="form-control" required maxlength="50">
                        </div>
                        
                        <div class="col-md-12">
                            <div style="display:flex;">
                                <label for="address" class="form-label">Address</label>
                            </div>
                            <textarea name="address" id="address" class="form-control" style="height: 150px" required maxlength="255"></textarea>
                        </div>
                        
                        <div class="col-md-12 d-flex gap-2">
                            <button type="submit" name="addlocation" class="btn btn-primary">
                                <ion-icon name="add-circle-outline"></ion-icon> Add Location
                            </button> 
                            <button type="button" class="btn btn-danger" onclick="clearForm()">
                                <ion-icon name="trash-outline"></ion-icon> Clear
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </section>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
    <script>
        function clearForm() {
            document.getElementById('locationForm').reset();
        }
        
        // Auto-close success alert after 3 seconds
        <?php if ($success): ?>
            setTimeout(() => {
                const alert = document.querySelector('.alert-success');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>