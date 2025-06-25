<?php
session_start();

// Database connection
require_once __DIR__ . '/config/database.php';

// Initialize variables
$error = '';
$username = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']);

    // Validate inputs
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Check credentials against database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Set cookie if "remember me" is checked (30 days expiration)
            if ($remember) {
                $cookie_value = base64_encode($user['user_id'] . ':' . hash('sha256', $user['password']));
                setcookie('remember_token', $cookie_value, time() + (86400 * 30), "/");
            }
            
            // Redirect to dashboard
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    }
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | InduStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), 
                        url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .auth-container {
            max-width: 450px;
            width: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .brand-header {
            background-color: var(--primary);
            color: white;
            padding: 1.5rem;
            text-align: center;
            border-bottom: 3px solid var(--secondary);
        }
        
        .brand-header img {
            height: 40px;
            margin-bottom: 0.5rem;
        }
        
        .brand-header h1 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
            letter-spacing: 1px;
        }
        
        .brand-header p {
            margin: 0;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .auth-body {
            padding: 2rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            margin-bottom: 1.25rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(44, 62, 80, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary);
            border: none;
            padding: 0.75rem;
            font-weight: 600;
            width: 100%;
            margin-top: 0.5rem;
        }
        
        .btn-primary:hover {
            background-color: #1a252f;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
        }
        
        .is-invalid {
            border-color: #dc3545 !important;
        }
        
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 576px) {
            .auth-container {
                margin: 0 1rem;
            }
            
            .auth-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="brand-header">
            <img src="https://via.placeholder.com/150x40?text=InduStock" alt="InduStock Logo">
            <h1>INDUSTOCK</h1>
            <p>Industrial Inventory Management</p>
        </div>
        
        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control <?= $error ? 'is-invalid' : '' ?>" 
                               id="username" name="username" value="<?= htmlspecialchars($username) ?>" 
                               placeholder="Enter your username or email" required>
                    </div>
                    <?php if ($error): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control <?= $error ? 'is-invalid' : '' ?>" 
                               id="password" name="password" placeholder="Enter your password" required>
                    </div>
                    <?php if ($error): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                    <a href="forgot-password.php" class="float-end">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt me-2"></i> Login
                </button>
                
                <div class="text-center mt-3">
                    <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add client-side validation similar to the original login.html
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            let isValid = true;

            // Reset validation
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Validate inputs
            if (!username) {
                document.getElementById('username').classList.add('is-invalid');
                document.querySelector('#username + .invalid-feedback').textContent = 'Please enter your username or email';
                isValid = false;
            }
            
            if (!password) {
                document.getElementById('password').classList.add('is-invalid');
                document.querySelector('#password + .invalid-feedback').textContent = 'Please enter your password';
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
            } else {
                // Show loading state
                const btn = this.querySelector('button[type="submit"]');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Authenticating...';
                btn.disabled = true;
            }
        });
    </script>
</body>
</html>