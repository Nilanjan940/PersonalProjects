<?php
require_once 'config/constants.php';
require_once 'config/database.php';
require_once 'functions/auth.php';

$db = new Database();
$conn = $db->getConnection();

$pageTitle = "Register";
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Validate inputs
    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    } elseif (strlen($username) < 4) {
        $errors['username'] = 'Username must be at least 4 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = 'Username can only contain letters, numbers and underscores.';
    } elseif (usernameExists($conn, $username)) {
        $errors['username'] = 'Username is already taken.';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email.';
    } elseif (emailExists($conn, $email)) {
        $errors['email'] = 'Email is already registered.';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters.';
    }
    
    if ($password !== $confirmPassword) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        if (registerUser($conn, $username, $email, $password)) {
            $success = true;
        } else {
            $errors['general'] = 'Registration failed. Please try again.';
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Register</h2>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            Registration successful! You can now <a href="login.php" class="alert-link">login</a>.
                        </div>
                    <?php else: ?>
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['username']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                <?php if (isset($errors['email'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?php echo $errors['confirm_password']; ?></div>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <a href="login.php">Already have an account? Login</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>