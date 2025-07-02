<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");
$c_category = count_by_id('category');
$c_inventory = count_by_id('inventory');
$c_users = count_by_id('users');

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
       <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
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

    <li class="navList active">
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
                <a href="Order.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
                    <h2><ion-icon name="swap-horizontal"></ion-icon>     Transfer Product</h2>
     <form class="row g-3" action="process_order.php" method="post">
    <div class="col-md-2">
        <div style="display:flex;">
        <label for="product_id" class="form-label">Product ID</label> 
        </div>
        <input type="text" name="product_id" id="product_id" class="form-control" required="" readonly >
    </div>
    
    <div class="col-md-2">
        <div style="display:flex;">
        <label for="product_name" class="form-label">Name</label> 
        </div>
        <input type="text" name="product_name" id="product_name" class="form-control" required="" readonly>
    </div>


    <div class="col-md-2">
    <div style="display:flex;">
        <label for="quantity" class="form-label">Quantity</label>
        </div>
        <input type="number" name="quantity" id="quantity" class="form-control" required="" placeholder="">
    </div>

    <div class="col-md-2">
        <div style="display:flex;">
        <label for="unit_price" class="form-label">Unit Price</label> 
        </div>
        <input type="text" name="unit_price" id="unit_price" class="form-control" required="" readonly >
    </div>
    
    <div class="col-md-2">
        <label for="location" class="form-label">Location</label>
       <select name="location" id="status" class="form-select" required="" placeholder="">
        <option value="">Select a location</option> <!-- Blank option -->
        <?php
        // Fetch category names from the category table
        $locationQuery = mysqli_query($conn, "SELECT name FROM location");
        
        // Check if the query was successful
        if ($locationQuery) {
            // Loop through the results and populate the dropdown
            while ($row = mysqli_fetch_assoc($locationQuery)) {
                echo "<option value='" . $row['name'] . "'>" . $row['name'] . "</option>";
            }
        }
        ?>
    </select>
    </div>

    

    <div class="col-md-2">
        <button type="submit" name="addorder" class="allButton" style="margin-top:25px;">
            Transfer</button> 
        </div>

        
</div>

</form>

<h3 style="margin-top:5px;">Select Product From Inventory</h3>

<div class="row" id="lightgallery" style="margin-top:10px;">
            <div class="col-md-12">
                <div class="table-responsive">
                <table class="table table-bordered table-striped" id="inventory" style="border-top: 1px solid #dee2e6; border-left: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6;">
                        <thead>
                            <th>Select</th>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>UnitPrice</th>
                            
                           
                        </thead>
                        <tbody>
                            <?php
                            require_once "config.php";

                            if(isset($_GET['page_no']) && $_GET['page_no']!=""){
                                $page_no=$_GET['page_no'];
                            }else{
                                $page_no=1;
                            }

                           


                            $sql=mysqli_query($conn, "SELECT * FROM inventory ");
                            $count=1;
                            $row=mysqli_num_rows($sql);
                            if($row >0){
                                while($row =mysqli_fetch_array($sql)){

                                    ?>
                                    <tr style="vertical-align: middle;">                                      
                                        <td><input type="checkbox" class="select-checkbox"></td>
                                        <td><?php echo $row['id'];?></td>
                                        <td><?php echo $row['name'];?></td>
                                        <td><?php echo $row['quantity'];?></td>
                                        <td><?php echo $row['unitprice'];?></td>

                                    </tr>

                                    <?php
                                    $count=$count+1;
                                }
                            }
                            ?>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
      $(document).ready(function () {
    $('#inventory').DataTable({
      "searching": true,
    
    });
  });

  $(document).ready(function () {
    // Check if DataTable is already initialized on the table
    if (!$.fn.DataTable.isDataTable('#inventory')) {
        // If not initialized, then initialize DataTable
        $('#inventory').DataTable({
            "searching": true,
        });
    }

    // Use event delegation for checkboxes
    $('#inventory tbody').on('change', '.select-checkbox', function () {
        // Uncheck all other checkboxes
        $('.select-checkbox').not(this).prop('checked', false);

        // Get the values of the selected row and update textboxes
        var selectedRow = $(this).closest('tr');
        var selectedInventoryId = selectedRow.find('td:eq(1)').text();
        var selectedInventoryName = selectedRow.find('td:eq(2)').text();
        var selectedInventoryQuantity = selectedRow.find('td:eq(3)').text();
        var selectedInventoryUnitPrice = selectedRow.find('td:eq(4)').text();

        // Update the textboxes with the selected values
        $('#product_id').val(selectedInventoryId);
        $('#product_name').val(selectedInventoryName);
        $('#quantity').val(selectedInventoryQuantity);
        $('#unit_price').val(selectedInventoryUnitPrice);
    });
});
</script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>

</html>
