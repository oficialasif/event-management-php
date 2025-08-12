<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured events
$featured_events = getFeaturedEvents($conn, 6);

// Get upcoming events
$upcoming_events = getUpcomingEvents($conn, 8);

// Get event categories
$categories = getEventCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="copyright" content="© 2024 EventHub. All rights reserved by OficialAsif.">
    <title>EventHub - Modern Event Management Platform</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-left">
                <h1 class="hero-title">
                    Effortlessly <span class="hero-highlight">plan your events</span>
                </h1>
                <p class="hero-description">
                    Make event planning easy with our comprehensive platform. From start to finish, we streamline the process and offer dedicated support to make your event a success.
                </p>
                <div class="hero-buttons">
                    <a href="events.php" class="hero-btn hero-btn-primary">Plan an Event</a>
                    <a href="contact.php" class="hero-btn hero-btn-secondary">Find an Expert</a>
                </div>
            </div>
            <div class="hero-right">
                <div class="hero-gallery">
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1468359601543-843bfaef291a?q=80&w=1174&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Concert Event">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1651313948618-31644c7fec18?q=80&w=1074&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Formal Event">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1469371670807-013ccf25f16a?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Wedding Event">
                    </div>
                    <div class="gallery-item">
                        <img src="https://images.unsplash.com/photo-1653821355736-0c2598d0a63e?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Party Event">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Events Section -->
    <section class="featured-events">
        <div class="container">
            <div class="section-header">
                <h2>Featured Events</h2>
                <a href="events.php" class="btn btn-outline">View All Events</a>
            </div>
            <div class="events-grid">
                <?php foreach ($featured_events as $event): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="<?= htmlspecialchars($event['banner_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                        </div>
                        <div class="event-content">
                            <h3><?= htmlspecialchars($event['title']) ?></h3>
                            <div class="event-meta">
                                <span><i class="fas fa-calendar"></i> <?= formatDate($event['event_date']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= $event['event_time'] ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                            </div>
                            <p><?= substr(htmlspecialchars($event['description']), 0, 100) ?>...</p>
                            <div class="event-footer">
                                <span class="price">$<?= number_format($event['price'], 2) ?></span>
                                <a href="event-details.php?id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <div class="section-header">
                <h2>Event Categories</h2>
                <p>Explore events by category</p>
            </div>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="<?= getCategoryIcon($category['name']) ?>"></i>
                        </div>
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= htmlspecialchars($category['description']) ?></p>
                        <a href="events.php?category=<?= $category['id'] ?>" class="btn btn-outline">Browse Events</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Upcoming Events Section -->
    <section class="upcoming-events">
        <div class="container">
            <div class="section-header">
                <h2>Upcoming Events</h2>
                <a href="events.php" class="btn btn-outline">View All</a>
            </div>
            <div class="events-grid">
                <?php foreach ($upcoming_events as $event): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="<?= htmlspecialchars($event['banner_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                        </div>
                        <div class="event-content">
                            <h3><?= htmlspecialchars($event['title']) ?></h3>
                            <div class="event-meta">
                                <span><i class="fas fa-calendar"></i> <?= formatDate($event['event_date']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= $event['event_time'] ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                            </div>
                            <p><?= substr(htmlspecialchars($event['description']), 0, 80) ?>...</p>
                            <div class="event-footer">
                                <span class="seats-left"><?= $event['available_seats'] ?> seats left</span>
                                <a href="event-details.php?id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalEvents($conn) ?></div>
                    <div class="stat-label">Total Events</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalUsers($conn) ?></div>
                    <div class="stat-label">Registered Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalRegistrations($conn) ?></div>
                    <div class="stat-label">Event Registrations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalCategories($conn) ?></div>
                    <div class="stat-label">Event Categories</div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 