<?php
session_start();
include "config.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the user is logged in, you may want to add additional checks if needed
    if (!isset($_SESSION['id'])) {
        header("Location: login.php");
        exit();
    }

    $companyName = $_POST['company_name'];
    $eid = 1; // Assuming the companyprofile table has only one row with id 1

    // Handle image upload
    $uploadDirectory = 'picture/';
    if (!file_exists($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadedFileName = $_FILES['image']['name'];
        $uploadedFileTemp = $_FILES['image']['tmp_name'];

        // Check if the uploaded file is an image
        $imageFileType = strtolower(pathinfo($uploadedFileName, PATHINFO_EXTENSION));
        if (getimagesize($uploadedFileTemp) !== false) {
            // Move the uploaded file to the desired directory
            $newImagePath = $uploadDirectory . $uploadedFileName;
            if (move_uploaded_file($uploadedFileTemp, $newImagePath)) {
                // Get the old company profile details before the update
                $oldCompanyProfileQuery = "SELECT * FROM companyprofile WHERE id = ?";
                $stmtOldCompanyProfile = mysqli_prepare($conn, $oldCompanyProfileQuery);
                mysqli_stmt_bind_param($stmtOldCompanyProfile, "i", $eid);

                if (mysqli_stmt_execute($stmtOldCompanyProfile)) {
                    $resultOldCompanyProfile = mysqli_stmt_get_result($stmtOldCompanyProfile);
                    $oldCompanyProfileDetails = mysqli_fetch_assoc($resultOldCompanyProfile);
                    mysqli_stmt_close($stmtOldCompanyProfile);

                    // Update the company profile in the database
                    $updateQuery = "UPDATE companyprofile SET name = ?, profilepicture = ? WHERE id = ?";
                    $stmtUpdateCompanyProfile = mysqli_prepare($conn, $updateQuery);
                    mysqli_stmt_bind_param($stmtUpdateCompanyProfile, "ssi", $companyName, $newImagePath, $eid);

                    if (mysqli_stmt_execute($stmtUpdateCompanyProfile)) {
                        mysqli_stmt_close($stmtUpdateCompanyProfile); // Close the statement
                        echo "<script>alert('Company profile updated successfully!');</script>";
                    } else {
                        echo "Error updating company profile: " . mysqli_error($conn);
                    }
                } else {
                    echo "Error retrieving old company profile details: " . mysqli_error($conn);
                }
            } else {
                echo "<script>alert('Error uploading image!');</script>";
            }
        } else {
            echo "<script>alert('Invalid file format! Please upload an image.');</script>";
        }
    } else {
        // Update the company name only if no image is uploaded
        $updateQuery = "UPDATE companyprofile SET name = ? WHERE id = ?";
        $stmtUpdateCompanyName = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmtUpdateCompanyName, "si", $companyName, $eid);

        if (mysqli_stmt_execute($stmtUpdateCompanyName)) {
            mysqli_stmt_close($stmtUpdateCompanyName); // Close the statement
            echo "<script>alert('Company profile updated successfully!');</script>";
        } else {
            echo "Error updating company profile: " . mysqli_error($conn);
        }
    }
} else {
    echo "<script>alert('Invalid request');</script>";
}

// Redirect back to the company profile page
echo "<script>document.location='company_profile.php';</script>";
?>



