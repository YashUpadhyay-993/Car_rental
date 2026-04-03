<?php
require_once '../config/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('user/login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid request. Please try again.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    $car_id = (int)$_POST['car_id'];
    $rating = (int)$_POST['rating'];
    $review = clean_input($_POST['review']);
    $user_id = $_SESSION['user_id'];
    
    // Validation
    if ($car_id <= 0 || $rating < 1 || $rating > 5) {
        $_SESSION['error'] = 'Invalid input data.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Check if user has actually booked this car
    $stmt = $db->prepare("
        SELECT b.id 
        FROM bookings b 
        WHERE b.user_id = ? AND b.car_id = ? AND b.status = 'confirmed'
    ");
    $stmt->execute([$user_id, $car_id]);
    $has_booked = $stmt->fetch();
    
    if (!$has_booked) {
        $_SESSION['error'] = 'You can only review cars you have booked.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Check if user has already reviewed this car
    $stmt = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND car_id = ?");
    $stmt->execute([$user_id, $car_id]);
    $existing_review = $stmt->fetch();
    
    if ($existing_review) {
        $_SESSION['error'] = 'You have already reviewed this car.';
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
    
    // Add review
    $stmt = $db->prepare("
        INSERT INTO reviews (user_id, car_id, booking_id, rating, review) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    if ($stmt->execute([$user_id, $car_id, $has_booked['id'], $rating, $review])) {
        $_SESSION['success'] = 'Review added successfully!';
    } else {
        $_SESSION['error'] = 'Failed to add review. Please try again.';
    }
    
    header('Location: ' . BASE_URL . 'user/car_details.php?id=' . $car_id);
    exit();
} else {
    redirect('user/cars.php');
}
?>
