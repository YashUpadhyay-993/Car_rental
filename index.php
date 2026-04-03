<?php
require_once 'config/config.php';
$page_title = 'Home - Car Rental Pro';

// Get featured cars
$stmt = $db->query("SELECT c.*, AVG(r.rating) as avg_rating FROM cars c LEFT JOIN reviews r ON c.id = r.car_id WHERE c.availability = 1 GROUP BY c.id ORDER BY c.created_at DESC LIMIT 6");
$featured_cars = $stmt->fetchAll();

// Get statistics
$total_cars = $db->query("SELECT COUNT(*) as count FROM cars WHERE availability = 1")->fetch()['count'];
$total_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch()['count'];
$total_bookings = $db->query("SELECT COUNT(*) as count FROM bookings")->fetch()['count'];
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4 fade-in">Find Your Perfect Ride</h1>
                <p class="lead mb-4 fade-in">Premium car rental service with affordable rates, well-maintained vehicles, and exceptional customer support.</p>
                <div class="d-flex gap-3 fade-in">
                    <?php if (!is_logged_in()): ?>
                        <a href="<?php echo BASE_URL; ?>user/register.php" class="btn btn-light btn-lg">Get Started</a>
                        <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-outline-light btn-lg">View Cars</a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-light btn-lg">Book Now</a>
                        <a href="<?php echo BASE_URL; ?>user/dashboard.php" class="btn btn-outline-light btn-lg">Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center fade-in">
                    <i class="bi bi-car-front" style="font-size: 12rem; opacity: 0.8;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="text-primary mb-3">
                        <i class="bi bi-car-front-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold"><?php echo $total_cars; ?></h3>
                    <p class="text-muted">Available Cars</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="text-success mb-3">
                        <i class="bi bi-people-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold"><?php echo $total_users; ?></h3>
                    <p class="text-muted">Happy Customers</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="text-warning mb-3">
                        <i class="bi bi-calendar-check-fill" style="font-size: 3rem;"></i>
                    </div>
                    <h3 class="fw-bold"><?php echo $total_bookings; ?></h3>
                    <p class="text-muted">Total Bookings</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Cars Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Featured Cars</h2>
            <p class="text-muted">Choose from our selection of premium vehicles</p>
        </div>
        
        <div class="row g-4">
            <?php if (empty($featured_cars)): ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No cars available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($featured_cars as $car): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card car-card h-100">
                            <?php if ($car['availability']): ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-2">Available</span>
                            <?php else: ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Unavailable</span>
                            <?php endif; ?>
                            
                            <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $car['image']; ?>" class="card-img-top" alt="<?php echo $car['name']; ?>">
                            
                            <div class="card-body">
                                <h5 class="card-title car-name"><?php echo htmlspecialchars($car['name']); ?></h5>
                                <p class="text-muted car-type"><?php echo htmlspecialchars($car['type']); ?></p>
                                
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="car-price"><?php echo format_price($car['price_per_day']); ?></div>
                                    <div class="text-muted small">/ day</div>
                                </div>
                                
                                <div class="mb-3">
                                    <div data-rating="<?php echo get_average_rating($car['id']); ?>"></div>
                                    <small class="text-muted"><?php echo get_average_rating($car['id']); ?> out of 5</small>
                                </div>
                                
                                <p class="card-text small text-muted"><?php echo substr(htmlspecialchars($car['description']), 0, 100); ?>...</p>
                                
                                <?php if (is_logged_in() && $car['availability']): ?>
                                    <a href="<?php echo BASE_URL; ?>user/book_car.php?id=<?php echo $car['id']; ?>" class="btn btn-primary w-100">Book Now</a>
                                <?php elseif (!is_logged_in()): ?>
                                    <a href="<?php echo BASE_URL; ?>user/login.php" class="btn btn-outline-primary w-100">Login to Book</a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100" disabled>Not Available</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-outline-primary btn-lg">View All Cars</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose Us</h2>
            <p class="text-muted">We provide the best car rental experience</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4 text-center">
                <div class="feature-icon mb-3">
                    <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                </div>
                <h4>Fully Insured</h4>
                <p class="text-muted">All our vehicles are fully insured for your peace of mind</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-icon mb-3">
                    <i class="bi bi-headset text-success" style="font-size: 3rem;"></i>
                </div>
                <h4>24/7 Support</h4>
                <p class="text-muted">Round-the-clock customer support for all your needs</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-icon mb-3">
                    <i class="bi bi-cash-coin text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4>Best Prices</h4>
                <p class="text-muted">Competitive pricing with no hidden charges</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
