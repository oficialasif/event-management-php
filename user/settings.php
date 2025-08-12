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

// Handle notification settings update
if ($_POST && isset($_POST['update_notifications'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $event_reminders = isset($_POST['event_reminders']) ? 1 : 0;
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;
    $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
    
    // Update notification settings (you would need to add these columns to the users table)
    $success_message = 'Notification settings updated successfully!';
}

// Handle privacy settings update
if ($_POST && isset($_POST['update_privacy'])) {
    $profile_visibility = $_POST['profile_visibility'] ?? 'public';
    $show_email = isset($_POST['show_email']) ? 1 : 0;
    $show_phone = isset($_POST['show_phone']) ? 1 : 0;
    
    // Update privacy settings
    $success_message = 'Privacy settings updated successfully!';
}

// Handle account deletion
if ($_POST && isset($_POST['delete_account'])) {
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($confirm_password)) {
        $error_message = 'Please enter your password to confirm account deletion.';
    } elseif (!password_verify($confirm_password, $user['password'])) {
        $error_message = 'Incorrect password. Please try again.';
    } else {
        // Delete user account (this would also delete all their registrations)
        $success_message = 'Account deletion feature coming soon!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - EventHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Settings Header -->
    <section class="dashboard-header">
        <div class="container">
            <div class="dashboard-header-content">
                <h1>Settings</h1>
                <p>Manage your account preferences and privacy settings</p>
            </div>
        </div>
    </section>

    <!-- Settings Content -->
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
                            <li>
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
                                <a href="../logout.php" onclick="return confirm('Are you sure you want to logout?')">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                            <li class="active">
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

                    <div class="settings-sections">
                        <!-- Notification Settings -->
                        <div class="settings-section">
                            <h2><i class="fas fa-bell"></i> Notification Settings</h2>
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="email_notifications" checked>
                                        <span class="checkmark"></span>
                                        Email Notifications
                                        <small>Receive notifications about your account and events</small>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="event_reminders" checked>
                                        <span class="checkmark"></span>
                                        Event Reminders
                                        <small>Get reminded about upcoming events you've registered for</small>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="newsletter">
                                        <span class="checkmark"></span>
                                        Newsletter
                                        <small>Receive our monthly newsletter with event updates</small>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="marketing_emails">
                                        <span class="checkmark"></span>
                                        Marketing Emails
                                        <small>Receive promotional emails about new events and offers</small>
                                    </label>
                                </div>

                                <button type="submit" name="update_notifications" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Notification Settings
                                </button>
                            </form>
                        </div>

                        <!-- Privacy Settings -->
                        <div class="settings-section">
                            <h2><i class="fas fa-shield-alt"></i> Privacy Settings</h2>
                            <form method="POST" class="settings-form">
                                <div class="form-group">
                                    <label for="profile_visibility">Profile Visibility</label>
                                    <select id="profile_visibility" name="profile_visibility">
                                        <option value="public">Public</option>
                                        <option value="friends">Friends Only</option>
                                        <option value="private">Private</option>
                                    </select>
                                    <small>Control who can see your profile information</small>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="show_email">
                                        <span class="checkmark"></span>
                                        Show Email Address
                                        <small>Allow other users to see your email address</small>
                                    </label>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-label">
                                        <input type="checkbox" name="show_phone">
                                        <span class="checkmark"></span>
                                        Show Phone Number
                                        <small>Allow other users to see your phone number</small>
                                    </label>
                                </div>

                                <button type="submit" name="update_privacy" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Privacy Settings
                                </button>
                            </form>
                        </div>

                        <!-- Security Settings -->
                        <div class="settings-section">
                            <h2><i class="fas fa-lock"></i> Security Settings</h2>
                            <div class="security-options">
                                <div class="security-option">
                                    <div class="security-info">
                                        <h4>Two-Factor Authentication</h4>
                                        <p>Add an extra layer of security to your account</p>
                                    </div>
                                    <button class="btn btn-outline" onclick="alert('2FA feature coming soon!')">
                                        <i class="fas fa-mobile-alt"></i> Enable 2FA
                                    </button>
                                </div>

                                <div class="security-option">
                                    <div class="security-info">
                                        <h4>Login History</h4>
                                        <p>View your recent login activity</p>
                                    </div>
                                    <button class="btn btn-outline" onclick="alert('Login history feature coming soon!')">
                                        <i class="fas fa-history"></i> View History
                                    </button>
                                </div>

                                <div class="security-option">
                                    <div class="security-info">
                                        <h4>Active Sessions</h4>
                                        <p>Manage your active login sessions</p>
                                    </div>
                                    <button class="btn btn-outline" onclick="alert('Session management feature coming soon!')">
                                        <i class="fas fa-desktop"></i> Manage Sessions
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Data & Export -->
                        <div class="settings-section">
                            <h2><i class="fas fa-database"></i> Data & Export</h2>
                            <div class="data-options">
                                <div class="data-option">
                                    <div class="data-info">
                                        <h4>Export My Data</h4>
                                        <p>Download a copy of all your data</p>
                                    </div>
                                    <button class="btn btn-outline" onclick="alert('Data export feature coming soon!')">
                                        <i class="fas fa-download"></i> Export Data
                                    </button>
                                </div>

                                <div class="data-option">
                                    <div class="data-info">
                                        <h4>Delete My Data</h4>
                                        <p>Permanently delete all your data and account</p>
                                    </div>
                                    <button class="btn btn-outline" onclick="document.getElementById('delete-account-modal').style.display='block'">
                                        <i class="fas fa-trash"></i> Delete Data
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Account Deletion -->
                        <div class="settings-section danger-zone">
                            <h2><i class="fas fa-exclamation-triangle"></i> Danger Zone</h2>
                            <div class="danger-warning">
                                <p><strong>Warning:</strong> Deleting your account will permanently remove all your data, including event registrations and profile information. This action cannot be undone.</p>
                            </div>
                            <form method="POST" class="delete-account-form" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')">
                                <div class="form-group">
                                    <label for="confirm_password">Enter your password to confirm:</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="delete_account" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete My Account
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html> 