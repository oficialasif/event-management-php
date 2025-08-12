<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

// Handle registration actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['registration_id'])) {
                    $registration_id = (int)$_POST['registration_id'];
                    $stmt = $conn->prepare("DELETE FROM registrations WHERE id = ?");
                    $stmt->execute([$registration_id]);
                    $success_message = "Registration cancelled successfully!";
                }
                break;
        }
    }
}

// Get all registrations with user and event info
$stmt = $conn->prepare("
    SELECT r.*, 
           u.name as user_name, u.email as user_email,
           e.title as event_title, e.event_date, e.event_time, e.location,
           c.name as category_name
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    JOIN categories c ON e.category_id = c.id
    ORDER BY r.registration_date DESC
");
$stmt->execute();
$registrations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Registrations - Admin Dashboard</title>
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
                <h1>Manage Registrations</h1>
                <p>View and manage all event registrations</p>
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
                            <li>
                                <a href="users.php">
                                    <i class="fas fa-users"></i> Users
                                </a>
                            </li>
                            <li class="active">
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

                    <!-- Registrations Management Section -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Registrations</h2>
                            <span class="text-muted"><?= count($registrations) ?> total registrations</span>
                        </div>

                        <div class="registrations-list">
                            <?php foreach ($registrations as $registration): ?>
                                <div class="registration-item">
                                    <div class="registration-info">
                                        <div class="user-info">
                                            <h4><?= htmlspecialchars($registration['user_name']) ?></h4>
                                            <p><?= htmlspecialchars($registration['user_email']) ?></p>
                                        </div>
                                        <div class="event-info">
                                            <h4><?= htmlspecialchars($registration['event_title']) ?></h4>
                                            <p>
                                                <i class="fas fa-calendar"></i> <?= formatDate($registration['event_date']) ?> at <?= $registration['event_time'] ?><br>
                                                <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($registration['location']) ?><br>
                                                <i class="fas fa-tag"></i> <?= htmlspecialchars($registration['category_name']) ?>
                                            </p>
                                        </div>
                                        <div class="registration-details">
                                            <p><strong>Ticket Code:</strong> <?= htmlspecialchars($registration['ticket_code']) ?></p>
                                            <p><strong>Registered:</strong> <?= date('M j, Y g:i A', strtotime($registration['registration_date'])) ?></p>
                                            <?php if (strtotime($registration['event_date']) < time()): ?>
                                                <span class="status past">Past Event</span>
                                            <?php else: ?>
                                                <span class="status upcoming">Upcoming</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="registration-actions">
                                        <a href="../event-details.php?id=<?= $registration['event_id'] ?>" class="btn btn-sm btn-outline" target="_blank">
                                            <i class="fas fa-eye"></i> View Event
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this registration?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="registration_id" value="<?= $registration['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times"></i> Cancel
                                            </button>
                                        </form>
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