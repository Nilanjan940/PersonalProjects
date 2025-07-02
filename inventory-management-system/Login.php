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
	<title>Login</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="testin3.css">
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
<style>
	body{
		background: linear-gradient(90deg, #4b6cb7 0%, #182848 100%);
		font-family:Arial;
	}
	</style>
<body>

<div style="text-align: center;">
    <h1 style="color:white; font-size:50px; margin-top:25px; font-family:Arial;">
        <strong><?php echo $companyName; ?></strong>
    </h1>
</div>


<div class="col-md-12">
	<form class="loginform" action="login2.php" method="post" style="">
	<?php
    if (!empty($companyProfileImagePath)) {
        echo '<img src="' . $companyProfileImagePath . '" alt="Company Logo" class="logoimage" style="width:180px; display:block; margin:auto; height:120px; margin-top:-100px;">';
    } else {
        echo '<img src="path/to/placeholder/image.jpg" alt="No Image" style="">';
    }
    ?>
	<p class="loginform-title" >Log in</p>
	<?php if ($Incorrect): ?>
		<div class="alert alert-danger d-flex align-items-center alert-dismissible" role="alert" >
            <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Danger:"><use xlink:href="#exclamation-triangle-fill"/></svg>
            <div>
  <?php echo $IncorrectMessage; ?>
  </div>
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="margin-left:auto;"></button>
</div>
<?php endif; ?>
		<div class="input-container">
		<input type="text" name="username" placeholder="Enter username" class="form-control" required>
		<span>
          </span>
		</div>
		<div class="input-container">
		<input type="password" name="password" placeholder="Enter password" class="form-control" required>
		</div>
		<button type="submit" class="submit">Login</button>
</form>
	</div>
	
</body>
</html>