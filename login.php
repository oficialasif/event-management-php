<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error_message = '';
$success_message = '';

// Handle login form submission
if ($_POST && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Attempt login
        if (loginUser($conn, $email, $password)) {
            $success_message = 'Login successful! Redirecting...';
            // Redirect based on user role
            if (isAdmin()) {
                header('refresh:2;url=admin/dashboard.php');
            } else {
                header('refresh:2;url=user/dashboard.php');
            }
        } else {
            $error_message = 'Invalid email or password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EventHub</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Login Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Welcome Back</h1>
                        <p>Sign in to your account to continue</p>
                    </div>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form" id="login-form">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="Enter your email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" required 
                                       placeholder="Enter your password">
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="remember" id="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                        </div>

                        <button type="submit" name="login" class="btn btn-primary btn-large">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                        <p><a href="forgot-password.php">Forgot your password?</a></p>
                    </div>


                </div>

                <div class="auth-image">
                    <img src="https://images.unsplash.com/photo-1509822929063-6b6cfc9b42f2?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Login">
                    <div class="auth-image-content">
                        <h2>Join EventHub</h2>
                        <p>Discover amazing events and connect with people who share your interests.</p>
                        <a href="register.php" class="btn btn-outline">Create Account</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleBtn.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html> 