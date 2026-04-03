<?php
require_once '../config/config.php';
$page_title = 'Car Details - Car Rental Pro';

// Check if car ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('user/cars.php');
}

$car_id = $_GET['id'];

// Get car details
$stmt = $db->prepare("SELECT * FROM cars WHERE id = ? AND availability = 1");
$stmt->execute([$car_id]);
$car = $stmt->fetch();

if (!$car) {
    redirect('user/cars.php');
}

// Get car reviews with user names
$stmt = $db->prepare("
    SELECT r.*, u.name as user_name 
    FROM reviews r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.car_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$car_id]);
$reviews = $stmt->fetchAll();

// Get average rating
$avg_rating = get_average_rating($car_id);

// Check if user has already reviewed this car
$user_reviewed = false;
if (is_logged_in()) {
    $stmt = $db->prepare("SELECT id FROM reviews WHERE car_id = ? AND user_id = ?");
    $stmt->execute([$car_id, $_SESSION['user_id']]);
    $user_reviewed = $stmt->fetch() ? true : false;
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
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($car['name']); ?></li>
                </ol>
            </nav>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Car Image -->
        <div class="col-lg-6">
            <div class="card">
                <img src="<?php echo BASE_URL; ?>assets/images/<?php echo $car['image']; ?>" 
                     class="card-img-top" alt="<?php echo $car['name']; ?>" style="height: 400px; object-fit: cover;">
            </div>
        </div>
        
        <!-- Car Details -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($car['name']); ?></h1>
                    
                    <div class="mb-3">
                        <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($car['type']); ?></span>
                        <span class="badge bg-success fs-6 ms-2">Available</span>
                    </div>
                    
                    <div class="mb-4">
                        <h3 class="text-primary"><?php echo format_price($car['price_per_day']); ?></h3>
                        <p class="text-muted">per day</p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Description</h5>
                        <p><?php echo nl2br(htmlspecialchars($car['description'])); ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <h5>Rating & Reviews</h5>
                        <div class="d-flex align-items-center mb-2">
                            <div data-rating="<?php echo $avg_rating; ?>"></div>
                            <span class="ms-2 fw-bold"><?php echo $avg_rating; ?></span>
                            <span class="text-muted ms-2">(<?php echo count($reviews); ?> reviews)</span>
                        </div>
                    </div>
                    
                    <?php if (is_logged_in()): ?>
                        <a href="<?php echo BASE_URL; ?>user/book_car.php?id=<?php echo $car['id']; ?>" 
                           class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-calendar-plus"></i> Book This Car
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>user/login.php" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login to Book
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Customer Reviews</h4>
                        <?php if (is_logged_in() && !$user_reviewed): ?>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                <i class="bi bi-star"></i> Write Review
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-chat-square-text text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-3">No reviews yet. Be the first to review this car!</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($reviews as $review): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="border rounded p-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($review['user_name']); ?></h6>
                                                <div data-rating="<?php echo $review['rating']; ?>"></div>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                            </small>
                                        </div>
                                        <?php if (!empty($review['review'])): ?>
                                            <p class="mb-0"><?php echo htmlspecialchars($review['review']); ?></p>
                                        <?php endif; ?>
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

<!-- Review Modal -->
<?php if (is_logged_in() && !$user_reviewed): ?>
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Write a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?php echo BASE_URL; ?>user/add_review.php">
                <div class="modal-body">
                    <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="star-rating-input">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>" class="star-label">
                                    <i class="bi bi-star"></i>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="review" class="form-label">Your Review</label>
                        <textarea class="form-control" id="review" name="review" rows="4" placeholder="Share your experience with this car..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.star-rating-input {
    display: flex;
    gap: 5px;
}

.star-rating-input input[type="radio"] {
    display: none;
}

.star-rating-input .star-label {
    font-size: 1.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.star-rating-input input[type="radio"]:checked ~ .star-label,
.star-rating-input .star-label:hover,
.star-rating-input .star-label:hover ~ .star-label {
    color: #fbbf24;
}

.star-rating-input input[type="radio"]:checked + .star-label {
    color: #fbbf24;
}
</style>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
