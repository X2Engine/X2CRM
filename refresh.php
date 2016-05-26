<?php

// CONFIGURATION

// Test database:
$dbHost = '127.0.0.1';
$dbUser = 'testing';
$dbPass = 'aWd3VODVT67P2dNIkcw41mPub';
$dbName = 'demo';

// Paths:
$home = '/home/testing';
$path = "$home/public_html/builds/demo/x2engine"; // location of installation; root folder
$source = "$home/X2Development";

// Server:
$webHost = '52.33.121.218/builds/demo/x2engine';
$installScript = "http://$webHost/initialize.php";

// **comment these out on the live demo server (these are for testing on SC3)**
// $home = '/home/httpd';
// $path = "$home/public_html/DemoServerAutoScriptTest";
// $webHost = 'trial.x2engine.com';

// Source files:
$installScript="$path/initialize.php";

// INITIALIZE CONNECTION
mysql_connect($dbHost,$dbUser,$dbPass) or die('Could not connect: ' . mysql_error());

// COPY THE FILES OVER AND PATCH
system("rsync -aq --delete $source/x2engine/ $path");
system("cp -a $source/index-inprogress.php $path/index.php");
unlink("$path/install.php"); // Silly people, trying to access this script directly
system("cp -a $source/installConfig.php $path/installConfig.php");


// /home/beta50/BetaSource
// PURGE DATABASE
mysql_query("DROP DATABASE IF EXISTS $dbName") or die('Could not drop DB: ' . mysql_error());
mysql_query("CREATE DATABASE $dbName") or die('Could not create new DB: ' . mysql_error());
mysql_close();

// CALL THE INSTALLATION SCRIPT TO APPLY CHANGES
system("cd $path; php $installScript silent");
system("cp -a $source/x2engine/index.php $path/index.php");
system("mkdir -p $path/protected/runtime/cache");
system("chmod -R 777 $path/protected/runtime");

mysql_connect($dbHost,$dbUser,$dbPass) or die('Could not connect: ' . mysql_error());
mysql_select_db($dbName);
mysql_query("UPDATE x2_profile SET formSettings='{\"contacts\":[1,1,1,1,0,1]}'");
mysql_query("UPDATE x2_admin SET appDescription='X2Engine 5.0 Beta'");
mysql_query("INSERT INTO `x2_credentials` VALUES (1,'Chloe Greigo',1,1,1,'GMailAccount',1414118100,1414118275,'r94JIIi0dYYofIcl4aSE3ivXMaNqD2mRtKAc77cwkJJ+YDmDMmO9AKkDOmYlP4tJAtqBOVzncmZVrD4NGYBeFW7onQxJvZ2BnPwKmaBKeBq8QP2XoK13O4vIZNvqM9gIQQRTyxQ+XLepzvE8a0a70gUPMkLU8e53A8l8ZoN0o0lnoMkTfJ1Z+zaQyBNiCEAvYgWCNjIHH9MwnzVtHeh+JQJQl+yT2koZofukOBpwlC+UFPFhKd6hf3bBNAcWNCyx'),(2,'Support Demo',1,1,1,'GMailAccount',1414118169,1414118169,'iPyQ3xBBau6nDOUByPW5mesnt8YJgYJfJxmGUOxnH0EYxpw9QGziwCRVMCDZrvbZY2r6R+srvcR7agnBUjtE/AauIGRXsHtFCepraybNmZ1XgKhHCjCTTjMxf7IIavUy7I0NY/uO71Hi6SGc2K3RQwrhIq9gbkuPYa+DmUZyuzQO4Yes+KfzChmO2mk2+tBgNxHD3+xmVEgA9QUdqJm8Zc8FBXr8/0CV4/1ft2hOX/MHo7e/4PjOEXj8JJFhvKc7')");
mysql_query("INSERT INTO `x2_email_inboxes` (id, name, credentialId, shared, assignedTo, lastUpdated) VALUES (1,'My Inbox',1,0,'admin',1414118984),(2,'Support',2,1,'acarisella, apelletier, bto, coconner, admin, chames, kxu, ncordova, rpatel',1414119006);");
mysql_query('UPDATE x2_profile SET emailInboxes = \'["1","2"]\'');
mysql_query('UPDATE x2_modules SET menuPosition = menuPosition + 1;');
mysql_query('UPDATE x2_modules SET menuPosition = 0 WHERE name = "emailInboxes";');
system("cp -a $source/encryption.key $source/encryption.iv $path/protected/config/");
?>
