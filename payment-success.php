<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get event ID from URL
$event_id = intval($_GET['event_id'] ?? 0);

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

// Get registration details
$stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ? ORDER BY registration_date DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id'], $event_id]);
$registration = $stmt->fetch();

if (!$registration) {
    header('Location: events.php');
    exit();
}

// Get payment details
$stmt = $conn->prepare("SELECT * FROM payments WHERE user_id = ? AND event_id = ? ORDER BY payment_date DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id'], $event_id]);
$payment = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Success Section -->
    <section class="payment-success">
        <div class="container">
            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                
                <h1>Payment Successful!</h1>
                <p class="success-message">Your registration has been confirmed. You're all set for the event!</p>
                
                <div class="confirmation-details">
                    <div class="detail-card">
                        <h3>Event Details</h3>
                        <div class="event-info">
                            <div class="event-image">
                                <img src="<?= htmlspecialchars($event['banner_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                            </div>
                            <div class="event-details">
                                <h4><?= htmlspecialchars($event['title']) ?></h4>
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?= formatDate($event['event_date']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?= $event['event_time'] ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($event['location']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="detail-card">
                        <h3>Registration Details</h3>
                        <div class="registration-info">
                            <div class="info-item">
                                <span class="label">Registration ID:</span>
                                <span class="value">#<?= $registration['ticket_code'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Registration Date:</span>
                                <span class="value"><?= formatDate($registration['registration_date']) ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Amount Paid:</span>
                                <span class="value price">$<?= number_format($event['price'], 2) ?></span>
                            </div>
                            <?php if ($payment): ?>
                            <div class="info-item">
                                <span class="label">Payment Method:</span>
                                <span class="value">Card ending in <?= $payment['card_last_four'] ?></span>
                            </div>
                            <div class="info-item">
                                <span class="label">Payment Date:</span>
                                <span class="value"><?= formatDate($payment['payment_date']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h3>What's Next?</h3>
                    <div class="steps-grid">
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h4>Confirmation Email</h4>
                            <p>You'll receive a confirmation email with event details and your ticket.</p>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <h4>Add to Calendar</h4>
                            <p>Add the event to your calendar to ensure you don't miss it.</p>
                        </div>
                        <div class="step-item">
                            <div class="step-icon">
                                <i class="fas fa-ticket-alt"></i>
                            </div>
                            <h4>Download Ticket</h4>
                            <p>Download your ticket from your dashboard for easy access.</p>
                        </div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="user/tickets.php" class="btn btn-primary">
                        <i class="fas fa-ticket-alt"></i> View My Tickets
                    </a>
                    <a href="event-details.php?id=<?= $event_id ?>" class="btn btn-outline">
                        <i class="fas fa-info-circle"></i> Event Details
                    </a>
                    <a href="events.php" class="btn btn-outline">
                        <i class="fas fa-calendar"></i> Browse More Events
                    </a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 