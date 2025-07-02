<?php
session_start();
require_once "config.php";
isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");
$success=false;
$successMessage="";
if (isset($_POST['addcategory'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];
    $description = $_POST['category_description'];
    
    $checkDuplicateQuery = mysqli_query($conn, "SELECT categoryid FROM category WHERE categoryid = '$category_id'");
    if (mysqli_num_rows($checkDuplicateQuery) > 0) {
        // Product ID already exists, show an error message or take appropriate action
        echo "<script>alert('Error: Category ID already exists, please enter another a different category ID.');</script>";
    } else {
    $stmt = mysqli_prepare($conn, "INSERT INTO category (categoryid, categoryname, categorydescription) VALUES (?, ?, ?)");

    mysqli_stmt_bind_param($stmt, "sss", $category_id, $category_name, $description);
    
    if (mysqli_stmt_execute($stmt)) {
        $success=true;
        $successMessage="Category successfully added!";
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
    
    mysqli_stmt_close($stmt);
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
    <title>Document</title>
</head>
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
                            <h2>Add Category</h2>
                        </div> 
                        <div class="col-md-12">
                         <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center" role="alert">
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
  <div>
  <?php echo $successMessage; ?>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="margin-left:auto;"></button>
</div>
<?php endif; ?>
                         </div>
       <div style="margin-top:20px;">
                        <form class="row g-3" method="POST" id="categoryForm">
                        <div class="col-md-5">
                            <label for="category_id" class="form-label">Category ID</label>
                                <input type="text" name="category_id" id="category_id" class="form-control" required="" placeholder="">
                        </div>

                    <div class="col-md-7">
                        <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" name="category_name" id="category_name" class="form-control" required="" placeholder="">
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Description</label>
                            <textarea name="category_description" id="category_description" class="form-control" required="" placeholder="" style="height: 100px"></textarea>
                    </div>

                    <div class="col-md-2">
                        <button type="submit" name="addcategory" class="allButton">Add</button>
                        
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