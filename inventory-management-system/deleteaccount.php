<?php
session_start();
require_once "config.php"; // Include your database connection file

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");

if (isset($_POST['delete_account'])) {
    // Get user inputs
    $selectedUsername = $_POST['username'];
    $password = $_POST['password'];

    // Validate inputs
    if (empty($selectedUsername) || empty($password)) {
        header("Location: deleteaccount.php?error=Please select a username and enter the password");
        exit();
    }

    // Fetch user details based on the selected username
    $getUserQuery = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $getUserQuery);
    mysqli_stmt_bind_param($stmt, "s", $selectedUsername);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify password
        if (password_verify($password, $row['passw'])) {
            // Password is correct, proceed with account deletion
        
            // Prepare the query to delete the user
            $deleteUserQuery = "DELETE FROM users WHERE username = ?";
            $stmtDelete = mysqli_prepare($conn, $deleteUserQuery);
        
            // Bind parameters and execute the deletion query
            mysqli_stmt_bind_param($stmtDelete, "s", $selectedUsername);
            mysqli_stmt_execute($stmtDelete);
        
            // Close the prepared statement
            mysqli_stmt_close($stmtDelete);
        
            // Redirect or perform other actions after successful deletion
            echo "<script>alert('Account deleted successfully!');  window.location.href='viewaccount.php';</script>";
        } else {
            // Incorrect password, redirect with an error message and retain the username
            header("Location: deleteaccount.php?error=Incorrect password for the selected username&username=" . urlencode($selectedUsername));
            exit();
        }
        
    } else {
        // User not found
        header("Location: deleteaccount.php?error=Selected username not found");
        exit();
    }

    // Close the statements
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
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

    <li class="navList active">
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
                    <a href="viewaccount.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
                    <form class="loginform" method="post">
                        <p class="loginform-title">Delete Your Account</p>
                        <?php
                        if (isset($_GET['error'])) {
                            echo "<p class='error'>" . $_GET['error'] . "</p>";
                        }
                        ?>

<div class="input-container">
    <input type="text" name="username" id="username" class="form-control" required value="<?php echo htmlspecialchars($_GET['username'] ?? ''); ?>" readOnly>
</div>


                        <div class="input-container">
                            <input type="password" name="password" placeholder="Enter password" class="form-control" required>
                        </div>

                        <button type="submit" class="submit" name="delete_account">Delete Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
    <script src="./index.js"></script>
</body>

</html>