<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get event ID from URL
$event_id = intval($_GET['id'] ?? 0);

if (!$event_id) {
    header('Location: events.php');
    exit();
}

// Get event details
$event = getEventById($conn, $event_id);

if (!$event) {
    header('Location: events.php');
    exit();
}

// Check if user is already registered
$is_registered = false;
if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$_SESSION['user_id'], $event_id]);
    $is_registered = $stmt->fetch() ? true : false;
}

// Registration message (for display purposes only)
$registration_message = '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?> - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Event Details Section -->
    <section class="event-details">
        <div class="container">
            <div class="event-details-grid">
                <!-- Event Image and Info -->
                <div class="event-main">
                    <div class="event-image-large">
                        <img src="<?= htmlspecialchars($event['banner_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                    </div>

                    <div class="event-info">
                        <h1><?= htmlspecialchars($event['title']) ?></h1>
                        
                        <div class="event-meta-detailed">
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <strong>Date</strong>
                                    <span><?= formatDate($event['event_date']) ?></span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <strong>Time</strong>
                                    <span><?= $event['event_time'] ?></span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong>Location</strong>
                                    <span><?= htmlspecialchars($event['location']) ?></span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <strong>Available Seats</strong>
                                    <span><?= $event['available_seats'] ?> seats left</span>
                                </div>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <strong>Price</strong>
                                    <span class="price">$<?= number_format($event['price'], 2) ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="event-description">
                            <h3>About This Event</h3>
                            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                        </div>

                        <!-- Countdown Timer -->
                        <div class="countdown-section">
                            <h3>Event Starts In</h3>
                            <div class="countdown-timer" data-target-date="<?= $event['event_date'] ?> <?= $event['event_time'] ?>">
                                <!-- Countdown will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Sidebar -->
                <div class="event-sidebar">
                    <div class="registration-card">
                        <h3>Register for Event</h3>
                        
                        <?php if ($registration_message): ?>
                            <div class="alert alert-<?= strpos($registration_message, 'success') === 0 ? 'success' : 'error' ?>">
                                <?= substr($registration_message, strpos($registration_message, ':') + 1) ?>
                            </div>
                        <?php endif; ?>

                        <div class="registration-info">
                            <div class="info-item">
                                <span class="label">Price:</span>
                                <span class="value price">$<?= number_format($event['price'], 2) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Available Seats:</span>
                                <span class="value"><?= $event['available_seats'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Event Date:</span>
                                <span class="value"><?= formatDate($event['event_date']) ?></span>
                            </div>
                        </div>

                        <?php if (isLoggedIn()): ?>
                            <?php if ($is_registered): ?>
                                <div class="registered-status">
                                    <i class="fas fa-check-circle"></i>
                                    <span>You are registered for this event</span>
                                </div>
                                <a href="user/dashboard.php" class="btn btn-primary">View My Events</a>
                            <?php else: ?>
                                <?php if ($event['available_seats'] > 0): ?>
                                    <a href="register-event.php?id=<?= $event_id ?>" class="btn btn-primary btn-large">
                                        <i class="fas fa-ticket-alt"></i> Register Now
                                    </a>
                                <?php else: ?>
                                    <div class="sold-out">
                                        <i class="fas fa-times-circle"></i>
                                        <span>Event is sold out</span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="login-required">
                                <p>Please log in to register for this event</p>
                                <a href="login.php" class="btn btn-primary">Login</a>
                                <a href="register.php" class="btn btn-outline">Register</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Share Event -->
                    <div class="share-card">
                        <h3>Share This Event</h3>
                        <div class="share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="share-btn facebook">
                                <i class="fab fa-facebook"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>&text=<?= urlencode($event['title']) ?>" 
                               target="_blank" class="share-btn twitter">
                                <i class="fab fa-twitter"></i> Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($_SERVER['REQUEST_URI']) ?>" 
                               target="_blank" class="share-btn linkedin">
                                <i class="fab fa-linkedin"></i> LinkedIn
                            </a>
                            <button onclick="copyEventLink()" class="share-btn copy">
                                <i class="fas fa-link"></i> Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section class="location-section">
        <div class="container">
            <h2>Event Location</h2>
            <div class="location-content">
                <div class="map-container">
                    <div id="map" style="height: 400px; width: 100%; border-radius: var(--radius-lg);"></div>
                </div>
                <div class="location-info">
                    <h3><?= htmlspecialchars($event['location']) ?></h3>
                    <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></p>
                    <p><i class="fas fa-calendar"></i> <?= formatDate($event['event_date']) ?> at <?= $event['event_time'] ?></p>
                    <a href="https://maps.google.com/?q=<?= urlencode($event['location']) ?>" target="_blank" class="btn btn-outline">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Similar Events Section -->
    <section class="similar-events">
        <div class="container">
            <div class="section-header">
                <h2>Similar Events</h2>
                <p>You might also be interested in these events</p>
            </div>
            <div class="events-grid">
                <?php
                // Get similar events (same category, different events)
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
                    WHERE e.category_id = ? AND e.id != ? AND e.event_date >= CURDATE()
                    ORDER BY e.event_date ASC 
                    LIMIT 4
                ");
                $stmt->execute([$event['category_id'], $event_id]);
                $similar_events = $stmt->fetchAll();
                
                foreach ($similar_events as $similar_event):
                ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="<?= htmlspecialchars($similar_event['banner_image']) ?>" alt="<?= htmlspecialchars($similar_event['title']) ?>">
                        </div>
                        <div class="event-content">
                            <h3><?= htmlspecialchars($similar_event['title']) ?></h3>
                            <div class="event-meta">
                                <span><i class="fas fa-calendar"></i> <?= formatDate($similar_event['event_date']) ?></span>
                                <span><i class="fas fa-clock"></i> <?= $similar_event['event_time'] ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($similar_event['location']) ?></span>
                            </div>
                            <p><?= substr(htmlspecialchars($similar_event['description']), 0, 80) ?>...</p>
                            <div class="event-footer">
                                <span class="price">$<?= number_format($similar_event['price'], 2) ?></span>
                                <a href="event-details.php?id=<?= $similar_event['id'] ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Initialize Google Maps
        function initMap() {
            const location = "<?= htmlspecialchars($event['location']) ?>";
            const geocoder = new google.maps.Geocoder();
            
            geocoder.geocode({ address: location }, function(results, status) {
                if (status === 'OK') {
                    const map = new google.maps.Map(document.getElementById('map'), {
                        zoom: 15,
                        center: results[0].geometry.location,
                        styles: [
                            {
                                featureType: 'poi',
                                elementType: 'labels',
                                stylers: [{ visibility: 'off' }]
                            }
                        ]
                    });
                    
                    new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location,
                        title: "<?= htmlspecialchars($event['title']) ?>"
                    });
                } else {
                    document.getElementById('map').innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted);"><i class="fas fa-map-marker-alt" style="font-size: 2rem; margin-right: 1rem;"></i>Map not available</div>';
                }
            });
        }

        // Copy event link to clipboard
        function copyEventLink() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                // Show notification
                if (typeof NotificationSystem !== 'undefined') {
                    NotificationSystem.success('Event link copied to clipboard!');
                } else {
                    alert('Event link copied to clipboard!');
                }
            });
        }

        // Initialize map when page loads
        window.addEventListener('load', initMap);
    </script>
</body>
</html> 