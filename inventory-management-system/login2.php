<?php
session_start();
include "config.php";


if (isset($_POST['username']) && isset($_POST['password'])) {

    function validate($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $username = validate($_POST['username']);
    $password = validate($_POST['password']);

    if (empty($username)) {
        header("Location: Login.php?error=User name is required");
        exit();
    } else if (empty($password)) {
        header("Location: Login.php?error=Password is required");
        exit();
    } else {
        $sql = "SELECT * FROM users WHERE username=?";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($result) === 1) {
                $row = mysqli_fetch_assoc($result);

                // Verify hashed password
                if (password_verify($password, $row['passw'])) {
                    // Check if a record already exists for this user in loginouthistory
                    $existingRecordQuery = "SELECT * FROM loginouthistory WHERE username = ? AND logout IS NULL";
                    $existingRecordStmt = mysqli_prepare($conn, $existingRecordQuery);

                    if ($existingRecordStmt) {
                        mysqli_stmt_bind_param($existingRecordStmt, "s", $username);
                        mysqli_stmt_execute($existingRecordStmt);

                        $existingRecordResult = mysqli_stmt_get_result($existingRecordStmt);

                        if (mysqli_num_rows($existingRecordResult) == 0) {
                            // No existing record, create a new one
                            $loginHistoryQuery = "INSERT INTO loginouthistory (username, login) VALUES (?, CURRENT_TIMESTAMP)";
                            $loginHistoryStmt = mysqli_prepare($conn, $loginHistoryQuery);

                            if ($loginHistoryStmt) {
                                mysqli_stmt_bind_param($loginHistoryStmt, "s", $username);
                                mysqli_stmt_execute($loginHistoryStmt);
                                mysqli_stmt_close($loginHistoryStmt);
                            }
                        } else {
                            // Existing record, update it
                            $loginHistoryUpdateQuery = "UPDATE loginouthistory SET login = CURRENT_TIMESTAMP WHERE username = ? AND logout IS NULL";
                            $loginHistoryUpdateStmt = mysqli_prepare($conn, $loginHistoryUpdateQuery);

                            if ($loginHistoryUpdateStmt) {
                                mysqli_stmt_bind_param($loginHistoryUpdateStmt, "s", $username);
                                mysqli_stmt_execute($loginHistoryUpdateStmt);
                                mysqli_stmt_close($loginHistoryUpdateStmt);
                            }
                        }

                        // Log the login action to the audittrails table
                        $auditTrailQuery = "INSERT INTO audittrails (datetime, username, action) VALUES (CURRENT_TIMESTAMP, ?, 'Login')";
                        $auditTrailStmt = mysqli_prepare($conn, $auditTrailQuery);

                        if ($auditTrailStmt) {
                            mysqli_stmt_bind_param($auditTrailStmt, "s", $username);
                            mysqli_stmt_execute($auditTrailStmt);
                            mysqli_stmt_close($auditTrailStmt);
                        }

                        // Set session variables
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['id'] = $row['id'];

                        // Redirect to dashboard or desired location
                        $_SESSION['login']=true;
                        header("Location: Dashboard.php");
                        
                        exit();
                    }
                }
            }
        }

        // If we reach here, the login failed
        $_SESSION['Incorrect'] = true; // Set successEdit to true
        $_SESSION['IncorrectMessage'] = 'Incorrect Password, please try again!';
        echo "<script>document.location='Login.php';</script>";
    }
}
?>




