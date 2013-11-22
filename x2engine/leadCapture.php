<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/
?>

<?php
include('webLeadConfig.php');
$authData=array('user'=>$user,'userKey'=>$userKey);

if($url==""){
    $url=$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
    $url=substr($url,0,-15);
}
$email=$_POST['email'];
$date=mktime(0,0,0,date('m'),date('d'),date('Y'));
$count=preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email);
if($count==0){
    die("Invalid e-mail address!");
}

$ccUrl = $url.'/index.php/api/lookUp/model/Contacts';

$defaultOpts = array(
	CURLOPT_HTTP200ALIASES => array(400,401,403,404,500),
	CURLOPT_RETURNTRANSFER => 1
);

$ccSession = curl_init($ccUrl);
curl_setopt_array($ccSession,$defaultOpts);
curl_setopt_array($ccSession,array(
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => array_merge($authData,array('email'=>$email)),
));
$ccResult = curl_exec($ccSession);
$code = curl_getinfo($ccSession,CURLINFO_HTTP_CODE);
$response = $ccResult ? json_decode($ccResult,1) : false;

if($code == 200){ // update info
	$id = $response['id'];
	$newInfo = $response['backgroundInfo']."\n\n".$_POST['backgroundInfo'];
	$ccUrl = $url.'/index.php/api/update/model/Contacts/id/'.$id;
	$ccSession = curl_init($ccUrl);
	$data = array('backgroundInfo' => $newInfo, 'user' => $user, 'userKey' => $userKey);
	curl_setopt_array($ccSession, $defaultOpts);
	curl_setopt_array($ccSession, array(
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_POST => 1,
	));

	$ccResult = curl_exec($ccSession);
	$code = curl_getinfo($ccSession,CURLINFO_HTTP_CODE);
	curl_close($ccSession);
}else if($code == 404){
	$time = time();
	$data = array(
		'assignedTo' => 'Anyone',
		'visibility' => '1',
		'createDate' => $time,
		'lastUpdated' => $time,
		'updatedBy' => 'admin',
		'user' => $user,
		'userKey' => $userKey,
	);
	foreach($_POST as $field => $value){
		$data[$field] = $value;
	}

	$actionData = array(
		'type' => '',
		'actionDescription' => 'Web Lead',
		'assignedTo' => 'Anyone',
		'visibility' => '1',
		'dueDate' => $time,
		'associationType' => 'contacts',
		'associationId' => '',
		'associationName' => $data['firstName']." ".$data['lastName'],
		'priority' => 'High',
		'createDate' => $time,
		'lastUpdated' => $time,
		'updatedBy' => 'admin',
	);
	$ccUrl = $url.'/index.php/admin/getRoutingType';
	$ccSession = curl_init($ccUrl);
	curl_setopt($ccSession, CURLOPT_POST, 1);
	curl_setopt($ccSession, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
	$ccResult = curl_exec($ccSession);

	curl_close($ccSession);
	$data['assignedTo'] = $ccResult;
	$data['user'] = $user;
	$data['userKey'] = $userKey;
	$actionData['assignedTo'] = $ccResult;

	$ccUrl = $url.'/index.php/api/create/model/Contacts';
	$ccSession = curl_init($ccUrl);
	curl_setopt($ccSession, CURLOPT_POST, 1);
	curl_setopt($ccSession, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
	$ccResult = curl_exec($ccSession);
	curl_close($ccSession);

	$ccUrl = $url.'/index.php/api/lookUp/model/Contacts/email/'.$email;
	$ccSession = curl_init($ccUrl);
	curl_setopt($ccSession, CURLOPT_POST, 1);
	curl_setopt($ccSession, CURLOPT_POSTFIELDS, $authData);
	curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
	$ccResult = curl_exec($ccSession);

	$response = $ccResult ? json_decode($ccResult,1) : false;
	$id = $response['id'];

	$actionData['associationId'] = $id;

	curl_close($ccSession);

	$ccUrl = $url.'/index.php/api/create/model/Actions';
	$ccSession = curl_init($ccUrl);

	curl_setopt($ccSession, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ccSession, CURLOPT_POST, 1);
	curl_setopt($ccSession, CURLOPT_POSTFIELDS, $actionData);
	curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
	$ccResult = curl_exec($ccSession);
	curl_close($ccSession);

	if(!empty($photourl)){
		// save profile picture
		$ccUrl = $url.'/index.php/site/uploadProfilePicture';
		$postdata['photourl'] = $photourl;
		$postdata['type'] = 'contacts';
		$postdata['associationId'] = $id;
		$ccSession = curl_init($ccUrl);
		curl_setopt($ccSession, CURLOPT_POST, 1);
		curl_setopt($ccSession, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ccSession, CURLOPT_RETURNTRANSFER, 1);
		$ccResult = curl_exec($ccSession);
		curl_close($ccSession);
	}
}

?>
<html>
	<head></head>
	<body>
    <h1>
        Thank You!
    </h1>
    <p>Thank you for your interest!</p>
    <p>Someone will be in touch shortly.</p>
	</body>
</html>