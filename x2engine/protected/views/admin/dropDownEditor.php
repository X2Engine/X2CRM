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

Yii::app()->clientScript->registerScript('customModuleFields', "
function deleteStage(object) {
	$(object).closest('li').remove();
}

function moveStageUp(object) {
	var prev = $(object).closest('li').prev().find('input');
	if(prev.length>0) {
                var temp=$(prev).val();
		$(prev).val($(object).closest('li').find('input').val());
                $(object).closest('li').find('input').val(temp);
	}
}
function moveStageDown(object) {
	var next = $(object).closest('li').next().find('input');
	if(next.length>0) {
                var temp=$(next).val();
		$(next).val($(object).closest('li').find('input').val());
                $(object).closest('li').find('input').val(temp);
	}
}

function addStage() {
	$('#workflow-stages ol').append(' \
	<li>\
                <input type=\"text\" size=\"30\" name=\"Dropdowns[options][]\" />\
        <div class=\"\">\
            <a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\">[".Yii::t('workflow','Up')."]</a>\
            <a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\">[".Yii::t('workflow','Down')."]</a>\
            <a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[".Yii::t('workflow','Del')."]</a>\
        </div><br />\
	</li>');
}

",CClientScript::POS_HEAD);



?>
<div class="page-title"><h2><?php echo Yii::t('admin','Dropdown Editor'); ?></h2></div>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'field-form',
	'enableAjaxValidation'=>false,
        'action'=>'dropDownEditor',
)); ?>

    <div class="row">
        <?php echo $form->labelEx($model,'name'); ?>
        <?php echo $form->textField($model,'name'); ?>
        <?php echo $form->error($model,'name'); ?>
    </div>
    <div id="workflow-stages">
        <label><?php echo Yii::t('admin','Dropdown Options');?></label>
        <ol>
        <li>
            <input type="text" size="30" name="Dropdowns[options][]" />

            <div class="">
                <a href="javascript:void(0)" onclick="moveStageUp(this);">[<?php echo Yii::t('workflow','Up'); ?>]</a>
                <a href="javascript:void(0)" onclick="moveStageDown(this);">[<?php echo Yii::t('workflow','Down'); ?>]</a>
                <a href="javascript:void(0)" onclick="deleteStage(this);">[<?php echo Yii::t('workflow','Del'); ?>]</a>
            </div>
            <br />
        </li>
        </ol>
    </div>
    <a href="javascript:void(0)" onclick="addStage();" class="add-workflow-stage">[<?php echo Yii::t('admin','Add Option'); ?>]</a>
    <div class="row buttons">
        <br />
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
    </div>
<?php $this->endWidget();?>
</div>
