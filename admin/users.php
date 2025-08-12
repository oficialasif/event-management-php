<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['user_id'])) {
                    $user_id = (int)$_POST['user_id'];
                    // Don't allow admin to delete themselves
                    if ($user_id != $_SESSION['user_id']) {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $success_message = "User deleted successfully!";
                    } else {
                        $error_message = "You cannot delete your own account!";
                    }
                }
                break;
            case 'toggle_role':
                if (isset($_POST['user_id'])) {
                    $user_id = (int)$_POST['user_id'];
                    // Don't allow admin to change their own role
                    if ($user_id != $_SESSION['user_id']) {
                        $stmt = $conn->prepare("UPDATE users SET role = CASE WHEN role = 'admin' THEN 'user' ELSE 'admin' END WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $success_message = "User role updated successfully!";
                    } else {
                        $error_message = "You cannot change your own role!";
                    }
                }
                break;
        }
    }
}

// Get all users with registration count
$stmt = $conn->prepare("
    SELECT u.*, 
           COALESCE(r.registration_count, 0) as registration_count,
           COALESCE(r.last_registration, 'Never') as last_registration
    FROM users u 
    LEFT JOIN (
        SELECT user_id, 
               COUNT(*) as registration_count,
               MAX(registration_date) as last_registration
        FROM registrations 
        GROUP BY user_id
    ) r ON u.id = r.user_id
    ORDER BY u.created_at DESC
");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Admin Dashboard Header -->
    <section class="admin-header">
        <div class="container">
            <div class="admin-header-content">
                <h1>Manage Users</h1>
                <p>View and manage all user accounts</p>
            </div>
        </div>
    </section>

    <!-- Admin Dashboard Content -->
    <section class="admin-content">
        <div class="container">
            <div class="admin-grid">
                <!-- Sidebar -->
                <div class="admin-sidebar">
                    <nav class="admin-nav">
                        <ul>
                            <li>
                                <a href="dashboard.php">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a href="events.php">
                                    <i class="fas fa-calendar"></i> Events
                                </a>
                            </li>
                            <li class="active">
                                <a href="users.php">
                                    <i class="fas fa-users"></i> Users
                                </a>
                            </li>
                            <li>
                                <a href="registrations.php">
                                    <i class="fas fa-ticket-alt"></i> Registrations
                                </a>
                            </li>
                            <li>
                                <a href="categories.php">
                                    <i class="fas fa-tags"></i> Categories
                                </a>
                            </li>
                            <li>
                                <a href="../logout.php" onclick="return confirm('Are you sure you want to logout?')">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="admin-main">
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Users Management Section -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Users</h2>
                            <span class="text-muted"><?= count($users) ?> total users</span>
                        </div>

                        <div class="events-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Registrations</th>
                                        <th>Member Since</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?php if (!empty($user['profile_photo'])): ?>
                                                            <img src="<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile Photo">
                                                        <?php else: ?>
                                                            <i class="fas fa-user"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div>
                                                        <h4><?= htmlspecialchars($user['name']) ?></h4>
                                                        <p><?= htmlspecialchars($user['phone'] ?? 'No phone') ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <?php if ($user['role'] === 'admin'): ?>
                                                    <span class="status active">Admin</span>
                                                <?php else: ?>
                                                    <span class="status">User</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong><?= $user['registration_count'] ?></strong> events<br>
                                                    <span>Last: <?= $user['last_registration'] ?></span>
                                                </div>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <div class="event-actions">
                                                    <a href="user-details.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="toggle_role">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-secondary">
                                                                <i class="fas fa-user-shield"></i> <?= $user['role'] === 'admin' ? 'Remove Admin' : 'Make Admin' ?>
                                                            </button>
                                                        </form>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Current User</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
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