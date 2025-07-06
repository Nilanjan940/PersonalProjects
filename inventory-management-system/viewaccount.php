<?php
session_start();
require_once "config.php";

isset($_SESSION['login']) && $_SESSION['login']===true ? '' : header("Location:Login.php");

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
    <title>Manage Accounts - <?php echo $companyName; ?></title>
    <style>
        .account-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .table-actions {
            white-space: nowrap;
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
                    <a href="Settings.php" class="btn btn-light mb-3">
                        <ion-icon name="arrow-back-outline"></ion-icon> Back to Settings
                    </a>
                    
                    <div class="account-card">
                        <h2>
                            <ion-icon name="people-outline"></ion-icon> Manage Accounts
                        </h2>
                        
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success d-flex align-items-center">
                                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Success:">
                                    <use xlink:href="#check-circle-fill"/>
                                </svg>
                                <div><?php echo htmlspecialchars($_GET['success']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">User Accounts</h4>
                            <a href="AddAccount.php" class="btn btn-primary">
                                <ion-icon name="person-add-outline"></ion-icon> Add Account
                            </a>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Username</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $user_info_query = mysqli_query($conn, "SELECT * FROM users");
                                    if ($user_info_query) {
                                        while ($user_data = mysqli_fetch_assoc($user_info_query)) {
                                            echo "<tr>";
                                            echo "<td>{$user_data['username']}</td>";
                                            echo "<td class='table-actions'>";
                                            echo "<a href='edit_account.php?editid={$user_data['username']}' class='btn btn-warning btn-sm me-2'>";
                                            echo "<ion-icon name='key-outline'></ion-icon> Change Password";
                                            echo "</a>";
                                            echo "<a href='deleteaccount.php?username={$user_data['username']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this account?\")'>";
                                            echo "<ion-icon name='trash-outline'></ion-icon> Delete";
                                            echo "</a>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='2'>Error fetching user information: " . mysqli_error($conn) . "</td></tr>";
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
</section>

<script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
<script src="./index.js"></script>
</body>
</html>