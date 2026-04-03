<?php
require_once '../config/config.php';
$page_title = 'Dashboard - Car Rental Pro';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('user/login.php');
}

// Get user statistics
$user_id = $_SESSION['user_id'];

// Total bookings
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_bookings = $stmt->fetch()['count'];

// Active bookings
$stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ? AND status = 'confirmed' AND end_date >= CURDATE()");
$stmt->execute([$user_id]);
$active_bookings = $stmt->fetch()['count'];

// Total spent
$stmt = $db->prepare("SELECT SUM(total_price) as total FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$stmt->execute([$user_id]);
$total_spent = $stmt->fetch()['total'] ?: 0;

// Recent bookings
$stmt = $db->prepare("
    SELECT b.*, c.name as car_name, c.image as car_image 
    FROM bookings b 
    JOIN cars c ON b.car_id = c.id 
    WHERE b.user_id = ? 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
                <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Book a Car
                </a>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="dashboard-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_bookings; ?></div>
                    <div>Total Bookings</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $active_bookings; ?></div>
                    <div>Active Bookings</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo format_price($total_spent); ?></div>
                    <div>Total Spent</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Bookings</h5>
                <a href="<?php echo BASE_URL; ?>user/bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($recent_bookings)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-3">No bookings yet. Book your first car now!</p>
                    <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-primary">Browse Cars</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Car</th>
                                <th>Booking Period</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $booking['car_image']; ?>" 
                                                 alt="<?php echo $booking['car_name']; ?>" 
                                                 style="width: 50px; height: 40px; object-fit: cover;" class="rounded me-3">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($booking['car_name']); ?></div>
                                                <small class="text-muted">ID: #<?php echo $booking['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo date('M d, Y', strtotime($booking['start_date'])); ?></div>
                                        <small class="text-muted">to <?php echo date('M d, Y', strtotime($booking['end_date'])); ?></small>
                                    </td>
                                    <td><?php echo format_price($booking['total_price']); ?></td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        switch($booking['status']) {
                                            case 'confirmed':
                                                $status_class = 'status-confirmed';
                                                break;
                                            case 'pending':
                                                $status_class = 'status-pending';
                                                break;
                                            case 'cancelled':
                                                $status_class = 'status-cancelled';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>user/booking_details.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($booking['status'] == 'confirmed' && strtotime($booking['start_date']) > time()): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="confirmCancel(<?php echo $booking['id']; ?>)" 
                                                        title="Cancel Booking">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-car-front text-primary" style="font-size: 2rem;"></i>
                    <h5 class="mt-3">Browse Cars</h5>
                    <p class="text-muted">Find your perfect ride from our collection</p>
                    <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-primary">View Cars</a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-success" style="font-size: 2rem;"></i>
                    <h5 class="mt-3">Booking History</h5>
                    <p class="text-muted">View all your past and current bookings</p>
                    <a href="<?php echo BASE_URL; ?>user/bookings.php" class="btn btn-success">View History</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmCancel(bookingId) {
    if (confirm('Are you sure you want to cancel this booking?')) {
        window.location.href = '<?php echo BASE_URL; ?>user/cancel_booking.php?id=' + bookingId;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
