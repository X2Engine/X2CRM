<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

$this->pageTitle=Yii::app()->name . ' - Login';

?>
<div class="form" id="login-form">
<div class="cell">
    <?php echo CHtml::image(Yii::app()->baseUrl.'/images/x2engine_crm_login.png','X2Engine',array('id'=>'login-logo','width'=>74,'height'=>84)); ?>
</div>
<?php

Yii::app()->clientScript->registerCss('fixMenuShadow',"
#page .container {
	position:relative;
	z-index:2;
}
",'screen',CClientScript::POS_HEAD);

$admin=Admin::model()->findByPk(1);
if(isset($admin->googleIntegration) && $admin->googleIntegration=='1'){

$timezone = date_default_timezone_get();
require_once 'protected/extensions/google-api-php-client/src/apiClient.php';
require_once "protected/extensions/google-api-php-client/src/contrib/apiCalendarService.php"; // for google calendar sync
require_once 'protected/extensions/google-api-php-client/src/contrib/apiOauth2Service.php'; // for google oauth login
date_default_timezone_set($timezone);

$client = new apiClient();
$client->setApplicationName("X2Engine CRM");
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
$client->setClientId($admin->googleClientId);
$client->setClientSecret($admin->googleClientSecret);
$client->setRedirectUri( (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('') );
//$client->setDeveloperKey('insert_your_developer_key');
$oauth2 = new apiOauth2Service($client);
$googleCalendar = new apiCalendarService($client);

if (isset($_REQUEST['logout'])) {
  unset($_SESSION['access_token']);
}

if (isset($_GET['code']) && !isset($failure)) {
  $client->authenticate();
  $_SESSION['access_token'] = $client->getAccessToken();
  header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
}

if (isset($_SESSION['access_token'])) {
  $client->setAccessToken($_SESSION['access_token']);
}

if ($client->getAccessToken() && !isset($failure)) {
	$user = $oauth2->userinfo->get();

  // These fields are currently filtered through the PHP sanitize filters.
  // See http://www.php.net/manual/en/filter.filters.sanitize.php
  $email = filter_var($user['email'], FILTER_SANITIZE_EMAIL);

  // The access token may have been updated lazily.
  $_SESSION['token'] = $client->getAccessToken();
  // The access token may have been updated lazily.
  $_SESSION['access_token'] = $client->getAccessToken();
} else {
  $authUrl = $client->createAuthUrl();
}
if(!isset($authUrl) && !isset($failure)){
	$this->redirect('googleLogin');
}
?>
<?php  ?>
<div id="login-box">
    
	<div id="error-message">
	<?php
		if(isset($failure) && $failure=='email'){
			echo "A user with email address: <b>$email</b> was not found.  Please contact an administrator.";
			echo "<div><br /><a class='x2-button' href='".$this->createUrl('login')."'>Return To Login Screen</a></div>";
		}else{
			echo "Click the button below to log into X2Engine CRM with your Google ID.";
		}
	?>
	</div>
	<br />
	<a class='x2-button' href='<?php echo $authUrl;?>'>Login with Google ID</a>
</div>
<?php 

}else{
	?>
<div id="login-box">
	<div id="error-message">
		Google Integration is not enabled for this instance of X2Engine.  Please contact an administrator.
	</div>
	<br />
	<a class='x2-button' href='<?php echo $this->createUrl('login');?>'>Return to Login Screen</a>
</div>
<?php
} ?>

<div class="row" style="margin-top:10px;text-align:center;">
    <?php echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/google_icon.png" id="google-icon" /> '.Yii::t('app','Login with Google'),
            (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . 
            ((substr($_SERVER['HTTP_HOST'],0,4)=='www.')?substr($_SERVER['HTTP_HOST'],4):$_SERVER['HTTP_HOST']) . 
            $this->createUrl('/site/googleLogin'),array('class'=>'x2touch-link')); ?>
    <?php echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/mobile.png" id="mobile-icon" /> X2Touch Mobile',Yii::app()->getBaseUrl() . '/index.php/x2touch',array('class'=>'x2touch-link')); ?>
</div>
</div>
