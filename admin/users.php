<?php
require_once '../config/config.php';
$page_title = 'Manage Users - Car Rental Pro';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

$success = '';
$error = '';

// Handle user deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];
    
    // Check if user has any bookings
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $booking_count = $stmt->fetch()['count'];
    
    if ($booking_count > 0) {
        $error = 'Cannot delete user with existing bookings.';
    } else {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $success = 'User deleted successfully.';
        } else {
            $error = 'Failed to delete user.';
        }
    }
}

// Get all users with booking counts
$stmt = $db->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM bookings WHERE user_id = u.id) as booking_count,
           (SELECT SUM(total_price) FROM bookings WHERE user_id = u.id AND status = 'confirmed') as total_spent
    FROM users u 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="fw-bold mb-4">Manage Users</h1>
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
            <?php if (empty($users)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No Users Found</h3>
                    <p class="text-muted">No users have registered yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User Details</th>
                                <th>Contact Information</th>
                                <th>Bookings</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h6>
                                                <small class="text-muted">ID: #<?php echo $user['id']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <div><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></div>
                                            <?php if (!empty($user['phone'])): ?>
                                                <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($user['phone']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="fw-bold"><?php echo $user['booking_count']; ?></div>
                                            <small class="text-muted">bookings</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-center">
                                            <div class="fw-bold text-success"><?php echo format_price($user['total_spent'] ?: 0); ?></div>
                                            <small class="text-muted">total spent</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>admin/view_user.php?id=<?php echo $user['id']; ?>" 
                                               class="btn btn-outline-primary" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" 
                                               class="btn btn-outline-info" title="Send Email">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                            <?php if ($user['booking_count'] == 0): ?>
                                                <button class="btn btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['name']); ?>')" 
                                                        title="Delete User">
                                                    <i class="bi bi-trash"></i>
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
</div>

<script>
function confirmDelete(userId, userName) {
    if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
        window.location.href = '<?php echo BASE_URL; ?>admin/users.php?delete=' + userId;
    }
}
</script>

<?php include '../includes/footer.php'; ?>
