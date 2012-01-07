<?php
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
        <div class=\"cell\">\
            <a href=\"javascript:void(0)\" onclick=\"moveStageUp(this);\">[".Yii::t('workflow','Up')."]</a>\
            <a href=\"javascript:void(0)\" onclick=\"moveStageDown(this);\">[".Yii::t('workflow','Down')."]</a>\
            <a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[".Yii::t('workflow','Del')."]</a>\
        </div><br />\
	</li>');
}

",CClientScript::POS_HEAD);



?>
<h3>Dropdown Editor</h3>
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
        <label>Dropdown Options</label>
        <ol>
        <li>
            <input type="text" size="30" name="Dropdowns[options][]" />
            
            <div class="cell">
                <a href="javascript:void(0)" onclick="moveStageUp(this);">[<?php echo Yii::t('workflow','Up'); ?>]</a>
                <a href="javascript:void(0)" onclick="moveStageDown(this);">[<?php echo Yii::t('workflow','Down'); ?>]</a>
                <a href="javascript:void(0)" onclick="deleteStage(this);">[<?php echo Yii::t('workflow','Del'); ?>]</a>
            </div>
            <br />
        </li>
        </ol>
    </div>
    <a href="javascript:void(0)" onclick="addStage();" class="add-workflow-stage">[<?php echo Yii::t('workflow','Add'); ?>]</a>
    <br />
    <div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
    </div>
<?php $this->endWidget();?>
</div>