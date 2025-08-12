<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require login
requireLogin();

// Get user data
$user = getUserById($conn, $_SESSION['user_id']);

$error_message = '';
$success_message = '';

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $profile_photo = sanitizeInput($_POST['profile_photo']);
    
    // Validate input
    if (empty($name) || empty($email)) {
        $error_message = 'Name and email are required fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Check if email already exists (excluding current user)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $error_message = 'An account with this email already exists.';
        } else {
            // Update user profile
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, profile_photo = ? WHERE id = ?");
            if ($stmt->execute([$name, $email, $phone, $profile_photo, $_SESSION['user_id']])) {
                $success_message = 'Profile updated successfully!';
                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                // Refresh user data
                $user = getUserById($conn, $_SESSION['user_id']);
            } else {
                $error_message = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = 'All password fields are required.';
    } elseif (!password_verify($current_password, $user['password'])) {
        $error_message = 'Current password is incorrect.';
    } elseif (!validatePassword($new_password)) {
        $error_message = 'New password must be at least 6 characters long.';
    } elseif ($new_password !== $confirm_password) {
        $error_message = 'New passwords do not match.';
    } else {
        // Update password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed_password, $_SESSION['user_id']])) {
            $success_message = 'Password changed successfully!';
        } else {
            $error_message = 'Failed to change password. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - EventHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Profile Header -->
    <section class="dashboard-header">
        <div class="container">
            <div class="dashboard-header-content">
                <h1>My Profile</h1>
                <p>Manage your account information and settings</p>
            </div>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="dashboard-content">
        <div class="container">
            <div class="dashboard-grid">
                <!-- Sidebar -->
                <div class="dashboard-sidebar">
                    <div class="user-profile-card">
                        <div class="user-avatar">
                            <?php if (!empty($user['profile_photo'])): ?>
                                <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <h3><?= htmlspecialchars($user['name']) ?></h3>
                        <p><?= htmlspecialchars($user['email']) ?></p>
                        <span class="user-role"><?= ucfirst($user['role']) ?></span>
                    </div>

                    <nav class="dashboard-nav">
                        <ul>
                            <li>
                                <a href="dashboard.php">
                                    <i class="fas fa-home"></i> Dashboard
                                </a>
                            </li>
                            <li class="active">
                                <a href="profile.php">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a href="tickets.php">
                                    <i class="fas fa-ticket-alt"></i> My Tickets
                                </a>
                            </li>
                            <li>
                                <a href="settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="dashboard-main">
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

                    <div class="profile-sections">
                        <!-- Profile Information -->
                        <div class="profile-section">
                            <h2><i class="fas fa-user"></i> Profile Information</h2>
                            <form method="POST" class="profile-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Full Name *</label>
                                        <input type="text" id="name" name="name" required 
                                               value="<?= htmlspecialchars($user['name']) ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email Address *</label>
                                        <input type="email" id="email" name="email" required 
                                               value="<?= htmlspecialchars($user['email']) ?>">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" 
                                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="profile_photo">Profile Photo URL</label>
                                        <input type="url" id="profile_photo" name="profile_photo" 
                                               value="<?= htmlspecialchars($user['profile_photo'] ?? '') ?>"
                                               placeholder="https://example.com/photo.jpg">
                                        <small>Enter a URL for your profile photo</small>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="role">Account Type</label>
                                    <input type="text" id="role" value="<?= ucfirst($user['role']) ?>" readonly>
                                </div>

                                <div class="form-group">
                                    <label for="created_at">Member Since</label>
                                    <input type="text" id="created_at" 
                                           value="<?= date('F j, Y', strtotime($user['created_at'])) ?>" readonly>
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </form>
                        </div>

                        <!-- Change Password -->
                        <div class="profile-section">
                            <h2><i class="fas fa-lock"></i> Change Password</h2>
                            <form method="POST" class="profile-form">
                                <div class="form-group">
                                    <label for="current_password">Current Password *</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="new_password">New Password *</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password *</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>

                                <button type="submit" name="change_password" class="btn btn-secondary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                        </div>

                        <!-- Account Actions -->
                        <div class="profile-section">
                            <h2><i class="fas fa-shield-alt"></i> Account Actions</h2>
                            <div class="account-actions">
                                <a href="../logout.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                                <button class="btn btn-outline" onclick="alert('This feature is coming soon!')">
                                    <i class="fas fa-download"></i> Export My Data
                                </button>
                                <button class="btn btn-outline" onclick="alert('This feature is coming soon!')">
                                    <i class="fas fa-trash"></i> Delete Account
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script>
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        // Profile photo preview
        document.getElementById('profile_photo').addEventListener('input', function() {
            const url = this.value;
            const avatar = document.querySelector('.user-avatar');
            
            if (url) {
                avatar.innerHTML = `<img src="${url}" alt="Profile Photo" onerror="this.style.display='none'; this.parentNode.innerHTML='<i class=\\'fas fa-user\\'></i>';">`;
            } else {
                avatar.innerHTML = '<i class="fas fa-user"></i>';
            }
        });
    </script>
</body>
</html> 