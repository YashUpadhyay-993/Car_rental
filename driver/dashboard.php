<?php
require_once '../config/config.php';
$page_title = 'Driver Dashboard - Car Rental Pro';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

$driver_id = $_SESSION['driver_id'];

// Get driver statistics
$assigned_rides = get_driver_rides($driver_id, 'assigned');
$accepted_rides = get_driver_rides($driver_id, 'accepted');
$on_way_rides = get_driver_rides($driver_id, 'on_the_way');
$started_rides = get_driver_rides($driver_id, 'started');
$completed_rides = get_driver_rides($driver_id, 'completed');

// Count rides
$total_assigned = count($assigned_rides);
$total_accepted = count($accepted_rides);
$total_on_way = count($on_way_rides);
$total_started = count($started_rides);
$total_completed = count($completed_rides);
$total_rides = $total_assigned + $total_accepted + $total_on_way + $total_started + $total_completed;

// Get recent rides
$recent_rides = get_driver_rides($driver_id);
$recent_rides = array_slice($recent_rides, 0, 5);

// Get driver info
$stmt = $db->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Driver Dashboard</h1>
                <div>
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['driver_name']); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="dashboard-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_assigned; ?></div>
                    <div>New Assignments</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_accepted + $total_on_way + $total_started; ?></div>
                    <div>Active Rides</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dashboard-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_completed; ?></div>
                    <div>Completed Rides</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-primary mb-3">
                        <i class="bi bi-hourglass-split" style="font-size: 2rem;"></i>
                    </div>
                    <h5><?php echo $total_assigned; ?></h5>
                    <p class="text-muted mb-0">Pending Assignments</p>
                    <?php if ($total_assigned > 0): ?>
                        <a href="<?php echo BASE_URL; ?>driver/rides.php?status=assigned" class="btn btn-sm btn-outline-primary mt-2">View</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-success mb-3">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                    </div>
                    <h5><?php echo $total_accepted; ?></h5>
                    <p class="text-muted mb-0">Accepted Rides</p>
                    <?php if ($total_accepted > 0): ?>
                        <a href="<?php echo BASE_URL; ?>driver/rides.php?status=accepted" class="btn btn-sm btn-outline-success mt-2">View</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-warning mb-3">
                        <i class="bi bi-truck" style="font-size: 2rem;"></i>
                    </div>
                    <h5><?php echo $total_on_way + $total_started; ?></h5>
                    <p class="text-muted mb-0">In Progress</p>
                    <?php if (($total_on_way + $total_started) > 0): ?>
                        <a href="<?php echo BASE_URL; ?>driver/rides.php" class="btn btn-sm btn-outline-warning mt-2">View</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="text-info mb-3">
                        <i class="bi bi-flag-checkered" style="font-size: 2rem;"></i>
                    </div>
                    <h5><?php echo $total_completed; ?></h5>
                    <p class="text-muted mb-0">Completed</p>
                    <?php if ($total_completed > 0): ?>
                        <a href="<?php echo BASE_URL; ?>driver/rides.php?status=completed" class="btn btn-sm btn-outline-info mt-2">View</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Rides -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Rides</h5>
                <a href="<?php echo BASE_URL; ?>driver/rides.php" class="btn btn-sm btn-outline-primary">View All Rides</a>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($recent_rides)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No Rides Yet</h3>
                    <p class="text-muted">You haven't been assigned any rides yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Pickup</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_rides as $ride): ?>
                                <tr>
                                    <td>
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($ride['user_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($ride['user_phone']); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $ride['car_image']; ?>" 
                                                 alt="<?php echo $ride['car_name']; ?>" 
                                                 style="width: 40px; height: 30px; object-fit: cover;" class="rounded me-2">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($ride['car_name']); ?></div>
                                                <small class="text-muted"><?php echo date('M d, Y', strtotime($ride['start_date'])); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><?php echo date('M d, Y', strtotime($ride['start_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($ride['start_time'])); ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = 'status-' . str_replace('_', '-', $ride['status']);
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php 
                                            $status_text = str_replace('_', ' ', $ride['status']);
                                            echo ucwords($status_text); 
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>driver/ride_details.php?id=<?php echo $ride['id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php if ($ride['status'] == 'assigned'): ?>
                                                <a href="<?php echo BASE_URL; ?>driver/accept_ride.php?id=<?php echo $ride['id']; ?>" 
                                                   class="btn btn-outline-success" title="Accept Ride">
                                                    <i class="bi bi-check"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>driver/reject_ride.php?id=<?php echo $ride['id']; ?>" 
                                                   class="btn btn-outline-danger" title="Reject Ride"
                                                   onclick="return confirm('Are you sure you want to reject this ride?')">
                                                    <i class="bi bi-x"></i>
                                                </a>
                                            <?php elseif ($ride['status'] == 'accepted'): ?>
                                                <a href="<?php echo BASE_URL; ?>driver/start_ride.php?id=<?php echo $ride['id']; ?>" 
                                                   class="btn btn-outline-warning" title="Start Ride">
                                                    <i class="bi bi-play"></i>
                                                </a>
                                            <?php elseif ($ride['status'] == 'on_the_way'): ?>
                                                <a href="<?php echo BASE_URL; ?>driver/start_ride.php?id=<?php echo $ride['id']; ?>" 
                                                   class="btn btn-outline-warning" title="Start Ride">
                                                    <i class="bi bi-play"></i>
                                                </a>
                                            <?php elseif ($ride['status'] == 'started'): ?>
                                                <a href="<?php echo BASE_URL; ?>driver/complete_ride.php?id=<?php echo $ride['id']; ?>" 
                                                   class="btn btn-outline-success" title="Complete Ride">
                                                    <i class="bi bi-flag-checkered"></i>
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
