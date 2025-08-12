<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get search parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;

// Get events with pagination
$events = getEvents($conn, $search, $category, $page, $per_page);

// Get categories for filter
$categories = getEventCategories($conn);

// Calculate total pages (simplified - in real app you'd get total count from DB)
$total_events = count($events);
$total_pages = ceil($total_events / $per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - EventHub</title>
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
                <h1>Events</h1>
                <p>Discover and book amazing events in your area</p>
            </div>
        </div>
    </section>

    <!-- Search and Filter Section -->
    <section class="search-filter">
        <div class="container">
            <form action="events.php" method="GET" class="filter-form">
                <div class="filter-grid">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search events..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="filter-group">
                        <select name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <?php if ($search || $category): ?>
                            <a href="events.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events-section">
        <div class="container">
            <?php if ($search || $category): ?>
                <div class="search-results">
                    <h2>Search Results</h2>
                    <?php if ($search): ?>
                        <p>Showing results for: "<strong><?= htmlspecialchars($search) ?></strong>"</p>
                    <?php endif; ?>
                    <?php if ($category): ?>
                        <p>Category: "<strong><?= htmlspecialchars(array_filter($categories, fn($c) => $c['id'] == $category)[0]['name'] ?? '') ?></strong>"</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($events)): ?>
                <div class="no-events">
                    <div class="no-events-content">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No events found</h3>
                        <p>Try adjusting your search criteria or browse all events.</p>
                        <a href="events.php" class="btn btn-primary">Browse All Events</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="events-grid">
                    <?php foreach ($events as $event): ?>
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
                                <p><?= substr(htmlspecialchars($event['description']), 0, 120) ?>...</p>
                                <div class="event-footer">
                                    <div class="event-info">
                                        <span class="price">$<?= number_format($event['price'], 2) ?></span>
                                        <span class="seats-left"><?= $event['available_seats'] ?> seats left</span>
                                    </div>
                                    <a href="event-details.php?id=<?= $event['id'] ?>" class="btn btn-primary">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-link">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <div class="page-numbers">
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" 
                                   class="page-link <?= $i == $page ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                        </div>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-link">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <div class="container">
            <div class="section-header">
                <h2>Browse by Category</h2>
                <p>Find events that match your interests</p>
            </div>
            <div class="categories-grid">
                <?php foreach ($categories as $category_item): ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="<?= getCategoryIcon($category_item['name']) ?>"></i>
                        </div>
                        <h3><?= htmlspecialchars($category_item['name']) ?></h3>
                        <p><?= htmlspecialchars($category_item['description']) ?></p>
                        <a href="events.php?category=<?= $category_item['id'] ?>" class="btn btn-outline">Browse Events</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 