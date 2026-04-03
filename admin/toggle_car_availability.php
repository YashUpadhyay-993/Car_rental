<?php
require_once '../config/config.php';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $car_id = $_GET['id'];
    
    // Get current availability
    $stmt = $db->prepare("SELECT availability FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch();
    
    if ($car) {
        // Toggle availability
        $new_availability = $car['availability'] ? 0 : 1;
        
        $stmt = $db->prepare("UPDATE cars SET availability = ? WHERE id = ?");
        if ($stmt->execute([$new_availability, $car_id])) {
            $_SESSION['success'] = 'Car availability updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update car availability.';
        }
    }
}

redirect('admin/cars.php');
?>
