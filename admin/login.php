<?php
require_once '../config/config.php';
$page_title = 'Admin Login - Car Rental Pro';

// Redirect if already logged in
if (is_admin_logged_in()) {
    redirect('admin/dashboard.php');
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
        
        // Authenticate admin
        if (empty($errors)) {
            $stmt = $db->prepare("SELECT id, name, email, password FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                $_SESSION['admin_email'] = $admin['email'];
                
                // Redirect to admin dashboard
                redirect('admin/dashboard.php');
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
                        <i class="bi bi-shield-lock text-danger" style="font-size: 3rem;"></i>
                        <h2 class="mt-3">Admin Login</h2>
                        <p class="text-muted">Access admin dashboard</p>
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
                        
                        <button type="submit" class="btn btn-danger w-100 btn-lg">Login as Admin</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p><a href="<?php echo BASE_URL; ?>" class="text-muted">← Back to Home</a></p>
                        <p class="text-muted small">Default: admin@carrental.com / password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
