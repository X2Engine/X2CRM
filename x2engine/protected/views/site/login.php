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

$this->pageTitle=Yii::app()->name . ' - Login';

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

Yii::app()->clientScript->registerScript('loginFocus',
    'document.getElementById("LoginForm_username").focus ();', // for when autofocus isn't supported
CClientScript::POS_READY);
?>
<div class="container<?php echo (isset ($profileId) ? ' welcome-back-page' : ''); ?>" id="login-page">
<div id="login-box">
<?php $form=$this->beginWidget('CActiveForm', array(
    // 'id'=>'login-form',
    'enableClientValidation'=>false,
    'enableAjaxValidation'=>false,
    'clientOptions'=>array(
        'validateOnSubmit'=>false,
    ),
));
    ?><!--<h2><?php echo Yii::t('app','Welcome to {appName}.',array('{appName}'=>Yii::app()->name)); ?></h2>-->
<div class="form" id="login-form">
    <div class="row">
        <div class="cell company-logo-cell">
            <?php echo CHtml::image(Yii::app()->baseUrl.'/images/x2engine_crm_login.png','X2Engine',array('id'=>'login-logo','width'=>80,'height'=>71)); ?>
        </div>
        <div class="cell form-cell">
        
            <?php 
            if ($hasProfile) { 
            ?>
            <div id='full-name'><?php echo $fullName; ?></div>
            <?php
            }
            echo $form->label($model,'username', array ('style' => ($hasProfile ? 'display: none;' : '')));
            if ($hasProfile) { 
                echo $form->hiddenField($model,'username');
            } else {
                echo $form->textField($model,'username',array('autofocus'=>'autofocus'));
            }
            
            echo $form->label($model,'password',array('style'=>'margin-top:5px;')); 
            echo $form->passwordField($model,'password'); 
            echo $form->error($model,'password'); 

            if($model->useCaptcha && CCaptcha::checkRequirements()) { 
            ?>
            <div class="row captha-row">
                <?php
                echo '<div>';
                $this->widget('CCaptcha',array(
                    'clickableImage'=>true,
                    'showRefreshButton'=>false,
                    'imageOptions'=>array(
                        'style'=>'display:block;cursor:pointer;',
                        'title'=>Yii::t('app','Click to get a new image')
                    )
                )); echo '</div>';
                echo '<p class="hint">'.Yii::t('app','Please enter the letters in the image above.').'</p>';
                echo $form->textField($model,'verifyCode');
                ?>
            </div><?php } ?>
            <div class="row checkbox login-button-row">
                <div class="cell">
                    <?php echo CHtml::submitButton(
                        Yii::t('app','Login'),array('class'=>'x2-button')); ?>
                </div>
                <div class="cell remember-me-cell">
                    <?php 
                    if ($model->rememberMe) {
                    ?>
                    <a href='<?php echo Yii::app()->createUrl ('/site/site/forgetMe'); ?>'
                     class='x2-link x2-minimal-link'>
                        <?php echo Yii::t('app', 'Change User'); ?>
                    </a>
                    <?php
                    } else {
                        echo $form->checkBox(
                            $model,'rememberMe',array('value'=>'1','uncheckedValue'=>'0')); 
                        echo $form->label($model,'rememberMe',array('style'=>'font-size:10px;')); 
                        echo $form->error($model,'rememberMe'); 
                    }
                    ?><br>
                </div>        
            </div>
        </div>
        <?php
        if (isset ($profileId)) {
        ?>
        <div class='avatar-cell'>
            <span class='image-alignment-helper'></span>
            <?php Profile::renderFullSizeAvatar ($profileId, 105); ?>
        </div>
        <?php
        }
        ?>
    </div>
    <div class="row" id="login-links">
        <?php echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/google_icon.png" id="google-icon" /> '.Yii::t('app','Login with Google'),
                (@$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://') . 
                ((substr($_SERVER['HTTP_HOST'],0,4)=='www.')?substr($_SERVER['HTTP_HOST'],4):$_SERVER['HTTP_HOST']) . 
                $this->createUrl('/site/googleLogin'),array('class'=>'x2touch-link')); ?>
        <?php echo CHtml::link('<img src="'.Yii::app()->baseUrl.'/images/mobile.png" id="mobile-icon" /> X2Touch Mobile',Yii::app()->getBaseUrl() . '/index.php/mobile/site/login',array('class'=>'x2touch-link')); ?>
    </div>
</div>
<!--<div id="login-logo"></div>-->
<?php $this->endWidget(); ?>
</div>

<span id="login-version"><?php echo Yii::app()->params->edition=='pro'? 'PROFESSIONAL EDITION' : 'OPEN SOURCE EDITION'; ?>, VERSION <?php echo Yii::app()->params->version; ?> </span>
<br>
<span id="login-x2engine">
    <a href="http://www.x2engine.com">X2Engine, Inc.</a><?php 
    if(Yii::app()->params->admin->edition == 'opensource'){
        echo '&nbsp;&bull;&nbsp;'.CHtml::link("LICENSE",Yii::app()->baseUrl.'/LICENSE.txt');
    } ?></span>
</div>
