<?php
session_start();
require_once "config.php"; // Include your database connection file

isset($_SESSION['login']) && $_SESSION['login']===true? '': header("Location:Login.php");

$rawUsername = urldecode($_GET['editid'] ?? '');
$username = htmlspecialchars($rawUsername);
$errorOccurred = false;
$errorMessage = "";

if (isset($_POST['update_account'])) {
    // Get user inputs
    $selectedUsername = $_POST['username'];
    $oldPassword = $_POST['oldpassword'];
    $newPassword = $_POST['newpassword'];
    $confirmPassword = $_POST['confirmpassword'];

    // Validate inputs
    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        header("Location: edit_account.php?username={$selectedUsername}&error=Please fill in all fields");
        exit();
    }

    // Fetch user details based on the selected username
    $getUserQuery = "SELECT * FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $getUserQuery);
    mysqli_stmt_bind_param($stmt, "s", $selectedUsername);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verify old password
        if (password_verify($oldPassword, $row['passw'])) {
            // Old password is correct, proceed with further checks

            // Check if new password and confirm password match
            if ($newPassword !== $confirmPassword) {
                header("Location: edit_account.php?username={$selectedUsername}&error=New password and confirm password do not match");
                exit();
            }

            // Update the user's password in the database
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePasswordQuery = "UPDATE users SET passw = ? WHERE username = ?";
            $stmtUpdate = mysqli_prepare($conn, $updatePasswordQuery);
            mysqli_stmt_bind_param($stmtUpdate, "ss", $hashedNewPassword, $selectedUsername);
            mysqli_stmt_execute($stmtUpdate);

            // Close the prepared statements
            mysqli_stmt_close($stmt);
            mysqli_stmt_close($stmtUpdate);
            mysqli_close($conn);

            // Redirect or perform other actions after successful password update
            header("Location: Settings.php?username={$selectedUsername}");
            exit();
        } else {
            // Incorrect old password, redirect with an error message and retain the username
            $errorOccurred = true;
        $errorMessage = "Incorrect password for old and new password";
        }
    } else {
        // User not found
        header("Location: edit_account.php?username={$selectedUsername}&error=Selected username not found");
        exit();
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
                    <form class="loginform" style="width:600px;" method="post">
    <p class="loginform-title">Change password</p>

    <?php if ($errorOccurred): ?>
        <div class="alert alert-danger d-flex align-items-center alert-dismissible" role="alert" >
            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
            <div>
                <?php echo $errorMessage; ?>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php
    if (isset($_GET['error'])) {
        echo '<div class="error-message">' . $_GET['error'] . '</div>';
    }
    ?>

    <div class="input-container">
        <input type="text" name="username" id="username" class="form-control" required value="<?php echo htmlspecialchars($_GET['username'] ?? $username); ?>" readonly>
    </div>

    <div class="input-container">
        <input type="password" name="oldpassword" placeholder="Enter old password" class="form-control" required>
    </div>

    <div class="input-container">
        <input type="password" name="newpassword" placeholder="Enter new password" class="form-control" required>
    </div>

    <div class="input-container">
        <input type="password" name="confirmpassword" placeholder="Confirm new password" class="form-control" required>
    </div>

    <button type="submit" class="submit" name="update_account">Update Account</button>
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