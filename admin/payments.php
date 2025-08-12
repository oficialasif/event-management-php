<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is admin
requireAdmin();

// Handle payment status updates
if ($_POST && isset($_POST['update_status'])) {
    $payment_id = intval($_POST['payment_id']);
    $new_status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE payments SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $payment_id])) {
        $success_message = "Payment status updated successfully!";
    } else {
        $error_message = "Failed to update payment status.";
    }
}

// Get payments with user and event details
$stmt = $conn->prepare("
    SELECT p.*, u.name as user_name, u.email as user_email, e.title as event_title, e.event_date
    FROM payments p
    JOIN users u ON p.user_id = u.id
    JOIN events e ON p.event_id = e.id
    ORDER BY p.payment_date DESC
");
$stmt->execute();
$payments = $stmt->fetchAll();

// Calculate total revenue
$stmt = $conn->prepare("SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'completed'");
$stmt->execute();
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;

// Get payment statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_payments,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_payments,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_payments,
        COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_payments
    FROM payments
");
$stmt->execute();
$payment_stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="page-header-content">
                <h1>Payment Management</h1>
                <p>View and manage all payment transactions</p>
            </div>
        </div>
    </section>

    <!-- Admin Dashboard -->
    <section class="admin-dashboard">
        <div class="container">
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Revenue</h3>
                        <p class="stat-number">$<?= number_format($total_revenue, 2) ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Total Payments</h3>
                        <p class="stat-number"><?= $payment_stats['total_payments'] ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Completed</h3>
                        <p class="stat-number"><?= $payment_stats['completed_payments'] ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3>Pending</h3>
                        <p class="stat-number"><?= $payment_stats['pending_payments'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success">
                    <?= $success_message ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-error">
                    <?= $error_message ?>
                </div>
            <?php endif; ?>

            <!-- Payments Table -->
            <div class="admin-table-container">
                <div class="table-header">
                    <h2>Payment Records</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>User</th>
                                <th>Event</th>
                                <th>Amount</th>
                                <th>Card Info</th>
                                <th>Payment Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No payment records found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($payments as $payment): ?>
                                    <tr>
                                        <td>#<?= $payment['id'] ?></td>
                                        <td>
                                            <div class="user-info">
                                                <strong><?= htmlspecialchars($payment['user_name']) ?></strong>
                                                <span><?= htmlspecialchars($payment['user_email']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="event-info">
                                                <strong><?= htmlspecialchars($payment['event_title']) ?></strong>
                                                <span><?= formatDate($payment['event_date']) ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="price">$<?= number_format($payment['amount'], 2) ?></span>
                                        </td>
                                        <td>
                                            <div class="card-info">
                                                <span>**** **** **** <?= $payment['card_last_four'] ?></span>
                                                <span><?= htmlspecialchars($payment['card_holder']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= formatDate($payment['payment_date']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= $payment['status'] ?>">
                                                <?= ucfirst($payment['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <button class="btn btn-sm btn-outline" onclick="viewPaymentDetails(<?= $payment['id'] ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline" onclick="updatePaymentStatus(<?= $payment['id'] ?>, '<?= $payment['status'] ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Status Update Modal -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Payment Status</h3>
                <span class="close">&times;</span>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="payment_id" name="payment_id">
                    <div class="form-group">
                        <label for="new_status">New Status</label>
                        <select id="new_status" name="new_status" required>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                            <option value="refunded">Refunded</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                    <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="../assets/js/main.js"></script>
    <script>
        // Modal functionality
        const modal = document.getElementById('statusModal');
        const closeBtn = document.getElementsByClassName('close')[0];

        function updatePaymentStatus(paymentId, currentStatus) {
            document.getElementById('payment_id').value = paymentId;
            document.getElementById('new_status').value = currentStatus;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function viewPaymentDetails(paymentId) {
            // In a real application, this would show detailed payment information
            alert('Payment details for ID: ' + paymentId);
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        closeBtn.onclick = closeModal;
    </script>
</body>
</html> 