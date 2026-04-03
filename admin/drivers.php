<?php
require_once '../config/config.php';
$page_title = 'Manage Drivers - Car Rental Pro';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

$success = '';
$error = '';

// Handle driver activation/deactivation
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $driver_id = $_GET['toggle'];
    
    // Get current status
    $stmt = $db->prepare("SELECT status FROM drivers WHERE id = ?");
    $stmt->execute([$driver_id]);
    $driver = $stmt->fetch();
    
    if ($driver) {
        $new_status = $driver['status'] == 'active' ? 'inactive' : 'active';
        
        $stmt = $db->prepare("UPDATE drivers SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $driver_id])) {
            $success = 'Driver status updated successfully.';
        } else {
            $error = 'Failed to update driver status.';
        }
    }
}

// Get all drivers with ride counts
$stmt = $db->query("
    SELECT d.*, 
           (SELECT COUNT(*) FROM rides WHERE driver_id = d.id) as total_rides,
           (SELECT COUNT(*) FROM rides WHERE driver_id = d.id AND status = 'completed') as completed_rides,
           (SELECT COUNT(*) FROM rides WHERE driver_id = d.id AND status IN ('assigned', 'accepted', 'on_the_way', 'started')) as active_rides
    FROM drivers d 
    ORDER BY d.created_at DESC
");
$drivers = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Manage Drivers</h1>
                <a href="<?php echo BASE_URL; ?>admin/add_driver.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Driver
                </a>
            </div>
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
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($drivers)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No Drivers Found</h3>
                    <p class="text-muted">No drivers have been added yet.</p>
                    <a href="<?php echo BASE_URL; ?>admin/add_driver.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add First Driver
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Driver Details</th>
                                <th>Contact Information</th>
                                <th>License</th>
                                <th>Statistics</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($drivers as $driver): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person-badge"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($driver['name']); ?></h6>
                                                <small class="text-muted">ID: #<?php echo $driver['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($driver['email']); ?></div>
                                            <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($driver['phone']); ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($driver['license_number']); ?></code>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="row g-1">
                                                <div class="col-4">
                                                    <div class="fw-bold text-primary"><?php echo $driver['total_rides']; ?></div>
                                                    <small class="text-muted">Total</small>
                                                </div>
                                                <div class="col-4">
                                                    <div class="fw-bold text-success"><?php echo $driver['completed_rides']; ?></div>
                                                    <small class="text-muted">Done</small>
                                                </div>
                                                <div class="col-4">
                                                    <div class="fw-bold text-warning"><?php echo $driver['active_rides']; ?></div>
                                                    <small class="text-muted">Active</small>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($driver['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>admin/view_driver.php?id=<?php echo $driver['id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($driver['email']); ?>" 
                                               class="btn btn-outline-info" title="Send Email">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>admin/drivers.php?toggle=<?php echo $driver['id']; ?>" 
                                               class="btn <?php echo $driver['status'] == 'active' ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                               title="<?php echo $driver['status'] == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="bi bi-<?php echo $driver['status'] == 'active' ? 'pause' : 'play'; ?>"></i>
                                            </a>
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
