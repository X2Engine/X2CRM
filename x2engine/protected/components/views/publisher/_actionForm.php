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
?>

<div id='new-action' class='publisher-form' 
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
                    'id'=>'action-action-description'
                ));
            ?>
        </div>
    </div><!-- .row -->

    
    <div class="action-event-panel" class="row">
        
        <div class="cell">

            <?php 
            unset ($model->type); // get the correct label
            echo CHtml::activeLabel(
                $model,'dueDate',
                array('class' =>  'action-due-date-label')); 
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
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');"
                ), // fix datepicker so it's always on top
            ));
            ?>
        </div><!-- .cell  - the due date and reminder option-->
            
        <div class="cell">
            <?php 
            echo CHtml::activeLabel(
                $model,'priority',
                array('class'=>'action-priority-label')); 
            echo $form->dropDownList($model, 'priority', Actions::getPriorityLabels(),
                array('class'=>'action-priority x2-select')
            );
            ?>
        </div><!-- .cell -->
           
        <div class="cell">
            <?php /* Assigned To */ ?>
            <div class="cell">
                <?php 
                /* Users */ 
                echo $form->label($model, 'assignedTo',array('class'=>'action-assigned-to-label')); 
                echo $model->renderInput (
                    'assignedTo', array('class' => 'action-assignment-dropdown')); 
                ?>
            </div><!-- .cell -->
            <div class="cell">
                <?php 
                echo $form->label($model, 'visibility',array('class'=>'action-visibility-label')); 
                echo $form->dropDownList(
                    $model, 'visibility', 
                    X2PermissionsBehavior::getVisibilityOptions(),
                    array(
                        'class'=>'action-visibility-dropdown x2-select',
                    )); 
                ?>
            </div><!-- .cell -->
        </div>
        <div class='row' id='action-reminder-container'>
            <?php 
            echo $form->checkBox($model, 'reminder', array ('data-default' => '0')); 
            echo CHtml::label(Yii::t('actions', 'Create Reminder'),'reminder');
            ?>
            <div style='display: none;' id='action-reminder-inputs'>
                <?php 
                echo 
                    '<div>'.Yii::t('actions', 'Create a notification reminder for ').'</div>'.
                    CHtml::dropDownList(
                        'notificationUsers', !empty($notifType) ? $notifType : 'assigned',
                        array(
                            'me' => Yii::t('actions', 'me'),
                            'assigned' => Yii::t('actions', 'the assigned user'),
                            'both' => Yii::t('actions', 'me and the assigned user'),
                        ),
                        array (
                            'class' => 'left'
                        )
                    ).
                    CHtml::dropDownList(
                        'notificationTime', !empty($notifTime) ? $notifTime : 15,
                        array(
                            1 => Yii::t('actions', '1 minute'),
                            5 => Yii::t('actions', '5 minutes'),
                            10 => Yii::t('actions', '10 minutes'),
                            15 => Yii::t('actions', '15 minutes'),
                            30 => Yii::t('actions', '30 minutes'),
                            60 => Yii::t('actions', '1 hour')
                        )
                    ).
                    '<div>'.Yii::t('actions', ' before this action is due.').'</div>';
                ?>
            </div><!-- #action-reminders -->
        </div>
        
    </div><!-- #action-event-panel -->
</div>
