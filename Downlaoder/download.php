<?php
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get input data
$url = filter_input(INPUT_POST, 'url', FILTER_SANITIZE_URL);
$quality = filter_input(INPUT_POST, 'quality', FILTER_SANITIZE_STRING);
$platform = filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_STRING);

// Validate inputs
if (empty($url)) {
    http_response_code(400);
    echo json_encode(['error' => 'URL is required']);
    exit;
}

if (empty($platform) || !in_array($platform, ['youtube', 'instagram', 'facebook', 'twitter', 'whatsapp', 'tiktok'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Unsupported platform']);
    exit;
}

// In a real application, you would:
// 1. Validate the URL structure for each platform
// 2. Use appropriate APIs or libraries to fetch the media
// 3. Process the media based on quality selection
// 4. Provide the download to the user

// For this example, we'll simulate a successful download response
$response = [
    'status' => 'success',
    'message' => 'Download initiated',
    'data' => [
        'platform' => $platform,
        'quality' => $quality,
        'download_url' => '#' // In real app, this would be the actual download link
    ]
];

// Set appropriate headers for download
if (isset($_GET['direct'])) {
    // This would be the actual download handling code
    $filename = 'downloaded_media_' . time() . '.mp4';
    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary"); 
    header("Content-disposition: attachment; filename=\"" . $filename . "\""); 
    
    // In a real app, you would read the actual file or stream it
    // For this example, we'll just output a message
    echo "This would be the actual media file content for $url at $quality quality.";
    exit;
}

// Return JSON response for AJAX requests
echo json_encode($response);
?>