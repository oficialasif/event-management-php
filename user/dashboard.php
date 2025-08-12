<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require login
requireLogin();

// Get user data
$user = getUserById($conn, $_SESSION['user_id']);

// Get user registrations
$registrations = getUserRegistrations($conn, $_SESSION['user_id']);

// Handle registration cancellation
if ($_POST && isset($_POST['cancel_registration'])) {
    $registration_id = intval($_POST['registration_id']);
    if (cancelRegistration($conn, $registration_id, $_SESSION['user_id'])) {
        $success_message = 'Registration cancelled successfully.';
        // Refresh registrations
        $registrations = getUserRegistrations($conn, $_SESSION['user_id']);
    } else {
        $error_message = 'Unable to cancel registration.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventHub</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Dashboard Header -->
    <section class="dashboard-header">
        <div class="container">
            <div class="dashboard-header-content">
                <h1>Welcome back, <?= htmlspecialchars($user['name']) ?>!</h1>
                <p>Manage your events and profile</p>
            </div>
        </div>
    </section>

    <!-- Dashboard Content -->
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
                        <a href="profile.php" class="btn btn-outline">Edit Profile</a>
                    </div>

                    <nav class="dashboard-nav">
                        <ul>
                            <li class="active">
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
                                <a href="settings.php">
                                    <i class="fas fa-cog"></i> Settings
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
                <div class="dashboard-main">
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

                    <!-- Quick Stats -->
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= count($registrations) ?></h3>
                                <p>Registered Events</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= count(array_filter($registrations, fn($r) => strtotime($r['event_date']) >= time())) ?></h3>
                                <p>Upcoming Events</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= count(array_filter($registrations, fn($r) => strtotime($r['event_date']) < time())) ?></h3>
                                <p>Past Events</p>
                            </div>
                        </div>
                    </div>

                    <!-- My Events -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>My Events</h2>
                            <a href="../events.php" class="btn btn-outline">Browse More Events</a>
                        </div>

                        <?php if (empty($registrations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h3>No events registered yet</h3>
                                <p>Start exploring events and register for ones that interest you!</p>
                                <a href="../events.php" class="btn btn-primary">Browse Events</a>
                            </div>
                        <?php else: ?>
                            <div class="events-list">
                                <?php foreach ($registrations as $registration): ?>
                                    <div class="event-item">
                                        <div class="event-image">
                                            <img src="../<?= htmlspecialchars($registration['banner_image']) ?>" 
                                                 alt="<?= htmlspecialchars($registration['title']) ?>">
                                        </div>
                                        <div class="event-details">
                                            <h3><?= htmlspecialchars($registration['title']) ?></h3>
                                            <div class="event-meta">
                                                <span><i class="fas fa-calendar"></i> <?= formatDate($registration['event_date']) ?></span>
                                                <span><i class="fas fa-clock"></i> <?= $registration['event_time'] ?></span>
                                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($registration['location']) ?></span>
                                                <span><i class="fas fa-tag"></i> <?= htmlspecialchars($registration['category_name']) ?></span>
                                            </div>
                                            <div class="event-status">
                                                <?php if (strtotime($registration['event_date']) >= time()): ?>
                                                    <span class="status upcoming">Upcoming</span>
                                                <?php else: ?>
                                                    <span class="status past">Past Event</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="event-actions">
                                            <a href="../event-details.php?id=<?= $registration['event_id'] ?>" 
                                               class="btn btn-outline">View Details</a>
                                            <a href="ticket.php?id=<?= $registration['id'] ?>" 
                                               class="btn btn-primary">Download Ticket</a>
                                            <?php if (strtotime($registration['event_date']) > time()): ?>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                                    <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">
                                                    <button type="submit" name="cancel_registration" class="btn btn-secondary">
                                                        Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Activity -->
                    <div class="dashboard-section">
                        <h2>Recent Activity</h2>
                        <div class="activity-list">
                            <?php
                            $recent_activity = array_slice($registrations, 0, 5);
                            foreach ($recent_activity as $activity):
                            ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p>Registered for <strong><?= htmlspecialchars($activity['title']) ?></strong></p>
                                        <span class="activity-time"><?= formatDate($activity['registration_date']) ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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