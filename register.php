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

// Handle registration form submission
if ($_POST && isset($_POST['register'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitizeInput($_POST['phone']);
    
    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (!validatePassword($password)) {
        $error_message = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error_message = 'An account with this email already exists.';
        } else {
            // Register user
            $user_data = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'phone' => $phone
            ];
            
            if (registerUser($conn, $user_data)) {
                // Auto-login the user after successful registration
                if (loginUser($conn, $email, $password)) {
                    $success_message = 'Registration successful! Redirecting to dashboard...';
                    header('refresh:2;url=user/dashboard.php');
                } else {
                    $success_message = 'Registration successful! You can now log in.';
                }
                // Clear form data
                $_POST = [];
            } else {
                $error_message = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - EventHub</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Registration Section -->
    <section class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-card">
                    <div class="auth-header">
                        <h1>Create Account</h1>
                        <p>Join EventHub and start discovering amazing events</p>
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

                    <form method="POST" class="auth-form" id="register-form">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <div class="input-group">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" required 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                                       placeholder="Enter your full name">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <div class="input-group">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" required 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                       placeholder="Enter your email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-group">
                                <i class="fas fa-phone"></i>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                       placeholder="Enter your phone number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password *</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="password" name="password" required 
                                       placeholder="Create a password (min 6 characters)">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="password-strength"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <div class="input-group">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password" required 
                                       placeholder="Confirm your password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="terms" id="terms" required>
                                <span class="checkmark"></span>
                                I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and 
                                <a href="privacy.php" target="_blank">Privacy Policy</a>
                            </label>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="newsletter" id="newsletter">
                                <span class="checkmark"></span>
                                Subscribe to our newsletter for event updates
                            </label>
                        </div>

                        <button type="submit" name="register" class="btn btn-primary btn-large">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Already have an account? <a href="login.php">Sign in here</a></p>
                    </div>
                </div>

                <div class="auth-image">
                    <img src="https://images.unsplash.com/photo-1509822929063-6b6cfc9b42f2?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Register">
                    <div class="auth-image-content">
                        <h2>Why Join EventHub?</h2>
                        <ul class="benefits-list">
                            <li><i class="fas fa-check"></i> Discover amazing events in your area</li>
                            <li><i class="fas fa-check"></i> Easy registration and ticket management</li>
                            <li><i class="fas fa-check"></i> Connect with like-minded people</li>
                            <li><i class="fas fa-check"></i> Get personalized event recommendations</li>
                        </ul>
                        <a href="login.php" class="btn btn-outline">Already have an account?</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const toggleBtn = passwordInput.parentNode.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                toggleBtn.className = 'fas fa-eye';
            }
        }

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            let strength = 0;
            let message = '';
            let color = '';

            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;

            switch (strength) {
                case 0:
                case 1:
                    message = 'Very Weak';
                    color = '#ef4444';
                    break;
                case 2:
                    message = 'Weak';
                    color = '#f59e0b';
                    break;
                case 3:
                    message = 'Fair';
                    color = '#f59e0b';
                    break;
                case 4:
                    message = 'Good';
                    color = '#10b981';
                    break;
                case 5:
                    message = 'Strong';
                    color = '#10b981';
                    break;
            }

            strengthDiv.innerHTML = `<span style="color: ${color};">${message}</span>`;
        });

        // Confirm password validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html> 