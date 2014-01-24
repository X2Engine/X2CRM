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
