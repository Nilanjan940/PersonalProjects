<?php
session_start();
require_once "config.php";

$Incorrect = isset($_SESSION['Incorrect']) ? $_SESSION['Incorrect'] : false;
$IncorrectMessage = isset($_SESSION['IncorrectMessage']) ? $_SESSION['IncorrectMessage'] : "";

unset($_SESSION['Incorrect']);
unset($_SESSION['IncorrectMessage']);

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
    <title>Login - <?php echo $companyName; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .login-box {
            background: white;
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .logo {
            width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 20px;
        }
        .login-title {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 1.8rem;
        }
        .company-name {
            color: white;
            margin-bottom: 30px;
            font-size: 2rem;
            text-align: center;
        }
        .form-control {
            height: 45px;
            margin-bottom: 15px;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #071e3d;
            border: none;
        }
        .btn-login:hover {
            background: #0a2b5a;
        }
    </style>
</head>
<body>
    <h1 class="company-name"><?php echo $companyName; ?></h1>
    
    <div class="login-box">
        <?php if (!empty($companyProfileImagePath)): ?>
            <img src="<?php echo $companyProfileImagePath; ?>" alt="Company Logo" class="logo">
        <?php endif; ?>
        
        <h2 class="login-title">Login</h2>
        
        <?php if ($Incorrect): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:">
                    <use xlink:href="#exclamation-triangle-fill"/>
                </svg>
                <div><?php echo $IncorrectMessage; ?></div>
            </div>
        <?php endif; ?>
        
        <form action="login2.php" method="post">
            <div class="mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username" required>
            </div>
            <div class="mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-login">Login</button>
        </form>
    </div>

    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="check-circle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
        </symbol>
        <symbol id="exclamation-triangle-fill" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
        </symbol>
    </svg>
</body>
</html>