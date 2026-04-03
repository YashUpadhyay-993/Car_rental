<?php
require_once '../config/config.php';
$page_title = 'Driver Login - Car Rental Pro';

// Redirect if already logged in
if (is_driver_logged_in()) {
    redirect('driver/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email = clean_input($_POST['email']);
        $password = $_POST['password'];
        
        // Validation
        if (empty($email)) {
            $errors[] = 'Email is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        // Authenticate driver
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id, name, email, password, status FROM drivers WHERE email = ?");
            $stmt->execute([$email]);
            $driver = $stmt->fetch();
            
            if ($driver && password_verify($password, $driver['password'])) {
                // Check if driver is active
                if ($driver['status'] !== 'active') {
                    $errors[] = 'Your account is not active. Please contact admin.';
                } else {
                    // Login successful
                    $_SESSION['driver_id'] = $driver['id'];
                    $_SESSION['driver_name'] = $driver['name'];
                    $_SESSION['driver_email'] = $driver['email'];
                    
                    // Redirect to driver dashboard
                    redirect('driver/dashboard.php');
                }
            } else {
                $errors[] = 'Invalid email or password';
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-badge text-success" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Driver Login</h2>
                        <p class="text-muted">Access driver dashboard</p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100 btn-lg">Login as Driver</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p><a href="<?php echo BASE_URL; ?>" class="text-muted">← Back to Home</a></p>
                        <p class="text-muted small">Default drivers: john.driver@carrental.com / password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
