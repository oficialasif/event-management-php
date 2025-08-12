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
$stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
$stmt->execute([$_SESSION['user_id'], $event_id]);
$is_registered = $stmt->fetch() ? true : false;

if ($is_registered) {
    header('Location: event-details.php?id=' . $event_id);
    exit();
}

// Check if event is sold out
if ($event['available_seats'] <= 0) {
    header('Location: event-details.php?id=' . $event_id);
    exit();
}

// Handle payment submission
$payment_message = '';
if ($_POST && isset($_POST['process_payment'])) {
    // Validate payment form
    $card_number = trim($_POST['card_number'] ?? '');
    $card_holder = trim($_POST['card_holder'] ?? '');
    $expiry_month = trim($_POST['expiry_month'] ?? '');
    $expiry_year = trim($_POST['expiry_year'] ?? '');
    $cvv = trim($_POST['cvv'] ?? '');
    
    $errors = [];
    
    if (empty($card_number) || strlen($card_number) < 13) {
        $errors[] = 'Please enter a valid card number';
    }
    
    if (empty($card_holder)) {
        $errors[] = 'Please enter card holder name';
    }
    
    if (empty($expiry_month) || empty($expiry_year)) {
        $errors[] = 'Please enter expiry date';
    }
    
    if (empty($cvv) || strlen($cvv) < 3) {
        $errors[] = 'Please enter a valid CVV';
    }
    
    if (empty($errors)) {
        // Process payment (simulated)
        $payment_successful = true; // In real implementation, this would integrate with payment gateway
        
        if ($payment_successful) {
            // Register user for event
            if (registerForEvent($conn, $_SESSION['user_id'], $event_id)) {
                // Store payment information for admin
                $payment_data = [
                    'user_id' => $_SESSION['user_id'],
                    'event_id' => $event_id,
                    'amount' => $event['price'],
                    'card_number' => substr($card_number, -4), // Store only last 4 digits
                    'card_holder' => $card_holder,
                    'payment_date' => date('Y-m-d H:i:s'),
                    'status' => 'completed'
                ];
                
                // Insert payment record
                $stmt = $conn->prepare("INSERT INTO payments (user_id, event_id, amount, card_last_four, card_holder, payment_date, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $payment_data['user_id'],
                    $payment_data['event_id'],
                    $payment_data['amount'],
                    $payment_data['card_number'],
                    $payment_data['card_holder'],
                    $payment_data['payment_date'],
                    $payment_data['status']
                ]);
                
                // Redirect to success page
                header('Location: payment-success.php?event_id=' . $event_id);
                exit();
            } else {
                $payment_message = 'error:Registration failed. Please try again.';
            }
        } else {
            $payment_message = 'error:Payment failed. Please check your card details and try again.';
        }
    } else {
        $payment_message = 'error:' . implode(', ', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for <?= htmlspecialchars($event['title']) ?> - EventHub</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Event Registration</h1>
                <p>Complete your registration for <?= htmlspecialchars($event['title']) ?></p>
            </div>
        </div>
    </section>

    <!-- Registration Form -->
    <section class="registration-page">
        <div class="container">
            <div class="registration-grid">
                <!-- Event Summary -->
                <div class="event-summary">
                    <div class="summary-card">
                        <h3>Event Details</h3>
                        <div class="event-image">
                            <img src="<?= htmlspecialchars($event['banner_image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                        </div>
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
                        <div class="price-summary">
                            <span class="price-label">Registration Fee:</span>
                            <span class="price">$<?= number_format($event['price'], 2) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Form -->
                <div class="payment-form">
                    <div class="form-card">
                        <h3>Payment Information</h3>
                        
                        <?php if ($payment_message): ?>
                            <div class="alert alert-<?= strpos($payment_message, 'success') === 0 ? 'success' : 'error' ?>">
                                <?= substr($payment_message, strpos($payment_message, ':') + 1) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="payment-form-fields">
                            <div class="form-group">
                                <label for="card_holder">Card Holder Name</label>
                                <input type="text" id="card_holder" name="card_holder" required 
                                       placeholder="Enter card holder name">
                            </div>

                            <div class="form-group">
                                <label for="card_number">Card Number</label>
                                <input type="text" id="card_number" name="card_number" required 
                                       placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry_month">Expiry Month</label>
                                    <select id="expiry_month" name="expiry_month" required>
                                        <option value="">Month</option>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="<?= sprintf('%02d', $i) ?>"><?= sprintf('%02d', $i) ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="expiry_year">Expiry Year</label>
                                    <select id="expiry_year" name="expiry_year" required>
                                        <option value="">Year</option>
                                        <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                            <option value="<?= $i ?>"><?= $i ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV</label>
                                    <input type="text" id="cvv" name="cvv" required 
                                           placeholder="123" maxlength="4">
                                </div>
                            </div>

                            <div class="payment-summary">
                                <div class="summary-item">
                                    <span>Event Registration:</span>
                                    <span>$<?= number_format($event['price'], 2) ?></span>
                                </div>
                                <div class="summary-item total">
                                    <span>Total Amount:</span>
                                    <span>$<?= number_format($event['price'], 2) ?></span>
                                </div>
                            </div>

                            <button type="submit" name="process_payment" class="btn btn-primary btn-large">
                                <i class="fas fa-credit-card"></i> Pay $<?= number_format($event['price'], 2) ?>
                            </button>
                        </form>

                        <div class="security-notice">
                            <i class="fas fa-shield-alt"></i>
                            <p>Your payment information is secure and encrypted. We use industry-standard security measures to protect your data.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Format card number with spaces
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.replace(/\d{4}(?=.)/g, '$& ');
            e.target.value = formattedValue;
        });

        // Format CVV to numbers only
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html> 