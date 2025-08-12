<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EventHub</title>
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
                <h1>About EventHub</h1>
                <p>Connecting people through amazing events</p>
            </div>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-content">
        <div class="container">
            <div class="about-grid">
                <div class="about-text">
                    <h2>Our Story</h2>
                    <p>EventHub was born from a simple idea: to make event discovery and registration seamless and enjoyable. We believe that events have the power to bring people together, share knowledge, and create lasting memories.</p>
                    
                    <p>Founded in 2024, our platform has grown from a small local event listing to a comprehensive event management solution that serves communities worldwide. We're passionate about technology and its ability to enhance human connections.</p>
                    
                    <h3>Our Mission</h3>
                    <p>To provide a modern, user-friendly platform that connects event organizers with attendees, making event discovery and participation accessible to everyone.</p>
                    
                    <h3>Our Vision</h3>
                    <p>To become the leading event management platform that empowers communities to create, discover, and participate in meaningful events that enrich lives.</p>
                </div>
                
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1561489396-888724a1543d?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="About EventHub">
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose EventHub?</h2>
                <p>Discover what makes us different</p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Mobile-First Design</h3>
                    <p>Our platform is designed with mobile users in mind, ensuring a seamless experience across all devices.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Secure & Reliable</h3>
                    <p>Your data is protected with industry-standard security measures and reliable infrastructure.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Community Driven</h3>
                    <p>Built for communities, by communities. We listen to our users and continuously improve our platform.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3>Fast & Efficient</h3>
                    <p>Optimized performance ensures quick loading times and smooth user experience.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Modern UI/UX</h3>
                    <p>Beautiful, intuitive interface with dark/light mode support and smooth animations.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our dedicated support team is always ready to help you with any questions or issues.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalEvents($conn) ?>+</div>
                    <div class="stat-label">Events Hosted</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalUsers($conn) ?>+</div>
                    <div class="stat-label">Happy Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalRegistrations($conn) ?>+</div>
                    <div class="stat-label">Successful Registrations</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?= getTotalCategories($conn) ?>+</div>
                    <div class="stat-label">Event Categories</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="section-header">
                <h2>Meet Our Team</h2>
                <p>The passionate people behind EventHub</p>
            </div>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>John Smith</h3>
                    <p class="member-role">Founder & CEO</p>
                    <p class="member-bio">Passionate about technology and community building. Leads the vision and strategy for EventHub.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Sarah Johnson</h3>
                    <p class="member-role">Head of Design</p>
                    <p class="member-bio">Creates beautiful, user-friendly interfaces that make event discovery a joy.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Mike Chen</h3>
                    <p class="member-role">Lead Developer</p>
                    <p class="member-bio">Builds robust, scalable solutions that power our platform's core functionality.</p>
                </div>
                
                <div class="team-member">
                    <div class="member-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h3>Emily Davis</h3>
                    <p class="member-role">Community Manager</p>
                    <p class="member-bio">Ensures our users have the best experience and builds strong community relationships.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Get Started?</h2>
                <p>Join thousands of users who are already discovering amazing events on EventHub.</p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary">Create Account</a>
                    <a href="events.php" class="btn btn-outline">Browse Events</a>
                </div>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
</body>
</html> 