    </main>

    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i> Short.ly</h5>
                    <p class="text-muted">Free URL shortener to create memorable and short links. Track clicks and analyze traffic.</p>
                    <div class="d-flex mt-4">
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-github fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo BASE_URL; ?>dashboard.php" class="text-muted text-decoration-none">Dashboard</a></li>
                        <li class="mb-2"><a href="#features" class="text-muted text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="#about" class="text-muted text-decoration-none">About</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Resources</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">API</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Documentation</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Help Center</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Status</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="mb-3">Subscribe</h5>
                    <p class="text-muted">Get the latest updates and news.</p>
                    <form class="mt-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-primary" type="button">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="my-4 bg-secondary">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> Short.ly. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>