</div>
<?php require_once __DIR__ . '/sidebar.php'; ?>

<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?php echo SITE_NAME; ?></h5>
                <p>A complete blog management system</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>" class="text-white">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/category.php" class="text-white">Categories</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>