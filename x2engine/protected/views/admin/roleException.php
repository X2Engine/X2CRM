<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
?><h3>Add Exception</h3>
<div style="width:600px">
    <?php echo Yii::t('admin','Adding an exception will alter a Role\'s behavior while the contact is on a particular workflow stage.  You can change which fields are editable by whom to be dependent on where a contact is in workflow this way.') ?>
</div>
<div class="form">
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

        <label>Workflow</label>
        <?php echo CHtml::dropDownList('workflow','',$workflows,array(
        'empty'=>'Select a workflow',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getWorkflowStages'), //url to call.
                //Style: CController::createUrl('currentController/methodToCall')
                'update'=>'#workflowStages', //selector to update
                //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()' 
                //leave out the data key to pass all form values through
                ))); ?>
        <label>Workflow Stage</label>
        <?php echo CHtml::dropDownList('workflowStages','',array(),array('id'=>'workflowStages','empty'=>'Select a workflow first'));?>
        <div class="row">
            <label>Role Name</label>
            <?php echo $form->dropDownList($model,'name',$names,array(
                'empty'=>'Select a role',
                'ajax' => array(
                'type'=>'POST', //request type
                'url'=>CController::createUrl('admin/getRole'), //url to call.
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