// Main JavaScript file for PharmacyDB

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    initTooltips();
    
    // Handle delete confirmations
    initDeleteConfirmations();
    
    // Form validation
    initFormValidation();
});

/**
 * Initialize tooltips for action buttons
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[title]');
    tooltips.forEach(el => {
        el.addEventListener('mouseenter', function(e) {
            // Add custom tooltip implementation if needed
        });
    });
}

/**
 * Add confirmation dialog for delete actions
 */
function initDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    markFieldInvalid(field);
                } else {
                    markFieldValid(field);
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Add real-time validation
    const inputs = document.querySelectorAll('input[required], select[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (!this.value.trim()) {
                markFieldInvalid(this);
            } else {
                markFieldValid(this);
            }
        });
        
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                markFieldValid(this);
            }
        });
    });
}

/**
 * Mark form field as invalid
 */
function markFieldInvalid(field) {
    field.style.borderColor = '#f56565';
    field.style.boxShadow = '0 0 0 3px rgba(245, 101, 101, 0.1)';
}

/**
 * Mark form field as valid
 */
function markFieldValid(field) {
    field.style.borderColor = '#48bb78';
    field.style.boxShadow = '0 0 0 3px rgba(72, 187, 120, 0.1)';
}

/**
 * Show notification message
 * @param {string} message - Message to display
 * @param {string} type - Type of notification (success, error, info)
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

/**
 * Format currency
 * @param {number} amount - Amount to format
 * @returns {string} Formatted currency string
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(amount);
}

/**
 * Toggle form visibility
 * @param {string} formId - ID of the form to toggle
 */
function toggleForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
}

/**
 * Load data with AJAX
 * @param {string} url - URL to fetch data from
 * @param {Function} callback - Callback function to handle data
 */
function loadData(url, callback) {
    fetch(url)
        .then(response => response.json())
        .then(data => callback(data))
        .catch(error => {
            console.error('Error loading data:', error);
            showNotification('Error loading data', 'error');
        });
}

// Add notification styles dynamically
const style = document.createElement('style');
style.textContent = `
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 10000;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
        max-width: 300px;
    }
    
    .notification-success {
        background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
    }
    
    .notification-error {
        background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
    }
    
    .notification-info {
        background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
    }
    
    .notification i {
        font-size: 1.2rem;
    }
`;
document.head.appendChild(style);