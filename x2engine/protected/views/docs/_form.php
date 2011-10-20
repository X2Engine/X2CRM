<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
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
	width: '590px',
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