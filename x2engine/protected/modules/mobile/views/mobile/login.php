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




//$this->pageTitle = Yii::app()->settings->appName . ' - Login';

//Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl.'/css/x2IconsStandalone.css');
//Yii::app()->clientScript->registerCssFile(
//    Yii::app()->controller->module->assetsUrl.'/css/login.css'
//);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->controller->module->assetsUrl.'/js/LoginController.js');


$hasProfile = false;
if(isset($_COOKIE['LoginForm'])) {
    $model->setAttributes($_COOKIE['LoginForm']);
    if (is_array ($_COOKIE['LoginForm']) &&
        in_array ('username', array_keys ($_COOKIE['LoginForm']))) {

        $username = $_COOKIE['LoginForm']['username'];
        $profile = Profile::model ()->findByAttributes (array (
            'username' => $username
        ));
        if ($profile) {
            $profileId = $profile->id;
            $fullName = $profile->fullName;
            $hasProfile = true;
        } 
    }
}

$this->onPageLoad ("
    x2.main.controllers['$this->pageId'] = new x2.LoginController ();
");

 
if (Yii::app()->params->isPhoneGap) {
?>
<div data-role='popup' id='settings-menu'>
    <ul data-role='listview' data-inset='true'>
        <li>
            <a class='change-web-address-button' href='#'><?php 
                echo CHtml::encode (Yii::t('mobile', 'Change web address')); ?></a>
        </li>
    </ul>
</div>
<?php
}
 


if ($model->hasErrors () && !isset($_COOKIE['sessionToken'])) {
    $title = Yii::t('mobile', 'Login Failed');
    if ($model->hasErrors ('verifyCode')) {
        $message = Yii::t(
            'mobile', 'The verification code your entered is incorrect.');
    } elseif ($model->password && $model->username) {
        $message = Yii::t(
            'mobile', 'The username or password you entered is incorrect. Please try again.');
    } elseif (!$model->password && !$model->username) {
        $message = Yii::t('mobile', 'Username and password cannot be left blank.');
    } else {
        $message = implode (' ', $model->getAllErrorMessages ());
    }
?>
<div class='error-dialog' style='display: none;'>
    <div class='title'><?php echo CHtml::encode ($title); ?></div>
    <div class='message'><?php echo CHtml::encode ($message); ?></div>
</div>
<?php
}

?>

<div class="form">
    <?php
    $form = $this->beginWidget('MobileActiveForm', 
            array(
                'id' => 'login-form',
                'enableClientValidation' => true,
                'method' => 'post',
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                ),    
            )
        );
    ?>
    <div class='logo-container'>
    <?php

     
    $loginLogo = Media::getLoginLogo ();
    if ($loginLogo) {
        echo CHtml::image(
            $loginLogo->getPublicUrl (),
            Yii::app()->settings->appName,
            array (
                'id' => 'custom-login-form-logo',
                'class' => 'login-logo',
            ));
    } else {
     
        echo X2Html::logo ('mobile', array (
            'id' => 'login-form-logo',
            'class' => 'login-logo',
        ));
     
    }
     
    ?>
    </div>
    
    <script type="text/javascript">

        function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            var expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + "; " + expires;
        }
        
        // If browser supports localStorage
        if(typeof(Storage) !== void(0)){
            var sessionToken = localStorage.getItem("sessionToken")
            if(sessionToken !== null ){
                setCookie("sessionToken",sessionToken,6);
            } else {

            }
        }
    </script>

    <div data-role="fieldcontain">
        <!--?php echo $form->label($model, 'username', array()); ?-->
        <?php 
        
        if(isset($_COOKIE['sessionToken'])){
            $sessionTokenCookie = $_COOKIE['sessionToken'];
            $sessionTokenModel = X2Model::model('SessionToken')->findByPk($sessionTokenCookie);
            $admin = &Yii::app()->settings;
            
            if($sessionTokenModel === null)
                unset(Yii::app()->request->cookies['sessionToken']);
            if ($admin->tokenPersist === 0) {
                if ($sessionTokenModel->lastUpdated + $admin->loginCredsTimeout >= time()) 
                    unset(Yii::app()->request->cookies['sessionToken']);
                
            }         
        }
        
        if(isset($_COOKIE['sessionToken'])) {
            $model->sessionToken = $_COOKIE['sessionToken'];
            echo $form->hiddenField ($model, 'sessionToken');
        }
        if ($hasProfile) $model->username = $profile->username;
        echo $form->textField($model, 'username', 
            array(
                'placeholder'=>Yii::t('app','Username')
            )
        ); 
        
        ?>
    </div>
    <div data-role="fieldcontain">
        <!--?php echo $form->label($model, 'password', array()); ?-->
        <?php 
        $settings = Yii::app()->settings;
        if(Yii::app()->settings->locationTrackingFrequency != null) {
            Yii::app()->request->cookies['locationTrackingFrequency'] = new CHttpCookie('locationTrackingFrequency', $settings->locationTrackingFrequency);
        }
        if(Yii::app()->settings->locationTrackingSwitch != null) {
            Yii::app()->request->cookies['locationTrackingSwitch'] = new CHttpCookie('locationTrackingSwitch', $settings->locationTrackingSwitch);
        }
        if(isset($_COOKIE['sessionToken'])) {

        } else {
            echo $form->passwordField($model, 'password', 
                array(
                    'placeholder'=>Yii::t('app','Password')
                )
            );             
        }

        ?>
    </div>

    <?php 
    if($model->useCaptcha && CCaptcha::checkRequirements()) { 
    ?>
        <div data-role="field contain" class='captcha-container'>
            <?php
            $this->widget('application.modules.mobile.components.MobileCaptcha',array(
                'clickableImage'=>true,
                'showRefreshButton'=>false,
                'imageOptions'=>array(
                    'style'=>'display:block;cursor:pointer;',
                    'title'=>Yii::t('app','Click to get a new image')
                )
            ));
            echo '<p class="hint">'.Yii::t('app','Please enter the letters in the image above.').'</p>';
            echo $form->textField($model,'verifyCode', array('style'=>'height:50px;'));
            ?>
        </div>
    <?php 
    } 
    ?>
    <div data-role="fieldcontain">
    <?php
        if(isset($_COOKIE['sessionToken'])) {
            $model = new LoginForm;
            $this->login ($model, true);
       
        } else {
            echo CHtml::submitButton(Yii::t('app', 'Sign in'), array (
                'class' => 'no-css-override',
            ));             
        }

    ?>
    </div>
    <div data-role="fieldcontain" class='password-help-container'>
        <a href='<?php echo $this->createAbsoluteUrl ('/site/mobileResetPassword'); ?>' 
         class='password-help'><?php 
            echo CHtml::encode (Yii::t('app', 'Forgot your password?')); ?>
        </a>
    </div>
    <?php
    if (!Yii::app()->params->isPhoneGap) {
    ?>
    <div data-role="fieldcontain" class='full-app-link'>
        <a href='<?php echo $this->createAbsoluteUrl ('/site/login'); ?>' rel='external'><?php 
            echo CHtml::encode (Yii::t('app', 'Desktop version')); 
            setcookie('isMobileApp','true'); // save cookie?>
        </a>
    </div>
    <?php
    }
    $model->rememberMe = 1;
    echo $form->hiddenField ($model, 'rememberMe');
    $this->endWidget(); 
    ?>
</div>
<script>
// prevent ajax form post to ensure that application config settings get set after login
//$.mobile['ajaxEnabled'] = false; 
</script>
