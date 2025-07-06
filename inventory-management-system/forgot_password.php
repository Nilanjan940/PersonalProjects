<?php
require_once "config.php";
require_once "auth_functions.php";


$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'])) {
        // Step 1: Request password reset
        $email = sanitize_input($_POST['email']);
        
        $query = "SELECT username FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $query);
        if (!$stmt) {
            $error = "Database error. Please try again later.";
        } else {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                $token = createPasswordResetToken($row['username']);
                if ($token) {
                    $reset_link = APP_URL . "/reset_password.php?token=" . $token;
                    $subject = "Password Reset Request";
                    $body = "Click the following link to reset your password: <a href='$reset_link'>$reset_link</a> (expires in 1 hour)";
                    
                    if (sendEmail($email, $subject, $body)) {
                        $message = "Password reset link has been sent to your email.";
                    } else {
                        $error = "Failed to send reset email. Please try again.";
                    }
                } else {
                    $error = "Error generating reset token. Please try again.";
                }
            } else {
                $error = "No account found with that email.";
            }
        }
    } elseif (isset($_POST['new_password'])) {
        // Step 2: Reset password
        $token = $_POST['token'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $username = verifyPasswordResetToken($token);
            if ($username && updateUserPassword($username, $new_password)) {
                clearPasswordResetToken($token);
                $message = "Password has been reset successfully. You can now <a href='Login.php'>login</a>.";
            } else {
                $error = "Invalid or expired token.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="testin3.css">
</head>
<body>
    <div class="login-container">
        <?php if (!isset($_GET['token'])): ?>
            <!-- Step 1: Request password reset -->
            <form class="loginform" method="post">
                <p class="loginform-title">Forgot Password</p>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <div class="input-container">
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
                <button type="submit" class="submit">Reset Password</button>
                <div class="back-to-login">
                    <a href="Login.php">Back to Login</a>
                </div>
            </form>
        <?php else: ?>
            <!-- Step 2: Reset password -->
            <form class="loginform" method="post">
                <p class="loginform-title">Reset Password</p>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="input-container">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <div class="password-strength">
                        <div class="password-strength-bar"></div>
                    </div>
                </div>
                <div class="input-container">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
    <script>
        // Password strength indicator
        document.querySelector('input[name="new_password"]')?.addEventListener('input', function() {
            const strengthBar = document.querySelector('.password-strength-bar');
            const strength = calculatePasswordStrength(this.value);
            strengthBar.style.width = strength + '%';
            strengthBar.style.backgroundColor = getStrengthColor(strength);
        });

        function calculatePasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength += 25;
            if (/[A-Z]/.test(password)) strength += 25;
            if (/[0-9]/.test(password)) strength += 25;
            if (/[^A-Za-z0-9]/.test(password)) strength += 25;
            return Math.min(strength, 100);
        }

        function getStrengthColor(strength) {
            if (strength < 40) return '#f44336';
            if (strength < 70) return '#ff9800';
            return '#4CAF50';
        }
    </script>
</body>
</html>