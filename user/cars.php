<?php
require_once '../config/config.php';
$page_title = 'Available Cars - Car Rental Pro';

// Get filter parameters
$search = isset($_GET['search']) ? clean_input($_GET['search']) : '';
$type = isset($_GET['type']) ? clean_input($_GET['type']) : '';
$max_price = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

// Build query
$query = "SELECT c.*, AVG(r.rating) as avg_rating FROM cars c LEFT JOIN reviews r ON c.id = r.car_id WHERE c.availability = 1";
$params = [];

if (!empty($search)) {
    $query .= " AND c.name LIKE ?";
    $params[] = "%$search%";
}

if (!empty($type)) {
    $query .= " AND c.type = ?";
    $params[] = $type;
}

if ($max_price > 0) {
    $query .= " AND c.price_per_day <= ?";
    $params[] = $max_price;
}

$query .= " GROUP BY c.id ORDER BY c.name";

// Get cars
$stmt = $db->prepare($query);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Get unique car types for filter
$types = $db->query("SELECT DISTINCT type FROM cars WHERE availability = 1 ORDER BY type")->fetchAll(PDO::FETCH_COLUMN);
?>

<?php include '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="fw-bold mb-4">Available Cars</h1>
        </div>
    </div>
    
    <!-- Search and Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search Cars</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="type" class="form-label">Car Type</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">All Types</option>
                            <?php foreach ($types as $car_type): ?>
                                <option value="<?php echo $car_type; ?>" <?php echo $type == $car_type ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($car_type); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="max_price" class="form-label">Max Price/Day</label>
                        <input type="number" class="form-control" id="max_price" name="max_price" 
                               placeholder="e.g., 100" value="<?php echo $max_price; ?>" min="0" step="10">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
                <?php if (!empty($search) || !empty($type) || $max_price > 0): ?>
                    <div class="mt-3">
                        <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Results Count -->
    <div class="row mb-3">
        <div class="col-12">
            <p class="text-muted">
                <?php echo count($cars); ?> car<?php echo count($cars) != 1 ? 's' : ''; ?> found
            </p>
        </div>
    </div>
    
    <!-- Cars Grid -->
    <div class="row g-4">
        <?php if (empty($cars)): ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                    <h3 class="mt-3">No cars found</h3>
                    <p class="text-muted">Try adjusting your search criteria or browse all available cars.</p>
                    <a href="<?php echo BASE_URL; ?>user/cars.php" class="btn btn-primary">View All Cars</a>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($cars as $car): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card car-card h-100">
                        <span class="badge bg-success position-absolute top-0 end-0 m-2">Available</span>
                        
                        <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $car['image']; ?>" 
                             class="card-img-top" alt="<?php echo $car['name']; ?>">
                        
                        <div class="card-body">
                            <h5 class="card-title car-name"><?php echo htmlspecialchars($car['name']); ?></h5>
                            <p class="text-muted car-type">
                                <i class="bi bi-tag"></i> <?php echo htmlspecialchars($car['type']); ?>
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="car-price"><?php echo format_price($car['price_per_day']); ?></div>
                                <div class="text-muted small">per day</div>
                            </div>
                            
                            <div class="mb-3">
                                <div data-rating="<?php echo get_average_rating($car['id']); ?>"></div>
                                <small class="text-muted">
                                    <?php 
                                    $avg_rating = get_average_rating($car['id']);
                                    echo $avg_rating > 0 ? "$avg_rating out of 5" : "No ratings yet";
                                    ?>
                                </small>
                            </div>
                            
                            <p class="card-text small text-muted">
                                <?php echo substr(htmlspecialchars($car['description']), 0, 80); ?>...
                            </p>
                            
                            <div class="d-grid gap-2">
                                <a href="<?php echo BASE_URL; ?>user/car_details.php?id=<?php echo $car['id']; ?>" 
                                   class="btn btn-outline-primary">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                                <?php if (is_logged_in()): ?>
                                    <a href="<?php echo BASE_URL; ?>user/book_car.php?id=<?php echo $car['id']; ?>" 
                                       class="btn btn-primary">
                                        <i class="bi bi-calendar-plus"></i> Book Now
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo BASE_URL; ?>user/login.php" class="btn btn-primary">
                                        <i class="bi bi-box-arrow-in-right"></i> Login to Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
