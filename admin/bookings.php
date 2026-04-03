<?php
require_once '../config/config.php';
$page_title = 'Manage Bookings - Car Rental Pro';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

$success = '';
$error = '';

// Handle booking confirmation
if (isset($_GET['confirm']) && is_numeric($_GET['confirm'])) {
    $booking_id = $_GET['confirm'];
    
    $stmt = $db->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ? AND status = 'pending'");
    if ($stmt->execute([$booking_id])) {
        $success = 'Booking confirmed successfully.';
    } else {
        $error = 'Failed to confirm booking.';
    }
}

// Handle booking cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];
    
    $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    if ($stmt->execute([$booking_id])) {
        $success = 'Booking cancelled successfully.';
    } else {
        $error = 'Failed to cancel booking.';
    }
}

// Get filter status
$status = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Build query
$query = "
    SELECT b.*, u.name as user_name, u.email as user_email, c.name as car_name, c.image as car_image,
           d.name as driver_name, d.email as driver_email, r.status as ride_status, r.id as ride_id
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN cars c ON b.car_id = c.id
    LEFT JOIN rides r ON b.id = r.booking_id
    LEFT JOIN drivers d ON r.driver_id = d.id
";

if (!empty($status)) {
    $query .= " WHERE b.status = ?";
}

$query .= " ORDER BY b.created_at DESC";

// Get bookings
$stmt = !empty($status) ? $db->prepare($query) : $db->query($query);
if (!empty($status)) {
    $stmt->execute([$status]);
}
$bookings = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="fw-bold mb-4">Manage Bookings</h1>
        </div>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Filter Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?php echo empty($status) ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>admin/bookings.php">
                        All Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'pending' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>admin/bookings.php?status=pending">
                        <i class="bi bi-clock"></i> Pending
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'confirmed' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>admin/bookings.php?status=confirmed">
                        <i class="bi bi-check-circle"></i> Confirmed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'cancelled' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>admin/bookings.php?status=cancelled">
                        <i class="bi bi-x-circle"></i> Cancelled
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-calendar-x text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No Bookings Found</h3>
                    <p class="text-muted">
                        <?php 
                        if (!empty($status)) {
                            echo 'No ' . $status . ' bookings found.';
                        } else {
                            echo 'No bookings have been made yet.';
                        }
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Driver</th>
                                <th>Booking Period</th>
                                <th>Total Price</th>
                                <th>Status</th>
                                <th>Ride Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $booking['car_image']; ?>" 
                                                 alt="<?php echo $booking['car_name']; ?>" 
                                                 style="width: 40px; height: 30px; object-fit: cover;" class="rounded me-2">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($booking['car_name']); ?></div>
                                                <small class="text-muted">ID: #<?php echo $booking['car_id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($booking['driver_name']): ?>
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($booking['driver_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($booking['driver_email']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Not assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <div><?php echo date('M d, Y', strtotime($booking['start_date'])); ?></div>
                                            <small class="text-muted">
                                                <?php echo date('h:i A', strtotime($booking['start_time'])); ?> - 
                                                <?php echo date('h:i A', strtotime($booking['end_time'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?php echo format_price($booking['total_price']); ?></strong>
                                    </td>
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
                                        <?php if ($booking['ride_status']): ?>
                                            <?php
                                            $ride_status_class = 'status-' . str_replace('_', '-', $booking['ride_status']);
                                            $ride_status_text = str_replace('_', ' ', $booking['ride_status']);
                                            ?>
                                            <span class="badge <?php echo $ride_status_class; ?>">
                                                <?php echo ucwords($ride_status_text); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No Ride</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>admin/view_booking.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($booking['status'] == 'pending'): ?>
                                                <a href="<?php echo BASE_URL; ?>admin/bookings.php?confirm=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-outline-success" title="Confirm Booking"
                                                   onclick="return confirm('Are you sure you want to confirm this booking?')">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>admin/bookings.php?cancel=<?php echo $booking['id']; ?>" 
                                                   class="btn btn-outline-danger" title="Cancel Booking"
                                                   onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    <i class="bi bi-x"></i>
                                                </a>
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
</div>

<?php include '../includes/footer.php'; ?>
