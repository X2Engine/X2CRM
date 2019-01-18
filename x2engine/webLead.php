<?php 
/**
 * Copyright 2011 by X2Engine Inc. All rights reserved. 
 * http://www.x2engine.com 
 * Confidential, X2Engine Inc. Scotts Valley, CA USA 
 */
?><html>
<head>
<style type="text/css">

body {
	font-size:12px;
	font-family:Arial, Helvetica, sans-serif;
	width:189px;
}
html * {
	font-size:12px;
}
#contact-header{
color:white;
text-align:center;
font-size: 16px;
}
#submit{
position:absolute;
right:10px;
bottom:10px;
}

</style>
<script>
function clearText(thefield){
if (thefield.defaultValue==thefield.value)
thefield.value = ""
} 
</script>
</head>
<body>
<h3 id="contact-header">Contact Us:</h3>

<form name="leadCapture" action="leadCapture.php" method="POST">
    <div class="row"><b>First Name: *</b><br /> <input style="width:170px;" type="text" name="firstName" /><br /></div>
    <div class="row"><b>Last Name: *</b><br /> <input style="width:170px;" type="text" name="lastName" /><br /></div>
    <div class="row"><b>E-Mail Address: *</b><br /> <input style="width:170px;" type="text" name="email" /><br /></div>
    <div class="row"><b>Phone Number:</b><br /> <input style="width:170px;" type="text" name="phone" /><br /></div>
    <div class="row"><b>Interest:</b><br /> <textarea style="height:100px;width:170px;font-family:arial;font-size:10px;" name="backgroundInfo" onfocus="clearText(this);">Enter any additional information or questions regarding your interest here.
	</textarea><br /></div>
    <input id='submit' type="submit" value="Submit" />
    
</form>
</body>
</html>

