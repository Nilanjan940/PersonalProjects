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

    // Fetch the category details before deletion for logging
    $categoryDetailsQuery = "SELECT * FROM category WHERE categoryid = ?";
    $stmtDetails = mysqli_prepare($conn, $categoryDetailsQuery);
    mysqli_stmt_bind_param($stmtDetails, "s", $delid);

    if (mysqli_stmt_execute($stmtDetails)) {
        $resultDetails = mysqli_stmt_get_result($stmtDetails);
        $categoryDetails = mysqli_fetch_assoc($resultDetails);
        mysqli_stmt_close($stmtDetails);

        // Use prepared statement to prevent SQL injection
        $stmtDelete = mysqli_prepare($conn, "DELETE FROM category WHERE categoryid = ?");
        mysqli_stmt_bind_param($stmtDelete, "s", $delid);

        if (mysqli_stmt_execute($stmtDelete)) {
            // Category deleted successfully, log the deletion action
            $username = $_SESSION['username'];  // Assuming the user is logged in
            $deleteAction = "Delete Category: " . $categoryDetails['categoryname'];
            $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, ?, ?)";

            $stmtAuditTrail = mysqli_prepare($conn, $auditTrailQuery);
            mysqli_stmt_bind_param($stmtAuditTrail, "ss", $username, $deleteAction);

            $successDelete = true;
            $successDeleteMessage = "Category deleted successfully!";

            if (mysqli_stmt_execute($stmtAuditTrail)) {
                // Logging successful
                mysqli_stmt_close($stmtAuditTrail);
            } else {
                // Handle the case when there is an error in logging the deletion action
                echo "<script>alert('Error logging deletion action: " . mysqli_error($conn) . "');</script>";
            }
        } else {
            // Error in deletion
            echo "<script>alert('Error deleting category: " . mysqli_error($conn) . "');</script>";
        }

        mysqli_stmt_close($stmtDelete);
    } else {
        // Error in fetching category details
        echo "<script>alert('Error fetching category details: " . mysqli_error($conn) . "');</script>";
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
    <title>Category</title>
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
            <li class="navList active">
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
                <div class="col-md-12 d-flex justify-content-between align-items-center">
                    <h2><ion-icon name="grid"></ion-icon> Category</h2>
                    <a href="addcategory.php" class="btn btn-success btn-sm">
                        <ion-icon name="add-circle-outline"></ion-icon> Add Category
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
                <div class="col-md-6">
                    <input type="text" id="searchInput" class="form-control" placeholder="Search categories...">
                </div>
                <div class="col-md-6">
                    <select name="sortOrder" id="sortOrder" class="form-control">
                        <option value="asc">Sort A-Z</option>
                        <option value="desc">Sort Z-A</option>
                    </select>
                </div>
            </div>
                    
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="categoryTable">
                            <thead class="alert-info">
                                <tr>
                                    <th>Category ID</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                require_once "config.php";

                                $sql = mysqli_query($conn, "SELECT * FROM category");
                                $count = 1;
                                $row = mysqli_num_rows($sql);
                                if($row > 0){
                                    while($row = mysqli_fetch_array($sql)){
                                ?>
                                <tr style="vertical-align: middle;">                                      
                                    <td><?php echo $row['categoryid'];?></td>
                                    <td><?php echo $row['categoryname'];?></td>
                                    <td><?php echo $row['categorydescription'];?></td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="editcategory.php?editid=<?php echo htmlentities($row['categoryid']);?>" class="btn btn-primary btn-sm me-1" title="Edit">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </a>
                                            <a href="Category.php?delid=<?php echo htmlentities($row['categoryid']);?>" onClick="return confirm('Are you sure you want to delete this category?');" class="btn btn-danger btn-sm" title="Delete">
                                                <ion-icon name="trash-outline"></ion-icon>
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
    $('#categoryTable').DataTable({
        responsive: true,
        searching: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            { responsivePriority: 1, targets: 0 }, // Category ID
            { responsivePriority: 2, targets: 1 }, // Category Name
            { responsivePriority: 3, targets: 3 }, // Actions
            { responsivePriority: 4, targets: 2 }  // Description
        ]
    });

    // Custom search functionality
    $('#searchInput').keyup(function(){
        $('#categoryTable').DataTable().search($(this).val()).draw();
    });

    // Custom sort functionality
    $('#sortOrder').change(function(){
        var order = $(this).val();
        $('#categoryTable').DataTable().order([1, order]).draw();
    });
});

function confirmDelete() {
    return confirm('Are you sure you want to delete this category?');
}
</script>

</body>
</html>