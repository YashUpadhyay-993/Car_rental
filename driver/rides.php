<?php
require_once '../config/config.php';
$page_title = 'My Rides - Car Rental Pro';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

$driver_id = $_SESSION['driver_id'];

// Get filter status
$status = isset($_GET['status']) ? clean_input($_GET['status']) : '';

// Get rides based on status
if (!empty($status)) {
    $rides = get_driver_rides($driver_id, $status);
} else {
    $rides = get_driver_rides($driver_id);
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="fw-bold mb-4">My Rides</h1>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?php echo empty($status) ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php">
                        All Rides
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'assigned' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php?status=assigned">
                        <i class="bi bi-hourglass-split"></i> Assigned
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'accepted' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php?status=accepted">
                        <i class="bi bi-check-circle"></i> Accepted
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'on_the_way' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php?status=on_the_way">
                        <i class="bi bi-truck"></i> On the Way
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'started' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php?status=started">
                        <i class="bi bi-play-circle"></i> Started
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'completed' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php?status=completed">
                        <i class="bi bi-flag-checkered"></i> Completed
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status == 'rejected' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>driver/rides.php?status=rejected">
                        <i class="bi bi-x-circle"></i> Rejected
                    </a>
                </li>
            </ul>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($rides)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No Rides Found</h3>
                    <p class="text-muted">
                        <?php 
                        if (!empty($status)) {
                            echo 'No ' . str_replace('_', ' ', $status) . ' rides found.';
                        } else {
                            echo 'No rides assigned to you yet.';
                        }
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Ride ID</th>
                                <th>Customer</th>
                                <th>Car</th>
                                <th>Pickup Location</th>
                                <th>Drop Location</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rides as $ride): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($ride['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
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
                                                 style="width: 50px; height: 40px; object-fit: cover;" class="rounded me-2">
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($ride['car_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($ride['type']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><?php echo htmlspecialchars($ride['pickup_location']); ?></div>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($ride['start_date'])); ?> 
                                                <?php echo date('h:i A', strtotime($ride['start_time'])); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($ride['drop_location']); ?></div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><?php echo date('M d, Y', strtotime($ride['end_date'])); ?></div>
                                            <small class="text-muted"><?php echo date('h:i A', strtotime($ride['end_time'])); ?></small>
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
                                                <a href="<?php echo BASE_URL; ?>driver/on_way_ride.php?id=<?php echo $ride['id']; ?>" 
                                                   class="btn btn-outline-warning" title="On the Way">
                                                    <i class="bi bi-truck"></i>
                                                </a>
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
