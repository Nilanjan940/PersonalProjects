document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            trigger: 'hover'
        });
    });
    
    // URL Shortener Form
    const urlForm = document.getElementById('urlForm');
    if (urlForm) {
        urlForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const longUrl = document.getElementById('longUrl').value.trim();
            
            if (!isValidUrl(longUrl)) {
                showAlert('Please enter a valid URL starting with http:// or https://', 'danger');
                return;
            }
            
            // Show loading state
            const submitBtn = urlForm.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Shortening...';
            
            // Send AJAX request
            fetch('includes/config.php?action=shorten', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'long_url=' + encodeURIComponent(longUrl)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showShortUrlResult(data.short_url, data.short_code);
                } else {
                    showAlert('Error: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred. Please try again.', 'danger');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Shorten';
            });
        });
    }
    
    // New Link button
    const newLink = document.getElementById('newLink');
    if (newLink) {
        newLink.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('resultContainer').classList.add('d-none');
            document.getElementById('urlForm').classList.remove('d-none');
            document.getElementById('longUrl').value = '';
            document.getElementById('longUrl').focus();
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

function showShortUrlResult(shortUrl, shortCode) {
    const resultContainer = document.getElementById('resultContainer');
    const urlForm = document.getElementById('urlForm');
    const shortUrlInput = document.getElementById('shortUrl');
    const statsLink = document.getElementById('statsLink');
    
    shortUrlInput.value = shortUrl;
    statsLink.href = 'dashboard.php?code=' + shortCode;
    
    urlForm.classList.add('d-none');
    resultContainer.classList.remove('d-none');
    
    // Scroll to result
    resultContainer.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

function showAlert(message, type = 'info') {
    // Remove any existing alerts first
    const existingAlert = document.querySelector('.alert-dismissible');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    const container = document.querySelector('main .container') || document.querySelector('main');
    container.prepend(alertDiv);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
        alert.close();
    }, 5000);
}

function copyToClipboard(text = null) {
    const element = text ? text : document.getElementById('shortUrl');
    const value = text ? text : element.value;
    
    navigator.clipboard.writeText(value).then(() => {
        const btn = document.querySelector('.btn-outline-secondary, .btn-primary');
        if (btn) {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.add('copied');
            
            // Show tooltip
            const tooltip = bootstrap.Tooltip.getInstance(btn);
            if (tooltip) {
                tooltip.setContent({ '.tooltip-inner': 'Copied!' });
                tooltip.show();
            }
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('copied');
                if (tooltip) {
                    tooltip.setContent({ '.tooltip-inner': 'Copy to clipboard' });
                }
            }, 2000);
        }
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showAlert('Failed to copy text to clipboard', 'danger');
    });
}