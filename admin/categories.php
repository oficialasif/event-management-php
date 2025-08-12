<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require admin access
requireAdmin();

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['name']) && !empty($_POST['name'])) {
                    $name = trim($_POST['name']);
                    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
                    $stmt->execute([$name]);
                    $success_message = "Category added successfully!";
                }
                break;
            case 'delete':
                if (isset($_POST['category_id'])) {
                    $category_id = (int)$_POST['category_id'];
                    // Check if category has events
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE category_id = ?");
                    $stmt->execute([$category_id]);
                    $result = $stmt->fetch();
                    
                    if ($result['count'] > 0) {
                        $error_message = "Cannot delete category that has events. Please reassign or delete the events first.";
                    } else {
                        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
                        $stmt->execute([$category_id]);
                        $success_message = "Category deleted successfully!";
                    }
                }
                break;
            case 'update':
                if (isset($_POST['category_id']) && isset($_POST['name']) && !empty($_POST['name'])) {
                    $category_id = (int)$_POST['category_id'];
                    $name = trim($_POST['name']);
                    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
                    $stmt->execute([$name, $category_id]);
                    $success_message = "Category updated successfully!";
                }
                break;
        }
    }
}

// Get all categories with event count
$stmt = $conn->prepare("
    SELECT c.*, 
           COALESCE(e.event_count, 0) as event_count
    FROM categories c 
    LEFT JOIN (
        SELECT category_id, COUNT(*) as event_count
        FROM events 
        GROUP BY category_id
    ) e ON c.id = e.category_id
    ORDER BY c.name ASC
");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin Dashboard</title>
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
                <h1>Manage Categories</h1>
                <p>Create and manage event categories</p>
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
                            <li>
                                <a href="registrations.php">
                                    <i class="fas fa-ticket-alt"></i> Registrations
                                </a>
                            </li>
                            <li class="active">
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

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Add Category Section -->
                    <div class="admin-section">
                        <h2>Add New Category</h2>
                        <form method="POST" class="form-inline">
                            <input type="hidden" name="action" value="add">
                            <div class="form-group">
                                <input type="text" name="name" placeholder="Category name" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Category
                            </button>
                        </form>
                    </div>

                    <!-- Categories Management Section -->
                    <div class="admin-section">
                        <div class="section-header">
                            <h2>All Categories</h2>
                            <span class="text-muted"><?= count($categories) ?> total categories</span>
                        </div>

                        <div class="events-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Events</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td>
                                                <div class="category-info">
                                                    <h4><?= htmlspecialchars($category['name']) ?></h4>
                                                    <p>ID: <?= $category['id'] ?></p>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge"><?= $category['event_count'] ?> events</span>
                                            </td>
                                            <td>
                                                <div class="event-actions">
                                                    <button class="btn btn-sm btn-primary" onclick="editCategory(<?= $category['id'] ?>, '<?= htmlspecialchars($category['name']) ?>')">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <?php if ($category['event_count'] == 0): ?>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="text-muted">Cannot delete (has events)</span>
                                                    <?php endif; ?>
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

    <!-- Edit Category Modal -->
    <div id="editModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Edit Category</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="category_id" id="editCategoryId">
                <div class="form-group">
                    <label for="editCategoryName">Category Name</label>
                    <input type="text" id="editCategoryName" name="name" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>
    <script src="../assets/js/main.js"></script>
    <script>
        function editCategory(id, name) {
            document.getElementById('editCategoryId').value = id;
            document.getElementById('editCategoryName').value = name;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
    <style>
        .modal {
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: var(--bg-primary);
            margin: 15% auto;
            padding: 2rem;
            border-radius: var(--radius-lg);
            width: 80%;
            max-width: 500px;
        }
        .form-inline {
            display: flex;
            gap: 1rem;
            align-items: end;
        }
        .form-inline .form-group {
            flex: 1;
        }
        .badge {
            background-color: var(--primary-color);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius-md);
            font-size: 0.875rem;
        }
    </style>
</body>
</html> 