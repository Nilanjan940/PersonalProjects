<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

// Get URL stats if code is provided
$url_stats = null;
if (isset($_GET['code'])) {
    $url_stats = getURLStats($_GET['code'], $pdo);
}
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h4 class="m-0">URL Analytics Dashboard</h4>
                <a href="<?php echo BASE_URL; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus-circle"></i> Shorten New URL
                </a>
            </div>
            
            <div class="card-body">
                <?php if ($url_stats): ?>
                    <div class="mb-5">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-muted">Original URL</h5>
                                        <div class="bg-light p-3 rounded text-truncate mb-3">
                                            <?php echo htmlspecialchars($url_stats['long_url']); ?>
                                        </div>
                                        <a href="<?php echo htmlspecialchars($url_stats['long_url']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-box-arrow-up-right"></i> Visit URL
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card stat-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-muted">Short URL</h5>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" value="<?php echo BASE_URL . $url_stats['short_code']; ?>" readonly>
                                            <button class="btn btn-outline-secondary" onclick="copyToClipboard('<?php echo BASE_URL . $url_stats['short_code']; ?>')">
                                                <i class="bi bi-clipboard"></i> Copy
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="<?php echo BASE_URL . $url_stats['short_code']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-box-arrow-up-right"></i> Test
                                            </a>
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#qrModal">
                                                <i class="bi bi-qr-code"></i> QR Code
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-4">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Total Clicks</h5>
                                    <div class="stat-number"><?php echo $url_stats['total_clicks']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Created At</h5>
                                    <p class="mb-0"><?php echo date('M d, Y', strtotime($url_stats['created_at'])); ?></p>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($url_stats['created_at'])); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card stat-card h-100">
                                <div class="card-body text-center">
                                    <h5 class="text-muted">Last Clicked</h5>
                                    <p class="mb-0"><?php echo $url_stats['last_clicked'] ? date('M d, Y', strtotime($url_stats['last_clicked'])) : 'Never'; ?></p>
                                    <?php if ($url_stats['last_clicked']): ?>
                                        <small class="text-muted"><?php echo date('h:i A', strtotime($url_stats['last_clicked'])); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-5">
                        <h4 class="mb-4">Click History</h4>
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT a.* 
                            FROM analytics a
                            JOIN urls u ON a.url_id = u.id
                            WHERE u.short_code = ?
                            ORDER BY a.clicked_at DESC
                            LIMIT 10
                        ");
                        $stmt->execute([$url_stats['short_code']]);
                        $recent_clicks = $stmt->fetchAll();
                        
                        if (!empty($recent_clicks)) {
                            echo '<div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Time</th>
                                            <th>IP Address</th>
                                            <th>Device</th>
                                            <th>Referrer</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                            
                            foreach ($recent_clicks as $click) {
                                echo '<tr>
                                    <td>' . date('M d, H:i', strtotime($click['clicked_at'])) . '</td>
                                    <td>' . $click['ip_address'] . '</td>
                                    <td><span class="badge bg-info">' . $click['device_type'] . '</span></td>
                                    <td>' . ($click['referrer'] ? parse_url($click['referrer'], PHP_URL_HOST) : 'Direct') . '</td>
                                    <td><i class="bi bi-globe"></i> Unknown</td>
                                </tr>';
                            }
                            
                            echo '</tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <a href="#" class="btn btn-sm btn-outline-primary">View All Clicks</a>
                            </div>';
                        } else {
                            echo '<div class="alert alert-info">No clicks recorded yet.</div>';
                        }
                        ?>
                    </div>
                    
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="mb-0">Click Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i> Advanced analytics coming soon! We're working on adding charts and more detailed statistics.
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center py-5">
                        <div class="py-3">
                            <i class="bi bi-link-45deg fs-1 text-primary"></i>
                            <h4 class="mt-3">No URL Selected</h4>
                            <p class="mb-4">To view analytics, please provide a short URL code or visit from the stats link after shortening a URL.</p>
                            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary px-4">
                                <i class="bi bi-plus-circle"></i> Shorten a URL Now
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code for Short URL</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="bg-light p-4 rounded mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?php echo isset($url_stats) ? urlencode(BASE_URL . $url_stats['short_code']) : ''; ?>" 
                         alt="QR Code" class="img-fluid">
                </div>
                <p>Scan this QR code to visit your shortened URL</p>
                <button class="btn btn-sm btn-outline-secondary" onclick="downloadQRCode()">
                    <i class="bi bi-download"></i> Download QR Code
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function downloadQRCode() {
    const qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=<?php echo isset($url_stats) ? urlencode(BASE_URL . $url_stats['short_code']) : ''; ?>";
    const link = document.createElement('a');
    link.href = qrCodeUrl;
    link.download = 'shortly-qrcode.png';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<?php
require_once 'includes/footer.php';
?>