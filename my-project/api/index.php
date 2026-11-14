<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "<h1>Site is working</h1>";
<?php 
require 'main.php';
$fp = fopen("log.txt", "a");
fwrite($fp, "LOG : [".$_SERVER['REMOTE_ADDR']."]\n");
fclose($fp);
header("location: track/track.php".mkurl());
?>
