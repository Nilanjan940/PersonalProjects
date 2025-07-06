<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

$successDelete = false;
$successDeleteMessage = "";
$successEdit = isset($_SESSION['successEdit']) ? $_SESSION['successEdit'] : false;
$successEditMessage = isset($_SESSION['successEditMessage']) ? $_SESSION['successEditMessage'] : "";

unset($_SESSION['successEdit']);
unset($_SESSION['successEditMessage']);

// Check if delid is set and not empty
if (isset($_GET['delid']) && !empty($_GET['delid'])) {
    $delid = $_GET['delid'];

    // Fetch the product details before deletion for logging and insertion into deletedproducts
    $productDetailsQuery = "SELECT * FROM inventory WHERE id = ?";
    $stmtDetails = mysqli_prepare($conn, $productDetailsQuery);
    mysqli_stmt_bind_param($stmtDetails, "s", $delid);

    if (mysqli_stmt_execute($stmtDetails)) {
        $resultDetails = mysqli_stmt_get_result($stmtDetails);
        $productDetails = mysqli_fetch_assoc($resultDetails);
        mysqli_stmt_close($stmtDetails);

        // Use prepared statement to prevent SQL injection
        $stmtDelete = mysqli_prepare($conn, "DELETE FROM inventory WHERE id = ?");
        mysqli_stmt_bind_param($stmtDelete, "s", $delid);

        if (mysqli_stmt_execute($stmtDelete)) {
            // Product deleted successfully, log the deletion action
            $username = $_SESSION['username'];  // Assuming the user is logged in
            $deleteAction = "Delete Product: " . $productDetails['name'];
            $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, ?, ?)";

            $stmtAuditTrail = mysqli_prepare($conn, $auditTrailQuery);
            mysqli_stmt_bind_param($stmtAuditTrail, "ss", $username, $deleteAction);

            $successDelete = true;
            $successDeleteMessage = "Product deleted successfully!";

            if (mysqli_stmt_execute($stmtAuditTrail)) {
                // Logging successful
                $actionDescription = "Product deleted";
                $deletestatus = "Deleted";

                // Use prepared statement to prevent SQL injection
                $stmtInsertDeletedProduct = mysqli_prepare($conn, "INSERT INTO report (id, name, quantity, unitprice, description, category, variant, status, image, Action, date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");
                mysqli_stmt_bind_param($stmtInsertDeletedProduct, "ssdsssssss", $productDetails['id'], $productDetails['name'], $productDetails['quantity'], $productDetails['unitprice'], $productDetails['description'], $productDetails['category'], $productDetails['variant'], $deletestatus, $productDetails['image'], $actionDescription);

                mysqli_stmt_close($stmtInsertDeletedProduct);
            } else {
                // Handle the case when there is an error in logging the deletion action
                echo "<script>alert('Error logging deletion action: " . mysqli_error($conn) . "');</script>";
            }

            mysqli_stmt_close($stmtAuditTrail);
        } else {
            // Error in deletion
            echo "<script>alert('Error deleting product: " . mysqli_error($conn) . "');</script>";
        }

        mysqli_stmt_close($stmtDelete);
    } else {
        // Error in fetching product details
        echo "<script>alert('Error fetching product details: " . mysqli_error($conn) . "');</script>";
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
    <title>Inventory</title>
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet"/>
    <style>
        /* Enhanced table styling */
        #inventory {
            width: 100% !important;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        #inventory thead th {
            background-color: #071e3d;
            color: white;
            padding: 12px 15px;
            border: none;
            font-weight: 500;
        }
        
        #inventory tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }
        
        #inventory tbody tr {
            height: 60px; /* Fixed row height */
        }
        
        #inventory tbody tr:hover {
            background-color: rgba(7, 30, 61, 0.05);
        }
        
        /* Keep existing styles */
        .zoomable-image {
            cursor: zoom-in;
            transition: transform 0.3s ease;
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
        }
        
        .img-expanded {
            position: fixed;
            z-index: 9999;
            width: 80vw;
            height: auto;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            cursor: zoom-out;
            transition: all 0.3s ease;
            max-width: none !important;
            max-height: none !important;
        }
        
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        
        @media (max-width: 767px) {
            .action-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }
            
            .action-buttons .btn {
                flex: 1 0 auto;
                min-width: 40px;
            }
        }
    </style>
</head>
<body>
<nav>
    <div class="logo">
        <div class="logo-image">
            <?php
            if (!empty($companyProfileImagePath)) {
                echo '<img src="' . $companyProfileImagePath . '" alt="Company Logo" class="logoimage">';
            } else {
                echo '<img src="path/to/placeholder/image.jpg" alt="No Image">';
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
        </ul>
    </div>
</nav>

<section class="dashboard">
    <div class="top">
        <ion-icon class="navToggle" name="menu-outline"></ion-icon>
    </div>
    <div class="content-wrapper">
        <div class="container">
            <div class="row">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><ion-icon name="file-tray-full"></ion-icon> Inventory</h2>
                    <a href="generatepdf.php" class="btn btn-success btn-sm">
                        <ion-icon name="print-outline"></ion-icon> Print
                    </a>
                </div>
            </div>

            <?php if ($successDelete): ?>
                <div class="alert alert-success d-flex align-items-center alert-dismissible fade show" role="alert">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
                    <div><?php echo $successDeleteMessage; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($successEdit): ?>
                <div class="alert alert-success d-flex align-items-center alert-dismissible fade show" role="alert">
                    <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:"><use xlink:href="#check-circle-fill"/></svg>
                    <div><?php echo $successEditMessage; ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-12 col-sm-6 col-md-3 mb-2">
                    <select name="category" id="category" class="form-control" onchange="filterTable()">
                        <option value="">All Categories</option>
                        <?php
                        $categoryQuery = mysqli_query($conn, "SELECT categoryname FROM category");
                        if ($categoryQuery) {
                            while ($row = mysqli_fetch_assoc($categoryQuery)) {
                                echo "<option value='" . $row['categoryname'] . "'>" . $row['categoryname'] . "</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-2">
                    <select name="status" id="status" class="form-control" onchange="filterTable()">
                        <option value="">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-2">
                    <select name="quantityFilter" id="quantityFilter" class="form-control" onchange="filterTable()">
                        <option value="">Quantity Range</option>
                        <option value="0-10">0-10</option>
                        <option value="11-50">11-50</option>
                        <option value="51-100">51-100</option>
                        <option value="100+">100+</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <div id="customQuantityContainer" class="mt-1" style="display:none;">
                        <div class="input-group">
                            <input type="number" id="customQuantityMin" class="form-control" placeholder="Min">
                            <span class="input-group-text">-</span>
                            <input type="number" id="customQuantityMax" class="form-control" placeholder="Max">
                        </div>
                        <button class="btn btn-sm btn-primary w-100 mt-1" onclick="applyCustomQuantity()">Apply</button>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-2">
                    <select name="priceFilter" id="priceFilter" class="form-control" onchange="filterTable()">
                        <option value="">Price Range</option>
                        <option value="0-5">RM0-5</option>
                        <option value="6-20">RM6-20</option>
                        <option value="21-50">RM21-50</option>
                        <option value="50+">RM50+</option>
                        <option value="custom">Custom Range</option>
                    </select>
                    <div id="customPriceContainer" class="mt-1" style="display:none;">
                        <div class="input-group">
                            <span class="input-group-text">RM</span>
                            <input type="number" id="customPriceMin" class="form-control" placeholder="Min" step="0.01">
                            <span class="input-group-text">-</span>
                            <input type="number" id="customPriceMax" class="form-control" placeholder="Max" step="0.01">
                        </div>
                        <button class="btn btn-sm btn-primary w-100 mt-1" onclick="applyCustomPrice()">Apply</button>
                    </div>
                </div>
            </div>
                    
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="inventory">
                            <thead class="alert-info">
                                <tr>
                                    <th>Image</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Variant</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
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
                                <tr>                                      
                                    <td><img src="<?php echo $row['image'];?>" alt="Product Image" onclick="toggleImageSize(this)" class="zoomable-image img-thumbnail"></td>
                                    <td><?php echo $row['id'];?></td>
                                    <td><?php echo $row['name'];?></td>
                                    <td><?php echo $row['quantity'];?></td>
                                    <td>RM<?php echo number_format($row['unitprice'], 2);?></td>
                                    <td><?php echo ($row['variant'] !== null) ? $row['variant'] : '---'; ?></td>
                                    <td><?php echo $row['description'];?></td>
                                    <td><?php echo $row['category'];?></td>
                                    <td><span class="badge bg-<?php echo $row['status'] === 'Active' ? 'success' : 'danger'; ?>"><?php echo $row['status'];?></span></td>
                                    <td class="action-buttons">
                                        <div class="d-flex flex-wrap">
                                            <a href="edit.php?editid=<?php echo htmlentities($row['id']);?>" class="btn btn-sm btn-primary me-1 mb-1" title="Edit">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </a>
                                            <a href="Inventory.php?delid=<?php echo htmlentities($row['id']);?>" onClick="return confirm('Are you sure you want to delete this product?');" class="btn btn-sm btn-danger me-1 mb-1" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </a>
                                            <a href="printbarcode.php?barcodeid=<?php echo htmlentities($row['id']);?>" class="btn btn-sm btn-warning mb-1" title="Print Barcode">
                                                <ion-icon name="print-outline"></ion-icon>
                                            </a>
                                        </div>
                                    </td>
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
</section>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script src="./index.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {
        $('#inventory').DataTable({
            responsive: true,
            searching: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
            },
            columnDefs: [
                { responsivePriority: 1, targets: 0 }, // Image
                { responsivePriority: 2, targets: 2 }, // Name
                { responsivePriority: 3, targets: 9 }, // Actions
                { responsivePriority: 4, targets: 1 }, // ID
                { responsivePriority: 5, targets: 3 }, // Quantity
                { responsivePriority: 6, targets: 4 }, // Price
                { responsivePriority: 7, targets: 5 }, // Variant
                { responsivePriority: 8, targets: 6 }, // Description
                { responsivePriority: 9, targets: 7 }, // Category
                { responsivePriority: 10, targets: 8 } // Status
            ]
        });
    });

    // [Rest of your JavaScript functions remain the same...]
    function toggleImageSize(img) {
        if (img.classList.contains('img-expanded')) {
            img.classList.remove('img-expanded');
            img.style.position = '';
            img.style.zIndex = '';
            img.style.width = '';
            img.style.height = '';
            img.style.left = '';
            img.style.top = '';
            img.style.transform = '';
            img.style.cursor = '';
            img.style.transition = '';
        } else {
            img.classList.add('img-expanded');
            img.style.position = 'fixed';
            img.style.zIndex = '9999';
            img.style.width = '80vw';
            img.style.height = 'auto';
            img.style.left = '50%';
            img.style.top = '50%';
            img.style.transform = 'translate(-50%, -50%)';
            img.style.cursor = 'zoom-out';
            img.style.transition = 'all 0.3s ease';
        }
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('img-expanded')) {
            toggleImageSize(e.target);
        }
    });

    function filterTable() {
        var selectedCategory = $("#category").val().toLowerCase();
        var selectedStatus = $("#status").val().toLowerCase();
        var quantityRange = $("#quantityFilter").val();
        var priceRange = $("#priceFilter").val();
        
        $("#inventory tbody tr").each(function () {
            var rowCategory = $(this).find('td:eq(7)').text().toLowerCase();
            var rowStatus = $(this).find('td:eq(8)').text().toLowerCase();
            var rowQuantity = parseInt($(this).find('td:eq(3)').text());
            var rowPrice = parseFloat($(this).find('td:eq(4)').text().replace('RM', ''));
            var rowText = $(this).text().toLowerCase();
            
            var categoryMatch = (selectedCategory === "" || rowCategory === selectedCategory);
            var statusMatch = (selectedStatus === "" || rowStatus === selectedStatus);
            
            var quantityMatch = true;
            if (quantityRange !== "" && quantityRange !== "custom") {
                var range = quantityRange.split('-');
                if (range.length === 2) {
                    quantityMatch = (rowQuantity >= parseInt(range[0])) && 
                                   (range[1] === '' || rowQuantity <= parseInt(range[1]));
                } else if (quantityRange.endsWith('+')) {
                    var min = parseInt(quantityRange.replace('+', ''));
                    quantityMatch = (rowQuantity >= min);
                }
            } else if (quantityRange === "custom") {
                var customRange = $("#quantityFilter").data('custom');
                if (customRange) {
                    var rangeParts = customRange.split('-');
                    var min = rangeParts[0] === '∞' ? Infinity : parseInt(rangeParts[0]);
                    var max = rangeParts[1] === '∞' ? Infinity : parseInt(rangeParts[1]);
                    
                    quantityMatch = true;
                    if (!isNaN(min)) quantityMatch = rowQuantity >= min;
                    if (!isNaN(max)) quantityMatch = quantityMatch && rowQuantity <= max;
                }
            }
            
            var priceMatch = true;
            if (priceRange !== "" && priceRange !== "custom") {
                var range = priceRange.split('-');
                if (range.length === 2) {
                    priceMatch = (rowPrice >= parseFloat(range[0])) && 
                                 (range[1] === '' || rowPrice <= parseFloat(range[1]));
                } else if (priceRange.endsWith('+')) {
                    var min = parseFloat(priceRange.replace('+', ''));
                    priceMatch = (rowPrice >= min);
                }
            } else if (priceRange === "custom") {
                var customRange = $("#priceFilter").data('custom');
                if (customRange) {
                    var rangeParts = customRange.split('-');
                    var min = rangeParts[0] === '∞' ? Infinity : parseFloat(rangeParts[0]);
                    var max = rangeParts[1] === '∞' ? Infinity : parseFloat(rangeParts[1]);
                    
                    priceMatch = true;
                    if (!isNaN(min)) priceMatch = rowPrice >= min;
                    if (!isNaN(max)) priceMatch = priceMatch && rowPrice <= max;
                }
            }
            
            var showRow = categoryMatch && statusMatch && quantityMatch && priceMatch && 
                         (selectedCategory === "" || rowText.indexOf(selectedCategory) > -1);
            $(this).toggle(showRow);
        });
    }

    function applyCustomQuantity() {
        var min = $('#customQuantityMin').val();
        var max = $('#customQuantityMax').val();
        
        if (min === '' && max === '') {
            alert('Please enter at least one value');
            return;
        }
        
        var rangeValue = (min || '0') + '-' + (max || '∞');
        $('#quantityFilter').val('custom').data('custom', rangeValue);
        $('#customQuantityContainer').hide();
        filterTable();
    }

    function applyCustomPrice() {
        var min = $('#customPriceMin').val();
        var max = $('#customPriceMax').val();
        
        if (min === '' && max === '') {
            alert('Please enter at least one value');
            return;
        }
        
        var rangeValue = (min || '0') + '-' + (max || '∞');
        $('#priceFilter').val('custom').data('custom', rangeValue);
        $('#customPriceContainer').hide();
        filterTable();
    }
</script>

</body>
</html>