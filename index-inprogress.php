<?php 
if( preg_match('/index\.php\//',$_SERVER['REQUEST_URI'])) {
	header('Location: http://beta.x2engine.com');
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
	<meta charset="UTF-8" />
	<meta name="language" content="en" />
	<title>Installation In Progress</title>
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" type="text/css" href="themes/x2engine/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="themes/x2engine/css/main.css" />
	<link rel="stylesheet" type="text/css" href="themes/x2engine/css/form.css" />
	<link rel="stylesheet" type="text/css" href="themes/x2engine/css/install.css" />
<script type="text/javascript">
		
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-38081192-1']);
	_gaq.push(['_trackPageview']);
		
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
		
</script>

	<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
	<script type="text/javascript" src="js/backgroundImage.js"></script>
    </head>
    <body>
	<!--<img id="bg" src="uploads/defaultBg.jpg" alt="">-->
	<div id="installer-box">
	    <img src="images/x2engine_crm_pla.png" alt="X2Engine" id="installer-logo" style="width:80px;height:71px">
	    <h2 style="clear:none">The demo server is currently refreshing</h2>
		<p>Please wait 10-20 seconds before trying again, or <a href="http://www.x2engine.com/trial-server/">set up a free trial</a>.</p>
	    <div id="footer">


		Copyright &copy; <?php echo date('Y'); ?>&nbsp;<a href="http://www.x2engine.com">X2Engine Inc.</a><br /> 
All Rights Reserved
	    </div>
	</div>  
    </body>
</html>
