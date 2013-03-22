<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

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
require_once 'protected/extensions/google-api-php-client/src/Google_Client.php';
require_once "protected/extensions/google-api-php-client/src/contrib/Google_CalendarService.php"; // for google calendar sync
require_once 'protected/extensions/google-api-php-client/src/contrib/Google_Oauth2Service.php'; // for google oauth login
date_default_timezone_set($timezone);

$client = new Google_Client();
$client->setApplicationName("X2Engine CRM");
// Visit https://code.google.com/apis/console to generate your
// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
$client->setClientId($admin->googleClientId);
$client->setClientSecret($admin->googleClientSecret);
$client->setRedirectUri( (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $this->createUrl('') );
//$client->setDeveloperKey('insert_your_developer_key');
$oauth2 = new Google_Oauth2Service($client);
$googleCalendar = new Google_CalendarService($client);

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
