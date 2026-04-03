<?php
require_once '../config/config.php';
$page_title = 'Add Car - Car Rental Pro';

// Check if admin is logged in
if (!is_admin_logged_in()) {
    redirect('admin/login.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $name = clean_input($_POST['name']);
        $type = clean_input($_POST['type']);
        $price_per_day = (float)$_POST['price_per_day'];
        $description = clean_input($_POST['description']);
        $availability = isset($_POST['availability']) ? 1 : 0;
        
        // Handle image upload
        $image = '';
        if (isset($_FILES['car_image']) && $_FILES['car_image']['error'] == 0) {
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_info = pathinfo($_FILES['car_image']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            if (in_array($file_extension, $allowed_types)) {
                // Create unique filename
                $image = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $file_extension;
                $upload_path = '../assets/images/' . $image;
                
                if (!move_uploaded_file($_FILES['car_image']['tmp_name'], $upload_path)) {
                    $errors[] = 'Failed to upload image. Please try again.';
                }
            } else {
                $errors[] = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF';
            }
        } else {
            $errors[] = 'Car image is required';
        }
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Car name is required';
        }
        
        if (empty($type)) {
            $errors[] = 'Car type is required';
        }
        
        if ($price_per_day <= 0) {
            $errors[] = 'Price per day must be greater than 0';
        }
        
        // Add car if no errors
        if (empty($errors)) {
            $stmt = $db->prepare("
                INSERT INTO cars (name, type, price_per_day, image, description, availability) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            if ($stmt->execute([$name, $type, $price_per_day, $image, $description, $availability])) {
                $_SESSION['success'] = 'Car added successfully!';
                redirect('admin/cars.php');
            } else {
                $errors[] = 'Failed to add car. Please try again.';
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Add New Car</h1>
                <a href="<?php echo BASE_URL; ?>admin/cars.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Cars
                </a>
            </div>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Car Name *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="type" class="form-label">Car Type *</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="Sedan" <?php echo isset($_POST['type']) && $_POST['type'] == 'Sedan' ? 'selected' : ''; ?>>Sedan</option>
                                    <option value="SUV" <?php echo isset($_POST['type']) && $_POST['type'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                                    <option value="Luxury" <?php echo isset($_POST['type']) && $_POST['type'] == 'Luxury' ? 'selected' : ''; ?>>Luxury</option>
                                    <option value="Sports" <?php echo isset($_POST['type']) && $_POST['type'] == 'Sports' ? 'selected' : ''; ?>>Sports</option>
                                    <option value="Hatchback" <?php echo isset($_POST['type']) && $_POST['type'] == 'Hatchback' ? 'selected' : ''; ?>>Hatchback</option>
                                    <option value="Convertible" <?php echo isset($_POST['type']) && $_POST['type'] == 'Convertible' ? 'selected' : ''; ?>>Convertible</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="price_per_day" class="form-label">Price per Day ($) *</label>
                                <input type="number" class="form-control" id="price_per_day" name="price_per_day" 
                                       value="<?php echo isset($_POST['price_per_day']) ? htmlspecialchars($_POST['price_per_day']) : ''; ?>" 
                                       min="0" step="0.01" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="car_image" class="form-label">Car Image *</label>
                                <input type="file" class="form-control" id="car_image" name="car_image" 
                                       accept="image/*" required>
                                <div class="form-text">Allowed formats: JPG, JPEG, PNG, GIF (Max 5MB)</div>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="availability" name="availability" checked>
                                    <label class="form-check-label" for="availability">
                                        Available for Booking
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-circle"></i> Add Car
                                    </button>
                                    <a href="<?php echo BASE_URL; ?>admin/cars.php" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
