<?php
require_once '../config/config.php';
$page_title = 'Admin Dashboard - Car Rental Pro';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

// Get statistics
$total_cars = $db->query("SELECT COUNT(*) as count FROM cars")->fetch()['count'];
$available_cars = $db->query("SELECT COUNT(*) as count FROM cars WHERE availability = 1")->fetch()['count'];
$total_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$total_bookings = $db->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'];
$pending_bookings = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'")->fetch()['count'];
$confirmed_bookings = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'")->fetch()['count'];
$cancelled_bookings = $db->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'")->fetch()['count'];

// Recent bookings
$stmt = $db->query("
    SELECT b.*, u.name as user_name, u.email as user_email, c.name as car_name 
    FROM bookings b 
    JOIN users u ON b.user_id = u.id 
    JOIN cars c ON b.car_id = c.id 
    ORDER BY b.created_at DESC 
    LIMIT 5
");
$recent_bookings = $stmt->fetchAll();

// Recent users
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// Monthly revenue (from confirmed bookings)
$stmt = $db->query("
    SELECT SUM(total_price) as revenue, MONTH(created_at) as month 
    FROM bookings 
    WHERE status = 'confirmed' AND YEAR(created_at) = YEAR(CURRENT_DATE)
    GROUP BY MONTH(created_at)
    ORDER BY month
");
$monthly_revenue = $stmt->fetchAll();

// Driver statistics
$total_drivers = $db->query("SELECT COUNT(*) as count FROM drivers")->fetch()['count'];
$active_drivers = $db->query("SELECT COUNT(*) as count FROM drivers WHERE status = 'active'")->fetch()['count'];
$total_rides = $db->query("SELECT COUNT(*) as count FROM rides")->fetch()['count'];
$completed_rides = $db->query("SELECT COUNT(*) as count FROM rides WHERE status = 'completed'")->fetch()['count'];
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Admin Dashboard</h1>
                <div>
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Cars</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_cars; ?></h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> <?php echo $available_cars; ?> available
                            </small>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-car-front-fill" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Users</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_users; ?></h3>
                            <small class="text-muted">Registered customers</small>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-people-fill" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Bookings</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_bookings; ?></h3>
                            <small class="text-warning">
                                <i class="bi bi-clock"></i> <?php echo $pending_bookings; ?> pending
                            </small>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-calendar-check-fill" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Monthly Revenue</h6>
                            <h3 class="fw-bold mb-0">
                                <?php 
                                $current_month_revenue = 0;
                                foreach ($monthly_revenue as $revenue) {
                                    if ($revenue['month'] == date('n')) {
                                        $current_month_revenue = $revenue['revenue'];
                                        break;
                                    }
                                }
                                echo format_price($current_month_revenue);
                                ?>
                            </h3>
                            <small class="text-muted">This month</small>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-cash-stack" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Driver Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Drivers</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_drivers; ?></h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> <?php echo $active_drivers; ?> active
                            </small>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-person-badge-fill" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-2">Total Rides</h6>
                            <h3 class="fw-bold mb-0"><?php echo $total_rides; ?></h3>
                            <small class="text-success">
                                <i class="bi bi-arrow-up"></i> <?php echo $completed_rides; ?> completed
                            </small>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-car-front-fill" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Booking Status Overview -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                    </div>
                    <h4><?php echo $pending_bookings; ?></h4>
                    <p class="text-muted mb-0">Pending Bookings</p>
                    <a href="<?php echo BASE_URL; ?>admin/bookings.php?status=pending" class="btn btn-sm btn-outline-warning mt-2">View All</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h4><?php echo $confirmed_bookings; ?></h4>
                    <p class="text-muted mb-0">Confirmed Bookings</p>
                    <a href="<?php echo BASE_URL; ?>admin/bookings.php?status=confirmed" class="btn btn-sm btn-outline-success mt-2">View All</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-danger mb-3">
                        <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                    </div>
                    <h4><?php echo $cancelled_bookings; ?></h4>
                    <p class="text-muted mb-0">Cancelled Bookings</p>
                    <a href="<?php echo BASE_URL; ?>admin/bookings.php?status=cancelled" class="btn btn-sm btn-outline-danger mt-2">View All</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Recent Bookings -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Bookings</h5>
                        <a href="<?php echo BASE_URL; ?>admin/bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_bookings)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No bookings yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>Customer</th>
                                        <th>Car</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_bookings as $booking): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <div>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($booking['user_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($booking['user_email']); ?></small>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($booking['car_name']); ?></td>
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
                                                    <a href="<?php echo BASE_URL; ?>admin/view_booking.php?id=<?php echo $booking['id']; ?>" 
                                                       class="btn btn-outline-primary" title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <?php if ($booking['status'] == 'pending'): ?>
                                                        <a href="<?php echo BASE_URL; ?>admin/confirm_booking.php?id=<?php echo $booking['id']; ?>" 
                                                           class="btn btn-outline-success" title="Confirm">
                                                            <i class="bi bi-check"></i>
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
        
        <!-- Recent Users -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Users</h5>
                        <a href="<?php echo BASE_URL; ?>admin/users.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($recent_users)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted">No users yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recent_users as $user): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M d', strtotime($user['created_at'])); ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
