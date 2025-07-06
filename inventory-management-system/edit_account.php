<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

$rawUsername = urldecode($_GET['editid'] ?? '');
$username = htmlspecialchars($rawUsername);
$errorOccurred = false;
$errorMessage = "";

if (isset($_POST['update_account'])) {
    $selectedUsername = $_POST['username'];
    $oldPassword = $_POST['oldpassword'];
    $newPassword = $_POST['newpassword'];
    $confirmPassword = $_POST['confirmpassword'];

    if (empty($oldPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorOccurred = true;
        $errorMessage = "Please fill in all fields";
    } elseif ($newPassword !== $confirmPassword) {
        $errorOccurred = true;
        $errorMessage = "New password and confirm password do not match";
    } else {
        $getUserQuery = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $getUserQuery);
        mysqli_stmt_bind_param($stmt, "s", $selectedUsername);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($oldPassword, $row['passw'])) {
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePasswordQuery = "UPDATE users SET passw = ? WHERE username = ?";
                $stmtUpdate = mysqli_prepare($conn, $updatePasswordQuery);
                mysqli_stmt_bind_param($stmtUpdate, "ss", $hashedNewPassword, $selectedUsername);
                
                if (mysqli_stmt_execute($stmtUpdate)) {
                    header("Location: viewaccount.php?success=Password updated successfully");
                    exit();
                } else {
                    $errorOccurred = true;
                    $errorMessage = "Error updating password";
                }
            } else {
                $errorOccurred = true;
                $errorMessage = "Incorrect old password";
            }
        } else {
            $errorOccurred = true;
            $errorMessage = "User not found";
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
    <title>Change Password - <?php echo $companyName; ?></title>
    <style>
        .account-form {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .form-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .form-title ion-icon {
            margin-right: 10px;
        }
    </style>
</head>

<body>
<nav>
    <div class="logo">
        <div class="logo-image">
            <?php if (!empty($companyProfileImagePath)): ?>
                <img src="<?php echo $companyProfileImagePath; ?>" alt="Company Logo" class="logoimage">
            <?php else: ?>
                <img src="path/to/placeholder/image.jpg" alt="No Image">
            <?php endif; ?>
        </div>
        <div class="logo-name"><?php echo $companyName; ?></div>
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
                <div class="col-lg-12">
                    <a href="viewaccount.php" class="btn btn-light mb-3">
                        <ion-icon name="arrow-back-outline"></ion-icon> Back to Accounts
                    </a>
                    
                    <div class="account-form">
                        <h2 class="form-title">
                            <ion-icon name="key-outline"></ion-icon> Change Password
                        </h2>
                        
                        <?php if ($errorOccurred): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
                                    <use xlink:href="#exclamation-triangle-fill"/>
                                </svg>
                                <div><?php echo $errorMessage; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_GET['error'])): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
                                    <use xlink:href="#exclamation-triangle-fill"/>
                                </svg>
                                <div><?php echo htmlspecialchars($_GET['error']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo htmlspecialchars($_GET['username'] ?? $username); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="oldpassword" class="form-label">Old Password</label>
                                <input type="password" class="form-control" id="oldpassword" name="oldpassword" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="newpassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newpassword" name="newpassword" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmpassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100" name="update_account">
                                <ion-icon name="save-outline"></ion-icon> Update Password
                            </button>
                        </form>
                    </div>
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