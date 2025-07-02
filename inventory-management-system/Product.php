<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");
// Initialize variables
$errorOccurred = false;
$errorMessage = "";
$success = false;
$successMessage = "";



if (isset($_POST['addproduct'])) {
    function validate($data) {
        // Trim whitespace
        $data = trim($data);
        // Remove slashes
        $data = stripslashes($data);
        // Convert special characters to HTML entities
        $data = htmlspecialchars($data);
        return $data;
    }

    $product_id = validate($_POST['product_id']);
    $product_name = validate($_POST['product_name']);
    $status = $_POST['status'];
    $quantity = validate($_POST['quantity']);
    $unit_price = validate($_POST['unit_price']);
    $variant = $_POST['variant'];
    $description = validate($_POST['description']);
    $product_category = validate($_POST['category']);

    // Check if the product ID already exists
    $checkDuplicateQuery = mysqli_query($conn, "SELECT id FROM inventory WHERE id = '$product_id'");
    if (mysqli_num_rows($checkDuplicateQuery) > 0) {
        // Product ID already exists, show an error message or take appropriate action
        echo "<script>alert('Error: Product ID already exists, please enter another a different Product ID.');</script>";
    } else {
        // Product ID doesn't exist, proceed with the insertion
        $uploadDirectory = 'picture/';
        $uploadedFileName = $_FILES['image']['name'];
        $uploadedFileTemp = $_FILES['image']['tmp_name'];
        $uploadedFilePath = $uploadDirectory . $uploadedFileName;

        if (move_uploaded_file($uploadedFileTemp, $uploadedFilePath)) {
            $addeddate = date('Y-m-d H:i:s');

            $stmt = mysqli_prepare($conn, "INSERT INTO inventory (id, name, quantity, unitprice, variant, description, category, status, image, addeddate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssssssss", $product_id, $product_name, $quantity, $unit_price, $variant, $description, $product_category, $status, $uploadedFilePath, $addeddate);

            $actionDescription = "Added to inventory";

            $stmtInsertReport = mysqli_prepare($conn, "INSERT INTO report (id, name, quantity, unitprice, description, category, variant, status, image, Action, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            mysqli_stmt_bind_param($stmtInsertReport, "ssdsbsssss", $product_id, $product_name, $quantity, $unit_price, $description, $product_category, $variant, $status, $uploadedFilePath, $actionDescription);

            $success = true;
            $successMessage = "Product successfully added!";
        } else {
            echo "<script>alert('Error uploading file');</script>";
        }

        if ($success && mysqli_stmt_execute($stmt) && mysqli_stmt_execute($stmtInsertReport)) {
            $username = $_SESSION['username'];
            $addAction = "Add Product: $product_name";
            $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, '$username', '$addAction')";
            mysqli_query($conn, $auditTrailQuery);
            
        } else {
            echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
        }

        mysqli_stmt_close($stmt);
        mysqli_stmt_close($stmtInsertReport);
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

    <title>Admin Dashboard</title>
    <script>
        function previewImage(event) {
            var input = event.target;
            var preview = document.getElementById('imagePreview');
            
            var reader = new FileReader();
            reader.onload = function(){
                preview.src = reader.result;
            };
            
            reader.readAsDataURL(input.files[0]); // convert to base64 string
        }
       


    </script>
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
<li class="navList active">
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
                    <div class="page-header">
                    
                        <h2><ion-icon name="add-circle"></ion-icon> Product</h2>
                        <?php if ($errorOccurred): ?>
        <div class="alert alert-danger d-flex align-items-center alert-dismissible" role="alert" >
            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
            <div>
                <?php echo $errorMessage; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success d-flex align-items-center" role="alert" >
  <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
  <div>
  <?php echo $successMessage; ?>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="margin-left:auto;"></button>
</div>
<?php endif; ?>
    

                        
                    <form class="row g-3" method="POST" enctype="multipart/form-data" id="productForm">
    <div class="col-md-4">
        <div style="display:flex;">
        <label for="product_id" class="form-label">Product ID</label> 
        </div>
        <input type="text" name="product_id" id="product_id" class="form-control" required="" maxlength="8" >
    </div>
    

    <div class="col-md-5">
    <div style="display:flex;">
        <label for="product_name" class="form-label">Product Name</label>
        </div>
        <input type="text" name="product_name" id="product_name" class="form-control" required="" placeholder="" maxlength="28">
    </div>

    <div class="col-md-3">
        <label for="image" class="form-label">Browse Image</label>
        <input type="file" name="image" class="form-control" onchange="previewImage(event)" required="" accept="image/*" />

    </div>


    <div class="col-md-4">
    <div style="display:flex;">
        <label for="quantity" class="form-label">Quantity</label>
        </div>
        <input type="number" name="quantity" id="quantity" class="form-control" required="" placeholder="">
    </div>

    <div class="col-md-3">
    <div style="display:flex;">
        <label for="unit_price" class="form-label">Unit Price</label>
        </div>
        <input type="number" name="unit_price" id="unit_price" class="form-control" required="" placeholder="">
    </div>

   
    <div class="col-md-2">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select">
            <option selected>Active</option>
            <option>Inactive</option>
        </select>
    </div>

    <div class="col-md-9">
    <div style="display:flex;">
        <label for="description" class="form-label">Description</label>
        </div>
        <textarea name="description" id="description" class="form-control" style="height: 150px" required="" placeholder=""></textarea>
    </div>
    
    <div class="col-md-3">
        
        <img id="imagePreview" alt="Image Preview" style="width:300px; height: 183px;">
    </div>

    

    <div class="col-md-5">
    <div style="display:flex;">
        <label for="product_category" id="productcategory" class="form-label">Product Category</label>
        </div>
        <select name="category" id="category" class="form-select" required="" placeholder="">
        <option value="">---</option> <!-- Blank option -->
        <?php
        // Fetch category names from the category table
        $categoryQuery = mysqli_query($conn, "SELECT categoryname FROM category");
        
        // Check if the query was successful
        if ($categoryQuery) {
            // Loop through the results and populate the dropdown
            while ($row = mysqli_fetch_assoc($categoryQuery)) {
                echo "<option value='" . $row['categoryname'] . "'>" . $row['categoryname'] . "</option>";
            }
        }
        ?>
    </select>
    </div>

     <div class="col-md-4">
        <label for="variant" class="form-label">Variant</label>
        <input type="text" name="variant" id="variant" class="form-control" style="display:none;">
    </div>

    <?php
if (!empty($variant)) {
    echo "<script>
            var addAnother = confirm('Do you want to add another variant?');
            if (addAnother) {
             
                document.getElementById('product_name').value = '$product_name';
                document.getElementById('status').value = '$status';
                document.getElementById('quantity').value = '$quantity';
                document.getElementById('unit_price').value = '$unit_price';
                
                document.getElementById('description').value = '$description';

                // Set the value of the dropdown (productcategory) based on PHP value
                var productCategoryDropdown = document.getElementById('category');
                var selectedCategory = '$product_category';
                var option = document.querySelector('#category option[value=\"' + selectedCategory + '\"]');
                
                if (option) {
                    productCategoryDropdown.value = selectedCategory;
                }

                // Optionally, clear the image file input
                document.getElementById('image').value = '';

                // Optionally, focus on the first input field
                document.getElementById('product_id').focus();
            }
          </script>";
}
?>


    

    <div class="col-md-12">
        <button type="submit" name="addproduct" class="allButton" >
            <b>Add Product</b></button> 
            <button type="button" class="allButton" onclick="toggleVariantInput()" style="background-color:#2C88C1; border-color:#2C88C1;">
            <b>Add Variant</b>
        </button>
            <button type="button" class="allButton" onclick="clearForm()" style="background-color:red; border-color:red;">
            <b>Clear</b>
        </button>
       
    </div>
</form>

                </div>
            </div>
        </div>
</div>
    </section>
    <script>
function clearForm() {
    // Get all form elements
    var form = document.getElementById("productForm");

    // Reset text inputs, number inputs, and textarea
    var inputs = form.querySelectorAll("input[type=text], input[type=number], textarea");
    inputs.forEach(function(input) {
        input.value = "";
    });

    // Reset file input
    var fileInput = form.querySelector("input[type=file]");
    fileInput.value = "";

    // Reset select
    var select = form.querySelector("select");
    select.selectedIndex = 0; // Assuming the first option is the default option

    // Reset category dropdown
    var categorySelect = form.querySelector("select[name=category]");
    categorySelect.selectedIndex = 0; // Assuming the first option is the default option

    // Reset image preview
    var imagePreview = document.getElementById("imagePreview");
    imagePreview.src = ""; // Set the source to an empty string
}

function toggleVariantInput() {
    var variantInput = document.getElementById('variant');

    // Toggle visibility by changing the display style property
    variantInput.style.display = variantInput.style.display === 'none' ? 'block' : 'none';

    // Set focus to the input field if it's visible
    if (variantInput.style.display !== 'none') {
        variantInput.focus();
    }
}
</script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>

</html>