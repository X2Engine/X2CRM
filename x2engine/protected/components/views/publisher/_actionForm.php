<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
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

Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
?>

<div id='new-action' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>

    <div class="row">
        <div class="text-area-wrapper">
            <?php 
            echo $model->renderInput ('actionDescription',
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
                    '<div>'.Yii::t('actions', ' before this {action} is due.', array(
                        '{action}'=>strtolower(Modules::displayName(false, 'Actions'))
                    )).'</div>';
                ?>
            </div><!-- #action-reminders -->
        </div>
        
    </div><!-- #action-event-panel -->
</div>
