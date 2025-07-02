<?php
session_start();
require_once "config.php";
isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addaccount"])) {
    // Get form data
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmpassword"];
    $hashpassword=password_hash($password,PASSWORD_DEFAULT);
    
    $checkDuplicateQuery = mysqli_query($conn, "SELECT name FROM users WHERE name = '$username'");
    if (mysqli_num_rows($checkDuplicateQuery) > 0) {
        // Product ID already exists, show an error message or take appropriate action
        echo "<script>alert('Error: Username already exists, please enter another a different username.');</script>";
    } else {
    // Check if password and confirm password match
    if ($password == $confirmPassword) {
        // Hash the password before storing it in the database
        // Insert the data into the users table
        $insertQuery = "INSERT INTO users (username, passw, name) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "sss", $username, $hashpassword, $username);

        if (mysqli_stmt_execute($stmt)) {
            // User added successfully
            echo "<script>alert('Account added successfully!');  window.location.href='viewaccount.php';</script>";
           
        } else {
            // Error adding user
            $error = "Error adding account: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
    } else {
        // Password and confirm password do not match
        $error = "Error: Password and confirm password do not match.";
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
    <title>Document</title>
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
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
            <li class="navList ">
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

    <li class="navList active">
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
            <a href="Settings.php" style="font-size: 24px; text-decoration:none; color:black;">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                    </a>
<form class="loginform" method="POST">
<p class="loginform-title">Add account</p>
    <?php if ($error) { ?>
        <p class="error"><?php echo $error; ?></p>
    <?php } ?>
    <div class="input-container">
            <input required="" type="text"  placeholder="Enter username" class="form-control" name="username">
    </div>


        
    <div class="input-container">
        <input required="" type="password" placeholder="Enter password" class="form-control" name="password">
       
    </div>
    <div class="input-container">
        <input required="" type="password"  placeholder="Confirm password" class="form-control" name=confirmpassword>
        
    </div>
    <button class="submit" name="addaccount">Add account</button>

   
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