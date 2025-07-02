<?php
session_start();
include "config.php";

if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    if (isset($_SESSION['id'])) {
        $username = $_SESSION['username'];
        $logoutHistoryQuery = "UPDATE loginouthistory SET logout = CURRENT_TIMESTAMP WHERE username = '$username' AND logout IS NULL";

        if (mysqli_query($conn, $logoutHistoryQuery)) {
            $logoutAction = "Logout";
            $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, '$username', '$logoutAction')";
            mysqli_query($conn, $auditTrailQuery);

            // Destroy the session
            // Redirect to login page or any other page after logout
            session_destroy();

            echo '<script>
                    var logoutConfirmed = confirm("Are you sure you want to logout?");
                    if (logoutConfirmed) {
                        window.location.href = "Login.php";
                    } else {
                        // Redirect to some other page or stay on the current page
                        // window.location.href = "Dashboard.php";
                    }
                </script>';
        } else {
            echo "Error updating logout time in loginouthistory: " . mysqli_error($conn);
        }
    }
} else {
    header("Location: Login.php");
    exit();
}
?>




