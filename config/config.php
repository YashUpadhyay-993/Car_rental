<?php
// Start session
session_start();

// Define base URL
define('BASE_URL', 'http://localhost/car_rental/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'database.php';

// Create database connection
$database = new Database();
$db = $database->getConnection();

// Helper functions
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

function is_driver_logged_in() {
    return isset($_SESSION['driver_id']);
}

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function get_average_rating($car_id) {
    global $db;
    $stmt = $db->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE car_id = ?");
    $stmt->execute([$car_id]);
    $result = $stmt->fetch();
    return $result['avg_rating'] ? round($result['avg_rating'], 1) : 0;
}

function format_price($price) {
    return '$' . number_format($price, 2);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Driver assignment functions
function assign_driver_to_booking($booking_id) {
    global $db;
    
    // Get first available active driver
    $stmt = $db->prepare("
        SELECT d.id 
        FROM drivers d 
        WHERE d.status = 'active' 
        AND d.id NOT IN (
            SELECT DISTINCT r.driver_id 
            FROM rides r 
            WHERE r.status IN ('assigned', 'accepted', 'on_the_way', 'started')
        )
        ORDER BY d.created_at ASC 
        LIMIT 1
    ");
    $stmt->execute();
    $driver = $stmt->fetch();
    
    if ($driver) {
        // Insert ride record
        $stmt = $db->prepare("
            INSERT INTO rides (booking_id, driver_id, status, pickup_location, drop_location) 
            VALUES (?, ?, 'assigned', ?, ?)
        ");
        
        // Get booking details for location info
        $booking_stmt = $db->prepare("
            SELECT b.*, u.name as user_name, u.phone as user_phone, u.address as user_address 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            WHERE b.id = ?
        ");
        $booking_stmt->execute([$booking_id]);
        $booking = $booking_stmt->fetch();
        
        if ($booking) {
            $pickup_location = $booking['user_address'] ?: 'Customer Location';
            $drop_location = 'Car Rental Office';
            
            if ($stmt->execute([$booking_id, $driver['id'], $pickup_location, $drop_location])) {
                // Update booking ride status
                $update_stmt = $db->prepare("
                    UPDATE bookings 
                    SET driver_assigned = TRUE, ride_status = 'driver_assigned' 
                    WHERE id = ?
                ");
                $update_stmt->execute([$booking_id]);
                
                return $driver['id'];
            }
        }
    }
    
    return false;
}

function get_driver_rides($driver_id, $status = null) {
    global $db;
    
    $query = "
        SELECT r.*, b.*, u.name as user_name, u.phone as user_phone, c.name as car_name, c.image as car_image
        FROM rides r
        JOIN bookings b ON r.booking_id = b.id
        JOIN users u ON b.user_id = u.id
        JOIN cars c ON b.car_id = c.id
        WHERE r.driver_id = ?
    ";
    
    $params = [$driver_id];
    
    if ($status) {
        $query .= " AND r.status = ?";
        $params[] = $status;
    }
    
    $query .= " ORDER BY r.assigned_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function update_ride_status($ride_id, $status) {
    global $db;
    
    $stmt = $db->prepare("
        UPDATE rides r
        JOIN bookings b ON r.booking_id = b.id
        SET r.status = ?, r.updated_at = CURRENT_TIMESTAMP, b.ride_status = ?
        WHERE r.id = ?
    ");
    
    $booking_status = match($status) {
        'accepted' => 'ride_accepted',
        'on_the_way' => 'ride_accepted',
        'started' => 'ride_started',
        'completed' => 'ride_completed',
        'cancelled' => 'ride_cancelled',
        default => 'driver_assigned'
    };
    
    return $stmt->execute([$status, $booking_status, $ride_id]);
}
?>
