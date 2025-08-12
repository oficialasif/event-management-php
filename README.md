# EventHub - Modern Event Management Platform

A complete, modern event management website built with HTML, CSS, JavaScript, PHP, and MySQL. Features a responsive design, dark/light mode toggle, and both user and admin panels.

**© 2024 EventHub. All rights reserved by OficialAsif.**

## 🚀 Features

### Frontend (User Side)
- **Modern Responsive Design** - Works perfectly on desktop, tablet, and mobile
- **Dark/Light Mode Toggle** - Persistent theme preference
- **Hero Section** - Eye-catching banner with search functionality
- **Event Listings** - Grid and list views with filtering and pagination
- **Event Details** - Comprehensive event information with countdown timer
- **User Registration/Login** - Secure authentication system
- **User Dashboard** - Manage registrations and download tickets
- **Real-time Search** - Find events by keyword, category, or date
- **Google Maps Integration** - Event location visualization
- **Social Sharing** - Share events on social media platforms

### Backend (Admin Panel)
- **Admin Dashboard** - Overview with statistics and charts
- **Event Management** - Create, edit, delete events
- **User Management** - View and manage user accounts
- **Registration Management** - Track event registrations
- **Reports & Analytics** - Downloadable reports in CSV/PDF
- **Category Management** - Organize events by categories

### Technical Features
- **Secure Authentication** - Password hashing and session management
- **SQL Injection Protection** - Prepared statements throughout
- **Responsive Design** - CSS Grid and Flexbox
- **Modern UI/UX** - Smooth animations and transitions
- **AJAX Integration** - Real-time updates without page refresh
- **File Upload** - Image upload for event banners
- **QR Code Generation** - Unique ticket codes for events

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP, WAMP, or similar local development environment

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd eventmanagement
```

### 2. Database Setup
1. Create a MySQL database named `eventmanagement`
2. Import the database structure from `database/setup.sql`
3. Update database credentials in `config/database.php`

### 3. Configure Database
Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'eventmanagement');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Set Up Web Server
1. Place the project in your web server's document root
2. Ensure the web server has write permissions for uploads
3. Create the following directories if they don't exist:
   - `assets/images/events/`
   - `assets/images/`

### 5. Google Maps API (Optional)
For location features, add your Google Maps API key in `event-details.php`:
```javascript
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&libraries=places"></script>
```



## 📁 Project Structure

```
eventmanagement/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│       └── events/
├── config/
│   └── database.php
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── admin/
│   └── dashboard.php
├── user/
│   └── dashboard.php
├── database/
│   └── setup.sql
├── index.php
├── events.php
├── event-details.php
├── login.php
├── register.php
├── logout.php
└── README.md
```

## 🎨 Customization

### Colors and Themes
The application uses CSS custom properties for easy theming. Edit `assets/css/style.css`:

```css
:root {
    --primary-color: #6366f1;
    --secondary-color: #f59e0b;
    --accent-color: #10b981;
    /* ... other variables */
}
```

### Adding New Features
1. **New Pages**: Create PHP files in the root directory
2. **Admin Features**: Add to the `admin/` directory
3. **User Features**: Add to the `user/` directory
4. **Database**: Add new tables to `database/setup.sql`

## 🔧 Configuration

### Email Settings
To enable email functionality, configure your SMTP settings in the contact form.

### File Upload
Ensure the `assets/images/events/` directory has write permissions:
```bash
chmod 755 assets/images/events/
```

### Security
- Change default admin credentials
- Use HTTPS in production
- Regularly update dependencies
- Implement rate limiting for forms

## 📱 Responsive Design

The application is fully responsive and includes:
- Mobile-first design approach
- Touch-friendly navigation
- Optimized layouts for all screen sizes
- Progressive enhancement

## 🚀 Deployment

### Production Checklist
1. Update database credentials
2. Set proper file permissions
3. Enable HTTPS
4. Configure error reporting
5. Set up backup procedures
6. Optimize images and assets
7. Configure caching headers

### Performance Optimization
- Enable PHP OPcache
- Use CDN for external libraries
- Compress CSS and JavaScript
- Optimize database queries
- Implement caching strategies

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check database exists

**Image Upload Issues**
- Verify directory permissions
- Check file size limits in PHP configuration
- Ensure proper file types are allowed

**Session Issues**
- Check PHP session configuration
- Verify session storage permissions
- Clear browser cookies

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the code comments

## 🔄 Updates

To update the application:
1. Backup your database and files
2. Download the latest version
3. Replace files (except config and uploads)
4. Run any database migrations
5. Test thoroughly

---

**All rights reserved by OficialAsif** 