<?php
/*********************************************************************************
 * Copyright (C) 10011-10014 X2Engine Inc. All Rights Reserved.
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

Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
Yii::app()->clientScript->registerCss('eventTabCss',"

#calendar + br + #publisher-form #save-publisher {
    float: right !important;
}

");

if ($associationType === 'calendar') {
    $associationTypeOptions = X2Model::getAssociationTypeOptions ();
    unset ($associationTypeOptions['calendar']);
    $associationTypeOptions = 
        array ('calendar' => '-------------------') + $associationTypeOptions;
    $associationModels = array (); 
    // get the association type => model name mapping for available options
    foreach ($associationTypeOptions as $typ => $title) {
        $associationModels[$typ] = X2Model::getModelName ($typ);
    }
    Yii::app()->clientScript->registerScript('eventTabJS',"
(function () {

$('#Actions_associationType').change (function () {
    var that = this;
    var associationModels = ".CJSON::encode ($associationModels).";
    if ($(this).val () === 'calendar') {
        $('#association-type-autocomplete-container').hide ();
        return false;
    }
    x2.forms.inputLoading ($(this));
    $.ajax ({
        type: 'GET',
        url: '".Yii::app()->controller->createUrl ('ajaxGetModelAutocomplete')."',
        data: {
            modelType: associationModels[$(this).val ()],
            name: 'Actions[associationName]'
        },
        success: function (data) {
            if (data !== 'failure') {
                // remove span element used by jQuery widget
                $('#association-type-autocomplete-container input').
                    first ().next ('span').remove ();
                // replace old autocomplete with the new one
                $('#association-type-autocomplete-container input').first ().replaceWith (data); 
                $('#association-type-autocomplete-container').show ();
            } else {
                $('#association-type-autocomplete-container').hide ();
            }
            x2.forms.inputLoadingStop ($(that));
        }
    });
});

}) ();
    ", CClientScript::POS_READY);
}

?>

<div id='new-event' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>


    <div class="row">
        <div class="text-area-wrapper">
            <?php 
            echo $form->textArea(
                $model, 'actionDescription', 
                array(
                    'rows' => 3,
                    'cols' => 40,
                    'class'=>'action-description',
                    'id'=>'event-action-description'
                ));
            ?>
        </div>
    </div><!-- .row -->

    <div class="action-event-panel" class="row">

        <div class="cell action-duration">
            <div class="action-duration-input">
                <label for="timetrack-hours"><?php echo Yii::t('actions','Hours'); ?></label>
                <input class="action-duration-display" type="number" min="0" max="99" 
                 name="timetrack-hours" />
            </div>
            <span class="action-duration-display">:</span>
            <div class="action-duration-input">
                <label for="timetrack-minutes"><?php echo Yii::t('actions','Minutes'); ?></label>
                <input class="action-duration-display" type="number" min="0" max="59" 
                 name="timetrack-minutes" />
            </div>
        </div>

        <div class="cell">

            <?php 
            $model->type = 'event';
            echo CHtml::activeLabel(
                $model,'dueDate',
                array('class' => 'action-start-time-label')); 
            $this->widget('CJuiDateTimePicker', array(
                'model' => $model, //Model object
                'attribute' => 'dueDate', //attribute name
                'mode' => 'datetime', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'ampm' => Formatter::formatAMPM(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                'htmlOptions' => array(
                    'class'=>'action-due-date',
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');",
                    'id' => 'event-form-action-due-date'
                ), // fix datepicker so it's always on top
            ));

            echo CHtml::activeLabel(
                $model,'completeDate', 
                array('class' => 'action-end-time-label'));
            $this->widget('CJuiDateTimePicker', array(
                'model' => $model, //Model object
                'attribute' => 'completeDate', //attribute name
                'mode' => 'datetime', //use "time","date" or "datetime" (default)
                'options' => array(
                    'dateFormat' => Formatter::formatDatePicker('medium'),
                    'timeFormat' => Formatter::formatTimePicker(),
                    'ampm' => Formatter::formatAMPM(),
                    'changeMonth' => true,
                    'changeYear' => true,
                ), // jquery plugin options
                'language' => (Yii::app()->language == 'en') ? '' : Yii::app()->getLanguage(),
                'htmlOptions' => array(
                    // fix datepicker so it's always on top
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');", 
                    'class' => 'action-complete-date x2-forms',
                    'id' => 'event-form-action-complete-date'
                ),
            ));
            ?>
        </div>
        
        <div class="cell">
            <?php 
            echo CHtml::activeLabel(
                $model,'priority',
                array('class'=>'action-priority-label')); 
            echo $form->dropDownList($model, 'priority', array(
                '1' => Yii::t('actions', 'Low'),
                '2' => Yii::t('actions', 'Medium'),
                '3' => Yii::t('actions', 'High'))
                    ,
                array('class'=>'action-priority')
            );
            echo $form->label($model, 'color',array('id'=>'action-color-label')); 
            echo $form->dropDownList(
                $model, 'color', Actions::getColors(),array('id'=>'action-color-dropdown')); 
            ?>
        </div><!-- .cell -->
           
        <?php /* Assigned To */ ?>
        <div class="cell">
            <?php 
            /* Users */ 
            echo $form->label($model, 'assignedTo',array('class'=>'action-assigned-to-label')); 
            echo $model->renderInput (
                'assignedTo', array('class' => 'action-assignment-dropdown')); 
            ?>
        </div><!-- .cell -->
        <div class='cell'>
            <?php
            echo $form->label($model, 'visibility',array('class'=>'action-visibility-label')); 
            echo $form->dropDownList(
                $model, 'visibility', 
                array(
                    0 => Yii::t('actions', 'Private'), 1 => Yii::t('actions', 'Public'),
                    2 => Yii::t('actions', "User's Group")
                ),
                array('class'=>'action-visibility-dropdown')); 

            ?>
        </div>
        <?php
        if ($associationType === 'calendar') {
        ?>
        <div class='cell'>
            <?php
            echo $form->label(
                $model, 'associationType',
                array('class'=>'action-associationType-label')); 

            echo $form->dropDownList(
                $model, 'associationType', 
                $associationTypeOptions,
                array('class'=>'action-associationType-dropdown')); 
            ?>
            <div id='association-type-autocomplete-container' <?php 
             echo ($model->associationType === 'calendar' ? 'style="display: none;"' : ''); ?>>
            <?php
                echo CHtml::label(
                    Yii::t('app', 'Association Name'),
                    'associationName',
                    array('class'=>'action-associationName-label')); 
                $autocomplete = X2Model::renderModelAutocomplete (
                    X2Model::getModelName ($model->associationType), false, array (
                        'name' => 'Actions[associationName]'
                    ));
                if ($autocomplete !== 'failure') {
                    echo $autocomplete;
                } else {
                    // dummy input to be replaced with autocomplete
                    echo '<input disabled="disabled">';
                }
                echo $form->hiddenField($model, 'associationId', array (
                    'data-default' => ''
                )); 
            ?>
            </div>
            <?php
            echo CHtml::hiddenField('calendarEventTab', true); 
            ?>
        </div>
        <?php  
        }
        ?> 
        
    </div><!-- #action-event-panel -->
</div>
