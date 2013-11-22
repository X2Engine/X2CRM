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

$this->pageTitle=Yii::app()->name . ' - Login';
if(isset($_COOKIE['LoginForm']))
    $model->setAttributes($_COOKIE['LoginForm']);

Yii::app()->clientScript->registerScript('loginFocus',
	'window.onload = function() { document.getElementById("LoginForm_username").focus(); }',
CClientScript::POS_HEAD);
?>
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
		<div class="cell">
			<?php echo CHtml::image(Yii::app()->baseUrl.'/images/x2engine_crm_login.png','X2Engine',array('id'=>'login-logo','width'=>80,'height'=>71)); ?>
		</div>
		<div class="cell" style="margin:0;width:225px;">
		
			<?php echo $form->label($model,'username'); ?>
			<?php echo $form->textField($model,'username',array('autofocus'=>'autofocus')); ?>
			<?php //echo $form->error($model,'username'); ?>

			<?php echo $form->label($model,'password',array('style'=>'margin-top:5px;')); ?>
			<?php echo $form->passwordField($model,'password'); ?>
			<?php echo $form->error($model,'password'); ?>
			<?php if($model->useCaptcha && CCaptcha::checkRequirements()) { ?>
			<div class="row" style="margin-top:5px;">
				<?php
				// CHtml::$errorCss = 'error';
				// CHtml::$errorSummaryCss = 'error';

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
			<div class="row checkbox" style="margin-top:5px;">
				<div class="cell">
					<?php echo CHtml::submitButton(Yii::t('app','Login'),array('class'=>'x2-button')); ?>
				</div>

				<div class="cell" style="margin-left:10px;padding-top:12px;padding-left:5px;">
					<?php echo $form->checkBox($model,'rememberMe',array('value'=>'1','uncheckedValue'=>'0')); ?>
					<?php echo $form->label($model,'rememberMe',array('style'=>'font-size:10px;')); ?>
					<?php echo $form->error($model,'rememberMe'); ?><br>
					
				</div>		
			</div>
		</div>
	</div>
	<div class="row" id="login-links" style="margin-top:10px;text-align:center;">
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
