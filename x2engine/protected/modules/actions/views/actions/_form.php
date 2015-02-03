<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

// default view parameters
$modelList = !isset ($modelList) ? Fields::getDisplayedModelNamesList() : $modelList;
$actionModel = !isset ($actionModel) ? $model : $actionModel;

Yii::app()->clientScript->registerCss('actionsFormCss',"
    #Actions_actionDescription {
        box-sizing: border-box;
    }
");

Yii::app()->clientScript->registerScript('validate', '
$(document).ready(function(){
	$("#actions-newCreate-form").submit(function(){
        x2.forms.clearErrorMessages ($("#action-form"));
		if($("#action-form #'.CHtml::activeId($actionModel, 'associationType').'").val()!="none"){
			if($("#action-form #'.CHtml::activeId($actionModel, 'associationId').'").val()==""){
                $("#auto_select").addClass ("error");
                x2.forms.errorSummaryAppend ($("#action-form"), [
				    "'.Yii::t('actions', "Please enter a valid association").'"
                ]);
				return false;
			}
		}
        var actionDescription$ = 
            $("#action-form #'.CHtml::activeId($actionModel, 'actionDescription').'");
        if(actionDescription$.hasClass ("x2-required") && 
           actionDescription$.val()=="" && 
           $("#'.CHtml::activeId($actionModel, 'subject').'").val()==""){

            actionDescription$.addClass ("error");
            $("#'.CHtml::activeId($actionModel, 'subject').'").addClass ("error");
            x2.forms.errorSummaryAppend ($("#action-form"), [
                "'.Yii::t('actions', "Please enter a description or subject").'"
            ]);
            return false;
        }
	}
	);
}
);');
Yii::app()->clientScript->registerScript('highlightSaveAction', "
$(function(){
	$('#action-form input, #action-form select, #action-form textarea').change(function(){
        //css('background','yellow');
		$('#save-button, #save-button1, #save-button2').addClass('highlight'); 
	});
}
);");
$themeUrl = Yii::app()->theme->getBaseUrl();
$backdating = !(Yii::app()->user->checkAccess('ActionsAdmin') || 
    Yii::app()->settings->userActionBackdating);
?>
<div class="form" id="action-form">
    <?php
    $form = $this->beginWidget('X2ActiveForm', array(
        'id' => 'actions-newCreate-form',
        'namespace' => isset ($namespace) ? $namespace : '',
        'enableAjaxValidation' => false,
    ));
    echo $form->errorSummary($actionModel);
    ?>
    <div class="row">
        <b><?php echo $form->labelEx($actionModel, 'subject'); ?></b>
        <?php 
        echo $form->textField($actionModel, 'subject', array('class' => 'x2-xxwide-input')); 
        ?>
        <div class="row">
            <b><?php echo $form->labelEx($actionModel, 'actionDescription'); ?></b>
            <div>
                <?php 
                echo $actionModel->renderInput ('actionDescription',
                    array(
                        'class' => 'x2-xxwide-input', 'rows' => 6
                    )); 
                ?>
            </div>
            <div class="row">
                <div class="cell">
                    <?php echo $form->label($actionModel, 'associationType'); 
                    echo $form->dropDownList(
                        $actionModel, 'associationType', 
                        array_merge(array('none' => Yii::t('app','None')), $modelList), 
                        array(
                            'ajax' => array(
                                'type' => 'POST', //request type
                                //url to call.
                                'url' => CController::createUrl('/actions/actions/parseType'), 
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
                            $linkSource = $this->createUrl(
                                X2Model::model($linkModel)->autoCompleteSource);
                    }else{
                        $linkSource = "";
                    }
                    ?>
                </div>
                <div class="cell" id="auto_complete" 
                 style="<?php echo empty($linkSource) ? "display:none;" : "" ?>">
                    <?php
                    echo $form->label($actionModel, 'associationName');
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                        'name' => 'auto_select',
                        'value' => $actionModel->associationName,
                        'source' => $linkSource,
                        'options' => array(
                            'minLength' => '2',
                            'select' => 'js:function( event, ui ) {
                                $("#'.CHtml::activeId($actionModel, 'associationId').'").
                                    val(ui.item.id);
                                $(this).val(ui.item.value);
                                return false;
                            }',
                        ),
                    ));
                    ?>
                </div>
                <div class="cell">
                    <?php 
                    echo $form->hiddenField($actionModel, 'associationId');
                    if(!$actionModel->isTimedType) {
                        if($actionModel->type == 'event')
                            echo $form->label($actionModel, 'startDate');
                        else
                            echo $form->label($actionModel, 'dueDate');
                    if(is_numeric($actionModel->dueDate))
                        $actionModel->dueDate = Formatter::formatDateTime(
                            $actionModel->dueDate); //format date from DATETIME

                    echo X2Html::activeDatePicker ($actionModel, 'dueDate', 
                        $form->resolveHtmlOptions ($actionModel, 'dueDate', array(
                            // fix datepicker so it's always on top
                            'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                        )), 'datetime', array (
                            'dateFormat' => Formatter::formatDatePicker ('medium'),
                            'timeFormat' => Formatter::formatTimePicker (),
                            'ampm' => Formatter::formatAMPM (),
                        ));
                    echo $form->error($actionModel, 'dueDate'); 
                    ?>
                    <?php
                    if($actionModel->type == 'event'){
                        echo $form->label($actionModel, 'endDate');
                        if($actionModel->isNewRecord)
                            if(isset($this->controller)) // inline action?
                                //default to tomorow for new actions
                                $actionModel->completeDate = Formatter::formatDateEndOfDay(time()); 
                            else
                                //default to tomorow for new actions
                                $actionModel->completeDate = Formatter::formatDateEndOfDay(time()); 
                        else
                            //format date from DATETIME
                            $actionModel->completeDate = Formatter::formatDateTime( 
                                $actionModel->completeDate); 
                        echo X2Html::activeDatePicker ($actionModel, 'completeDate', 
                            $form->resolveHtmlOptions ($actionModel, 'completeDate', array(
                                // fix datepicker so it's always on top
                                'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                            )), 'datetime', array (
                                'dateFormat' => Formatter::formatDatePicker ('medium'),
                                'timeFormat' => Formatter::formatTimePicker (),
                                'ampm' => Formatter::formatAMPM (),
                            ));
                        echo $form->error($actionModel, 'completeDate');
                        echo $form->label($actionModel, 'allDay');
                        echo $form->checkBox($actionModel, 'allDay');
                    }
                    }
                    ?>
                </div>
                <div class="cell">
                    <?php 
                    echo $form->label($actionModel, 'priority');
                    echo $form->dropDownList(
                        $actionModel, 'priority', Actions::getPriorityLabels());
                    if($actionModel->type == 'event'){
                        echo $form->label($actionModel, 'color');
                        echo $actionModel->renderInput('color');
                    }
                    ?>
                </div>
                <div class="cell">
                    <?php 
                    echo $form->label($actionModel, 'assignedTo');
                    echo $actionModel->renderInput (
                        'assignedTo', array('id' => 'actionsAssignedToDropdown')); 
                    echo $form->error($actionModel, 'assignedTo'); 
                    ?>
                </div>

                <div class="cell">
                    <?php 
                    echo $form->label($actionModel, 'visibility');
                    $visibility = array(
                        1 => Yii::t('actions', 'Public'), 0 => Yii::t('actions', 'Private'));
                    echo $form->dropDownList($actionModel, 'visibility', $visibility); 
                    ?>
                </div>
                <div class="cell buttons" style="float:right;">
                    <?php 
                    echo CHtml::htmlButton(
                        $actionModel->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Save'),
                        array(
                            'type' => 'submit',
                            'class' => 'x2-button',
                            'id' => 'save-button1',
                            'name' => 'submit'
                        )); 
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="form">
        <span><?php 
        echo CHtml::link(
            CHtml::image(
                $themeUrl.'/images/icons/Collapse_Widget.png', '', array('style' => 'float:left;')).
                "<label style='cursor:pointer'>&nbsp;".
                    Yii::t(
                        'actions', '{action} Reminders', 
                        array('{action}'=>Modules::displayName(false))).
                "</label>", '#', 
                array(
                    'id' => 'reminder-link',
                    'style' => 'color:black;text-decoration:none;'
                )); 
        ?></span>
        <div id="action-reminders">
            <br>
            <?php 
            echo $form->checkBox($actionModel, 'reminder'); 
            echo Yii::t(
                'actions',
                'Create a notification reminder for {user} {time} before this {action} is due',
                array(
                    '{user}' => CHtml::dropDownList(
                        'notificationUsers', 
                        !empty($notifType) ? $notifType : 'assigned', 
                        array(
                            'me' => Yii::t('actions', 'me'),
                            'assigned' => Yii::t('actions', 'the assigned user'),
                            'both' => Yii::t('actions', 'me and the assigned user'),
                        )
                    ),
                    '{time}' => CHtml::dropDownList(
                        'notificationTime', !empty($notifTime) ? $notifTime : 15, array(
                            1 => Yii::t('actions','1 minute'),
                            5 => Yii::t('actions','5 minutes'),
                            10 => Yii::t('actions','10 minutes'),
                            15 => Yii::t('actions','15 minutes'),
                            30 => Yii::t('actions','30 minutes'),
                            60 => Yii::t('actions','1 hour'),
                            1440 => Yii::t('actions','1 day'),
                            10080 => Yii::t('actions','1 week')
                        )),
                    '{action}' => lcfirst(Modules::displayName(false)),
                ));
            ?>
        </div>
    </div>
    <div class="form">
        <span>
            <?php
            $linkContent = CHtml::image($themeUrl.'/images/icons/Expand_Inverted.png', '', array(
                'style' => 'float:left;'
            ))."<label style='cursor:pointer;'>&nbsp;".
                Yii::t('actions','{action} Backdating', array(
                    '{action}' => Modules::displayName(false),
                ))."</label>";
            echo CHtml::link($linkContent, '#', array(
                'id' => 'backdating-link',
                'style' => 'color:black;text-decoration:none;'
            )); ?>
        </span>
        <div id="action-backdating" style="display:none;" class="row">
            <br>
            <div class="cell">
                <?php 
                echo $form->labelEx($actionModel, 'createDate'); 
                $actionModel->createDate = Formatter::formatDateTime($actionModel->createDate);
                echo X2Html::activeDatePicker ($actionModel, 'createDate',
                    $form->resolveHtmlOptions ($actionModel, 'createDate', array(
                        // fix datepicker so it's always on top
                        'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                    )), 'datetime', array (
                        'dateFormat' => Formatter::formatDatePicker ('medium'),
                        'timeFormat' => Formatter::formatTimePicker (),
                        'ampm' => Formatter::formatAMPM (),
                        'changeMonth' => false,
                    ));
                ?>
            </div><!-- .cell -->
            <div class="cell">
                <?php echo $form->labelEx($actionModel, 'lastUpdated'); ?>
                <?php
                $actionModel->lastUpdated = Formatter::formatDateTime($actionModel->lastUpdated);
                echo X2Html::activeDatePicker ($actionModel, 'lastUpdated', 
                    $form->resolveHtmlOptions ($actionModel, 'lastUpdated', array(
                        // fix datepicker so it's always on top
                        'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                    )), 'datetime', array (
                        'dateFormat' => Formatter::formatDatePicker ('medium'),
                        'timeFormat' => Formatter::formatTimePicker (),
                        'ampm' => Formatter::formatAMPM (),
                        'changeMonth' => false,
                    ));
                ?>
            </div><!-- .cell -->
            <?php if($actionModel->isTimedType) { ?>
            <div class="cell">
                <?php echo $form->labelEx($actionModel, 'startDate'); ?>
                <?php
                $actionModel->dueDate = Formatter::formatDateTime($actionModel->dueDate);
                echo X2Html::activeDatePicker ($actionModel, 'dueDate', 
                    $form->resolveHtmlOptions ($actionModel, 'dueDate', array(
                        // fix datepicker so it's always on top
                        'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                    )), 'datetime', array (
                        'dateFormat' => Formatter::formatDatePicker ('medium'),
                        'timeFormat' => Formatter::formatTimePicker (),
                        'ampm' => Formatter::formatAMPM (),
                        'changeMonth' => false,
                    ));
                ?>
            </div>
            <?php 
            }
            if($actionModel->complete == 'Yes' || $actionModel->isTimedType){ ?>
                <div class="cell">
                    <?php 
                    echo $form->labelEx(
                        $actionModel, $actionModel->isTimedType ? 'endDate' : 'completeDate');
                    $actionModel->completeDate = Formatter::formatDateTime(
                        $actionModel->completeDate);
                    echo X2Html::activeDatePicker ($actionModel, 'completeDate',
                        $form->resolveHtmlOptions ($actionModel, 'completeDate', array(
                            // fix datepicker so it's always on top
                            'onClick' => "$('#ui-datepicker-div').css('z-index', '20');"
                        )), 'datetime', array (
                            'dateFormat' => Formatter::formatDatePicker ('medium'),
                            'timeFormat' => Formatter::formatTimePicker (),
                            'ampm' => Formatter::formatAMPM (),
                            'changeMonth' => false,
                        ));
                    ?>
                </div>
            <?php 
            } 
            ?>
        </div><!-- #action-backdating -->
</div><!-- .form -->
<?php 
if(!$backdating && 
    file_exists(__DIR__.DIRECTORY_SEPARATOR.'_actionTimersForm.php') && 
    $actionModel->complete == 'Yes') { 

    $this->renderPartial('_actionTimersForm',array(
        'model' => $actionModel,
        'form' => $form,
    ));
} 
?>
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
