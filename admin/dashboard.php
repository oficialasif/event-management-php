<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

// Get statistics
$total_events = getTotalEvents($conn);
$total_users = getTotalUsers($conn);
$total_registrations = getTotalRegistrations($conn);
$total_categories = getTotalCategories($conn);

// Get recent events
$stmt = $conn->prepare("
    SELECT e.*, c.name as category_name,
           (e.total_seats - COALESCE(r.registered_count, 0)) as available_seats
    FROM events e 
    JOIN categories c ON e.category_id = c.id 
    LEFT JOIN (
        SELECT event_id, COUNT(*) as registered_count 
        FROM registrations 
        GROUP BY event_id
    ) r ON e.id = r.event_id
    ORDER BY e.created_at DESC 
    LIMIT 5
");
$stmt->execute();
$recent_events = $stmt->fetchAll();

// Get recent registrations
$stmt = $conn->prepare("
    SELECT r.*, u.name as user_name, e.title as event_title
    FROM registrations r
    JOIN users u ON r.user_id = u.id
    JOIN events e ON r.event_id = e.id
    ORDER BY r.registration_date DESC
    LIMIT 10
");
$stmt->execute();
$recent_registrations = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EventHub</title>
    <link rel="icon" type="image/x-icon" href="../favicon.ico">
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
                <h1>Admin Dashboard</h1>
                <p>Manage events, users, and system settings</p>
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
                            <li class="active">
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
                            <li>
                                <a href="registrations.php">
                                    <i class="fas fa-ticket-alt"></i> Registrations
                                </a>
                            </li>
                            <li>
                                <a href="payments.php">
                                    <i class="fas fa-credit-card"></i> Payments
                                </a>
                            </li>
                            <li>
                                <a href="categories.php">
                                    <i class="fas fa-tags"></i> Categories
                                </a>
                            </li>
                            <li>
                                <a href="reports.php">
                                    <i class="fas fa-chart-bar"></i> Reports
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
                <div class="admin-main">
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="events.php?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Event
                        </a>
                        <a href="users.php" class="btn btn-outline">
                            <i class="fas fa-users"></i> Manage Users
                        </a>
                        <a href="reports.php" class="btn btn-outline">
                            <i class="fas fa-chart-bar"></i> View Reports
                        </a>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $total_events ?></h3>
                                <p>Total Events</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $total_users ?></h3>
                                <p>Registered Users</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $total_registrations ?></h3>
                                <p>Total Registrations</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-tags"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?= $total_categories ?></h3>
                                <p>Event Categories</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Events -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>Recent Events</h2>
                            <a href="events.php" class="btn btn-outline">View All Events</a>
                        </div>
                        <div class="events-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Seats</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_events as $event): ?>
                                        <tr>
                                            <td>
                                                <div class="event-info">
                                                    <img src="../<?= htmlspecialchars($event['banner_image']) ?>" 
                                                         alt="<?= htmlspecialchars($event['title']) ?>">
                                                    <div>
                                                        <h4><?= htmlspecialchars($event['title']) ?></h4>
                                                        <p><?= htmlspecialchars($event['location']) ?></p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($event['category_name']) ?></td>
                                            <td><?= formatDate($event['event_date']) ?></td>
                                            <td><?= $event['available_seats'] ?>/<?= $event['total_seats'] ?></td>
                                            <td>
                                                <?php if ($event['status'] === 'active'): ?>
                                                    <span class="status active">Active</span>
                                                <?php elseif ($event['status'] === 'cancelled'): ?>
                                                    <span class="status cancelled">Cancelled</span>
                                                <?php else: ?>
                                                    <span class="status completed">Completed</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="../event-details.php?id=<?= $event['id'] ?>" 
                                                       class="btn btn-sm btn-outline">View</a>
                                                    <a href="events.php?action=edit&id=<?= $event['id'] ?>" 
                                                       class="btn btn-sm btn-primary">Edit</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Registrations -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>Recent Registrations</h2>
                            <a href="registrations.php" class="btn btn-outline">View All</a>
                        </div>
                        <div class="registrations-list">
                            <?php foreach ($recent_registrations as $registration): ?>
                                <div class="registration-item">
                                    <div class="registration-info">
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <h4><?= htmlspecialchars($registration['user_name']) ?></h4>
                                                <p>Ticket: <?= htmlspecialchars($registration['ticket_code']) ?></p>
                                            </div>
                                        </div>
                                        <div class="event-info">
                                            <h4><?= htmlspecialchars($registration['event_title']) ?></h4>
                                            <p><?= formatDate($registration['registration_date']) ?></p>
                                        </div>
                                    </div>
                                    <div class="registration-actions">
                                        <a href="registrations.php?event_id=<?= $registration['event_id'] ?>" 
                                           class="btn btn-sm btn-outline">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Quick Stats Chart -->
                    <div class="admin-section">
                        <h2>Registration Trends</h2>
                        <div class="chart-container">
                            <canvas id="registrationChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Registration trends chart
        const ctx = document.getElementById('registrationChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Registrations',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(99, 102, 241)',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Monthly Registration Trends'
                    }
                }
            }
        });
    </script>
</body>
</html> 