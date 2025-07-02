<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login'] === true ? '' : header("Location: Login.php");
$c_category = count_by_id('category');
$c_inventory = count_by_id('inventory');
$c_users = count_by_id('users');

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order'])) {
    // Process the form data

    // Redirect to orderpdf.php after processing
    header("Location: orderpdf.php");
    exit(); // Make sure to exit after setting the location header
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

$locationQuery = mysqli_query($conn, "SELECT DISTINCT location FROM deliverorder") or die(mysqli_error($conn));
$locations = [];

while ($locationFetch = mysqli_fetch_assoc($locationQuery)) {
    $locations[] = $locationFetch['location'];
}
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
    <title>Admin Dashboard</title>
</head>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

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
                <h2><ion-icon name="swap-horizontal"></ion-icon>     Product Transfer</h2>
                <form class="form-inline" method="POST" id="orderForm" action="">
        <div class="row">
            <div class="col-md-4">
                <label for="date1">Date From:</label>
                <input type="date" class="form-control" placeholder="Start" name="date1"
                    value="<?php echo isset($_POST['date1']) ? $_POST['date1'] : '' ?>" />
            </div>
            <div class="col-md-4">
                <label for="date2">To</label>
                <input type="date" class="form-control" placeholder="End" name="date2"
                    value="<?php echo isset($_POST['date2']) ? $_POST['date2'] : '' ?>" />
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary" name="search" style="margin-top:24px; "><ion-icon
                            name="search-outline"></ion-icon></button>
            </div>
            <div class="col-md-1">
                <a href="Order.php" type="button" class="btn btn-success" style="margin-top:24px; margin-left:-40px;"><ion-icon
                            name="refresh-outline"></ion-icon></a>
            </div>
            <div class="col-md-2">
            <a href="Deliver_order.php" class="allButton" style="margin-top:18px;;">Transfer Product</a>
            </div>
</form>
<form class="form-inline" method="POST" id="orderForm" action="orderpdf.php" onsubmit="return validateLocation();">
    <?php
    $date1 = isset($_POST['date1']) ? $_POST['date1'] : '';
    $date2 = isset($_POST['date2']) ? $_POST['date2'] : '';
    ?>
   
    <input type="hidden" name="date1" value="<?php echo $date1; ?>" >
    <input type="hidden" name="date2" value="<?php echo $date2; ?>">

    
    <div class="row">
    <div class="col-md-10">
        <label for="locationFilter">Filter by Location:</label>
        <select name="locationFilter" id="locationFilter" class="form-select" onchange="filterTable()">
            <option value="">All Locations</option>
            <?php
            foreach ($locations as $location) {
                echo "<option value='$location'>$location</option>";
            }
            ?>
        </select>
    </div>
    
    <div class="col-md-2">
        <input type="submit" value="Print" name="order" style="margin-top:18px; background-color:green; border-color:green;" class="allButton">
    </div>
</div>



    
</form>


<h3 style="margin-top:5px;">Transfered Product</h3>
<div class="row" id="lightgallery" >
            <div class="col-md-12">
                <div class="table-responsive">
                <table class="table table-bordered table-striped" id="inventory"
    style="border-top: 1px solid #dee2e6; border-left: 1px solid #dee2e6; border-bottom: 1px solid #dee2e6; margin-top:5px;">
    <thead>
        <tr>
            <th>Tracking ID</th>
            <th>Product ID</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Location</th>
            <th>Transfered Date</th>
        </tr>
    </thead>
    <tbody>
    <?php include 'orderrange.php' ?>
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
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap.min.css" rel="stylesheet"/>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
   
  function filterTable() {
        var locationFilter = document.getElementById('locationFilter');
        var selectedLocation = locationFilter.value.toLowerCase();

        var tableRows = document.querySelectorAll('#inventory tbody tr');

        tableRows.forEach(function (row) {
            var locationCell = row.querySelector('td:nth-child(6)'); // Assuming location is in the 6th column

            if (!selectedLocation || locationCell.textContent.toLowerCase() === selectedLocation) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function generatePDF() {
        // Get the HTML content of the table
        var tableContent = $('#inventory').html();

        // Send the HTML content to the server
        $.post('orderpdf.php', { tableContent: tableContent }, function (response) {
            // Handle the response as needed (e.g., show a link to the generated PDF)
            alert(response);
        });
    }

    function submitForm() {
        document.getElementById('orderForm').submit();
    }

    function validateLocation() {
        var selectedLocation = document.getElementById('locationFilter').value;

        if (selectedLocation === "") {
         
        }

        return true; // Allow form submission
    }
</script>

    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>

</html>
<?php
// Close the database connection
mysqli_close($conn);
?>