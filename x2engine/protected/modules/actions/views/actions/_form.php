<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

Yii::app()->clientScript->registerScript('validate','
$(document).ready(function(){
	$("#actions-newCreate-form").submit(function(){
		if($("#'.CHtml::activeId($actionModel,'associationType').'").val()=="contacts" || $("#'.CHtml::activeId($actionModel,'associationType').'").val()=="accounts"
			|| $("#'.CHtml::activeId($actionModel,'associationType').'").val()=="opportunities"){
			if($("#'.CHtml::activeId($actionModel,'associationId').'").val()==""){
				alert("Please enter a valid association");
				return false;
			}
		}
	}
	);
}
);');
Yii::app()->clientScript->registerScript('highlightSaveAction',"
$(function(){
	$('#action-form input, #action-form select, #action-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').addClass('highlight'); //css('background','yellow');
	}
	);
}
);");

// $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Actions'));

$inlineForm = (isset($inlineForm)); // true if this is in the InlineActionForm
$quickCreate = $inlineForm? false : ($this->getAction()->getId() == 'quickCreate');	// true if we're inside the quickCreate view
if(isset($_GET['inline']))
    $inlineForm=$_GET['inline'];
$action = $inlineForm? array('/actions/actions/create','inline'=>1) : null;

if(!isset($showLogACall))
	$showLogACall = true;
if(!isset($showNewAction))
	$showNewAction = true;
if(!isset($showNewComment))
	$showNewComment = true;
if(!isset($showNewEvent))
	$showNewEvent = true;

if($inlineForm){ ?>

<script>
$(function() {
		var tabs=$( "#tabs" ).tabs();
		$("#actions-newCreate-form").submit(function(){
		   $("#save-button1").val(tabs.tabs('option', 'selected'));
		   if(tabs.tabs('option', 'selected') == 3) // New Event tab
				$("#actions-newCreate-form").append('<input type="hidden" name="inCalendar">'); // tell Actions Controller we are creating an event
		   <?php if(isset($inCalendar)) { ?>
				<?php if($inCalendar) { ?>
					if(tabs.tabs('option', 'selected') == 1)
						$("#actions-newCreate-form").append('<input type="hidden" name="inCalendar">'); // tell Actions Controller we are creating an event
					else
						$("#save-button1").val(1); // fix for creating action while viewing calendar
				<?php } ?>
		   <?php } ?>
	   });
});
</script>

<div id="tabs">
	<ul>
		<li class="publisher-label"><?php echo Yii::t('actions','Publisher'); ?></li>
		<?php if($showLogACall) { ?>
		<li><a href="#tabs-1"><?php echo Yii::t('actions','Log A Call'); ?></a></li>
		<?php } ?>
		<?php if($showNewAction) { ?>
		<li><a href="#tabs-2"><?php echo Yii::t('actions','New Action'); ?></a></li>
		<?php } ?>
		<?php if($showNewComment) { ?>
		<li><a href="#tabs-3"><?php echo Yii::t('actions','New Comment'); ?></a></li>
		<?php } ?>
		<?php if($showNewEvent) { ?>
		<li><a href="#tabs-4"><?php echo Yii::t('actions','New Event'); ?></a></li>
		<?php } ?>
	</ul>
<?php

}

// check if this form is being recycled in the quickCreate view
if (!$quickCreate) {
	echo '<div class="form" id="action-form">';
	$form=$this->beginWidget('CActiveForm', array(
		'action'=>$action,
		'id'=>'actions-newCreate-form',
		'enableAjaxValidation'=>false,
	));
	//echo '<em>'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";
}
echo $form->errorSummary($actionModel);
if($inlineForm){
    Yii::app()->clientScript->registerScript('inline-actions-validate',"
        $('#actions-newCreate-form').submit(function(){
            if($('#Actions_actionDescription').val()==''){
                alert('Please enter a description');return false;
            }
        });
");
}
?>
    <div class="row">
	<b><?php echo $form->labelEx($actionModel,'actionDescription'); ?></b>
	<?php //echo $form->label($actionModel,'actionDescription'); ?>
	<div class="text-area-wrapper">
	<?php echo $form->textArea($actionModel,'actionDescription',array('rows'=>($inlineForm?3:6), 'cols'=>40)); ?>
	<?php //echo $form->error($actionModel,'actionDescription'); ?>
	</div>
<div id="tabs-1">

</div>
	
<div id="tabs-2">
		

<div class="row">
	<?php
	if (!$quickCreate) {
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
                        var str=window.location+"";
                        var loc=str.split("?");
                        if(loc.length>1){
                            var loc2=loc[1].split("&");
                            if(loc2.length>1 || loc2[0].substr(0,2)=="id"){
                                window.location="?"+loc2[0]+"&param='.Yii::app()->user->getName().';"+data+":0";
                            }else{
                                window.location="?param='.Yii::app()->user->getName().';"+data+":0";
                            }  
                        }else{
                            window.location="?param='.Yii::app()->user->getName().';"+data+":0";
                        }
					}'
				)
			)
		);
		echo $form->error($actionModel,'associationType'); ?>
	</div>
	<div class="cell" id="auto_complete">
		<?php

		if($actionModel->associationType!="none" && isset(X2Model::$associationModels[$actionModel->associationType])){
			
			$linkModel = X2Model::$associationModels[$actionModel->associationType];
			
			if(class_exists($linkModel)){
                $linkSource = $this->createUrl(X2Model::model($linkModel)->autoCompleteSource);
                echo $form->label($actionModel,'associationName');
                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                    'name'=>'auto_select',
                    'value'=>$actionModel->associationName,
                    'source' => $linkSource,
                    'options'=>array(
                        'minLength'=>'2',
                        'select'=>'js:function( event, ui ) {
                            $("#'.CHtml::activeId($actionModel,'associationId').'").val(ui.item.id);
                            $(this).val(ui.item.value);
                            return false;
                        }',
                    ),
                ));
            }
		}
		//echo $form->error($actionModel,'associationName');
		?>
	</div>
</div>
	<?php
		}
	}
	?>
        
	<div class="cell">
		<?php echo $form->hiddenField($actionModel,'associationId'); ?>
		<?php if($actionModel->type == 'event')
				echo $form->label($actionModel,'startDate');
			else
				echo $form->label($actionModel,'dueDate');
		if ($actionModel->isNewRecord)
			if(isset($this->controller)) // inline action?
				$actionModel->dueDate = $this->controller->formatDateEndOfDay(time());	//default to tomorow for new actions
			else
				$actionModel->dueDate = $this->formatDateEndOfDay(time());	//default to tomorow for new actions
		else
			$actionModel->dueDate = X2Model::formatDateTime($actionModel->dueDate);	//format date from DATETIME

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$actionModel, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>( (isset($this->controller))? $this->controller->formatDatePicker('medium') : $this->formatDatePicker('medium') ),
				'timeFormat'=>( (isset($this->controller))? $this->controller->formatTimePicker() : $this->formatTimePicker() ),
				'ampm'=>( (isset($this->controller))? $this->controller->formatAMPM() : $this->formatAMPM() ),
				'changeMonth'=>true,
				'changeYear'=>true
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions'=>array('onClick'=>"$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
		));
		?>
		<?php echo $form->error($actionModel,'dueDate'); ?>
		



		<?php if($actionModel->type == 'event') {
		
			echo $form->label($actionModel,'endDate');
			if ($actionModel->isNewRecord)
				if(isset($this->controller)) // inline action?
					$actionModel->completeDate = $this->controller->formatDateEndOfDay(time());	//default to tomorow for new actions
				else
					$actionModel->completeDate = $this->formatDateEndOfDay(time());	//default to tomorow for new actions
			else
				$actionModel->completeDate = X2Model::formatDateTime($actionModel->completeDate);	//format date from DATETIME
			
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
			$this->widget('CJuiDateTimePicker',array(
				'model'=>$actionModel, //Model object
				'attribute'=>'completeDate', //attribute name
				'mode'=>'datetime', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>( (isset($this->controller))? $this->controller->formatDatePicker('medium') : $this->formatDatePicker('medium') ),
					'timeFormat'=>( (isset($this->controller))? $this->controller->formatTimePicker() : $this->formattimePicker() ),
					'ampm'=>( (isset($this->controller))? $this->controller->formatAMPM() : $this->formatAMPM() ),
					'changeMonth'=>true,
					'changeYear'=>true
				), // jquery plugin options
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
				'htmlOptions'=>array('onClick'=>"$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
			));
			echo $form->error($actionModel,'completeDate');
		} ?>




		
		<?php echo $form->label($actionModel, 'allDay'); ?>
		<?php echo $form->checkBox($actionModel, 'allDay'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'priority'); ?>
		<?php echo $form->dropDownList($actionModel,'priority',array(
			'Low'=>Yii::t('actions','Low'),
			'Medium'=>Yii::t('actions','Medium'),
			'High'=>Yii::t('actions','High')));
		//echo $form->error($actionModel,'priority'); ?>
		
		<?php echo $form->label($actionModel, 'color'); ?>
		<?php echo $form->dropDownList($actionModel, 'color', Actions::getColors()); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($actionModel,'assignedTo'); ?>
		<?php echo $form->dropDownList($actionModel,'assignedTo',$users,array('id'=>'actionsAssignedToDropdown')); ?>
		<?php //echo $form->error($actionModel,'assignedTo'); ?>
            <?php /* x2temp */
                            echo "<br />";
                            if(isset($this->module) && $this->module instanceof ActionsModule){
                                $url=$this->createUrl('/groups/getGroups');
                            }else{
                                $url=$this->controller->createUrl('/groups/getGroups');
                            }
                            echo "<label>".Yii::t('app','Group?')."</label>";
                            echo CHtml::checkBox('group','',array(
                                'id'=>'groupCheckbox',
                                'ajax'=>array(
                                    'type'=>'POST', //request type
                                        'url'=>$url, //url to call.
                                        //Style: CController::createUrl('currentController/methodToCall')
                                        'update'=>'#actionsAssignedToDropdown', //selector to update
                                        'data'=>'js:{checked: $(this).attr("checked")=="checked"}',
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
        <?php 
        
            ?>
</div>
</div>
<div id="tabs-3">

</div>

<?php if($inlineForm) { ?>
<div id="tabs-4">
<?php
$event = new CalendarEvent;
$event->associationType = $actionModel->associationType;
$event->associationId = $actionModel->associationId;
$event->type = 'event';
$event->assignedTo = $actionModel->assignedTo;
?>

	<div class="cell">
		<?php echo $form->hiddenField($event,'associationType'); ?>
		<?php echo $form->hiddenField($event,'associationId'); ?>
		<?php echo $form->hiddenField($event, 'type'); ?>
		<?php echo $form->label($event,'startDate');
		$event->dueDate = X2Model::formatDateTime(time());	//default to tomorow for new actions

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$event, //Model object
			'attribute'=>'dueDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>( (isset($this->controller))? $this->controller->formatDatePicker('medium') : $this->formatDatePicker('medium') ),
				'timeFormat'=>( (isset($this->controller))? $this->controller->formatTimePicker() : $this->formatTimePicker() ),
				'ampm'=>( (isset($this->controller))? $this->controller->formatAMPM() : $this->formatAMPM() ),
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions'=>array('onClick'=>"$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
		));
		?>
		<?php echo $form->error($event,'dueDate'); ?>
		
		<?php echo $form->label($event,'endDate');

		Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
		$this->widget('CJuiDateTimePicker',array(
			'model'=>$event, //Model object
			'attribute'=>'completeDate', //attribute name
			'mode'=>'datetime', //use "time","date" or "datetime" (default)
			'options'=>array(
				'dateFormat'=>( (isset($this->controller))? $this->controller->formatDatePicker('medium') : $this->formatDatePicker('medium') ),
				'timeFormat'=>( (isset($this->controller))? $this->controller->formatTimePicker() : $this->formatTimePicker() ),
				'ampm'=>( (isset($this->controller))? $this->controller->formatAMPM() : $this->formatAMPM() ),
				'changeMonth'=>true,
				'changeYear'=>true,
			), // jquery plugin options
			'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			'htmlOptions'=>array('onClick'=>"$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
		));
		?>
		
		<?php echo $form->error($event,'completeDate'); ?>
		
		<?php echo $form->label($event, 'allDay'); ?>
		<?php echo $form->checkBox($event, 'allDay'); ?>
		

		
	</div>
	
	<div class="cell">
		<?php echo $form->label($event,'priority'); ?>
		<?php echo $form->dropDownList($event,'priority',array(
			'Low'=>Yii::t('actions','Low'),
			'Medium'=>Yii::t('actions','Medium'),
			'High'=>Yii::t('actions','High')));
		//echo $form->error($actionModel,'priority'); ?>
		
		<?php echo $form->label($event, 'color'); ?>
		<?php echo $form->dropDownList($event, 'color', Actions::getColors()); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($event,'assignedTo'); ?>
		<?php echo $form->dropDownList($event,'assignedTo',$users,array('id'=>'eventsAssignedToDropdown')); ?>
		<?php //echo $form->error($actionModel,'assignedTo'); ?>
            <?php /* x2temp */
                            echo "<br />";
                            if($this instanceof CController){
                                $url=$this->createUrl('/groups/getGroups');
                            }else{
                                $url=$this->controller->createUrl('/groups/getGroups');
                            }
                            echo "<label>".Yii::t('app','Group?')."</label>";
                            echo CHtml::checkBox('group','',array(
                                'id'=>'eventGroupCheckbox',
                                'ajax'=>array(
                                    'type'=>'POST', //request type
                                        'url'=>$url, //url to call.
                                        //Style: CController::createUrl('currentController/methodToCall')
                                        'update'=>'#eventsAssignedToDropdown', //selector to update
                                        'data'=>'js:{checked: $(this).attr("checked")=="checked"}',
                                        'complete'=>'function(){
                                            if($("#eventGroupCheckbox").attr("checked")!="checked"){
                                                $("#eventGroupCheckbox").attr("checked","checked");
                                                $("#Actions_visibility option[value=\'2\']").remove();
                                            }else{
                                                $("#eventGroupCheckbox").removeAttr("checked");
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
		<?php echo $form->label($event,'visibility'); ?>
                <?php
                    $visibility=array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private'));
                    /* x2temp */
                    $visibility[2]='User\'s Groups';
                    /* end x2temp */
                    ?>
		<?php echo $form->dropDownList($event,'visibility',$visibility); ?> 
		<?php //echo $form->error($actionModel,'visibility'); ?>
	</div>
	<div class="cell">
		<?php echo $form->label($event,'reminder'); ?>
		<?php //echo $form->checkBox($actionModel,'reminder',array('value'=>'Yes','uncheckedValue'=>'No')); ?>
		<?php echo $form->dropDownList($event,'reminder',array('No'=>Yii::t('actions','No'),'Yes'=>Yii::t('actions','Yes'))); ?> 
	</div>
	
</div>

<?php } ?>

</div>
</div>
<?php
if (!$quickCreate) {	//if we're not in quickCreate, end the form
?>
	<div class="row buttons">
		<?php echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app','Save'):Yii::t('app','Save'),
				array('type'=>'submit','class'=>'x2-button','id'=>'save-button1','name'=>'submit')); ?>
	</div>
<?php
$this->endWidget();
echo "</div>\n";
} 
?>