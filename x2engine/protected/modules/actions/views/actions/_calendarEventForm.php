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




$submitButton = isset($submitButton) ? $submitButton : true;
$htmlOptions = !isset($htmlOptions) ? array() : $htmlOptions;
$namespace = !isset($namespace) ? null : $namespace;

$form = $this->beginWidget('CalendarEventActiveForm', array(
    'formModel' => $model,
    'htmlOptions' => $htmlOptions,
    'namespace' => $namespace,
        ));
echo $form->textArea($model, 'actionDescription');
?>
<div class="row">
    <div class="cell">
        <?php
        if (empty($model->calendarId)) {
            $model->calendarId = Yii::app()->params->profile->defaultCalendar;
        }
        echo $form->label($model, 'calendarId');
        echo $form->dropDownList($model, 'calendarId', X2CalendarPermissions::getEditableUserCalendarNames());
        ?>
    </div>
    <div class="cell">
        <?php
            echo $form->label($model, 'allDay');
            echo $form->renderInput($model, 'allDay');
        ?>
    </div>
    <div class="cell">
        <?php 
            echo $form->label($model, 'invite');
            echo $form->checkBox($model, 'invite');
        ?>
    </div>
    <div class="cell">
        <?php
            echo $form->label ($model, 'reminder');
            echo $form->checkBox($model, 'reminder');
        ?>
    </div>
</div>
<div class='row'>
    <div class='cell'>
        <div class='cell'>
            <?php
            echo $form->dateRangeInput($model, 'dueDate', 'completeDate', array('timeTracker' => false));
            ?>
        </div>
        <div class='cell'>
            <?php
            echo '<div class="clearfix"></div>';
            echo $form->label($model, 'assignedTo');
            echo $form->renderInput($model, 'assignedTo');
            ?>

        </div>
    </div>
    <div class='cell'>
        <div class='cell'>
            <?php

            echo $form->label($model, 'priority');
            echo $form->renderInput($model, 'priority');

            echo $form->label($model, 'color');
            echo $form->renderInput($model, 'color');
            ?>
        </div>
        <div class='cell'>
            <?php
            echo $form->label($model, 'eventSubtype');
            echo $form->renderInput($model, 'eventSubtype');

            echo $form->label($model, 'eventStatus');
            echo $form->renderInput($model, 'eventStatus');
            
            echo $form->label($model, 'visibility');
            echo $form->renderInput($model, 'visibility');

            ?>
        </div>
        <div class='cell'>
            <?php
            if (empty($model->associationId)) {
                echo $form->label($model, 'associationType');
                echo $form->renderInput($model, 'associationType');
            } else {
                echo $form->hiddenField($model, 'associationType');
                echo $form->hiddenField($model, 'associationId');
                echo $form->hiddenField($model, 'associationName');
            }
            echo CHtml::hiddenField('modelName', 'calendar');
            ?>
        </div>
    </div>
</div>
<div id="email-invites" class="row" style="display:none;">
    <br>
    <div class="cell" style="width:100%; max-width:640px;">
        <?php 
        $model->emailAddresses = $email;
        echo $form->label($model,'emailAddresses');
        echo $form->textArea($model, 'emailAddresses');
        ?>
    </div>
</div>
<?php echo $model->renderReminderConfig(); ?>
<?php
if ($submitButton)
    echo $form->submitButton();

$this->endWidget();

Yii::app()->clientScript->registerScript('email-invites',"
    $('#CalendarEventFormModel_invite').on('click',function(){
        if($(this).is(':checked') && $('#email-invites').is(':hidden')){
            $('#email-invites').slideDown();
        } else if (!$(this).is(':checked') && $('#email-invites').is(':visible')){
            $('#email-invites').slideUp();
        }
    });
    $('#CalendarEventFormModel_reminder').on('click',function(){
        if($(this).is(':checked') && $('.reminder-config').is(':hidden')){
            $('.reminder-config').slideDown();
        } else if (!$(this).is(':checked') && $('.reminder-config').is(':visible')){
            $('.reminder-config').slideUp();
        }
    });
");
?>
