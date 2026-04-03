// Car Rental System JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize star rating display
    initializeStarRatings();
    
    // Handle form submissions
    handleFormSubmissions();
    
    // Handle date validation
    handleDateValidation();
    
    // Handle image preview
    handleImagePreview();
});

// Star Rating Display
function initializeStarRatings() {
    const ratingElements = document.querySelectorAll('[data-rating]');
    
    ratingElements.forEach(element => {
        const rating = parseFloat(element.dataset.rating);
        const stars = generateStarHTML(rating);
        element.innerHTML = stars;
    });
}

function generateStarHTML(rating) {
    let html = '<div class="star-rating">';
    
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            html += '<i class="bi bi-star-fill star filled"></i>';
        } else if (i - 0.5 <= rating) {
            html += '<i class="bi bi-star-half star half-filled"></i>';
        } else {
            html += '<i class="bi bi-star star"></i>';
        }
    }
    
    html += '</div>';
    return html;
}

// Form Submission Handling
function handleFormSubmissions() {
    const forms = document.querySelectorAll('form[data-ajax]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> Processing...';
            
            // Simulate AJAX submission (replace with actual AJAX call)
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                
                // Show success message
                showAlert('Form submitted successfully!', 'success');
            }, 1500);
        });
    });
}

// Date Validation for Booking
function handleDateValidation() {
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    
    if (startDateInput && endDateInput) {
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        startDateInput.min = today;
        endDateInput.min = today;
        
        startDateInput.addEventListener('change', function() {
            endDateInput.min = this.value;
            
            if (endDateInput.value && endDateInput.value < this.value) {
                endDateInput.value = this.value;
            }
        });
    }
}

// Image Preview for File Uploads
function handleImagePreview() {
    const imageInput = document.getElementById('car_image');
    const previewContainer = document.getElementById('image_preview');
    
    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px;">
                        <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeImagePreview()">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    `;
                };
                
                reader.readAsDataURL(file);
            }
        });
    }
}

function removeImagePreview() {
    const imageInput = document.getElementById('car_image');
    const previewContainer = document.getElementById('image_preview');
    
    if (imageInput && previewContainer) {
        imageInput.value = '';
        previewContainer.innerHTML = '';
    }
}

// Alert System
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container') || createAlertContainer();
    
    const alertHTML = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    alertContainer.insertAdjacentHTML('beforeend', alertHTML);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.lastElementChild;
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

function createAlertContainer() {
    const container = document.createElement('div');
    container.id = 'alert-container';
    container.className = 'position-fixed top-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Utility Functions
function formatPrice(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price);
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// AJAX Helper Functions
async function makeRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('Request failed:', error);
        showAlert('Request failed. Please try again.', 'danger');
        throw error;
    }
}

// Car Search and Filter
function initializeCarSearch() {
    const searchInput = document.getElementById('car_search');
    const typeFilter = document.getElementById('type_filter');
    const priceFilter = document.getElementById('price_filter');
    
    if (searchInput || typeFilter || priceFilter) {
        const filterElements = [searchInput, typeFilter, priceFilter].filter(el => el);
        
        filterElements.forEach(element => {
            element.addEventListener('input', debounce(filterCars, 300));
        });
    }
}

function filterCars() {
    const searchValue = document.getElementById('car_search')?.value.toLowerCase() || '';
    const typeValue = document.getElementById('type_filter')?.value || '';
    const maxValue = document.getElementById('price_filter')?.value || '';
    
    const carCards = document.querySelectorAll('.car-card');
    
    carCards.forEach(card => {
        const name = card.querySelector('.car-name')?.textContent.toLowerCase() || '';
        const type = card.querySelector('.car-type')?.textContent || '';
        const price = parseFloat(card.querySelector('.car-price')?.textContent.replace(/[^0-9.]/g, '') || 0);
        
        let show = true;
        
        if (searchValue && !name.includes(searchValue)) {
            show = false;
        }
        
        if (typeValue && type !== typeValue) {
            show = false;
        }
        
        if (maxValue && price > parseFloat(maxValue)) {
            show = false;
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}

// Debounce function for search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Initialize car search if on cars page
if (document.getElementById('car_search') || document.getElementById('type_filter')) {
    initializeCarSearch();
}
