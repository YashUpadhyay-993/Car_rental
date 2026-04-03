# Car Rental Pro - Complete PHP Application

A modern, feature-rich car rental management system built with Core PHP, MySQL, and Bootstrap 5.

## Features

### User Module
- User registration and login with secure password hashing
- Browse available cars with advanced filtering
- Book cars with date/time selection
- View booking history and details
- Rate and review cars after booking
- User dashboard with statistics

### Admin Module
- Separate admin authentication
- Manage cars (add, edit, delete, toggle availability)
- Manage bookings (confirm, cancel, view details)
- Manage users (view details, delete inactive users)
- Manage drivers (add, activate/deactivate, view statistics)
- Admin dashboard with comprehensive statistics
- Real-time booking status management

### Driver Module
- Separate driver authentication system
- Driver dashboard with ride statistics
- Automatic ride assignment from confirmed bookings
- Ride management (accept, reject, start, complete)
- Real-time ride status tracking
- Driver profile with performance metrics
- Customer contact information access

### Core Features
- Responsive, modern UI with Bootstrap 5
- Star rating system with average calculations
- Image upload for cars
- CSRF protection
- SQL injection prevention with prepared statements
- Session-based authentication
- Booking conflict prevention
- Email validation
- File upload security

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- GD Library (for image processing)

## Installation

### 1. Database Setup

1. Create a new MySQL database named `car_rental`
2. Import the `database.sql` file using phpMyAdmin or MySQL command line:

```sql
mysql -u username -p car_rental < database.sql
```

3. Import the driver module updates using `database_updates.sql`:

```sql
mysql -u username -p car_rental < database_updates.sql
```

### 2. Configuration

1. Update database credentials in `config/database.php` if needed:
```php
private $host = 'localhost';
private $db_name = 'car_rental';
private $username = 'root';
private $password = '';
```

### 3. File Permissions

Ensure the following directories are writable:
- `assets/images/` (for car image uploads)

### 4. Web Server Configuration

Place the entire `car_rental` folder in your web root (e.g., `htdocs/` for XAMPP).

## Default Access

### Admin Access
- **Email:** admin@carrental.com
- **Password:** password

### Driver Access
- **Email:** john.driver@carrental.com
- **Password:** password

## Directory Structure

```
car_rental/
├── admin/                  # Admin module files
│   ├── dashboard.php
│   ├── cars.php
│   ├── add_car.php
│   ├── bookings.php
│   ├── users.php
│   ├── drivers.php
│   └── login.php
├── driver/                 # Driver module files
│   ├── dashboard.php
│   ├── rides.php
│   ├── ride_details.php
│   ├── profile.php
│   └── login.php
├── user/                   # User module files
│   ├── dashboard.php
│   ├── cars.php
│   ├── car_details.php
│   ├── book_car.php
│   ├── login.php
│   └── register.php
├── config/                 # Configuration files
│   ├── database.php
│   └── config.php
├── includes/               # Reusable components
│   ├── header.php
│   └── footer.php
├── assets/                 # Static assets
│   ├── css/
│   ├── js/
│   └── images/
├── database.sql           # Main database schema
├── database_updates.sql   # Driver module updates
├── index.php              # Homepage
└── README.md              # This file
```

## Usage

### For Users

1. Register a new account or login
2. Browse available cars with filters
3. View car details and ratings
4. Book cars by selecting dates and times
5. View booking history
6. Rate and review cars after confirmed bookings

### For Admins

1. Login with admin credentials
2. View dashboard statistics
3. Add/edit/delete cars
4. Manage car availability
5. Confirm or cancel bookings
6. View user details and statistics
7. Add and manage drivers
8. Monitor ride assignments and completions

### For Drivers

1. Login with driver credentials
2. View dashboard with ride statistics
3. Check assigned rides in "My Rides"
4. Accept or reject ride assignments
5. Update ride status (On the Way, Started, Completed)
6. View customer contact information
7. Track performance metrics

## Security Features

- Password hashing with PHP's `password_hash()`
- CSRF token protection
- Prepared statements for SQL injection prevention
- Input sanitization and validation
- Session management
- File upload security
- Access control for admin, user, and driver areas

## Ride Assignment Flow

1. **User books a car** → Booking created with status 'pending'
2. **Admin confirms booking** → Status changes to 'confirmed'
3. **Automatic driver assignment** → System assigns available driver
4. **Driver receives notification** → Ride appears in driver dashboard
5. **Driver accepts/rejects** → Status updates accordingly
6. **Ride execution** → Driver updates status through ride lifecycle
7. **Ride completion** → Status marked as completed

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## License

This project is open source and available under the MIT License.

## Support

For any issues or questions, please check the code comments or contact the development team.

---

**Note:** Make sure to change the default admin password after first login for security reasons.
