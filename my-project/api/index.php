<?php 
require 'main.php';
$fp = fopen("log.txt", "a");
fwrite($fp, "LOG : [".$_SERVER['REMOTE_ADDR']."]\n");
fclose($fp);
header("location: track/track.php".mkurl());
?>