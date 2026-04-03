<?php
require_once '../config/config.php';
$page_title = 'Manage Cars - Car Rental Pro';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

$success = '';
$error = '';

// Handle car deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $car_id = $_GET['delete'];
    
    // Check if car has any bookings
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE car_id = ?");
    $stmt->execute([$car_id]);
    $booking_count = $stmt->fetch()['count'];
    
    if ($booking_count > 0) {
        $error = 'Cannot delete car with existing bookings.';
    } else {
        $stmt = $db->prepare("DELETE FROM cars WHERE id = ?");
        if ($stmt->execute([$car_id])) {
            $success = 'Car deleted successfully.';
        } else {
            $error = 'Failed to delete car.';
        }
    }
}

// Get all cars
$stmt = $db->query("SELECT * FROM cars ORDER BY created_at DESC");
$cars = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Manage Cars</h1>
                <a href="<?php echo BASE_URL; ?>admin/add_car.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Car
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
            <?php if (empty($cars)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No Cars Found</h3>
                    <p class="text-muted">Start by adding your first car to the system.</p>
                    <a href="<?php echo BASE_URL; ?>admin/add_car.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Add First Car
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Car Details</th>
                                <th>Type</th>
                                <th>Price/Day</th>
                                <th>Availability</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cars as $car): ?>
                                <tr>
                                    <td>
                                        <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $car['image']; ?>" 
                                             alt="<?php echo $car['name']; ?>" 
                                             style="width: 80px; height: 60px; object-fit: cover;" class="rounded">
                                    </td>
                                    <td>
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($car['name']); ?></h6>
                                            <small class="text-muted">ID: #<?php echo $car['id']; ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($car['type']); ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo format_price($car['price_per_day']); ?></strong>
                                    </td>
                                    <td>
                                        <?php if ($car['availability']): ?>
                                            <span class="badge bg-success">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div data-rating="<?php echo get_average_rating($car['id']); ?>"></div>
                                        <small class="text-muted"><?php echo get_average_rating($car['id']); ?>/5</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>admin/edit_car.php?id=<?php echo $car['id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <?php if ($car['availability']): ?>
                                                <a href="<?php echo BASE_URL; ?>admin/toggle_car_availability.php?id=<?php echo $car['id']; ?>" 
                                                   class="btn btn-outline-warning" title="Make Unavailable">
                                                    <i class="bi bi-pause-circle"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo BASE_URL; ?>admin/toggle_car_availability.php?id=<?php echo $car['id']; ?>" 
                                                   class="btn btn-outline-success" title="Make Available">
                                                    <i class="bi bi-play-circle"></i>
                                                </a>
                                            <?php endif; ?>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="confirmDelete(<?php echo $car['id']; ?>, '<?php echo htmlspecialchars($car['name']); ?>')" 
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
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

<script>
function confirmDelete(carId, carName) {
    if (confirm(`Are you sure you want to delete "${carName}"? This action cannot be undone.`)) {
        window.location.href = '<?php echo BASE_URL; ?>admin/cars.php?delete=' + carId;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
