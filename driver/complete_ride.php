<?php
require_once '../config/config.php';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ride_id = $_GET['id'];
    $driver_id = $_SESSION['driver_id'];
    
    // Verify ride belongs to this driver and is started
    $stmt = $db->prepare("SELECT id FROM rides WHERE id = ? AND driver_id = ? AND status = 'started'");
    $stmt->execute([$ride_id, $driver_id]);
    $ride = $stmt->fetch();
    
    if ($ride) {
        // Update ride status to completed
        if (update_ride_status($ride_id, 'completed')) {
            $_SESSION['success'] = 'Ride completed successfully! Thank you for your service.';
        } else {
            $_SESSION['error'] = 'Failed to complete ride. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'Invalid ride or ride not started.';
    }
}

redirect('driver/rides.php');
?>
