<?php
require_once '../config/config.php';
$page_title = 'Driver Profile - Car Rental Pro';

// Check if driver is logged in
if (!is_driver_logged_in()) {
    redirect('driver/login.php');
}

$driver_id = $_SESSION['driver_id'];

// Get driver information
$stmt = $db->prepare("SELECT * FROM drivers WHERE id = ?");
$stmt->execute([$driver_id]);
$driver = $stmt->fetch();

// Get driver statistics
$rides = get_driver_rides($driver_id);
$total_rides = count($rides);
$completed_rides = 0;
$rejected_rides = 0;

foreach ($rides as $ride) {
    if ($ride['status'] == 'completed') {
        $completed_rides++;
    } elseif ($ride['status'] == 'rejected') {
        $rejected_rides++;
    }
}

$success_rate = $total_rides > 0 ? round(($completed_rides / $total_rides) * 100, 1) : 0;
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="fw-bold mb-4">My Profile</h1>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Profile Information -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                         style="width: 100px; height: 100px;">
                        <i class="bi bi-person-badge fs-1"></i>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($driver['name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($driver['email']); ?></p>
                    
                    <?php if ($driver['status'] == 'active'): ?>
                        <span class="badge bg-success">Active Driver</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactive</span>
                    <?php endif; ?>
                    
                    <hr>
                    
                    <div class="text-start">
                        <div class="mb-3">
                            <label class="form-label text-muted">License Number</label>
                            <p class="fw-medium"><?php echo htmlspecialchars($driver['license_number']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone Number</label>
                            <p class="fw-medium"><?php echo htmlspecialchars($driver['phone']); ?></p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Joined Date</label>
                            <p class="fw-medium"><?php echo date('F d, Y', strtotime($driver['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Performance Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-primary"><?php echo $total_rides; ?></div>
                                <div class="text-muted">Total Rides</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-success"><?php echo $completed_rides; ?></div>
                                <div class="text-muted">Completed</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-danger"><?php echo $rejected_rides; ?></div>
                                <div class="text-muted">Rejected</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="fs-2 fw-bold text-info"><?php echo $success_rate; ?>%</div>
                                <div class="text-muted">Success Rate</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Success Rate Progress Bar -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Success Rate</span>
                            <span class="fw-medium"><?php echo $success_rate; ?>%</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: <?php echo $success_rate; ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Activity</h5>
                        <a href="<?php echo BASE_URL; ?>driver/rides.php" class="btn btn-sm btn-outline-primary">View All Rides</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($rides)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-car-front text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No rides assigned yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php 
                            $recent_rides = array_slice($rides, 0, 5);
                            foreach ($recent_rides as $ride): 
                            ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">Ride #<?php echo str_pad($ride['id'], 6, '0', STR_PAD_LEFT); ?></h6>
                                            <p class="mb-1 text-muted">
                                                Customer: <?php echo htmlspecialchars($ride['user_name']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y h:i A', strtotime($ride['assigned_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <?php
                                            $status_class = 'status-' . str_replace('_', '-', $ride['status']);
                                            $status_text = str_replace('_', ' ', $ride['status']);
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo ucwords($status_text); ?>
                                            </span>
                                        </div>
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
