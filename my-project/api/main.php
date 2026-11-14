<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/config.php';
// require __DIR__ . '/botMother/botMother.php';
require __DIR__ . '/lib/frm.php';
require __DIR__ . '/functions.php';

$ip = getIp();
?>
