// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);
});

// Confirm before delete actions
function confirmDelete(itemType) {
    return confirm(`Are you sure you want to delete this ${itemType}? This action cannot be undone.`);
}

// Toggle sidebar on mobile
document.getElementById('sidebarToggle').addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('active');
});

// Password strength indicator
function checkPasswordStrength(password) {
    let strength = 0;
    
    // Length check
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Contains numbers
    if (password.match(/\d/)) strength++;
    
    // Contains special chars
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    
    // Contains both lower and upper case
    if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
    
    return Math.min(strength, 4);
}

// Initialize password strength indicator
document.getElementById('password')?.addEventListener('input', function() {
    const strength = checkPasswordStrength(this.value);
    const indicator = document.getElementById('passwordStrength');
    if (indicator) {
        indicator.className = 'strength-' + strength;
    }
});