<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 text-center">
        <div class="card shadow">
            <div class="card-body py-5">
                <h1 class="display-1 text-danger">404</h1>
                <h2 class="mb-4">Page Not Found</h2>
                <p class="lead">The short URL you requested doesn't exist or has been removed.</p>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg mt-3">Go to Homepage</a>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>