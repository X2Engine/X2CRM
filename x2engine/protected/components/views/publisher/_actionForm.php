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

<div id='new-action' class='publisher-form' style='display: none;'>

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
            ?>
        </div><!-- .cell -->
           
        <?php /* Assigned To */ ?>
        <div class="cell">
            <?php 
            /* Users */ 
            echo $form->label($model, 'assignedTo',array('class'=>'action-assigned-to-label')); 
            echo $form->dropDownList(
                $model, 'assignedTo', X2Model::getAssignmentOptions(true,true), 
                array('class' => 'action-assignment-dropdown')); 
            echo $form->label($model, 'visibility',array('class'=>'action-visibility-label')); 
            echo $form->dropDownList(
                $model, 'visibility', 
                array(
                    0 => Yii::t('actions', 'Private'), 1 => Yii::t('actions', 'Public'),
                    2 => Yii::t('actions', "User's Group")
                ),
                array('class'=>'action-visibility-dropdown')); 
            ?>
        </div><!-- .cell -->
        
    </div><!-- #action-event-panel -->
</div>
