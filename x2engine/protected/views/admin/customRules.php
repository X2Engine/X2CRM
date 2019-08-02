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




Yii::app()->clientScript->registerCss('customRulesCss',"

#routing-grid_c0 {
    width: 58px;
}

#routing-grid_c2 {
    width: 30%;
}

#routing-grid_c3 {
    width: 20%;
}

");

?>
<div class="page-title"><h2><?php echo Yii::t('admin','Manage Lead Routing'); ?></h2></div>
<div class="form">
<?php echo Yii::t('admin','Manage routing criteria. This setting is only required if lead distribution is set to "Custom Round Robin"'); ?>
 </div>
<?php
$str="<select name=\"Values[field][]\">";
foreach(X2Model::model('Contacts')->attributeLabels() as $field=>$label){
    $str.="<option value=\"$field\">".CHtml::encode($label)."</option>";
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
    'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.
        Yii::app()->theme->name.'/css/gridview',
    'dataProvider'=>$dataProvider,
    'columns'=>array(
        array(
            'name'=>'priority',
            'header'=>Yii::t('admin','Priority'),
            'value'=>'$data->priority',
            'type'=>'raw',
        ),
        array(
            'name'=>'value',
            'header'=>Yii::t('admin','Criteria'),
            'value'=>'LeadRouting::humanizeText($data->criteria)',
            'type'=>'raw',
            'htmlOptions' => array ('style' => 'text-overflow: ellipsis;'),
        ),
        array(
            'name'=>'users',
            'header'=>Yii::t('admin','Users'),
            'value'=>'User::getUserLinks($data->users)',
            'type'=>'raw',
        ),
        array(
            'name'=>'delete',
            'header'=>Yii::t('admin','Delete'),
            'value'=>'CHtml::link(Yii::t("app","Delete"),"deleteRouting/$data->id")',
            'type'=>'raw',
        ),

    ),
));
?>
<br>
<div class="page-title rounded-top"><h2><?php echo Yii::t('admin','Add Criteria for Lead Routing'); ?></h2></div>
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
