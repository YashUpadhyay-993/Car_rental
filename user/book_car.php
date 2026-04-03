<?php
require_once '../config/config.php';
$page_title = 'Book Car - Car Rental Pro';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('user/login.php');
}

// Check if car ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('user/cars.php');
}

$car_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Get car details
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ? AND availability = 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('user/cars.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF token validation
    if (!verify_csrf_token($_POST['csrf_token'])) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        // Validation
        if (empty($start_date) || empty($end_date) || empty($start_time) || empty($end_time)) {
            $errors[] = 'All fields are required';
        }
        
        if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
            $errors[] = 'Start date cannot be in the past';
        }
        
        if (strtotime($end_date) < strtotime($start_date)) {
            $errors[] = 'End date must be after start date';
        }
        
        if (strtotime($start_date) == strtotime($end_date) && strtotime($end_time) <= strtotime($start_time)) {
            $errors[] = 'End time must be after start time';
        }
        
        // Check for booking conflicts
        if (empty($errors)) {
            $stmt = $db->prepare("
                SELECT COUNT(*) as count 
                FROM bookings 
                WHERE car_id = ? 
                AND status != 'cancelled' 
                AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                )
            ");
            $stmt->execute([
                $car_id, 
                $start_date, $start_date,
                $end_date, $end_date,
                $start_date, $end_date
            ]);
            $conflict = $stmt->fetch()['count'];
            
            if ($conflict > 0) {
                $errors[] = 'Car is already booked for the selected dates';
            }
        }
        
        // Calculate total price
        if (empty($errors)) {
            $days = ceil((strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24)) + 1;
            $total_price = $days * $car['price_per_day'];
            
            // Create booking
            $stmt = $db->prepare("
                INSERT INTO bookings (user_id, car_id, start_date, end_date, start_time, end_time, total_price, status, driver_assigned, ride_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', FALSE, 'pending_driver')
            ");
            
            if ($stmt->execute([$user_id, $car_id, $start_date, $end_date, $start_time, $end_time, $total_price])) {
                $booking_id = $db->lastInsertId();
                
                // Assign driver automatically
                $driver_id = assign_driver_to_booking($booking_id);
                
                redirect('user/booking_success.php?id=' . $booking_id);
            } else {
                $errors[] = 'Booking failed. Please try again.';
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>user/cars.php">Cars</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>user/car_details.php?id=<?php echo $car_id; ?>"><?php echo htmlspecialchars($car['name']); ?></a></li>
                    <li class="breadcrumb-item active">Book Car</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Car Summary -->
        <div class="col-lg-4">
            <div class="card">
                <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $car['image']; ?>" 
                     class="card-img-top" alt="<?php echo $car['name']; ?>" style="height: 200px; object-fit: cover;">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($car['name']); ?></h5>
                    <p class="text-muted"><?php echo htmlspecialchars($car['type']); ?></p>
                    <h4 class="text-primary"><?php echo format_price($car['price_per_day']); ?></h4>
                    <p class="text-muted small">per day</p>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span>Rating:</span>
                        <div data-rating="<?php echo get_average_rating($car_id); ?>"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h4 class="mb-0">Complete Your Booking</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $error): ?>
                                <div><?php echo $error; ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="start_time" class="form-label">Pickup Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label">Return Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h5>Price Calculation</h5>
                            <div class="border rounded p-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Daily Rate:</span>
                                    <span><?php echo format_price($car['price_per_day']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Number of Days:</span>
                                    <span id="num_days">0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Price:</span>
                                    <span id="total_price" class="text-primary"><?php echo format_price(0); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-check-circle"></i> Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms Modal -->
<div class="modal fade" id="termsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>1. Booking Terms</h6>
                <p>All bookings are subject to availability and confirmation. A booking is only confirmed when you receive a confirmation email or notification.</p>
                
                <h6>2. Payment</h6>
                <p>Payment is required at the time of pickup. We accept cash, credit cards, and digital payments.</p>
                
                <h6>3. Cancellation Policy</h6>
                <p>Free cancellation up to 24 hours before pickup. Cancellations within 24 hours may incur a fee.</p>
                
                <h6>4. Driver Requirements</h6>
                <p>Driver must be at least 21 years old with a valid driver's license and ID proof.</p>
                
                <h6>5. Vehicle Care</h6>
                <p>Renter is responsible for any damage to the vehicle during the rental period. Insurance coverage is available.</p>
                
                <h6>6. Fuel Policy</h6>
                <p>Vehicle should be returned with the same fuel level as at the time of pickup.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
const dailyRate = <?php echo $car['price_per_day']; ?>;
const startDateInput = document.getElementById('start_date');
const endDateInput = document.getElementById('end_date');
const numDaysElement = document.getElementById('num_days');
const totalPriceElement = document.getElementById('total_price');

function calculatePrice() {
    const startDate = new Date(startDateInput.value);
    const endDate = new Date(endDateInput.value);
    
    if (startDate && endDate && endDate >= startDate) {
        const days = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
        const totalPrice = days * dailyRate;
        
        numDaysElement.textContent = days;
        totalPriceElement.textContent = formatPrice(totalPrice);
    } else {
        numDaysElement.textContent = '0';
        totalPriceElement.textContent = formatPrice(0);
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

startDateInput.addEventListener('change', function() {
    endDateInput.min = this.value;
    if (endDateInput.value && endDateInput.value < this.value) {
        endDateInput.value = this.value;
    }
    calculatePrice();
});

endDateInput.addEventListener('change', calculatePrice);

// Set minimum dates
const today = new Date().toISOString().split('T')[0];
startDateInput.min = today;
endDateInput.min = today;
</script>

<?php include '../includes/footer.php'; ?>
