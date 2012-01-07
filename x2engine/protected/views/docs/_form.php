<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

// editor CSS file	
Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/yui/build/editor/assets/skins/x2engine/simpleeditor.css');

// editor javascript files
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/yahoo-dom-event/yahoo-dom-event.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/element/element-min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/container/container_core-min.js');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/menu/menu-min.js');
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/button/button-min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/yui/build/editor/simpleeditor-min.js');

Yii::app()->clientScript->registerScript('yuiEditor',"
var myEditor = new YAHOO.widget.SimpleEditor('msgpost', {
	height: '800px',
	width: '100%',
	handleSubmit: true,
	dompath: false, //Turns on the bar at the bottom
	animate: false //Animates the opening, closing and moving of Editor windows
});
myEditor.render();
",CClientScript::POS_HEAD);

$form=$this->beginWidget('CActiveForm', array(
	'id'=>'docs-form',
	'enableAjaxValidation'=>false,
)); ?>
<div class="form" id="doc-form">
	<div class="row">
		<?php echo $form->errorSummary($model); ?>
		<?php echo $form->label($model,'title'); ?>
		<?php echo $form->textField($model,'title',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'title'); ?>
	<?php if(isset($_GET['saved'])){
                $date=date("g:i:s A",$_GET['time']);
                echo "Saved at $date.";
        }
            echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button float')); ?>
	</div>
	<div class="row">
		<?php 
		if($model->isNewRecord){
			echo $form->label($model,'editPermissions');
			echo $form->dropDownList($model,'editPermissions',$users,array('multiple'=>'multiple','size'=>'5'));
			echo $form->error($model,'editPermissions');
		}
		?>
	</div>
</div>
<div class="yui-skin-x2engine">
	<textarea name="msgpost" id="msgpost" cols="50" rows="10"><?php echo $model->text; ?>
	</textarea>
</div>
<?php echo $form->error($model,'text'); ?>

<?php $this->endWidget(); ?>