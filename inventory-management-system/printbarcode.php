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
    <script src="https://raw.githack.com/eKoopmans/html2pdf/master/dist/html2pdf.bundle.js"></script>
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
                <a href="Inventory.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
<?php
$eid = isset($_GET['barcodeid']) ? $_GET['barcodeid'] : '';
$sql = mysqli_query($conn, "SELECT * FROM inventory WHERE id='$eid'");
while ($row = mysqli_fetch_array($sql)) {
?>  
                <div class="loginform" >
<p class="loginform-title">Print barcode</p>
    
    <div class="input-container">
    <div style="display:flex;" class="form-control">
        <label style="">Product ID:</label>
        <span style="margin-left:auto;"><?php echo $row['id'];?></span>
    </div>
    </div>

    <div class="input-container">
    <div style="display:flex;" class="form-control">
        <label style="">Product Name:</label>
        <span style="margin-left:auto;"><?php echo $row['name'];?></span>
    </div>
    </div>

    <div class="input-container">
    <div style="display:flex;" class="form-control">
        <label style="">Unit Price:</label>
        <span style="margin-left:auto;"><?php echo $row['unitprice'];?></span>
    </div>
    </div>

        
    <label style="">Barcode:</label>
    <div id="print-section" style="width:160px; border: 1px solid #000; padding: 2px; padding-top:8px; border-radius:3px;">
        <span id="product_name" style="margin-left: 8px;"><?php echo $row['name'];?></span>
        <div id="barcodeContainer" style="margin-top:4px;"></div>
        <span id="product_id" style="margin-left: 8px;"><?php echo $row['id'];?></span>
        <br>
        <div class="row">
            <span id="product_price" style="margin-left: 8px; font-size:20px;">RM       <?php echo $row['unitprice'];?></span>
        </div>
</div>
    

    <div style="display:flex;" >
        <label style="">Quantity:</label>
        
        <div class="col-md-8" style="margin-left:auto;"> 
       
        <input type="number" name="quantity" id="quantity"  class="form-control" required="" placeholder="">
</div>
  
</div>

    <button onclick="generatePDF()" class="submit">Print</button>
<?php } ?>
        
    </div>
  

   
</div>
                  


</div>
</div>
</div>
</div>
</div>
</section>
<script src="https://cdn.jsdelivr.net/jsbarcode/3.11.0/JsBarcode.all.min.js"></script>
<script>
   function generateBarcode() {
        var productId = document.getElementById('product_id').innerText;
        var barcodeContainer = document.getElementById('barcodeContainer');

        // Use an image tag to display the barcode
        barcodeContainer.innerHTML = '<img src="barcode/barcode.php?text=' + productId + '" alt="Barcode">';
    }
    generateBarcode();

    function generatePDF() {
        // Get the quantity from the input
        var quantity = parseInt(document.getElementById('quantity').value, 10);

        // Check if quantity is a positive number
        if (isNaN(quantity) || quantity <= 0) {
            alert('Please enter a valid quantity greater than 0.');
            return;
        }

        // Use html2pdf to convert the content to a PDF
        var element = document.getElementById('print-section');

        // Create a container to hold the repeated content
        var repeatedContent = document.createElement('div');

        // Generate the barcode for the first section
        generateBarcode();

        // Clone the print-section and append it to the container
        var firstClonedSection = element.cloneNode(true);
        repeatedContent.appendChild(firstClonedSection);

        // Create a loop to repeat the content and generate barcodes
        for (var i = 1; i < quantity; i++) {
            // Clone the print-section and append it to the container
            var clonedSection = element.cloneNode(true);
            repeatedContent.appendChild(clonedSection);

            // Generate the barcode for each section
            generateBarcode();
        }

        // Append the repeated content to the document
        document.body.appendChild(repeatedContent);

        // Convert the content to a PDF
        html2pdf(repeatedContent, {
            margin: 0,
            filename: 'barcode.pdf',
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            output: 'save'
        });

        // Remove the repeated content from the document
        document.body.removeChild(repeatedContent);
    }
</script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>

</html>