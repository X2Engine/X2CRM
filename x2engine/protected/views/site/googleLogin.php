<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




LoginThemeHelper::init();

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->getBaseUrl().'/css/login.css');

$credentials = Yii::app()->settings->getGoogleIntegrationCredentials ();

$this->pageTitle = Yii::app()->settings->appName.' - Login';
$admin = Admin::model()->findByPk(1);


$loginBoxHeight = 230;


if (X2_PARTNER_DISPLAY_BRANDING) {
    //$loginBoxHeight -= 36;
} 


Yii::app()->clientScript->registerCss('googleLogin', "

#login-box-outer {
    top: ".$loginBoxHeight."px;
}

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

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
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
<div id="login-box-outer">
<div class="container<?php echo (isset ($profileId) ? ' welcome-back-page' : ''); ?>" id="login-page">
<div id="login-box">
<div class="form" id="login-form">
    <?php 
    if (isset($admin->googleIntegration) && $admin->googleIntegration == '1' && 
        isset ($credentials)) { ?>
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
                      data-clientid="<?php echo trim($credentials['clientId']); ?>"
                      data-redirecturi="postmessage"
                      data-accesstype="offline"
                      data-cookiepolicy="single_host_origin"
                      data-callback="signInCallback">
                </span>
            </div>
            <div id="result"></div>
        </div>
        <?php } ?>
    <?php } else { ?>
        <div id="login-box">
            <div id="error-message">
                Google Integration is not enabled for this instance of X2Engine.  Please contact an administrator.
            </div>
            <br />
            <a class='x2-button' href='<?php echo $this->createUrl('login'); ?>'>Return to Login Screen</a>
        </div>
    <?php }
    ?>
</div>
</div>
</div>
<?php
$this->renderPartial ('loginCompanyInfo');
?>
</div>
<script type="text/javascript">
function signInCallback(authResult) {
  if (authResult.code) {
    // Hide the sign-in button now that the user is authorized, for example:
    $('#signinButton').attr('style', 'display: none');
    $('#result').html('<div><div class="loading-icon" style="vertical-align:middle;"></div> <span><b>Logging you in...</b></span></div>');
    // Send the code to the server
    var csrfToken = '<?php echo Yii::app()->request->getCsrfToken (); ?>';
    $.ajax({
      type: 'POST',
      url: 'storeToken',
      success: function(result) {
        window.location=window.location;
      },
      data: {
        code: authResult.code,
        YII_CSRF_TOKEN: csrfToken
      }
    });
  } else if (authResult.error) {
    // There was an error.
    // Possible error codes:
    //   "access_denied" - User denied access to your app
    //   "immediate_failed" - Could not automatially log in the user
    // console.log('There was an error: ' + authResult.error);
  }
}
</script>
