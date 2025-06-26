document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Confirm actions
    document.querySelectorAll('.confirm-action').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirmMessage || 'Are you sure you want to perform this action?')) {
                e.preventDefault();
            }
        });
    });

    // Toggle sidebar
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }

    // Image preview for file uploads
    document.querySelectorAll('.image-upload').forEach(input => {
        input.addEventListener('change', function() {
            const preview = document.getElementById(this.dataset.previewId);
            const file = this.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        });
    });

    // Auto-slug generation for posts
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove non-word characters
                .replace(/\s+/g, '-')     // Replace spaces with -
                .replace(/--+/g, '-');     // Replace multiple - with single -
            slugInput.value = slug;
        });
    }

    // Initialize Select2 for tags
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#tags').select2({
            placeholder: 'Select tags',
            tags: true,
            tokenSeparators: [',', ' ']
        });
    }
});