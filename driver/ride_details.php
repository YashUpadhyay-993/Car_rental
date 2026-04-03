<?php
require_once '../config/config.php';
$page_title = 'Ride Details - Car Rental Pro';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

// Check if ride ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('driver/rides.php');
}

$ride_id = $_GET['id'];
$driver_id = $_SESSION['driver_id'];

// Get ride details
$stmt = $db->prepare("
    SELECT r.*, b.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.address as user_address,
           c.name as car_name, c.type as car_type, c.image as car_image
    FROM rides r
    JOIN bookings b ON r.booking_id = b.id
    JOIN users u ON b.user_id = u.id
    JOIN cars c ON b.car_id = c.id
    WHERE r.id = ? AND r.driver_id = ?
");
$stmt->execute([$ride_id, $driver_id]);
$ride = $stmt->fetch();

if (!$ride) {
    redirect('driver/rides.php');
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>driver/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>driver/rides.php">My Rides</a></li>
                    <li class="breadcrumb-item active">Ride Details</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Ride Information -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Ride Information</h5>
                        <div>
                            <?php
                            $status_class = 'status-' . str_replace('_', '-', $ride['status']);
                            $status_text = str_replace('_', ' ', $ride['status']);
                            ?>
                            <span class="badge <?php echo $status_class; ?> fs-6">
                                <?php echo ucwords($status_text); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Ride ID</label>
                            <p class="fw-bold">#<?php echo str_pad($ride['id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Booking ID</label>
                            <p class="fw-bold">#<?php echo str_pad($ride['booking_id'], 6, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Pickup Date</label>
                            <p><?php echo date('l, F d, Y', strtotime($ride['start_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Pickup Time</label>
                            <p><?php echo date('h:i A', strtotime($ride['start_time'])); ?></p>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-muted">Return Date</label>
                            <p><?php echo date('l, F d, Y', strtotime($ride['end_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Return Time</label>
                            <p><?php echo date('h:i A', strtotime($ride['end_time'])); ?></p>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted">Pickup Location</label>
                            <p class="fw-medium"><?php echo htmlspecialchars($ride['pickup_location']); ?></p>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted">Drop Location</label>
                            <p class="fw-medium"><?php echo htmlspecialchars($ride['drop_location']); ?></p>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label text-muted">Total Price</label>
                            <p class="fs-4 fw-bold text-primary"><?php echo format_price($ride['total_price']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto" 
                             style="width: 60px; height: 60px;">
                            <i class="bi bi-person fs-3"></i>
                        </div>
                    </div>
                    
                    <div class="text-center mb-3">
                        <h6 class="fw-bold"><?php echo htmlspecialchars($ride['user_name']); ?></h6>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Email</label>
                        <p><?php echo htmlspecialchars($ride['user_email']); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Phone</label>
                        <p><?php echo htmlspecialchars($ride['user_phone']); ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Address</label>
                        <p><?php echo htmlspecialchars($ride['user_address']); ?></p>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="tel:<?php echo htmlspecialchars($ride['user_phone']); ?>" class="btn btn-outline-primary">
                            <i class="bi bi-telephone"></i> Call Customer
                        </a>
                        <a href="mailto:<?php echo htmlspecialchars($ride['user_email']); ?>" class="btn btn-outline-info">
                            <i class="bi bi-envelope"></i> Email Customer
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Car Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Car Information</h5>
                </div>
                <div class="card-body">
                    <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $ride['car_image']; ?>" 
                         alt="<?php echo $ride['car_name']; ?>" 
                         class="img-fluid rounded mb-3" style="width: 100%; height: 150px; object-fit: cover;">
                    
                    <h6 class="fw-bold"><?php echo htmlspecialchars($ride['car_name']); ?></h6>
                    <p class="text-muted"><?php echo htmlspecialchars($ride['car_type']); ?></p>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Daily Rate:</span>
                        <strong><?php echo format_price($ride['price_per_day']); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-2">Ride Actions</h6>
                            <p class="text-muted mb-0">Update the status of this ride</p>
                        </div>
                        <div class="btn-group">
                            <?php if ($ride['status'] == 'assigned'): ?>
                                <a href="<?php echo BASE_URL; ?>driver/accept_ride.php?id=<?php echo $ride['id']; ?>" 
                                   class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Accept Ride
                                </a>
                                <a href="<?php echo BASE_URL; ?>driver/reject_ride.php?id=<?php echo $ride['id']; ?>" 
                                   class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this ride?')">
                                    <i class="bi bi-x-circle"></i> Reject Ride
                                </a>
                            <?php elseif ($ride['status'] == 'accepted'): ?>
                                <a href="<?php echo BASE_URL; ?>driver/on_way_ride.php?id=<?php echo $ride['id']; ?>" 
                                   class="btn btn-warning">
                                    <i class="bi bi-truck"></i> On the Way
                                </a>
                                <a href="<?php echo BASE_URL; ?>driver/start_ride.php?id=<?php echo $ride['id']; ?>" 
                                   class="btn btn-warning">
                                    <i class="bi bi-play-circle"></i> Start Ride
                                </a>
                            <?php elseif ($ride['status'] == 'on_the_way'): ?>
                                <a href="<?php echo BASE_URL; ?>driver/start_ride.php?id=<?php echo $ride['id']; ?>" 
                                   class="btn btn-warning">
                                    <i class="bi bi-play-circle"></i> Start Ride
                                </a>
                            <?php elseif ($ride['status'] == 'started'): ?>
                                <a href="<?php echo BASE_URL; ?>driver/complete_ride.php?id=<?php echo $ride['id']; ?>" 
                                   class="btn btn-success">
                                    <i class="bi bi-flag-checkered"></i> Complete Ride
                                </a>
                            <?php endif; ?>
                            
                            <a href="<?php echo BASE_URL; ?>driver/rides.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Rides
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
