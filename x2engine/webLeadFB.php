<?php 
/**
 * Copyright 2011 by X2Engine Inc. All rights reserved. 
 * http://www.x2engine.com 
 * Confidential, X2Engine Inc. Scotts Valley, CA USA 
 */
?>
<html>
<head>

</head>
<body>

<!-- Facebook enabled Web Lead Form -->
<?php
$server = "http://".$_SERVER["SERVER_NAME"];
$file = end(explode("/", $_SERVER['SCRIPT_NAME']));
$directory = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], $file));
$captureURL = $server.$directory."leadCapture.php";
?>
<iframe src="http://x2single.com/webLead.php?captureURL=<?php echo $captureURL; ?>"
        height="400" width="200" scrolling="no">
</iframe>
<!-- end Web Lead Form -->

</body>
</html>

