<?php
require_once '../config/config.php';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $ride_id = $_GET['id'];
    $driver_id = $_SESSION['driver_id'];
    
    // Verify ride belongs to this driver and is assigned
    $stmt = $db->prepare("SELECT id, booking_id FROM rides WHERE id = ? AND driver_id = ? AND status = 'assigned'");
    $stmt->execute([$ride_id, $driver_id]);
    $ride = $stmt->fetch();
    
    if ($ride) {
        // Update ride status to rejected
        if (update_ride_status($ride_id, 'rejected')) {
            // Try to assign another driver to this booking
            $new_driver_id = assign_driver_to_booking($ride['booking_id']);
            
            if ($new_driver_id) {
                $_SESSION['success'] = 'Ride rejected. Another driver has been assigned.';
            } else {
                $_SESSION['success'] = 'Ride rejected successfully.';
            }
        } else {
            $_SESSION['error'] = 'Failed to reject ride. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'Invalid ride or ride not assigned to you.';
    }
}

redirect('driver/rides.php');
?>
