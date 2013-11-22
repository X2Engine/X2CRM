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
?><div class="page-title"><h2><?php echo Yii::t('admin','Manage Lead Routing'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','Manage routing criteria. This setting is only required if lead distribution is set to "Custom Round Robin"'); ?>
 </div>
<?php
$str="<select name=\"Values[field][]\">";
foreach(X2Model::model('Contacts')->attributeLabels() as $field=>$label){
    $str.="<option value=\"$field\">$label</option>";
}
$str.="</select>";
Yii::app()->clientScript->registerScript('leadRules', "
function deleteStage(object) {
	$(object).closest('li').remove();
}

function addStage() {
	$('#criteria ul').append(' \
	<li>\
                ".Yii::t('admin','AND')." ".$str."\
                <select name=\"Values[comparison][]\">\
                    <option value=\"<\">".Yii::t('admin','Less Than')."</option>\
                    <option value=\">\">".Yii::t('admin','Greater Than')."</option>\
                    <option value=\"=\">".Yii::t('admin','Equal To')."</option>\
                    <option value=\"!=\">".Yii::t('admin','Not Equal To')."</option>\
                    <option value=\"contains\">".Yii::t('admin','Contains')."</option>\
                </select>\
                <input type=\"text\" size=\"30\" name=\"Values[value][]\" /><br />\
        <div class=\"cell\">\
            <a href=\"javascript:void(0)\" onclick=\"deleteStage(this);\">[".Yii::t('workflow','Del')."]</a>\
        </div><br />\
	</li>');
}

",CClientScript::POS_HEAD);

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'routing-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
	'dataProvider'=>$dataProvider,
	'columns'=>array(
                array(

			'name'=>'priority',
			'header'=>Yii::t('admin','Priority'),
			'value'=>'$data->priority',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
		array(

			'name'=>'value',
			'header'=>Yii::t('admin','Criteria'),
			'value'=>'LeadRouting::humanizeText($data->criteria)',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
                array(

			'name'=>'users',
			'header'=>Yii::t('admin','Users'),
			'value'=>'User::getUserLinks($data->users)',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),
                array(

			'name'=>'delete',
			'header'=>Yii::t('admin','Delete'),
			'value'=>'CHtml::link(Yii::t("app","Delete"),"deleteRouting/$data->id")',
			'type'=>'raw',
			'htmlOptions'=>array('width'=>'80%'),
		),

	),
));
?>
<br>
<div class="page-title"><h2><?php echo Yii::t('admin','Add Criteria for Lead Routing'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','To add a condition which will affect how leads are distributed, please fill out the form below.'); ?>
</div>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'routing-form',
	'enableAjaxValidation'=>false,
)); ?>

	<em><?php echo Yii::t('app','Fields with <span class="required">*</span> are required.'); ?></em><br>

        <div id="criteria">
        <label><?php echo Yii::t('admin','Criteria');?></label>
        <ul>
        <li>
            <?php echo CHtml::dropDownList('Values[field][]','',X2Model::model('Contacts')->attributeLabels()); ?>
            <select name="Values[comparison][]">
                    <option value="<"><?php echo Yii::t('admin','Less Than');?></option>
                    <option value=">"><?php echo Yii::t('admin','Greater Than');?></option>
                    <option value="="><?php echo Yii::t('admin','Equal To');?></option>
                    <option value="!="><?php echo Yii::t('admin','Not Equal To');?></option>
                    <option value="contains"><?php echo Yii::t('admin','Contains');?></option>
                </select>
                <input type="text" size="30" name="Values[value][]" />
                <br />
            <div class="cell">
                <a href="javascript:void(0)" onclick="deleteStage(this);">[<?php echo Yii::t('workflow','Del'); ?>]</a>
            </div>
            <br />
        </li>
        </ul>
        <a href="javascript:void(0)" onclick="addStage();" class="add-workflow-stage">[<?php echo Yii::t('workflow','Add'); ?>]</a>
    </div>

        <div class="row">
            <?php echo $form->labelEx($model,'users'); ?>
            <?php echo $form->dropDownList($model,'users',$users,array('multiple'=>'multiple','size'=>7,'id'=>'assignedToDropdown')); ?>
            <?php echo $form->error($model,'users'); ?>
            <?php /* x2temp */
                            echo "<br>";
                            $url=$this->createUrl('/groups/groups/getGroups');
                            echo "<label>".Yii::t('app','Group?')."</label>";
                            echo CHtml::checkBox('group','',array(
                                'id'=>'groupCheckbox',
                                'ajax'=>array(
                                    'type'=>'POST', //request type
                                        'url'=>$url, //url to call.
                                        //Style: CController::createUrl('currentController/methodToCall')
                                        'update'=>'#assignedToDropdown', //selector to update
                                        'complete'=>'function(){
                                            if($("#groupCheckbox").attr("checked")!="checked"){
                                                $("#groupCheckbox").attr("checked","checked");
                                                $("#groupType").show();
                                            }else{
                                                $("#groupCheckbox").removeAttr("checked");
                                                $("#assignedToDropdown option[value=\'\']").remove();
                                                $("#assignedToDropdown option[value=\'admin\']").remove();
                                                $("#groupType").hide();
                                            }
                                        }'
                                )
                            ));
                            echo "<br>";
                            echo CHtml::dropDownList('groupType', '', array('0'=>Yii::t('admin','Within Group(s)'),'1'=>Yii::t('admin','Between Group(s)')),array('id'=>'groupType','style'=>'display:none'))
                        /* end x2temp */ ?>
        </div>

        <div class="row">
            <?php echo $form->labelEx($model,'priority');?>
            <?php echo $form->dropDownList($model,'priority',$priorityArray,array('selected'=>LeadRouting::model()->count()));?>
        </div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
