<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-store, no-cache, must-revalidate');

$BADBOT_REDIRECT   = 'https://www.google.com';
$BLOCKED_REDIRECT  = '/privacy.html';
$BADBOT_FILE       = __DIR__ . '/badbots.txt';
$CIRCUIT_BREAKER   = __DIR__ . '/panel/data/circuit_breaker.json';

$TRUSTED_SHORTLINKS = [
    'bit.ly','goo.gl','tinyurl.com','t.co','fb.me','lnkd.in',
    'ow.ly','is.gd','buff.ly','adf.ly','j.mp','tr.im','cli.gs',
    'short.to','budurl.com','ping.fm','post.ly','just.as',
    'bkite.com','snipr.com','fic.kr','loopt.us','doiop.com',
    'short.ie','kl.am','wp.me','rubyurl.com','om.ly','to.ly',
    'bit.do','db.tt','qr.ae','cur.lv','ity.im','q.gs','po.st',
    'bc.vc','twitthis.com','u.to','j.gs','v.gd','rb.gy',
    'shorturl.at','cutt.ly','t.ly','vurl.io'
];

$TRUSTED_SEARCH = [
    'google.com','www.google.com','google.co','google.',
    'bing.com','www.bing.com','bing.',
    'yahoo.com','www.yahoo.com','search.yahoo.com',
    'duckduckgo.com','www.duckduckgo.com','ddg.gg',
    'yandex.com','www.yandex.com','yandex.ru',
    'baidu.com','www.baidu.com','baidu.',
    'ask.com','www.ask.com','aol.com','www.aol.com',
    'startpage.com','www.startpage.com',
    'qwant.com','www.qwant.com',
    'search.brave.com','brave.com'
];

$TRUSTED_SOCIAL = [];

$ALLOWED_COUNTRIES = [
    'AL','AT','BA','BE','BG','BY','CH','CZ','DE','DK','EE','ES','FI',
    'FR','GB','GR','HR','HU','IE','IS','IT','LT','LU','LV','MD','ME',
    'MK','NL','NO','PL','PT','RO','RS','RU','SE','SI','SK','UA','XK',
    'CA','US','AU','NZ','IL','BF','BI','BJ','BW','CD','CF','CG','CI',
    'ER','ET','GA','GH','GM','GN','GQ','KE','LR','LS','LY','MA','ML',
    'MR','MW','MZ','NA','NE','NG','RW','SD','SN','SO','SS','SZ','TD',
    'TG','TN','TZ','UG','ZA','ZM','ZW','CM','DJ','DZ','EG',
    'AE','AF','AM','AZ','BD','BH','BN','BT','CN','CY','GE','HK','ID',
    'IL','IN','IQ','IR','JO','JP','KG','KH','KR','KW','KZ','LA','LB',
    'LK','MM','MN','MO','MY','NP','OM','PH','PK','PS','QA','SA','SG',
    'SY','TH','TJ','TM','TR','TW','UZ','VN','YE'
];

$DENY_COUNTRIES = [

];

$LOCAL_RANGES = [
    'ES' => ['80.0.0.0/8','81.0.0.0/8','83.0.0.0/8','84.0.0.0/8','88.0.0.0/8','212.0.0.0/8','213.0.0.0/8','217.0.0.0/8','188.0.0.0/8','193.0.0.0/8','37.10.0.0/16','46.24.0.0/13','95.16.0.0/12','79.144.0.0/12'],
    'FR' => ['5.0.0.0/8','37.0.0.0/8','51.0.0.0/8','62.0.0.0/8','78.0.0.0/8','176.0.0.0/8','80.10.0.0/15','81.48.0.0/12','82.224.0.0/11','83.192.0.0/10','84.96.0.0/11','86.192.0.0/10','88.160.0.0/11','89.80.0.0/12','90.0.0.0/8','91.160.0.0/11','92.128.0.0/10','93.0.0.0/10','109.0.0.0/11','193.248.0.0/13','194.2.0.0/15'],
    'DE' => ['2.0.0.0/8','31.0.0.0/8','46.0.0.0/8','79.0.0.0/8','178.0.0.0/8','188.0.0.0/8','77.0.0.0/10','80.128.0.0/11','82.80.0.0/12','84.128.0.0/10','87.128.0.0/11','88.64.0.0/11','91.0.0.0/10','92.192.0.0/10','93.128.0.0/9','94.128.0.0/9','95.88.0.0/13','109.84.0.0/14','141.0.0.0/8','217.0.0.0/10'],
    'IT' => ['79.0.0.0/8','87.0.0.0/8','151.0.0.0/8','193.0.0.0/8','2.16.0.0/12','5.88.0.0/13','31.194.0.0/15','37.160.0.0/12','62.94.0.0/15','93.32.0.0/11','94.16.0.0/12','95.224.0.0/11','188.208.0.0/12'],
    'PT' => ['77.0.0.0/8','80.0.0.0/8','188.0.0.0/8','2.80.0.0/13','46.189.0.0/16','81.193.0.0/16','82.154.0.0/15','85.240.0.0/12','92.250.0.0/15','94.48.0.0/12','95.92.0.0/14','109.48.0.0/16','213.13.0.0/16'],
    'NL' => ['31.0.0.0/8','37.0.0.0/8','145.0.0.0/8','213.0.0.0/8','5.192.0.0/11','46.226.0.0/15','62.108.0.0/13','77.160.0.0/11','80.56.0.0/13','81.204.0.0/14','82.161.0.0/16','83.80.0.0/12','84.24.0.0/13','86.80.0.0/12','92.108.0.0/14'],
    'BE' => ['81.0.0.0/8','178.0.0.0/8','195.0.0.0/8','5.135.0.0/16','27.33.0.0/16','37.252.0.0/14','78.20.0.0/13','84.192.0.0/13','85.201.0.0/16','91.176.0.0/12','94.224.0.0/13','109.128.0.0/13','212.224.0.0/12'],
    'GB' => ['2.0.0.0/8','25.0.0.0/8','51.0.0.0/8','81.0.0.0/8','82.0.0.0/8','86.0.0.0/8','31.48.0.0/12','62.252.0.0/14','77.96.0.0/12','80.0.0.0/10','84.64.0.0/11','87.74.0.0/15','90.192.0.0/10','92.0.0.0/10','94.0.0.0/11','109.144.0.0/12','132.185.0.0/16','185.0.0.0/8','212.58.0.0/16','217.32.0.0/11'],
    'CH' => ['31.6.0.0/15','37.35.0.0/16','62.2.0.0/15','212.161.0.0/16','81.62.0.0/15','83.76.0.0/14','85.0.0.0/11','92.104.0.0/13','178.192.0.0/12','194.230.0.0/15'],
    'AT' => ['31.16.0.0/12','80.108.0.0/15','46.252.0.0/14','62.40.0.0/13','77.116.0.0/14','84.112.0.0/13','88.116.0.0/14','91.112.0.0/12','178.188.0.0/14','188.20.0.0/14'],
    'IE' => ['5.0.0.0/8','51.0.0.0/8','31.128.0.0/11','37.228.0.0/14','46.7.0.0/16','78.16.0.0/13','79.97.0.0/16','83.70.0.0/15','86.40.0.0/13','89.100.0.0/14','95.44.0.0/14','109.76.0.0/14','178.167.0.0/16'],
    'SE' => ['5.150.0.0/16','81.216.0.0/13','2.248.0.0/13','31.208.0.0/12','62.101.0.0/16','78.64.0.0/11','83.226.0.0/15','85.224.0.0/12','90.224.0.0/11','94.254.0.0/15','95.192.0.0/11','155.4.0.0/14','193.10.0.0/15','213.64.0.0/13'],
    'NO' => ['31.0.0.0/8','84.208.0.0/12','46.9.0.0/16','51.174.0.0/15','62.70.0.0/15','77.16.0.0/12','79.160.0.0/14','80.202.0.0/15','85.19.0.0/16','88.88.0.0/13','92.220.0.0/14','109.247.0.0/16','193.212.0.0/15'],
    'DK' => ['2.104.0.0/13','5.103.0.0/16','31.20.0.0/14','37.96.0.0/13','62.242.0.0/15','77.66.0.0/15','80.160.0.0/13','83.88.0.0/13','85.80.0.0/12','87.48.0.0/12','93.160.0.0/13','94.18.0.0/15','188.176.0.0/12','212.98.0.0/15'],
    'FI' => ['5.132.0.0/14','37.33.0.0/16','46.132.0.0/14','62.236.0.0/14','80.220.0.0/14','83.148.0.0/14','84.230.0.0/15','85.156.0.0/14','87.92.0.0/13','88.112.0.0/13','91.152.0.0/13','93.106.0.0/15','193.64.0.0/13','212.50.0.0/16'],
    'CA' => ['24.0.0.0/8','50.0.0.0/8','64.0.0.0/8','72.0.0.0/8','99.0.0.0/8','142.0.0.0/8','156.0.0.0/8','167.0.0.0/8','173.0.0.0/8','184.0.0.0/8','192.0.0.0/8'],
    'US' => ['3.0.0.0/8','4.0.0.0/8','6.0.0.0/8','7.0.0.0/8','8.0.0.0/8','9.0.0.0/8','11.0.0.0/8','12.0.0.0/8','13.0.0.0/8','15.0.0.0/8','16.0.0.0/8','17.0.0.0/8','18.0.0.0/8','20.0.0.0/8','23.0.0.0/8','24.0.0.0/8','26.0.0.0/8','27.0.0.0/8','28.0.0.0/8','29.0.0.0/8','30.0.0.0/8','32.0.0.0/8','34.0.0.0/8','35.0.0.0/8','40.0.0.0/8','44.0.0.0/8','45.0.0.0/8','47.0.0.0/8','48.0.0.0/8','50.0.0.0/8','52.0.0.0/8','54.0.0.0/8','63.0.0.0/8','64.0.0.0/8','65.0.0.0/8','66.0.0.0/8','67.0.0.0/8','68.0.0.0/8','69.0.0.0/8','70.0.0.0/8','71.0.0.0/8','72.0.0.0/8','73.0.0.0/8','74.0.0.0/8','75.0.0.0/8','76.0.0.0/8','96.0.0.0/8','97.0.0.0/8','98.0.0.0/8','99.0.0.0/8','104.0.0.0/8','107.0.0.0/8','108.0.0.0/8','142.0.0.0/8','143.0.0.0/8','144.0.0.0/8','157.0.0.0/8','158.0.0.0/8','159.0.0.0/8','160.0.0.0/8','161.0.0.0/8','162.0.0.0/8','163.0.0.0/8','164.0.0.0/8','165.0.0.0/8','166.0.0.0/8','167.0.0.0/8','168.0.0.0/8','169.0.0.0/8','170.0.0.0/8','172.0.0.0/8','173.0.0.0/8','174.0.0.0/8','184.0.0.0/8','198.0.0.0/8','199.0.0.0/8','204.0.0.0/8','205.0.0.0/8','206.0.0.0/8','207.0.0.0/8','208.0.0.0/8','209.0.0.0/8','216.0.0.0/8']
];

function json_exit(array $data) {
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}

function getClientIP(): string {
    $keys = ['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_CLIENT_IP','HTTP_X_FORWARDED','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED','REMOTE_ADDR'];
    foreach ($keys as $k) {
        if (!empty($_SERVER[$k])) {
            $ip = explode(',', $_SERVER[$k])[0];
            $ip = trim($ip);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function containsString(string $haystack, string $needle): bool {
    return strpos($haystack, $needle) !== false;
}

function isInWhitelist(string $url, array $whitelist): bool {
    foreach ($whitelist as $domain) {
        if (containsString($url, $domain)) return true;
    }
    return false;
}

function isTrustedReferrer(string $referrer, array $searchEngines, array $shortlinks, array $social): bool {
    if (empty($referrer)) return false;
    $refLower = strtolower($referrer);
    if (isInWhitelist($refLower, $searchEngines)) return true;
    if (isInWhitelist($refLower, $shortlinks)) return true;
    if (isInWhitelist($refLower, $social)) return true;
    return false;
}

function loadBadBots(string $file): array {
    if (!is_file($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_map('strtolower', array_map('trim', $lines));
}

function isBadBot(string $ua, array $badBots): bool {
    if ($ua === '' || strlen($ua) < 15) return true;
    $ua = strtolower($ua);
    if (isset($_SERVER['HTTP_SEC_CH_UA']) && containsString(strtolower($_SERVER['HTTP_SEC_CH_UA']), 'headless')) return true;
    if (!empty($_SERVER['HTTP_SEC_FETCH_MODE']) && $_SERVER['HTTP_SEC_FETCH_MODE'] === 'navigate' && empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) return true;
    foreach ($badBots as $bot) {
        if ($bot !== '' && containsString($ua, $bot)) return true;
    }
    return false;
}

function ipInCidr(string $ip, string $cidr): bool {
    if ($ip === '::1') return true;
    if (strpos($cidr, ':') !== false) return false;
    [$subnet, $bits] = explode('/', $cidr);
    $ip = ip2long($ip);
    $subnet = ip2long($subnet);
    $mask = -1 << (32 - (int)$bits);
    return ($ip & $mask) === ($subnet & $mask);
}

// Geo cache using JSON file
function geoCacheGet(string $ip): ?string {
    $cacheFile = __DIR__ . '/panel/data/geo_cache.json';
    if (!is_file($cacheFile)) return null;
    $data = json_decode(file_get_contents($cacheFile), true);
    if (!is_array($data)) return null;
    $entry = $data[$ip] ?? null;
    if (!$entry) return null;
    if (time() - $entry['ts'] > 86400) return null;
    return $entry['cc'];
}

function geoCacheSet(string $ip, string $cc): void {
    $cacheFile = __DIR__ . '/panel/data/geo_cache.json';
    $data = [];
    if (is_file($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if (!is_array($data)) $data = [];
    }
    $data[$ip] = ['cc' => $cc, 'ts' => time()];
    // Keep max 500 entries to avoid bloat
    if (count($data) > 500) {
        $data = array_slice($data, -500, null, true);
    }
    file_put_contents($cacheFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
}

function detectCountry(string $ip, array $localRanges): string {
    if ($ip === '127.0.0.1' || $ip === '::1') return 'Localhost';

    // Check geo cache first
    $cached = geoCacheGet($ip);
    if ($cached !== null) return $cached;

    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        $cc = strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
        geoCacheSet($ip, $cc);
        return $cc;
    }

    foreach ($localRanges as $cc => $ranges) {
        foreach ($ranges as $cidr) {
            if (ipInCidr($ip, $cidr)) {
                geoCacheSet($ip, $cc);
                return $cc;
            }
        }
    }

    // Retry with cURL for better reliability
    $cc = false;
    for ($i = 0; $i < 2; $i++) {
        $url = "http://ip-api.com/json/$ip?fields=status,countryCode";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($res && $httpCode === 200) {
            $j = json_decode($res, true);
            if (!empty($j['countryCode'])) {
                $cc = strtoupper($j['countryCode']);
                break;
            }
        }
        usleep(100000);
    }

    if ($cc) {
        geoCacheSet($ip, $cc);
        return $cc;
    }

    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return strtoupper(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    }

    return 'XX';
}

// Circuit breaker: track rapid repeat requests from same IP
function circuitBreakerCheck(string $ip): bool {
    $cbFile = __DIR__ . '/panel/data/circuit_breaker.json';
    $data = [];
    if (is_file($cbFile)) {
        $data = json_decode(file_get_contents($cbFile), true);
        if (!is_array($data)) $data = [];
    }
    $now = time();
    $entry = $data[$ip] ?? ['count' => 0, 'window_start' => $now, 'blocked_until' => 0];

    if ($entry['blocked_until'] > $now) return true;

    if ($now - $entry['window_start'] > 60) {
        $entry = ['count' => 0, 'window_start' => $now, 'blocked_until' => 0];
    }

    $entry['count']++;

    if ($entry['count'] > 30) {
        $entry['blocked_until'] = $now + 300;
        $entry['count'] = 0;
        $data[$ip] = $entry;
        file_put_contents($cbFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        return true;
    }

    $data[$ip] = $entry;
    file_put_contents($cbFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
    return false;
}

// Legacy compatibility: for submit.php includes
function get_country_info() {
    $ip = getClientIP();
    $cc = detectCountry($ip, $GLOBALS['LOCAL_RANGES']);
    $blocked = !in_array($cc, ['Localhost','TRUSTED'], true) && (!in_array($cc, $GLOBALS['ALLOWED_COUNTRIES'], true));
    return ['ip' => $ip, 'cc' => $cc, 'blocked' => $blocked, 'redirect' => $GLOBALS['BLOCKED_REDIRECT']];
}

// Only auto-execute when accessed directly (not when included)
$isDirectAccess = basename($_SERVER['PHP_SELF'] ?? '') === basename(__FILE__);
if (!$isDirectAccess) return;

$ip  = getClientIP();
$ua  = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ref = $_SERVER['HTTP_REFERER'] ?? '';

if (circuitBreakerCheck($ip)) {
    json_exit([
        'ip'      => $ip,
        'cc'      => 'BOT',
        'blocked' => true,
        'reason'  => 'rate_limited',
        'redirect'=> $BADBOT_REDIRECT
    ]);
}

$badBots = loadBadBots($BADBOT_FILE);

if (isBadBot($ua, $badBots)) {
    json_exit([
        'ip'      => $ip,
        'cc'      => 'BOT',
        'blocked' => true,
        'reason'  => 'bad_bot',
        'redirect'=> $BADBOT_REDIRECT
    ]);
}

if (isTrustedReferrer($ref, $TRUSTED_SEARCH, $TRUSTED_SHORTLINKS, $TRUSTED_SOCIAL)) {
    json_exit([
        'ip'      => $ip,
        'cc'      => 'TRUSTED',
        'blocked' => false,
        'source'  => 'trusted_referrer'
    ]);
}

$cc = detectCountry($ip, $LOCAL_RANGES);

if (in_array($cc, $DENY_COUNTRIES, true)) {
    json_exit([
        'ip'      => $ip,
        'cc'      => $cc,
        'blocked' => true,
        'reason'  => 'deny_country',
        'redirect'=> $BLOCKED_REDIRECT
    ]);
}

if (!in_array($cc, $ALLOWED_COUNTRIES, true) && $cc !== 'Localhost') {
    json_exit([
        'ip'      => $ip,
        'cc'      => $cc,
        'blocked' => true,
        'reason'  => 'not_allowed',
        'redirect'=> $BLOCKED_REDIRECT
    ]);
}

json_exit([
    'ip'      => $ip,
    'cc'      => $cc,
    'blocked' => false
]);
