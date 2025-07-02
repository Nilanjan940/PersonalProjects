<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_email = $_POST['user_email'];
    $feedback = $_POST['feedback'];
    $uploadDirectory = 'uploads/';

    if (!file_exists($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }

    // Check if the file was uploaded successfully
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $uploadedFileName = $_FILES['image']['name'];
        $uploadedFileTemp = $_FILES['image']['tmp_name'];

        // Check if the uploaded file is an image
        $imageFileType = strtolower(pathinfo($uploadedFileName, PATHINFO_EXTENSION));
        if (getimagesize($uploadedFileTemp) !== false) {
            // Move the uploaded file to the desired directory
            if (move_uploaded_file($uploadedFileTemp, $uploadDirectory . $uploadedFileName)) {
                $mail = new PHPMailer(true);

                try {
                    // Use SMTP
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'nilanjanpradhan940@gmail.com';  // Replace with your SMTP username
                    $mail->Password = 'bema xkfh prye jtti';  // Replace with your SMTP password
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom($user_email);
                    $mail->addAddress('nilanjanpradhan940@gmail.com'); // Replace with your design team's email
                    $mail->Subject = 'Feedback from User';
                    $mail->Body = "User Email: $user_email\n\nFeedback:\n$feedback";
                    $mail->addAttachment($uploadDirectory . $uploadedFileName);

                    $mail->send();
                    echo "<script>alert('Feedback sent successfully!');</script>";
                    echo "<script>document.location='Contact.php';</script>";
                } catch (Exception $e) {
                    echo "Feedback could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    echo "<script>document.location='Contact.php';</script>";
                }
            } else {
                echo "<script>alert('Error uploading file!');</script>";
                echo "<script>document.location='Contact.php';</script>";
            }
        } else {
            echo "<script>alert('Invalid file format! Please try again!');</script>";
            echo "<script>document.location='Contact.php';</script>";
        }
    } else {
        // Continue with sending the email even if no file is uploaded
        $mail = new PHPMailer(true);

        try {
            // Use SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'nilanjanpradhan940@gmail.com';  // Replace with your SMTP username
            $mail->Password = 'bema xkfh prye jtti';  // Replace with your SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom($user_email);
            $mail->addAddress('nilanjanpradhan940@gmail.com'); // Replace with your design team's email
            $mail->Subject = 'Feedback from User';
            $mail->Body = "User Email: $user_email\n\nFeedback:\n$feedback";

            $mail->send();
            echo "<script>alert('Feedback sent successfully!');</script>";
            echo "<script>document.location='Contact.php';</script>";
        } catch (Exception $e) {
            echo "Feedback could not be sent. Mailer Error: {$mail->ErrorInfo}";
            echo "<script>document.location='Contact.php';</script>";
        }
    }
}
?>

