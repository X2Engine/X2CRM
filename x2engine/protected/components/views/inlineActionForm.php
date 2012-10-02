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

Yii::app()->clientScript->registerScript('highlightSaveAction',"
$(function(){
	$('#action-form input, #action-form select, #action-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').addClass('highlight'); //css('background','yellow');
	}
	);
}
);");

$inlineForm = (isset($inlineForm)); // true if this is in the InlineActionForm
$quickCreate = $inlineForm? false : ($this->getAction()->getId() == 'quickCreate');	// true if we're inside the quickCreate view
if(isset($_GET['inline']))
    $inlineForm=$_GET['inline'];
$action = $inlineForm? array('/actions/create','inline'=>1) : null;

?>
<script>
    $(function() {
            var tabs=$( "#tabs" ).tabs();
            $("#actions-newCreate-form").submit(function(){
               $("#save-button1").val(tabs.tabs('option', 'selected'));
           }) 
    });

</script>
<div id="tabs">
<ul>
	<li><a href="#tabs-1"><?php echo Yii::t('actions','Log A Call'); ?></a></li>
	<li><a href="#tabs-2"><?php echo Yii::t('actions','New Action'); ?></a></li>
	<li><a href="#tabs-3"><?php echo Yii::t('actions','New Comment'); ?></a></li>
</ul>

<div class="form" id="action-form">
<?php
$form = $this->beginWidget('CActiveForm', array(
	'action'=>$action,
	'id'=>'actions-newCreate-form',
	'enableAjaxValidation'=>false,
));
//echo '<em>'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";

echo $form->errorSummary($actionModel);
?>
<div class="row">
<b><?php echo $form->labelEx($actionModel,'actionDescription'); ?></b>
<?php echo $form->textArea($actionModel,'actionDescription',array('rows'=>($inlineForm?3:6), 'cols'=>40,'style'=>'width:500px;')); ?>
<div id="tabs-1">

</div>
	
<div id="tabs-2">
		

<div class="row">
	<?php
	if ($inlineForm) {
		echo $form->hiddenField($actionModel,'associationType');
	} else {
	?>
	<div class="row">
		<div class="cell">
		<?php echo $form->label($actionModel,'associationType'); ?>
		<?php echo $form->dropDownList($actionModel,'associationType',
			array(
				'none'=>Yii::t('actions','None'),
				'contacts'=>Yii::t('actions','Contact'),
				'opportunities'=>Yii::t('actions','Opportunity'),
				'accounts'=>Yii::t('actions','Account'),
			),
			array(
				'ajax' => array(
					'type'=>'POST', //request type
					'url'=>CController::createUrl('/actions/parseType'), //url to call.
					//Style: CController::createUrl('currentController/methodToCall')
					'update'=>'#', //selector to update
					'success'=>'function(data){
							window.location="?param='.Yii::app()->user->getName().';"+data+":0";
						}'
					)
				)
			);
			echo $form->error($actionModel,'associationType'); ?>
		</div>
		<div class="cell" id="auto_complete">
			<?php
			echo $form->label($actionModel,'associationName');
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'auto_select',
				'value'=>$actionModel->associationName,
				'source' => $this->createUrl('/actions/getTerms',array('type'=>$actionModel->associationType)),
				'options'=>array(
					'minLength'=>'2',
					'select'=>'js:function( event, ui ) {
						$("#'.CHtml::activeId($actionModel,'associationId').'").val(ui.item.id);
						$(this).val(ui.item.value);
						return false;
					}',
				),
			));
			//echo $form->error($actionModel,'associationName');
			?>
		</div>
	</div>
	<?php } ?>

	<div class="cell">
		<?php echo $form->hiddenField($actionModel,'associationId'); ?>
		<?php echo $form->label($actionModel,'dueDate');
		if ($actionModel->isNewRecord)
			$actionModel->dueDate = date('M d, Y',time()).' 23:59';	//default to tomorow for new actions
		else
			$actionModel->dueDate = date('M d, Y H:i',$actionModel->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$actionModel, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>'M dd, yy',
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
		));
		?>
		<?php echo $form->error($actionModel,'dueDate'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'priority'); ?>
		<?php echo $form->dropDownList($actionModel,'priority',array(
			'Low'=>Yii::t('actions','Low'),
			'Medium'=>Yii::t('actions','Medium'),
			'High'=>Yii::t('actions','High')));
		//echo $form->error($actionModel,'priority'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'assignedTo'); ?>
		<?php echo $form->dropDownList($actionModel,'assignedTo',$users,array('id'=>'actionsAssignedToDropdown')); ?>
		<?php //echo $form->error($actionModel,'assignedTo'); ?>
		<?php /* x2temp */
			echo "<br />";
			if($this instanceof ActionsController){
				$url=$this->createUrl('groups/getGroups');
			}else{
				$url=$this->controller->createUrl('groups/getGroups');
			}
			echo "<label>Group?</label>";
			echo CHtml::checkBox('group','',array(
				'id'=>'groupCheckbox',
				'ajax'=>array(
					'type'=>'POST', //request type
						'url'=>$url, //url to call.
						//Style: CController::createUrl('currentController/methodToCall')
						'update'=>'#actionsAssignedToDropdown', //selector to update
						'complete'=>'function(){
							if($("#groupCheckbox").attr("checked")!="checked"){
								$("#groupCheckbox").attr("checked","checked");
								$("#Actions_visibility option[value=\'2\']").remove();
							}else{
								$("#groupCheckbox").removeAttr("checked");
								$("#Actions_visibility").append(
									$("<option></option>").val("2").html("User\'s Groups")
								);
							}
						}'
				)
			));
		/* end x2temp */ ?>
	</div>
        
	<div class="cell">
		<?php echo $form->label($actionModel,'visibility'); ?>
		<?php
			$visibility=array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private'));
			/* x2temp */
			$visibility[2]='User\'s Groups';
			/* end x2temp */
			?>
		<?php echo $form->dropDownList($actionModel,'visibility',$visibility); ?> 
		<?php //echo $form->error($actionModel,'visibility'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'reminder'); ?>
		<?php //echo $form->checkBox($actionModel,'reminder',array('value'=>'Yes','uncheckedValue'=>'No')); ?>
		<?php echo $form->dropDownList($actionModel,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes'))); ?> 
	</div>
</div>
</div>
<div id="tabs-3">

</div>
</div>
</div>
<div class="row buttons">
	<?php echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),
			array('type'=>'submit','class'=>'x2-button','id'=>'save-button1','name'=>'submit')); ?>
</div>
<?php $this->endWidget(); ?>
</div>