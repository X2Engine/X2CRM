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



?>
<div class="page-title rounded-top"><h2><?php echo Yii::t('admin','Add Exception'); ?></h2></div>
<div class="form">
<div style="max-width:500px">
    <?php echo Yii::t('admin',"Adding an exception will alter a Role's behavior while the contact is on a particular process stage.  You can change which fields are editable by whom to be dependent on where a contact is in the process this way.") ?>
</div><br>
<?php
//Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
//Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
//Yii::app()->clientScript->registerCss('multiselectCss',"
//.multiselect {
//	width: 460px;
//	height: 200px;
//}
//#switcher {
//	margin-top: 20px;
//}
//",'screen, projection');
$list=Roles::model()->findAll();
$names=array();
foreach($list as $role){
    $names[$role->name]=$role->name;
}
?>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'roleException-form',
	'enableAjaxValidation'=>false,
        'action'=>'roleException',
)); ?>

        <label><?php echo Yii::t('workflow','Processes'); ?></label>
        <?php echo CHtml::dropDownList('workflow','',$workflows,array(
        'empty'=>'Select a process',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('/admin/getWorkflowStages'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#workflowStages', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                ))); ?>
        <label><?php echo Yii::t('workflow','Process Stage'); ?></label>
        <?php echo CHtml::dropDownList('workflowStages','',array(),array('id'=>'workflowStages','empty'=>'Select a process first'));?>
        <div class="row">
            <label>Role Name</label>
            <?php echo $form->dropDownList($model,'name',$names,array(
                'empty'=>Yii::t('admin','Select a role'),
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('/admin/getRole', array('mode'=>'exception')), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#roleFormTwo', //selector to update
                'complete'=>"function(){
                    $('.multiselect').multiselect();
                    $('#users').hide();
                }"
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                //leave out the data key to pass all form values through
                ))); ?>
            <?php echo $form->error($model,'name'); ?>
        </div>

        <div id="roleFormTwo">

        </div>
        <br />
	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),array('class'=>'x2-button')); ?>
	</div>
<?php $this->endWidget(); ?>
</div>
