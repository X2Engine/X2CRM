<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

Yii::app()->clientScript->registerScript('validate', '
$(document).ready(function(){
	$("#actions-newCreate-form").submit(function(){
		if($("#'.CHtml::activeId($actionModel, 'associationType').'").val()!="none"){
			if($("#'.CHtml::activeId($actionModel, 'associationId').'").val()==""){
				alert("'.Yii::t('actions', "Please enter a valid association").'");
				return false;
			}
		}
        if($("#'.CHtml::activeId($actionModel, 'actionDescription').'").val()=="" && $("#'.CHtml::activeId($actionModel, 'subject').'").val()==""){
            alert("'.Yii::t('actions', "Please enter a description or subject").'");
            return false;
        }
	}
	);
}
);');
Yii::app()->clientScript->registerScript('highlightSaveAction', "
$(function(){
	$('#action-form input, #action-form select, #action-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').addClass('highlight'); //css('background','yellow');
	}
	);
}
);");
$themeUrl = Yii::app()->theme->getBaseUrl();
$backdating = !(Yii::app()->user->checkAccess('ActionsAdmin') || Yii::app()->params->admin->userActionBackdating);
?>
<div class="form" id="action-form">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'actions-newCreate-form',
        'enableAjaxValidation' => false,
            ));
    echo $form->errorSummary($actionModel);
    ?>
    <div class="row">
        <b><?php echo $form->labelEx($actionModel, 'subject'); ?></b>
        <?php echo $form->textField($actionModel, 'subject', array('size' => 80)); ?>
        <div class="row">
            <b><?php echo $form->labelEx($actionModel, 'actionDescription'); ?></b>
            <?php //echo $form->label($actionModel,'actionDescription'); ?>
            <div class="text-area-wrapper">
                <?php echo $form->textArea($actionModel, 'actionDescription', array('rows' => (6), 'cols' => 40)); ?>
                <?php //echo $form->error($actionModel,'actionDescription'); ?>
            </div>
            <div class="row">
                <div class="cell">
                    <?php echo $form->label($actionModel, 'associationType'); ?>
                    <?php
                    echo $form->dropDownList($actionModel, 'associationType', array_merge(array('none' => Yii::t('app','None'), 'calendar' => Yii::t('calendar', 'Calendar')), $modelList), array(
                        'ajax' => array(
                            'type' => 'POST', //request type
                            'url' => CController::createUrl('/actions/actions/parseType'), //url to call.
                            //Style: CController::createUrl('currentController/methodToCall')
                            'update' => '#', //selector to update
                            'success' => 'function(data){
                                        if(data){
                                            $("#auto_select").autocomplete("option","source",data);
                                            $("#auto_select").val("");
                                            $("#auto_complete").show();
                                        }else{
                                            $("#auto_complete").hide();
                                        }
                                    }'
                        )
                            )
                    );
                    echo $form->error($actionModel, 'associationType');
                    if($actionModel->associationType != 'none'){
                        $linkModel = X2Model::getModelName($actionModel->associationType);
                    }else{
                        $linkModel = null;
                    }
                    if(!empty($linkModel) && class_exists($linkModel)){
                        if($linkModel == 'X2Calendar')
                            $linkSource = '';
                        else
                            $linkSource = $this->createUrl(X2Model::model($linkModel)->autoCompleteSource);
                    }else{
                        $linkSource = "";
                    }
                    ?>
                </div>
                <div class="cell" id="auto_complete" style="<?php echo empty($linkSource) ? "display:none;" : "" ?>">
                    <?php
                    echo $form->label($actionModel, 'associationName');
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                        'name' => 'auto_select',
                        'value' => $actionModel->associationName,
                        'source' => $linkSource,
                        'options' => array(
                            'minLength' => '2',
                            'select' => 'js:function( event, ui ) {
                            $("#'.CHtml::activeId($actionModel, 'associationId').'").val(ui.item.id);
                            $(this).val(ui.item.value);
                            return false;
                        }',
                        ),
                    ));
                    ?>
                </div>
                <div class="cell">
                    <?php echo $form->hiddenField($actionModel, 'associationId'); ?>
                    <?php
                    if($actionModel->type == 'event')
                        echo $form->label($actionModel, 'startDate');
                    else
                        echo $form->label($actionModel, 'dueDate');
                    if(is_numeric($actionModel->dueDate))
                        $actionModel->dueDate = Formatter::formatDateTime($actionModel->dueDate); //format date from DATETIME
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $actionModel, //Model object
                        'attribute' => 'dueDate', //attribute name
                        'mode' => 'datetime', //use "time","date" or "datetime" (default)
                        'options' => array(
                            'dateFormat' => ( (isset($this->controller)) ? Formatter::formatDatePicker('medium') : Formatter::formatDatePicker('medium') ),
                            'timeFormat' => ( (isset($this->controller)) ? Formatter::formatTimePicker() : Formatter::formatTimePicker() ),
                            'ampm' => ( (isset($this->controller)) ? Formatter::formatAMPM() : Formatter::formatAMPM() ),
                            'changeMonth' => true,
                            'changeYear' => true
                        ), // jquery plugin options
                        'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                        'htmlOptions' => array('onClick' => "$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
                    ));
                    ?>
                    <?php echo $form->error($actionModel, 'dueDate'); ?>
                    <?php
                    if($actionModel->type == 'event'){
                        echo $form->label($actionModel, 'endDate');
                        if($actionModel->isNewRecord)
                            if(isset($this->controller)) // inline action?
                                $actionModel->completeDate = Formatter::formatDateEndOfDay(time()); //default to tomorow for new actions
                            else
                                $actionModel->completeDate = Formatter::formatDateEndOfDay(time()); //default to tomorow for new actions
                                else
                            $actionModel->completeDate = Formatter::formatDateTime($actionModel->completeDate); //format date from DATETIME

                        Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                        $this->widget('CJuiDateTimePicker', array(
                            'model' => $actionModel, //Model object
                            'attribute' => 'completeDate', //attribute name
                            'mode' => 'datetime', //use "time","date" or "datetime" (default)
                            'options' => array(
                                'dateFormat' => ( (isset($this->controller)) ? Formatter::formatDatePicker('medium') : Formatter::formatDatePicker('medium') ),
                                'timeFormat' => ( (isset($this->controller)) ? Formatter::formatTimePicker() : Formatter::formatTimePicker() ),
                                'ampm' => ( (isset($this->controller)) ? Formatter::formatAMPM() : Formatter::formatAMPM() ),
                                'changeMonth' => true,
                                'changeYear' => true
                            ), // jquery plugin options
                            'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                            'htmlOptions' => array('onClick' => "$('#ui-datepicker-div').css('z-index', '20');"), // fix datepicker so it's always on top
                        ));
                        echo $form->error($actionModel, 'completeDate');
                        echo $form->label($actionModel, 'allDay');
                        echo $form->checkBox($actionModel, 'allDay');
                    }
                    ?>
                </div>
                <div class="cell">
                    <?php echo $form->label($actionModel, 'priority'); ?>
                    <?php
                    echo $form->dropDownList($actionModel, 'priority', array(
                        '1' => Yii::t('actions', 'Low'),
                        '2' => Yii::t('actions', 'Medium'),
                        '3' => Yii::t('actions', 'High')));
                    ?>

                    <?php
                    if($actionModel->type == 'event'){
                        echo $form->label($actionModel, 'color');
                        ?>
                        <?php
                        echo $form->dropDownList($actionModel, 'color', Actions::getColors());
                    }
                    ?>
                </div>
                <div class="cell">
                    <?php echo $form->label($actionModel, 'assignedTo'); ?>
                    <?php echo $form->dropDownList($actionModel, 'assignedTo', X2Model::getAssignmentOptions(), array('id' => 'actionsAssignedToDropdown')); ?>
                    <?php echo $form->error($actionModel, 'assignedTo'); ?>
                </div>

                <div class="cell">
                    <?php echo $form->label($actionModel, 'visibility'); ?>
                    <?php
                    $visibility = array(1 => Yii::t('actions', 'Public'), 0 => Yii::t('actions', 'Private'));
                    ?>
                    <?php echo $form->dropDownList($actionModel, 'visibility', $visibility); ?>
                </div>
                <div class="cell buttons" style="float:right;">
                    <?php echo CHtml::htmlButton($actionModel->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save'), array('type' => 'submit', 'class' => 'x2-button', 'id' => 'save-button1', 'name' => 'submit')); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form">
        <span><?php echo CHtml::link(CHtml::image($themeUrl.'/images/icons/Collapse_Widget.png', '', array('style' => 'float:left;'))."<label style='cursor:pointer'>&nbsp;".Yii::t('actions', 'Action Reminders')."</label>", '#', array('id' => 'reminder-link', 'style' => 'color:black;text-decoration:none;')); ?></span>
        <div id="action-reminders">
            <br>
            <?php echo $form->checkBox($actionModel, 'reminder'); ?>
            <?php
            echo Yii::t('actions', 'Create a notification reminder for {user} {time} before this action is due', array(
                '{user}' => CHtml::dropDownList('notificationUsers', !empty($notifType) ? $notifType : 'assigned', array(
                    'me' => Yii::t('actions', 'me'),
                    'assigned' => Yii::t('actions', 'the assigned user'),
                    'both' => Yii::t('actions', 'me and the assigned user'),
                )),
                '{time}' => CHtml::dropDownList('notificationTime', !empty($notifTime) ? $notifTime : 15, array(
                    1 => Yii::t('actions','1 minute'),
                    5 => Yii::t('actions','5 minutes'),
                    10 => Yii::t('actions','10 minutes'),
                    15 => Yii::t('actions','15 minutes'),
                    30 => Yii::t('actions','30 minutes'),
                    60 => Yii::t('actions','1 hour')
                )),
            ));
            ?>
        </div>
    </div>
    <div class="form">
        <span><?php echo CHtml::link(CHtml::image($themeUrl.'/images/icons/Expand_Inverted.png', '', array('style' => 'float:left;'))."<label style='cursor:pointer;'>&nbsp;".Yii::t('actions','Action Backdating')."</label>", '#', array('id' => 'backdating-link', 'style' => 'color:black;text-decoration:none;')); ?></span>
        <div id="action-backdating" style="display:none;" class="row">
            <br>
            <div class="cell">
                <?php echo $form->labelEx($actionModel, 'createDate'); ?>
                <?php
                $actionModel->createDate = Formatter::formatDateTime($actionModel->createDate);
                $this->widget('CJuiDateTimePicker', array(
                    'model' => $actionModel, //Model object
                    'attribute' => 'createDate', //attribute name
                    'mode' => 'datetime', //use "time","date" or "datetime" (default)
                    'options' => array(
                        'dateFormat' => ( (isset($this->controller)) ? Formatter::formatDatePicker('medium') : Formatter::formatDatePicker('medium') ),
                        'timeFormat' => ( (isset($this->controller)) ? Formatter::formatTimePicker() : Formatter::formatTimePicker() ),
                        'ampm' => ( (isset($this->controller)) ? Formatter::formatAMPM() : Formatter::formatAMPM() ),
                        'changeMonth' => false,
                        'changeYear' => true,
                    ), // jquery plugin options
                    'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                    'htmlOptions' => array(
                        'disabled' => $backdating,
                    ),
                ));
                ?>
            </div>
            <div class="cell">
                <?php echo $form->labelEx($actionModel, 'lastUpdated'); ?>
                <?php
                $actionModel->lastUpdated = Formatter::formatDateTime($actionModel->lastUpdated);
                $this->widget('CJuiDateTimePicker', array(
                    'model' => $actionModel, //Model object
                    'attribute' => 'lastUpdated', //attribute name
                    'mode' => 'datetime', //use "time","date" or "datetime" (default)
                    'options' => array(
                        'dateFormat' => ( (isset($this->controller)) ? Formatter::formatDatePicker('medium') : Formatter::formatDatePicker('medium') ),
                        'timeFormat' => ( (isset($this->controller)) ? Formatter::formatTimePicker() : Formatter::formatTimePicker() ),
                        'ampm' => ( (isset($this->controller)) ? Formatter::formatAMPM() : Formatter::formatAMPM() ),
                        'changeMonth' => false,
                        'changeYear' => true,
                    ), // jquery plugin options
                    'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                    'htmlOptions' => array(
                        'disabled' => $backdating,
                    ),
                ));
                ?>
            </div>
            <?php if($actionModel->complete == 'Yes'){ ?>
                <div class="cell">
                    <?php echo $form->labelEx($actionModel, 'completeDate'); ?>
                    <?php
                    $actionModel->completeDate = Formatter::formatDateTime($actionModel->completeDate);
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $actionModel, //Model object
                        'attribute' => 'completeDate', //attribute name
                        'mode' => 'datetime', //use "time","date" or "datetime" (default)
                        'options' => array(
                            'dateFormat' => ( (isset($this->controller)) ? Formatter::formatDatePicker('medium') : Formatter::formatDatePicker('medium') ),
                            'timeFormat' => ( (isset($this->controller)) ? Formatter::formatTimePicker() : Formatter::formatTimePicker() ),
                            'ampm' => ( (isset($this->controller)) ? Formatter::formatAMPM() : Formatter::formatAMPM() ),
                            'changeMonth' => false,
                            'changeYear' => true,
                        ), // jquery plugin options
                        'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                        'htmlOptions' => array(
                            'disabled' => $backdating,
                        ),
                    ));
                    ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>
<script>
    $(document).on('click','#reminder-link',function(e){
        e.preventDefault();
        if($('#action-reminders').is(':hidden')){
            $('#action-reminders').slideDown();
            $(this).find('img').attr('src',yii.themeBaseUrl+'/images/icons/Collapse_Widget.png');
        }else{
            $('#action-reminders').slideUp();
            $(this).find('img').attr('src',yii.themeBaseUrl+'/images/icons/Expand_Inverted.png');
        }
    });
    $(document).on('click','#backdating-link',function(e){
        e.preventDefault();
        if($('#action-backdating').is(':hidden')){
            $('#action-backdating').slideDown();
            $(this).find('img').attr('src',yii.themeBaseUrl+'/images/icons/Collapse_Widget.png');
        }else{
            $('#action-backdating').slideUp();
            $(this).find('img').attr('src',yii.themeBaseUrl+'/images/icons/Expand_Inverted.png');
        }
    });
    $(document).on('ready',function(){
        $('#Actions_subject').focus();
    });
</script>
