<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

// Handle event actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete':
                if (isset($_POST['event_id'])) {
                    $event_id = (int)$_POST['event_id'];
                    $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
                    $stmt->execute([$event_id]);
                    $success_message = "Event deleted successfully!";
                }
                break;
            case 'toggle_featured':
                if (isset($_POST['event_id'])) {
                    $event_id = (int)$_POST['event_id'];
                    $stmt = $conn->prepare("UPDATE events SET is_featured = NOT is_featured WHERE id = ?");
                    $stmt->execute([$event_id]);
                    $success_message = "Event featured status updated!";
                }
                break;
        }
    }
}

// Get all events with category and registration info
$stmt = $conn->prepare("
    SELECT e.*, c.name as category_name,
           (e.total_seats - COALESCE(r.registered_count, 0)) as available_seats,
           COALESCE(r.registered_count, 0) as registered_count
    FROM events e 
    JOIN categories c ON e.category_id = c.id 
    LEFT JOIN (
        SELECT event_id, COUNT(*) as registered_count 
        FROM registrations 
        GROUP BY event_id
    ) r ON e.id = r.event_id
    ORDER BY e.created_at DESC
");
$stmt->execute();
$events = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Dashboard</title>
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
                <h1>Manage Events</h1>
                <p>Create, edit, and manage all events</p>
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
                            <li class="active">
                                <a href="events.php">
                                    <i class="fas fa-calendar"></i> Events
                                </a>
                            </li>
                            <li>
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

                    <!-- Events Management Section -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Events</h2>
                            <a href="add-event.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Event
                            </a>
                        </div>

                        <div class="events-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Category</th>
                                        <th>Date & Time</th>
                                        <th>Location</th>
                                        <th>Seats</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                        <tr>
                                            <td>
                                                <div class="event-info">
                                                    <h4><?= htmlspecialchars($event['title']) ?></h4>
                                                    <p><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($event['category_name']) ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= formatDate($event['event_date']) ?></strong><br>
                                                    <span><?= $event['event_time'] ?></span>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($event['location']) ?></td>
                                            <td>
                                                <div>
                                                    <strong><?= $event['registered_count'] ?>/<?= $event['total_seats'] ?></strong><br>
                                                    <span><?= $event['available_seats'] ?> available</span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($event['is_featured']): ?>
                                                    <span class="status active">Featured</span>
                                                <?php else: ?>
                                                    <span class="status">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="event-actions">
                                                    <a href="../event-details.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-outline" target="_blank">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                    <a href="edit-event.php?id=<?= $event['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="toggle_featured">
                                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-star"></i> <?= $event['is_featured'] ? 'Unfeature' : 'Feature' ?>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this event?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i> Delete
                                                        </button>
                                                    </form>
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