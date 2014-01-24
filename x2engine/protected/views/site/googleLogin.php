<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/login.css');

$this->pageTitle = Yii::app()->name.' - Login';
$admin = Admin::model()->findByPk(1);
Yii::app()->clientScript->registerCss('googleLogin', "
// fix menu shadow
#page .container {
	position:relative;
	z-index:2;
}

#google-login-logo {
    margin: 8px 10px 0 -5px;
}
", 'screen', CClientScript::POS_HEAD);
?>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js">
</script>
<script type="text/javascript">
    (function () {
      var po = document.createElement('script');
      po.type = 'text/javascript';
      po.async = true;
      po.src = 'https://plus.google.com/js/client:plusone.js?onload=start';
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(po, s);
    })();
</script>
<div class="container<?php echo (isset ($profileId) ? ' welcome-back-page' : ''); ?>" id="login-page">
<div id="login-box">
<div class="form" id="login-form">
    <div class="cell">
        <?php echo CHtml::image(Yii::app()->baseUrl.'/images/x2engine_crm_login.png', 'X2Engine', array('id' => 'google-login-logo', 'width' => 80, 'height' => 71)); ?>
    </div>
    <?php if(isset($admin->googleIntegration) && $admin->googleIntegration == '1'){ ?>
        <div id="login-box">
            <div id="error-message">
                <?php
                if(isset($failure) && $failure == 'email'){
                    echo "A user with email address: <b>$email</b> was not found.  Please contact an administrator.";
                    echo "<div><br /><a class='x2-button' href='".$this->createUrl('login')."'>Return To Login Screen</a></div>";
                }else{
                    echo "Click the button below to log into X2Engine CRM with your Google ID.";

                ?>
            </div>
            <br />
            <div id="signinButton">
                <span class="g-signin"
                      data-scope="https://www.googleapis.com/auth/plus.login
                      https://www.googleapis.com/auth/drive
                      https://www.googleapis.com/auth/userinfo.email
                      https://www.googleapis.com/auth/userinfo.profile
                      https://www.googleapis.com/auth/calendar
                      https://www.googleapis.com/auth/calendar.readonly"
                      data-clientid="<?php echo trim(Yii::app()->params->admin->googleClientId) ?>"
                      data-redirecturi="postmessage"
                      data-accesstype="offline"
                      data-cookiepolicy="single_host_origin"
                      data-callback="signInCallback">
                </span>
            </div>
            <div id="result"></div>
        </div>
        <?php } ?>
    <?php }else{ ?>
        <div id="login-box">
            <div id="error-message">
                Google Integration is not enabled for this instance of X2Engine.  Please contact an administrator.
            </div>
            <br />
            <a class='x2-button' href='<?php echo $this->createUrl('login'); ?>'>Return to Login Screen</a>
        </div>
    <?php }
    ?>

    <div class="row" style="margin-top:10px;text-align:center;">
        <?php
        echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/google_icon.png" id="google-icon" /> '.Yii::t('app', 'Login with Google'), (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://').
                ((substr($_SERVER['HTTP_HOST'], 0, 4) == 'www.') ? substr($_SERVER['HTTP_HOST'], 4) : $_SERVER['HTTP_HOST']).
                $this->createUrl('/site/googleLogin'), array('class' => 'x2touch-link'));
        ?>
        <?php echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/mobile.png" id="mobile-icon" /> X2Touch Mobile', Yii::app()->getBaseUrl().'/index.php/x2touch', array('class' => 'x2touch-link')); ?>
    </div>
</div>
</div>
</div>
<script type="text/javascript">
function signInCallback(authResult) {
  if (authResult['code']) {

    // Hide the sign-in button now that the user is authorized, for example:
    $('#signinButton').attr('style', 'display: none');
    $('#result').html('<div><div class="loading-icon" style="vertical-align:middle;"></div> <span><b>Logging you in...</b></span></div>');
    // Send the code to the server
    $.ajax({
      type: 'POST',
      url: 'storeToken',
      contentType: 'application/octet-stream; charset=utf-8',
      success: function(result) {
        window.location=window.location;
      },
      processData: false,
      data: authResult['code']
    });
  } else if (authResult['error']) {
    // There was an error.
    // Possible error codes:
    //   "access_denied" - User denied access to your app
    //   "immediate_failed" - Could not automatially log in the user
    // console.log('There was an error: ' + authResult['error']);
  }
}
</script>
