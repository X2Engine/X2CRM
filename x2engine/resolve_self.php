<?php
$self = ((isset($_SERVER['HTTPS'])?$_SERVER['HTTPS']:false)?'https':'http').'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

header('Content-type: text/plain');
if(!extension_loaded('curl'))
    die('This script cannot run because the cURL PHP extension is not available.');

if(isset($_GET['selftest'])) {
    echo 42;
    exit;
}

echo "Making a web request to myself: $self\n";
$ch = curl_init($self.'?selftest=1');
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
$result = curl_exec($ch);
$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
$success = $code == 200;
if($success) {
    echo "Web request succeeded.\n";
    $success = trim($result) == '42';
    echo $success ? "Response matched expected content.\n" : "Response did not match expected content.\n";
} else {
    echo "Web request did not succeed.\n";
}

echo "Conclusion: " .(!$success ? "cannot locally resolve this server/virtual host from itself, so API commands must be run from a remote server." : "success! Can run API commands locally from this server to itself.");
