<?php
session_start();
require_once "config.php";



isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");

if (isset($_POST['update'])) {
    $eid = isset($_GET['editid']) ? $_GET['editid'] : '';
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];
    $description = $_POST['category_description'];

    $checkDuplicateQuery = mysqli_query($conn, "SELECT categoryid FROM category WHERE categoryid = '$category_id' AND categoryid != '$eid'");
    if (mysqli_num_rows($checkDuplicateQuery) > 0) {
        echo "<script>alert('Error: Category ID already exists, please enter another a different category ID.');</script>";
    } else {

    $sql = "UPDATE category SET categoryid='$category_id', categoryname='$category_name', categorydescription='$description' WHERE categoryid='$eid'";
    
    if (mysqli_query($conn, $sql)) {
        
        $_SESSION['successEdit'] = true; // Set successEdit to true
        $_SESSION['successEditMessage'] = 'You have successfully updated the record!';
        echo "<script>document.location='Category.php';</script>";
    } else {
        echo "<script>alert('Something goes wrong: " . mysqli_error($conn) . "');</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
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

    <li class="navList active">
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

    <li class="navList">
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
                        <a href="Category.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
                      
<div class="page-header">
                            <h2>Edit Category</h2>
                        </div> 
                        
       <div>
                        <form class="row g-3" method="POST" id="categoryForm">
                        <?php
                            $eid = isset($_GET['editid']) ? $_GET['editid'] : '';
                            $sql = mysqli_query($conn, "SELECT * FROM category WHERE categoryid='$eid'");
                            if (!$sql) {
                                // Output the error message
                                die("Error: " . mysqli_error($conn));
                            }
                            while ($row = mysqli_fetch_array($sql)) {
                            ?>
                        <div class="col-md-5">
                            <label for="category_id" class="form-label">Category ID</label>
                                <input type="text" name="category_id" value="<?php echo $row['categoryid'];?>" id="category_id" class="form-control" required="" placeholder="">
                        </div>

                    <div class="col-md-7">
                        <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" name="category_name" value="<?php echo $row['categoryname'];?>" id="category_name" class="form-control" required="" placeholder="">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                            <textarea name="category_description" id="category_description" class="form-control" required="" placeholder="" style="height: 100px"><?php echo $row['categorydescription'];?></textarea>
                    </div>
                    <?php } ?>
                    <div class="col-md-2">
                        <button type="submit" name="update" class="allButton">Add</button>
                        
                    </div>
                    <div class="col-md-2">
<button type="button" class="allButton" onclick="clearCategoryForm()">
            Clear
        </button>
                        </div>
    </form>
                        </div>

            </div>
        </div>
    </div>


      
    
</div>

    </section>
    <script>
function clearCategoryForm() {
    // Get the category form
    var form = document.getElementById("categoryForm");

    // Reset text inputs and textarea
    var inputs = form.querySelectorAll("input[type=text], textarea");
    inputs.forEach(function(input) {
        input.value = "";
    });
}
</script>

        <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>
</html>