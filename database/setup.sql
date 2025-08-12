-- Event Management Database Setup
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS eventmanagement;
USE eventmanagement;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(200) NOT NULL,
    category_id INT NOT NULL,
    total_seats INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) DEFAULT 0.00,
    banner_image VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Registrations table
CREATE TABLE registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    ticket_code VARCHAR(50) UNIQUE NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (user_id, event_id)
);

-- Feedback table
CREATE TABLE feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, icon) VALUES
('Workshops', 'Interactive learning sessions and skill development workshops', 'fas fa-tools'),
('Cultural', 'Cultural events, festivals, and traditional celebrations', 'fas fa-theater-masks'),
('Sports', 'Sports events, tournaments, and athletic competitions', 'fas fa-running'),
('Academic', 'Educational conferences, seminars, and academic events', 'fas fa-graduation-cap'),
('Music', 'Concerts, music festivals, and live performances', 'fas fa-music'),
('Technology', 'Tech conferences, hackathons, and innovation events', 'fas fa-laptop-code'),
('Business', 'Business meetings, networking events, and corporate functions', 'fas fa-briefcase'),
('Health', 'Health and wellness events, fitness classes, and medical seminars', 'fas fa-heartbeat');

-- Insert admin user (password: admin123)
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin User', 'admin@eventhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890', 'admin');

-- Insert sample users (password: user123)
INSERT INTO users (name, email, password, phone) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567892'),
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567893');

-- Insert sample events
INSERT INTO events (title, description, event_date, event_time, location, category_id, total_seats, price, banner_image, is_featured) VALUES
('Web Development Workshop', 'Learn modern web development techniques including HTML5, CSS3, JavaScript, and PHP. This hands-on workshop will cover responsive design, modern frameworks, and best practices for building scalable web applications.', '2024-02-15', '09:00:00', 'Tech Hub Conference Center', 1, 50, 99.00, 'assets/images/events/web-dev-workshop.jpg', 1),
('Summer Music Festival', 'Join us for an amazing summer music festival featuring local and international artists. Enjoy live performances, food trucks, and a great atmosphere with music lovers from around the city.', '2024-03-20', '16:00:00', 'Central Park Amphitheater', 5, 200, 45.00, 'assets/images/events/music-festival.jpg', 1),
('Business Networking Event', 'Connect with industry professionals and expand your business network. This event includes keynote speakers, panel discussions, and networking sessions designed to help you grow your business.', '2024-02-28', '18:00:00', 'Downtown Business Center', 7, 100, 75.00, 'assets/images/events/networking.jpg', 0),
('Fitness Bootcamp', 'Get fit with our intensive fitness bootcamp! This event includes cardio training, strength building, and nutrition guidance. Suitable for all fitness levels.', '2024-02-10', '07:00:00', 'Community Fitness Center', 8, 30, 25.00, 'assets/images/events/fitness-bootcamp.jpg', 0),
('Cultural Heritage Festival', 'Celebrate our diverse cultural heritage with traditional music, dance performances, art exhibitions, and delicious international cuisine. A family-friendly event for all ages.', '2024-04-05', '12:00:00', 'City Cultural Center', 2, 150, 15.00, 'assets/images/events/cultural-festival.jpg', 1),
('Tech Innovation Summit', 'Discover the latest trends in technology innovation. This summit brings together tech leaders, entrepreneurs, and innovators to share insights about the future of technology.', '2024-03-15', '10:00:00', 'Innovation Center', 6, 80, 150.00, 'assets/images/events/tech-summit.jpg', 1),
('Academic Research Conference', 'Present and discuss cutting-edge research in various academic fields. This conference provides a platform for researchers to share their findings and collaborate on future projects.', '2024-02-25', '09:00:00', 'University Conference Hall', 4, 120, 50.00, 'assets/images/events/academic-conference.jpg', 0),
('Basketball Tournament', 'Join our annual basketball tournament! Teams from across the city will compete for the championship title. Spectators welcome with food and refreshments available.', '2024-03-10', '14:00:00', 'Community Sports Complex', 3, 300, 10.00, 'assets/images/events/basketball-tournament.jpg', 0);

-- Insert sample registrations
INSERT INTO registrations (user_id, event_id, ticket_code) VALUES
(2, 1, 'TIX001ABC123'),
(2, 3, 'TIX002DEF456'),
(3, 2, 'TIX003GHI789'),
(3, 5, 'TIX004JKL101'),
(4, 1, 'TIX005MNO202'),
(4, 6, 'TIX006PQR303');

-- Insert sample feedback
INSERT INTO feedback (user_id, event_id, rating, comment) VALUES
(2, 1, 5, 'Excellent workshop! Learned a lot about modern web development.'),
(3, 2, 4, 'Great music festival, really enjoyed the performances.'),
(4, 1, 5, 'Very informative and well-organized workshop.'),
(2, 3, 4, 'Good networking opportunities, met some interesting people.');

-- Create indexes for better performance
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_category ON events(category_id);
CREATE INDEX idx_events_featured ON events(is_featured);
CREATE INDEX idx_registrations_user ON registrations(user_id);
CREATE INDEX idx_registrations_event ON registrations(event_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role); 