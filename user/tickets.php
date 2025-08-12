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

$error_message = '';
$success_message = '';

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

// Handle ticket download
if ($_POST && isset($_POST['download_ticket'])) {
    $registration_id = intval($_POST['registration_id']);
    // This would generate a PDF ticket in a real application
    $success_message = 'Ticket download feature coming soon!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tickets - EventHub</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Tickets Header -->
    <section class="dashboard-header">
        <div class="container">
            <div class="dashboard-header-content">
                <h1>My Tickets</h1>
                <p>Manage your event registrations and tickets</p>
            </div>
        </div>
    </section>

    <!-- Tickets Content -->
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
                            <li class="active">
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

                    <!-- Tickets Summary -->
                    <div class="tickets-summary">
                        <div class="summary-card">
                            <div class="summary-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="summary-content">
                                <h3><?= count($registrations) ?></h3>
                                <p>Total Tickets</p>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="summary-content">
                                <h3><?= count(array_filter($registrations, function($r) { return strtotime($r['event_date']) >= time(); })) ?></h3>
                                <p>Upcoming Events</p>
                            </div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-icon">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            <div class="summary-content">
                                <h3><?= count(array_filter($registrations, function($r) { return strtotime($r['event_date']) < time(); })) ?></h3>
                                <p>Past Events</p>
                            </div>
                        </div>
                    </div>

                    <!-- Tickets List -->
                    <div class="tickets-section">
                        <h2><i class="fas fa-ticket-alt"></i> My Event Tickets</h2>
                        
                        <?php if (empty($registrations)): ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <h3>No Tickets Yet</h3>
                                <p>You haven't registered for any events yet. Start exploring events to get your first ticket!</p>
                                <a href="../events.php" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Browse Events
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="tickets-grid">
                                <?php foreach ($registrations as $registration): ?>
                                    <?php 
                                    $is_upcoming = strtotime($registration['event_date']) >= time();
                                    $is_today = date('Y-m-d') === $registration['event_date'];
                                    ?>
                                    <div class="ticket-card <?= $is_upcoming ? 'upcoming' : 'past' ?>">
                                        <div class="ticket-header">
                                            <div class="ticket-status">
                                                <?php if ($is_today): ?>
                                                    <span class="status-badge today">Today</span>
                                                <?php elseif ($is_upcoming): ?>
                                                    <span class="status-badge upcoming">Upcoming</span>
                                                <?php else: ?>
                                                    <span class="status-badge past">Past</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ticket-code">
                                                <strong>Ticket:</strong> <?= htmlspecialchars($registration['ticket_code']) ?>
                                            </div>
                                        </div>
                                        
                                        <div class="ticket-image">
                                            <img src="<?= htmlspecialchars($registration['banner_image']) ?>" 
                                                 alt="<?= htmlspecialchars($registration['title']) ?>"
                                                 onerror="this.src='https://via.placeholder.com/400x200/6B7280/FFFFFF?text=Event+Image'">
                                        </div>
                                        
                                        <div class="ticket-content">
                                            <h3><?= htmlspecialchars($registration['title']) ?></h3>
                                            <div class="ticket-meta">
                                                <span><i class="fas fa-calendar"></i> <?= formatDate($registration['event_date']) ?></span>
                                                <span><i class="fas fa-clock"></i> <?= $registration['event_time'] ?></span>
                                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($registration['location']) ?></span>
                                                <span><i class="fas fa-tag"></i> <?= htmlspecialchars($registration['category_name']) ?></span>
                                            </div>
                                            
                                            <div class="ticket-details">
                                                <p><strong>Registration Date:</strong> <?= formatDate($registration['registration_date']) ?></p>
                                                <p><strong>Status:</strong> <?= ucfirst($registration['status']) ?></p>
                                            </div>
                                        </div>
                                        
                                        <div class="ticket-actions">
                                            <?php if ($is_upcoming): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">
                                                    <button type="submit" name="download_ticket" class="btn btn-primary btn-small">
                                                        <i class="fas fa-download"></i> Download Ticket
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                                    <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">
                                                    <button type="submit" name="cancel_registration" class="btn btn-danger btn-small">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span class="event-completed">Event Completed</span>
                                            <?php endif; ?>
                                            
                                            <a href="../event-details.php?id=<?= $registration['event_id'] ?>" class="btn btn-outline btn-small">
                                                <i class="fas fa-eye"></i> View Event
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
</body>
</html> 