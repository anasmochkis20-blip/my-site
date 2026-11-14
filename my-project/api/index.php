<?php
$baseDir = __DIR__ . '/netflix/netflix';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    $file = $baseDir . '/index.php';
} else {
    $file = $baseDir . $uri;
}

if (file_exists($file) && !is_dir($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2'
    ];

    if ($ext !== 'php') {
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        readfile($file);
        exit;
    }

    chdir(dirname($file));
    require $file;
} else {
    chdir($baseDir);
    require $baseDir . '/index.php';
}
?>
