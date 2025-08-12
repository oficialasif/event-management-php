<?php
// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

// Navigation helper function
function getBaseUrl() {
    $current_path = $_SERVER['PHP_SELF'];
    $path_parts = explode('/', $current_path);
    
    // Remove the filename from the path
    array_pop($path_parts);
    
    // If we're in a subdirectory (like user/ or admin/), we need to go up one level
    if (count($path_parts) > 1 && ($path_parts[count($path_parts) - 1] === 'user' || $path_parts[count($path_parts) - 1] === 'admin')) {
        array_pop($path_parts);
    }
    
    $base_path = implode('/', $path_parts);
    return $base_path === '' ? './' : $base_path . '/';
}

// Event functions
function getFeaturedEvents($conn, $limit = 6) {
    $limit = (int)$limit; // Ensure it's an integer
    $stmt = $conn->prepare("
        SELECT e.*, c.name as category_name 
        FROM events e 
        JOIN categories c ON e.category_id = c.id 
        WHERE e.is_featured = 1 AND e.event_date >= CURDATE() 
        ORDER BY e.event_date ASC 
        LIMIT $limit
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getUpcomingEvents($conn, $limit = 8) {
    $limit = (int)$limit; // Ensure it's an integer
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
        WHERE e.event_date >= CURDATE() 
        ORDER BY e.event_date ASC 
        LIMIT $limit
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getEventById($conn, $id) {
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
        WHERE e.id = ?
    ");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getEvents($conn, $search = '', $category = '', $page = 1, $per_page = 12) {
    $offset = ($page - 1) * $per_page;
    $where_conditions = ["e.event_date >= CURDATE()"];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(e.title LIKE ? OR e.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if (!empty($category)) {
        $where_conditions[] = "e.category_id = ?";
        $params[] = $category;
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
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
        WHERE $where_clause
        ORDER BY e.event_date ASC 
        LIMIT $per_page OFFSET $offset
    ");
    
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getEventCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll();
}

// User functions
function registerUser($conn, $data) {
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, phone, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");
    
    return $stmt->execute([
        $data['name'],
        $data['email'],
        $hashed_password,
        $data['phone']
    ]);
}

function loginUser($conn, $email, $password) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        return true;
    }
    return false;
}

function getUserById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Registration functions
function registerForEvent($conn, $user_id, $event_id) {
    // Check if already registered
    $stmt = $conn->prepare("SELECT * FROM registrations WHERE user_id = ? AND event_id = ?");
    $stmt->execute([$user_id, $event_id]);
    if ($stmt->fetch()) {
        return false; // Already registered
    }
    
    // Check if seats available
    $event = getEventById($conn, $event_id);
    if ($event['available_seats'] <= 0) {
        return false; // No seats available
    }
    
    // Generate unique ticket code
    $ticket_code = generateTicketCode();
    
    $stmt = $conn->prepare("
        INSERT INTO registrations (user_id, event_id, ticket_code, registration_date) 
        VALUES (?, ?, ?, NOW())
    ");
    
    return $stmt->execute([$user_id, $event_id, $ticket_code]);
}

function getUserRegistrations($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT r.*, e.title, e.event_date, e.event_time, e.location, e.banner_image,
               c.name as category_name
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        JOIN categories c ON e.category_id = c.id
        WHERE r.user_id = ?
        ORDER BY e.event_date ASC
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

function cancelRegistration($conn, $registration_id, $user_id) {
    $stmt = $conn->prepare("
        DELETE FROM registrations 
        WHERE id = ? AND user_id = ? AND event_id IN (
            SELECT id FROM events WHERE event_date > CURDATE()
        )
    ");
    return $stmt->execute([$registration_id, $user_id]);
}

// Utility functions
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($date, $time) {
    return date('F j, Y g:i A', strtotime("$date $time"));
}

function generateTicketCode() {
    return 'TIX' . strtoupper(uniqid()) . rand(100, 999);
}

function getCategoryIcon($category_name) {
    $icons = [
        'Workshops' => 'fas fa-tools',
        'Cultural' => 'fas fa-theater-masks',
        'Sports' => 'fas fa-running',
        'Academic' => 'fas fa-graduation-cap',
        'Music' => 'fas fa-music',
        'Technology' => 'fas fa-laptop-code',
        'Business' => 'fas fa-briefcase',
        'Health' => 'fas fa-heartbeat'
    ];
    
    return $icons[$category_name] ?? 'fas fa-calendar';
}

// Statistics functions
function getTotalEvents($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
    $stmt->execute();
    return $stmt->fetch()['count'];
}

function getTotalUsers($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'user'");
    $stmt->execute();
    return $stmt->fetch()['count'];
}

function getTotalRegistrations($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations");
    $stmt->execute();
    return $stmt->fetch()['count'];
}

function getTotalCategories($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM categories");
    $stmt->execute();
    return $stmt->fetch()['count'];
}

// Admin functions
function getAllUsers($conn) {
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getAllEvents($conn) {
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
        ORDER BY e.event_date DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

function getEventRegistrations($conn, $event_id) {
    $stmt = $conn->prepare("
        SELECT r.*, u.name, u.email, u.phone
        FROM registrations r
        JOIN users u ON r.user_id = u.id
        WHERE r.event_id = ?
        ORDER BY r.registration_date DESC
    ");
    $stmt->execute([$event_id]);
    return $stmt->fetchAll();
}

// File upload function
function uploadImage($file, $target_dir = 'assets/images/events/') {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return false;
    }
    
    $new_filename = uniqid() . '.' . $file_extension;
    $target_path = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $target_path;
    }
    
    return false;
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 6;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?> 