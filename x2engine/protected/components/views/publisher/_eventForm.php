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




Yii::app()->clientScript->registerCss('eventTabCss',"

#calendar + br + #publisher-form #save-publisher {
    float: right !important;
}

");

if ($associationType === 'calendar') {
    $associationTypeOptions = X2Model::getAssociationTypeOptions ();
    unset ($associationTypeOptions['calendar']);
    $associationTypeOptions = 
        array ('calendar' => Yii::t('app', 'Select an option')) + $associationTypeOptions;
    $associationModels = array (); 
    // get the association type => model name mapping for available options
    foreach ($associationTypeOptions as $typ => $title) {
        $associationModels[$typ] = X2Model::getModelName ($typ);
    }
    Yii::app()->clientScript->registerScript('eventTabJS',"
;(function () {

$('#Actions_associationType').change (function () {
    var that = this;
    var associationModels = ".CJSON::encode ($associationModels).";
    if ($(this).val () === 'calendar') {
        $('#association-type-autocomplete-container').hide ();
        $('#association-type-autocomplete-container input').attr ('disabled', 'disabled');
        return false;
    }
    $('#association-type-autocomplete-container input').removeAttr ('disabled');
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

<div id='<?php echo $this->resolveId ('new-event'); ?>' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>


    <div class="row">
        <div class="text-area-wrapper">
            <?php 
            echo $model->renderInput ('actionDescription',
                array(
                    'rows' => 3,
                    'cols' => 40,
                    'class'=>'action-description',
                    'id'=>'event-action-description',
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
            echo X2Html::activeDatePicker ($model, 'dueDate', array(
                    // fix datepicker so it's always on top
                    'class'=>'action-due-date',
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');",
                    'id' => $this->resolveId ('event-form-action-due-date'),
                ), 'datetime', array (
                    'dateFormat' => Formatter::formatDatePicker ('medium'),
                    'timeFormat' => Formatter::formatTimePicker (),
                    'ampm' => Formatter::formatAMPM (),
                ));

            echo CHtml::activeLabel(
                $model,'completeDate', 
                array('class' => 'action-end-time-label'));
            echo X2Html::activeDatePicker ($model, 'completeDate', array(
                    // fix datepicker so it's always on top
                    'onClick' => "$('#ui-datepicker-div').css('z-index', '100');", 
                    'class' => 'action-complete-date x2-forms',
                    'id' => $this->resolveId ('event-form-action-complete-date'),
                ), 'datetime', array (
                    'dateFormat' => Formatter::formatDatePicker ('medium'),
                    'timeFormat' => Formatter::formatTimePicker (),
                    'ampm' => Formatter::formatAMPM (),
                ));
            ?>
        </div>

        <div class="cell">
            <?php /* All Day */
            echo $form->label($model, 'allDay', array('class' => 'action-allday-label'));
            echo $model->renderInput (
                'allDay', array('class'=>'action-allday'));
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
            echo $model->renderInput ('color', array('id'=>'action-color-dropdown')); 
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
            echo $form->labelEx ($model, 'eventSubtype');
            echo $model->renderInput ('eventSubtype');

            echo $form->labelEx ($model, 'eventStatus');
            echo $model->renderInput ('eventStatus');
        
            echo $form->label($model, 'visibility',array('class'=>'action-visibility-label')); 
            echo $form->dropDownList(
                $model, 'visibility', 
                array(
                    0 => Yii::t('actions', 'Private'), 1 => Yii::t('actions', 'Public'),
                    2 => Yii::t('actions', "{User}'s {Group}", array(
                        '{User}' => Modules::displayName(false, 'Users'),
                        '{Group}' => Modules::displayName(false, 'Groups'),
                    ))
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
            <div id='<?php echo $this->resolveId ('association-type-autocomplete-container'); ?>' 
             <?php 
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
