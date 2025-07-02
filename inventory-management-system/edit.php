<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");

if (isset($_POST['update'])) {
    $eid = isset($_GET['editid']) ? $_GET['editid'] : '';
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $status = $_POST['status'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $variant = $_POST['variant'];
    $description = $_POST['description'];
    $product_category = $_POST['category'];

    $checkDuplicateQuery = mysqli_query($conn, "SELECT id FROM inventory WHERE id = '$product_id' AND id != '$eid'");
    if (mysqli_num_rows($checkDuplicateQuery) > 0) {
        // Product ID already exists, show an error message or take appropriate action
        echo "<script>alert('Error: Product ID already exists, please enter another a different Product ID.');</script>";
    } else {
    // Get the old product details before the update
    $oldProductQuery = "SELECT * FROM inventory WHERE id = ?";
    $stmtOldProduct = mysqli_prepare($conn, $oldProductQuery);
    mysqli_stmt_bind_param($stmtOldProduct, "s", $eid);

    if (mysqli_stmt_execute($stmtOldProduct)) {
        $resultOldProduct = mysqli_stmt_get_result($stmtOldProduct);
        $oldProductDetails = mysqli_fetch_assoc($resultOldProduct);
        mysqli_stmt_close($stmtOldProduct);

        $imagePath = '';  // Initialize the variable to store the new image path

        // Check if a new image is uploaded
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $uploadedFileName = $_FILES['image']['name'];
            $uploadedFileTemp = $_FILES['image']['tmp_name'];
            $uploadDirectory = 'picture/';  // Change this to your actual upload directory
            $imagePath = $uploadDirectory . $uploadedFileName;

            // Move the uploaded file to the desired directory
            if (!move_uploaded_file($uploadedFileTemp, $imagePath)) {
                echo "<script>alert('Error uploading image!');</script>";
                exit();
            }
        } else {
            // No new image uploaded, use the old image path
            $imagePath = $oldProductDetails['image'];
        }

        // Perform the update
        $sql = "UPDATE inventory SET id='$product_id', name='$product_name', status='$status', quantity='$quantity', unitprice='$unit_price', variant='$variant', description='$description', category='$product_category', image='$imagePath' WHERE id='$eid'";
        if (mysqli_query($conn, $sql)) {
             $_SESSION['successEdit'] = true; // Set successEdit to true
        $_SESSION['successEditMessage'] = 'You have successfully updated the record!';
        echo "<script>document.location='Inventory.php';</script>";
            if ($quantity != $oldProductDetails['quantity']) {
                // Prepare and execute the statement to insert into the report table
                $actionDescription = "Quantity changed from " . $oldProductDetails['quantity'] . " to " . $quantity;
                if($status != $oldProductDetails['status']){
                    $actionDescription = "Quantity changed from " . $oldProductDetails['quantity'] . " to " . $quantity . " and Status changed from " . $oldProductDetails['status'] . "to" . $status;
                }
            }else{
                if($status != $oldProductDetails['status']){
                    $actionDescription =  "Status changed from " . $oldProductDetails['status'] . "to" . $status;
                }
                $actionDescription="Product edited";
            }
           
            $stmtInsertReport = mysqli_prepare($conn, "INSERT INTO report (id, name, quantity, unitprice, description, category, variant, status, image, Action, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
            mysqli_stmt_bind_param($stmtInsertReport, "ssdsbsssss", $product_id, $product_name, $quantity, $unit_price, $description, $product_category, $variant, $status, $imagePath, $actionDescription);
           

           
               
                
           
              
            if (mysqli_stmt_execute($stmtInsertReport)) {
                $username = $_SESSION['username'];
                $addAction = "Edit Product: $product_name";
                $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, '$username', '$addAction')";
                mysqli_query($conn, $auditTrailQuery);

                echo "<script>alert('You have successfully updated the record!');</script>";
                echo "<script>document.location='inventory.php';</script>";
            } else {
                echo "<script>alert('Error logging edit action: " . mysqli_error($conn) . "');</script>";
            }

            mysqli_stmt_close($stmtInsertReport);
        } else {
            echo "<script>alert('Error updating product: " . mysqli_error($conn) . "');</script>";
        }
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
            <li class="navList">
    <a href="Dashboard.php">
    <ion-icon name="stats-chart"></ion-icon>
        <span class="links">Dashboard</span>
    </a>
</li>
<li class="navList active">
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
                    <a href="Inventory.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
                        <h2><ion-icon name="create-outline"></ion-icon>     Edit Product</h2>
</div>

                        
                    <form class="row g-3" method="POST" enctype="multipart/form-data" id="productForm">
                    <?php
                            $eid = isset($_GET['editid']) ? $_GET['editid'] : '';
                            $sql = mysqli_query($conn, "SELECT * FROM inventory WHERE id='$eid'");
                            while ($row = mysqli_fetch_array($sql)) {
                            ?>


    <div class="col-md-4">
        <div style="display:flex;">
        <label for="product_id" class="form-label">Product ID</label> 
        </div>
        <input type="text" name="product_id" id="product_id" class="form-control" value="<?php echo $row['id'];?>" required="" >
    </div>
    

    <div class="col-md-5">
    <div style="display:flex;">
        <label for="product_name" class="form-label">Product Name</label>
        </div>
        <input type="text" name="product_name" value="<?php echo $row['name'];?>" id="product_name" class="form-control">
    </div>

   

    <div class="col-md-3">
        <label for="image" class="form-label">Browse Image</label>
        <input type="file" name="image" class="form-control" onchange="previewImage(event)" accept="image/*" />

    </div>


    <div class="col-md-4">
    <div style="display:flex;">
        <label for="quantity" class="form-label">Quantity</label>
        </div>
        <input type="number" name="quantity" id="quantity" class="form-control" required="" value="<?php echo $row['quantity'];?>" placeholder="">
    </div>

    <div class="col-md-3">
    <div style="display:flex;">
        <label for="unit_price" class="form-label">Unit Price</label>
        </div>
        <input type="number" name="unit_price" id="unit_price" class="form-control" required="" value="<?php echo $row['unitprice'];?>" placeholder="">
    </div>

   
    <div class="col-md-2">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" value="<?php echo $row['status'];?>">
            <option selected>Active</option>
            <option>Inactive</option>
        </select>
    </div>

    <div class="col-md-9">
    <div style="display:flex;">
        <label for="description" class="form-label">Description</label>
        </div>
        <textarea name="description" id="description" class="form-control" style="height: 150px"><?php echo $row['description']; ?></textarea>
    </div>
    
        
    <div class="col-md-3">
    <td><img src="<?php echo $row['image'];?>" alt="Product Image" onclick="toggleImageSize(this)" class="zoomable-image" style="text-align:center; width:295px; height: 183px;" id="imagePreview"></td>
</div>


        


    

    <div class="col-md-5">

        <label for="product_category" id="variant" class="form-label">Product Category</label>
        <select name="category" value="<?php echo $row['category'];?>" id="category" class="form-control">
        <option value="">---</option> <!-- Blank option -->
        <?php
        // Fetch category names from the category table
        $categoryQuery = mysqli_query($conn, "SELECT categoryname FROM category");
        
        // Check if the query was successful
        if ($categoryQuery) {
            // Loop through the results and populate the dropdown
            while ($categoryRow = mysqli_fetch_assoc($categoryQuery)) {
                $selected = ($categoryRow['categoryname'] == $row['category']) ? 'selected' : '';
                echo "<option value='" . $categoryRow['categoryname'] . "' $selected>" . $categoryRow['categoryname'] . "</option>";
            }
        }
        ?>
    </select>
    </div>

     <div class="col-md-4">
        <label for="variant" class="form-label">Variant</label>
        <input type="text" name="variant" id="variant" class="form-control" value="<?php echo $row['variant'];?>">
    </div>


<?php } ?>
    

    <div class="col-md-12">
    <button type="submit" name="update" class="allButton">
            Update Product
        </button>
            
       
    </div>
</form>

                </div>
            </div>
        </div>
</div>
    </section>






   

  

    

    <script>
   function addVariant() {
    var newVariant = prompt("Enter a new variant:");

    if (newVariant !== null && newVariant.trim() !== '') {
        // Set the HTML content of the dropdown with the new variant as the first option and select it
        $('#variant').html('<option selected value="' + newVariant + '">' + newVariant + '</option>');
    }
}

 
        function previewImage(event) {
            var input = event.target;
            var preview = document.getElementById('imagePreview');
            
            var reader = new FileReader();
            reader.onload = function(){
                preview.src = reader.result;
            };
            
            reader.readAsDataURL(input.files[0]); // convert to base64 string
        }
       


        function generateBarcode() {
            var productId = document.getElementById('product_id').value;
            var barcodeContainer = document.getElementById('barcodeContainer');

            // Use an image tag to display the barcode
            barcodeContainer.innerHTML = '<img src="barcode/barcode.php?text=' + productId + '" alt="Barcode">';
        }

        // Call the function initially to display the barcode
        generateBarcode();

        // Attach an event listener to update the barcode when the product ID changes
        document.getElementById('product_id').addEventListener('input', generateBarcode);

</script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>

</html>