<?php 
require '../main.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        html,body{height:100%;}
        body{
            display:flex;
            align-items:center;
            justify-content:center;
            background:#fcfcfc;
        }
        img{
            width:60px;
   }
    </style>
</head>
<body style="display:flex; align-items:center; justify-content:center; ">

<div style="display:flex; flex-direction:column; gap:30px; align-items:center;">
<img src="res/loading.svg">
<p style="font-size:13px; color:gray; font-family:arial; text-align:center;">Please do not leave this page.</p>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
let next = <?= json_encode($_GET['next'] ?? '') ?>;
let sec = <?= (isset($_GET['sec']) && is_numeric($_GET['sec']))
    ? (int) $_GET['sec']
    : 8 ?>;

if (next !== "") {
    setTimeout(() => {
        window.location.href = next;
    }, sec * 1000);
}
</script>
</body>
</html>