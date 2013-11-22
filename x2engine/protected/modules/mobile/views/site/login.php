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

$this->pageTitle = Yii::app()->name . ' - Login';

?>

<div class="form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'login-form',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
            ));
    	echo $form->errorSummary($model);
    ?>
    <div data-role="fieldcontain">
        <?php echo $form->label($model, 'username', array()); ?>
        <?php echo $form->textField($model, 'username',array('style'=>'height:50px;')); ?>
        <?php //echo $form->error($model, 'username'); ?>
    </div>
    <div data-role="fieldcontain">
        <?php echo $form->label($model, 'password', array()); ?>
        <?php echo $form->passwordField($model, 'password', array('style'=>'height:50px;')); ?>
        <?php //echo $form->error($model, 'password'); ?>
    </div>
    <div data-role="fieldcontain" class='remember-me-checkbox-container'>
		<?php echo $form->checkBox($model,'rememberMe',array('value'=>'1','uncheckedValue'=>'0')); ?>
		<?php echo $form->label($model,'rememberMe',array('style'=>'font-size:10px;')); ?>
		<?php echo $form->error($model,'rememberMe'); ?><br>
    </div>

	<?php if($model->useCaptcha && CCaptcha::checkRequirements()) { ?>
		<div data-role="field contain">
		    <?php
		    $this->widget('CCaptcha',array(
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
	<?php } ?>
    <?php echo CHtml::submitButton(Yii::t('app', 'Login')); ?>

    <?php $this->endWidget(); ?>
</div>
<script>
// prevent ajax form post to ensure that application config settings get set after login
$.mobile['ajaxEnabled'] = false; 
</script>
