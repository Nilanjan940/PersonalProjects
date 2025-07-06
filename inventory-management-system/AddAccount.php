<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["addaccount"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirmpassword"];
    
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $error = "Please fill in all fields";
    } elseif ($password !== $confirmPassword) {
        $error = "Password and confirm password do not match";
    } else {
        $checkDuplicateQuery = mysqli_query($conn, "SELECT username FROM users WHERE username = '$username'");
        if (mysqli_num_rows($checkDuplicateQuery) > 0) {
            $error = "Username already exists";
        } else {
            $hashpassword = password_hash($password, PASSWORD_DEFAULT);
            $insertQuery = "INSERT INTO users (username, passw, name) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $insertQuery);
            mysqli_stmt_bind_param($stmt, "sss", $username, $hashpassword, $username);

            if (mysqli_stmt_execute($stmt)) {
                header("Location: viewaccount.php?success=Account added successfully");
                exit();
            } else {
                $error = "Error adding account: " . mysqli_error($conn);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Account - <?php echo $companyName; ?></title>
    <link rel="stylesheet" href="testin3.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
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
                            <ion-icon name="person-add-outline"></ion-icon> Add New Account
                        </h2>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
                                    <use xlink:href="#exclamation-triangle-fill"/>
                                </svg>
                                <div><?php echo $error; ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirmpassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="confirmpassword" name="confirmpassword" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100" name="addaccount">
                                <ion-icon name="save-outline"></ion-icon> Create Account
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