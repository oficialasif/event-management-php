<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

$success_message = '';
$error_message = '';

// Get event ID from URL
$event_id = (int)($_GET['id'] ?? 0);

if ($event_id <= 0) {
    header('Location: events.php');
    exit();
}

// Get event data
$event = getEventById($conn, $event_id);

if (!$event) {
    header('Location: events.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $event_date = $_POST['event_date'] ?? '';
    $event_time = $_POST['event_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $total_seats = (int)($_POST['total_seats'] ?? 0);
    $banner_image = trim($_POST['banner_image'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;

    // Validation
    $errors = [];
    
    if (empty($title)) {
        $errors[] = 'Event title is required';
    }
    
    if (empty($description)) {
        $errors[] = 'Event description is required';
    }
    
    if ($category_id <= 0) {
        $errors[] = 'Please select a category';
    }
    
    if (empty($event_date)) {
        $errors[] = 'Event date is required';
    }
    
    if (empty($event_time)) {
        $errors[] = 'Event time is required';
    }
    
    if (empty($location)) {
        $errors[] = 'Event location is required';
    }
    
    if ($price < 0) {
        $errors[] = 'Price cannot be negative';
    }
    
    if ($total_seats <= 0) {
        $errors[] = 'Total seats must be greater than 0';
    }

    // If no errors, update the event
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("
                UPDATE events 
                SET title = ?, description = ?, category_id = ?, event_date = ?, event_time = ?,
                    location = ?, price = ?, total_seats = ?, banner_image = ?, is_featured = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $title, $description, $category_id, $event_date, $event_time,
                $location, $price, $total_seats, $banner_image, $is_featured, $event_id
            ]);
            
            $success_message = 'Event updated successfully!';
            
            // Refresh event data
            $event = getEventById($conn, $event_id);
            
        } catch (Exception $e) {
            $error_message = 'Error updating event: ' . $e->getMessage();
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Get categories for the form
$categories = getEventCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Admin Dashboard</title>
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
                <h1>Edit Event</h1>
                <p>Update event details</p>
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
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= $error_message ?>
                        </div>
                    <?php endif; ?>

                    <!-- Edit Event Form -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>Edit Event: <?= htmlspecialchars($event['title']) ?></h2>
                            <a href="events.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Events
                            </a>
                        </div>

                        <form method="POST" class="event-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="title">Event Title *</label>
                                    <input type="text" id="title" name="title" 
                                           value="<?= htmlspecialchars($event['title']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="category_id">Category *</label>
                                    <select id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                    <?= $event['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="description">Event Description *</label>
                                <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($event['description']) ?></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="event_date">Event Date *</label>
                                    <input type="date" id="event_date" name="event_date" 
                                           value="<?= htmlspecialchars($event['event_date']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="event_time">Event Time *</label>
                                    <input type="time" id="event_time" name="event_time" 
                                           value="<?= htmlspecialchars($event['event_time']) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="location">Event Location *</label>
                                <input type="text" id="location" name="location" 
                                       value="<?= htmlspecialchars($event['location']) ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="price">Ticket Price ($)</label>
                                    <input type="number" id="price" name="price" step="0.01" min="0" 
                                           value="<?= htmlspecialchars($event['price']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="total_seats">Total Seats *</label>
                                    <input type="number" id="total_seats" name="total_seats" min="1" 
                                           value="<?= htmlspecialchars($event['total_seats']) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="banner_image">Banner Image URL</label>
                                <input type="url" id="banner_image" name="banner_image" 
                                       value="<?= htmlspecialchars($event['banner_image']) ?>"
                                       placeholder="https://example.com/image.jpg">
                                <small>Enter a URL for the event banner image</small>
                            </div>

                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_featured" value="1" 
                                           <?= $event['is_featured'] ? 'checked' : '' ?>>
                                    <span class="checkmark"></span>
                                    Feature this event on the homepage
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Event
                                </button>
                                <a href="events.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <style>
        .event-form {
            max-width: 800px;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-size: 0.875rem;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .form-group small {
            display: block;
            margin-top: 0.25rem;
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border-color);
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.875rem;
        }
        .checkbox-label input[type="checkbox"] {
            display: none;
        }
        .checkmark {
            width: 18px;
            height: 18px;
            border: 2px solid var(--border-color);
            border-radius: 3px;
            position: relative;
            transition: var(--transition);
        }
        .checkbox-label input[type="checkbox"]:checked + .checkmark {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .checkbox-label input[type="checkbox"]:checked + .checkmark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</body>
</html> 