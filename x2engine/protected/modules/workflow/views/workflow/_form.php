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




Yii::app()->clientScript->registerCss('workflowFormCss',"

.color-picker-row {
    margin: 9px 0;
}

");

$financialFieldsUrl = $this->createUrl('workflow/getFinancialFields');
Yii::app()->clientScript->registerScript('workflowFinancial',"
    $('#Workflow_financial').on('change',function(){
        if($('#Workflow_financial').is(':checked')){
            $('#financialModel').show();
            $('#financialField').show();
        } else {
            $('#financialModel').hide();
            $('#financialField').hide();
        }
    });
    $('#Workflow_financialModel').on('change',function(){
        $.ajax({
            url: '$financialFieldsUrl',
            data: {modelType: $(this).val()},
            success: function(data){
                $('#Workflow_financialField').empty();
                $.each(JSON.parse(data), function(key, val){
                    $('#Workflow_financialField').append($('<option></option>')
                    .attr('value', key).text(val));
                });
            }
        });
    });
");

if(empty($model->stages))
	$model->stages = array(new WorkflowStage);	// start with at least 1 blank row

// look up all the available roles
$roles = array(''=>Yii::t('app','Anyone'));
$roleIds = Yii::app()->db->createCommand()->select('id')->from('x2_roles')->queryColumn();
$roleNames = Yii::app()->db->createCommand()->select('name')->from('x2_roles')->queryColumn();

if(!empty($roleIds) && !empty($roleNames) && count($roleIds) == count($roleNames))
	$roles += array_combine($roleIds,$roleNames);
unset($roleIds,$roleNames);		// cleanup temp vars

Yii::app()->clientScript->registerScript('addWorkflowStage', "
function deleteStage(object) {
	$(object).closest('li').animate({
		opacity: 0,
		height: 0
	}, 200,function() { $(this).remove(); updateStageNumbers(); });

	var stageCount = $('#workflow-stages li').length;
	$('#workflow-stages li select.workflow_requirePrevious').find('option:last').remove();
}

function addStage() {

	var stageCount = $('#workflow-stages li').length;

	$('#workflow-stages ol').append('\
	<li style=\"display:none;\">\
	<div class=\"handle\"></div>\
	<div class=\"content\">\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('name'),null)
			.CHtml::textField('WorkflowStages[][name]','',array('class'=>'workflow_name','style'=>'width:140px','maxlength'=>40)))
			// .CHtml::error('WorkflowStages_name'))
		." \
		</div> \
		<div class=\"cell\">\
			".addslashes(
                CHtml::label(
                    $model->stages[0]->getAttributeLabel('requirePrevious'),null)
			.' '.
            preg_replace(
                '/[\r\n]+/u','',
                CHtml::dropdownList(
                    'WorkflowStages[][requirePrevious]',
                    0,
                    array('0'=>Yii::t('app','None'), '1'=>Yii::t('app','All Previous')),
                    array('class'=>'workflow_requirePrevious','style'=>'width:100px;')
                )
            ))
		."</div>\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('roles'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[][roles][]','',$roles,array('multiple'=>'multiple','class'=>'workflow_roles','style'=>'width:100px;'))))
		."</div>\
		<div class=\"cell\">\
			".addslashes(CHtml::label($model->stages[0]->getAttributeLabel('requireComment'),null)
			.' '.preg_replace('/[\r\n]+/u','',CHtml::dropdownList('WorkflowStages[][requireComment]',0,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('class'=>'workflow_requireComment','style'=>'width:80px;'))))
		."</div>\
		<div class=\"cell\">\
			<a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\" title=\"".Yii::t('workflow','Delete')."\" class=\"del\"></a>\
		</div>\
	</div>\
	</li>');
	stageCount++;

	for(var i=1;i<stageCount;i++)
		$('#workflow-stages li:last-child select.workflow_requirePrevious').append('<option value=\"-'+i+'\">".addslashes(Yii::t('workflow','Stage'))." '+i+'</option>');
	$('#workflow-stages li select.workflow_requirePrevious').append('<option value=\"'+stageCount+'\">".addslashes(Yii::t('workflow','Stage'))." '+stageCount+'</option>');
	$('#workflow-stages li:last-child').slideDown(300);
	updateStageNumbers();
}

function updateStageNumbers() {
	$('#workflow-stages li').each(function(i,element) {
		$(this).find('.handle').html(i+1);
		$(this).find('input.workflow_name').attr('name','WorkflowStages['+(i+1)+'][name]');
		$(this).find('select.workflow_requirePrevious').attr('name','WorkflowStages['+(i+1)+'][requirePrevious]');
		$(this).find('select.workflow_roles').attr('name','WorkflowStages['+(i+1)+'][roles][]');
		$(this).find('select.workflow_requireComment').attr('name','WorkflowStages['+(i+1)+'][requireComment]');
                $(this).find('input.workflow_stageId').attr('name','WorkflowStages['+(i+1)+'][stageId]');
	});
}

$(function() {
	$('#workflow-stages ol').sortable({
		// tolerance:'intersect',
		// items:'.formSection',
		// placeholder:'formSectionPlaceholder',
		handle:'.handle',
		// opacity:0.5,
		axis:'y',
		distance:10,
		stop:updateStageNumbers
		// change:function() { window.layoutChanged = true; }
	});
});
",CClientScript::POS_HEAD);
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'workflow-form',
	'enableAjaxValidation'=>false,
)); ?>
	<?php echo $form->errorSummary($model); ?>

	<div class="row">
            <div class="cell">
                <?php
                echo $form->labelEx($model, 'name');
                echo $form->textField($model, 'name',
                        array('maxlength' => 250, 'class' => 'x2-wide-input'));
                echo $form->error($model, 'name');
                ?>
            </div>
            <div class="cell">
                <?php
                $moduleOptions = array(
                            Workflow::DEFAULT_ALL_MODULES => Yii::t('workflow',
                                    'All Modules')
                        ) + Modules::getDropdownOptions('id',
                                function ($record) {
                            $modelName = X2Model::getModelName($record['name']);
                            return $modelName && ($modelName::model() instanceof X2Model)
                                    &&
                                    $modelName::model()->supportsWorkflow;
                        });
                echo $form->labelEx($model, 'isDefaultFor');
                echo $form->dropDownList(
                        $model, 'isDefaultFor', $moduleOptions,
                        array(
                    'multiple' => 'multiple',
                    'class' => 'x2-multiselect-dropdown',
                    'style' => 'display: none',
                    'data-selected-text' => Yii::t('workflow', 'module(s)'),
                ));
                ?>
            </div>
            <div class="cell">
                <?php 
                echo $form->labelEx($model, 'financial');
                echo $form->checkBox($model, 'financial');
                echo $form->error($model, 'financial');
                ?>
            </div>
            <div class="cell" id="financialModel" style="<?php echo $model->financial?'':'display:none;'; ?>">
                <?php 
                echo $form->labelEx($model, 'financialModel');
                echo $form->dropDownList($model, 'financialModel', X2Model::getModelTypesWhichSupportWorkflow(true, true), array('empty'=>Yii::t('workflow','Select a model type')));
                echo $form->error($model, 'financialModel');
                ?>
            </div>
            <div class="cell" id="financialField" style="<?php echo $model->financial?'':'display:none;'; ?>">
                <?php 
                echo $form->labelEx($model, 'financialField');
                $currencyFields = !empty($model->financialModel)?Workflow::getCurrencyFields($model->financialModel):array();
                $emptyText = empty($currencyFields)?Yii::t('workflow','Select a model'):Yii::t('workflow','Select a field');
                echo $form->dropDownList($model, 'financialField', $currencyFields, array('empty'=>$emptyText));
                echo $form->error($model, 'financialField');
                ?>
            </div>
	</div>
	<div id="workflow-stages" class="x2-sortlist">
	<ol><?php

	$stageRequirements = array(
		'0'=>Yii::t('workflow','None'),
		'1'=>Yii::t('workflow','All Previous')
	);
	for($i=1;$i<=count($model->stages);$i++)
		$stageRequirements['-'.$i] = Yii::t('workflow','Stage').' '.$i;

	// $model->stages = array_reverse($model->stages);

	for($i=0; $i<count($model->stages); $i++) {
		$stage = $model->stages[$i];

		?><li>
		<div class="handle"><?php echo $i+1; ?></div>
		<div class="content">
			<div class="cell">
				<?php echo $form->labelEx($stage,'name'); ?>
				<?php echo CHtml::textField('WorkflowStages['.($i+1).'][name]',$stage->name,array('class'=>'workflow_name','style'=>'width:140px','maxlength'=>40)); ?>
				<?php echo CHtml::error($stage,'name'); ?>
			</div>

			<div class="cell">
				<?php echo $form->labelEx($stage,'requirePrevious'); ?>
				<?php
				if(empty($stage->roles))
					$stage->roles = array('');
				echo CHtml::dropdownList('WorkflowStages['.($i+1).'][requirePrevious]',$stage->requirePrevious,$stageRequirements,array('class'=>'workflow_requirePrevious','style'=>'width:100px;')); ?>
			</div>
			<div class="cell">
				<?php echo $form->label($stage,'roles'); ?>
				<?php echo CHtml::dropdownList('WorkflowStages['.($i+1).'][roles][]',$stage->roles,$roles,array('multiple'=>'multiple','class'=>'workflow_roles','style'=>'width:100px;')); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($stage,'requireComment'); ?>
				<?php echo CHtml::dropdownList('WorkflowStages['.($i+1).'][requireComment]',$stage->requireComment,array('0'=>Yii::t('app','No'),'1'=>Yii::t('app','Yes')),array('class'=>'workflow_requireComment','style'=>'width:80px;')); ?>
			</div>
                        <div class="cell">
                            <?php if (isset($stage->id)) {
                                echo CHtml::hiddenField('WorkflowStages['.($i+1).'][stageId]', $stage->id,array('class'=>'workflow_stageId'));
                            } ?>
                        </div>
			<a href="javascript:void(0)" onclick="deleteStage(this);" title="<?php echo Yii::t('workflow','Del'); ?>" class="del"></a>
		</div>
		</li>
		<?php
	}
	?>
	</ol>
	</div>
	<a href="javascript:void(0)" onclick="addStage()" class="x2-sortlist-add">[<?php echo Yii::t('workflow','Add'); ?>]</a>

    <?php
    $firstColor = isset($model->colors['first']) ? $model->colors['first'] : '';
    $lastColor = isset($model->colors['last']) ? $model->colors['last'] : '';
    ?>
    <div class='row color-picker-row'>
        <label for='colors[first]'><?php echo Yii::t('workflow', 'First Stage Color:'); ?></label>
        <input name='colors[first]' class='x2-color-picker' 
         value='<?php echo $firstColor; ?>'>
        <label for='colors[last]'><?php echo Yii::t('workflow', 'Last Stage Color:'); ?></label>
        <input name='colors[last]' class='x2-color-picker' 
         value='<?php echo $lastColor; ?>'>
    </div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create') : Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->
