<?php
require_once '../config/config.php';
$page_title = 'Booking Successful - Car Rental Pro';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('user/login.php');
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('user/dashboard.php');
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get booking details with car information
$stmt = $db->prepare("
    SELECT b.*, c.name as car_name, c.image as car_image, c.type as car_type
    FROM bookings b
    JOIN cars c ON b.car_id = c.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->execute([$booking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect('user/dashboard.php');
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h1 class="fw-bold mb-3">Booking Successful!</h1>
                    <p class="lead text-muted mb-4">Your car rental booking has been received and is pending confirmation.</p>
                    
                    <!-- Booking Details -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Booking Details</h5>
                            
                            <div class="row text-start">
                                <div class="col-md-6 mb-3">
                                    <strong>Booking ID:</strong><br>
                                    #<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Status:</strong><br>
                                    <span class="badge status-pending"><?php echo ucfirst($booking['status']); ?></span>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <strong>Car:</strong><br>
                                    <?php echo htmlspecialchars($booking['car_name']); ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Type:</strong><br>
                                    <?php echo htmlspecialchars($booking['car_type']); ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <strong>Pickup:</strong><br>
                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?> at <?php echo date('h:i A', strtotime($booking['start_time'])); ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Return:</strong><br>
                                    <?php echo date('M d, Y', strtotime($booking['end_date'])); ?> at <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                </div>
                                
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <strong>Total Price:</strong>
                                        <strong class="text-primary"><?php echo format_price($booking['total_price']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Car Image -->
                    <div class="mb-4">
                        <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $booking['car_image']; ?>" 
                             alt="<?php echo $booking['car_name']; ?>" 
                             class="img-thumbnail" style="max-width: 300px;">
                    </div>
                    
                    <!-- Important Information -->
                    <div class="alert alert-info text-start">
                        <h6><i class="bi bi-info-circle"></i> Important Information:</h6>
                        <ul class="mb-0">
                            <li>Your booking is currently pending confirmation</li>
                            <li>You will receive an email once your booking is confirmed</li>
                            <li>Please arrive 15 minutes before your pickup time</li>
                            <li>Bring your driver's license and ID proof</li>
                            <li>Payment will be collected at the time of pickup</li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn btn-primary">
                            <i class="bi bi-speedometer2"></i> Go to Dashboard
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/booking_details.php?id=<?php echo $booking['id']; ?>" 
                           class="btn btn-outline-primary">
                            <i class="bi bi-eye"></i> View Full Details
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-outline-secondary">
                            <i class="bi bi-car-front"></i> Browse More Cars
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
