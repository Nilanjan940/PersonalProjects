<?php
function generateShortCode($length = 6) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

function shortenURL($long_url, $pdo) {
    // Check if URL already exists
    $stmt = $pdo->prepare("SELECT short_code FROM urls WHERE long_url = ?");
    $stmt->execute([$long_url]);
    $result = $stmt->fetch();
    
    if ($result) {
        return BASE_URL . $result['short_code'];
    }
    
    // Generate a unique short code
    $short_code = generateShortCode();
    
    // Check if short code already exists (unlikely but possible)
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
    $stmt->execute([$short_code]);
    
    while ($stmt->rowCount() > 0) {
        $short_code = generateShortCode();
        $stmt->execute([$short_code]);
    }
    
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO urls (long_url, short_code, user_ip, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $long_url,
        $short_code,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
    
    return BASE_URL . $short_code;
}

function getLongURL($short_code, $pdo) {
    $stmt = $pdo->prepare("SELECT long_url FROM urls WHERE short_code = ?");
    $stmt->execute([$short_code]);
    $result = $stmt->fetch();
    
    if ($result) {
        // Update click count
        $stmt = $pdo->prepare("UPDATE urls SET clicks = clicks + 1 WHERE short_code = ?");
        $stmt->execute([$short_code]);
        
        // Record analytics
        recordAnalytics($short_code, $pdo);
        
        return $result['long_url'];
    }
    
    return false;
}

function recordAnalytics($short_code, $pdo) {
    // Get URL ID
    $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
    $stmt->execute([$short_code]);
    $url = $stmt->fetch();
    
    if (!$url) return;
    
    // Simple device detection
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $device_type = 'Desktop';
    
    if (strpos($user_agent, 'Mobile') !== false) {
        $device_type = 'Mobile';
    } elseif (strpos($user_agent, 'Tablet') !== false) {
        $device_type = 'Tablet';
    }
    
    // Insert analytics data
    $stmt = $pdo->prepare("INSERT INTO analytics (url_id, referrer, ip_address, device_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $url['id'],
        $_SERVER['HTTP_REFERER'] ?? '',
        $_SERVER['REMOTE_ADDR'],
        $device_type
    ]);
}

function getURLStats($short_code, $pdo) {
    $stmt = $pdo->prepare("
        SELECT u.*, 
               COUNT(a.id) as total_clicks,
               MAX(a.clicked_at) as last_clicked
        FROM urls u
        LEFT JOIN analytics a ON u.id = a.url_id
        WHERE u.short_code = ?
        GROUP BY u.id
    ");
    $stmt->execute([$short_code]);
    return $stmt->fetch();
}

function getRecentURLs($pdo, $limit = 5) {
    $limit = (int)$limit;
    $stmt = $pdo->prepare("SELECT * FROM urls ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}
?>