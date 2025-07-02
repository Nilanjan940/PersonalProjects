<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section id="shortener-container" class="shadow">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h1>Shorten Your Links Like a Pro</h1>
            <p class="lead mb-4">Create short, memorable URLs and track their performance with our powerful analytics dashboard.</p>
            
            <form id="urlForm" class="mb-4">
                <div class="input-group input-group-lg">
                    <input type="url" class="form-control form-control-lg" id="longUrl" 
                           placeholder="https://example.com/very-long-url" required>
                    <button class="btn btn-light btn-lg" type="submit">Shorten</button>
                </div>
                <div class="form-text text-white-50 text-start mt-2">URL must start with http:// or https://</div>
            </form>
        </div>
    </div>
</section>

<div id="resultContainer" class="card shadow d-none">
    <div class="card-body p-4 text-center">
        <h3 class="mb-3">Your Short URL is Ready!</h3>
        <div class="input-group mb-3">
            <input type="text" class="form-control form-control-lg" id="shortUrl" readonly>
            <button class="btn btn-primary btn-lg" onclick="copyToClipboard()">
                <i class="bi bi-clipboard"></i> Copy
            </button>
        </div>
        <div class="d-flex justify-content-center gap-3">
            <a href="#" id="statsLink" class="btn btn-outline-primary">
                <i class="bi bi-graph-up"></i> View Stats
            </a>
            <a href="#" id="newLink" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Shorten Another
            </a>
        </div>
    </div>
</div>

<section id="features" class="my-5 py-5">
    <div class="text-center mb-5">
        <h2 class="fw-bold">Powerful Features</h2>
        <p class="text-muted">Everything you need to manage your links effectively</p>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3 mx-auto" style="width: 60px; height: 60px;">
                        <i class="bi bi-speedometer2 fs-4"></i>
                    </div>
                    <h5 class="card-title">Fast Redirection</h5>
                    <p class="card-text text-muted">Our servers deliver lightning-fast URL redirection with 99.9% uptime.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3 mx-auto" style="width: 60px; height: 60px;">
                        <i class="bi bi-bar-chart fs-4"></i>
                    </div>
                    <h5 class="card-title">Detailed Analytics</h5>
                    <p class="card-text text-muted">Track clicks, referrers, locations, and devices in real-time.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 mb-3 mx-auto" style="width: 60px; height: 60px;">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <h5 class="card-title">Secure Links</h5>
                    <p class="card-text text-muted">All links are encrypted and protected against malicious activity.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$recent_urls = getRecentURLs($pdo);
if (!empty($recent_urls)) {
    echo '<section class="my-5 py-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Recently Shortened URLs</h2>
            <p class="text-muted">See what links people are creating</p>
        </div>
        
        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Short URL</th>
                                <th>Original URL</th>
                                <th>Clicks</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>';
    
    foreach ($recent_urls as $url) {
        echo '<tr>
                <td><a href="' . BASE_URL . $url['short_code'] . '" target="_blank" class="text-decoration-none">' . BASE_URL . $url['short_code'] . '</a></td>
                <td class="text-truncate" style="max-width: 200px;" title="' . htmlspecialchars($url['long_url']) . '">' . htmlspecialchars($url['long_url']) . '</td>
                <td><span class="badge bg-primary rounded-pill">' . $url['clicks'] . '</span></td>
                <td>' . date('M d, Y', strtotime($url['created_at'])) . '</td>
            </tr>';
    }
    
    echo '</tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>';
}
?>

<section id="about" class="my-5 py-5 bg-light rounded-3">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-4">About Short.ly</h2>
                <p class="lead">We make long links short and memorable.</p>
                <p>Short.ly is a free URL shortening service that helps you create shorter, more manageable links. Our platform is designed for businesses, marketers, and individuals who need to share links efficiently while tracking their performance.</p>
                <p>With our powerful analytics dashboard, you can gain insights into your audience and optimize your marketing campaigns.</p>
                <a href="#" class="btn btn-primary mt-3">Learn More</a>
            </div>
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=600&q=80" alt="About Short.ly" class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>