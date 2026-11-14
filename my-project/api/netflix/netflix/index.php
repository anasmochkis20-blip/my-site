<?php
$baseDir = __DIR__ . '/netflix/netflix';
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($uri === '/' || $uri === '') {
    $file = $baseDir . '/index.php';
} else {
    $file = $baseDir . $uri;
}

if (file_exists($file) && !is_dir($file)) {
    chdir(dirname($file));
    require $file;
} else {
    chdir($baseDir);
    require $baseDir . '/index.php';
}
?>
