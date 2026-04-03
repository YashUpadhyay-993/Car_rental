<?php
require_once '../config/config.php';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ride_id = $_GET['id'];
    $driver_id = $_SESSION['driver_id'];
    
    // Verify ride belongs to this driver and is accepted or on_the_way
    $stmt = $db->prepare("SELECT id FROM rides WHERE id = ? AND driver_id = ? AND status IN ('accepted', 'on_the_way')");
    $stmt->execute([$ride_id, $driver_id]);
    $ride = $stmt->fetch();
    
    if ($ride) {
        // Update ride status to started
        if (update_ride_status($ride_id, 'started')) {
            $_SESSION['success'] = 'Ride started successfully!';
        } else {
            $_SESSION['error'] = 'Failed to start ride. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'Invalid ride or ride not ready to start.';
    }
}

redirect('driver/rides.php');
?>
