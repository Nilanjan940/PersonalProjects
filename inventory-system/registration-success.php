<?php
session_start();
if (!isset($_SESSION['registration_success'])) {
    header('Location: register.php');
    exit();
}
unset($_SESSION['registration_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful | InduStock</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="card">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h2 class="mb-3">Registration Successful!</h2>
                        <p class="mb-4">Your account has been created. You can now login to your account.</p>
                        <a href="login.php" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt me-2"></i> Go to Login
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>