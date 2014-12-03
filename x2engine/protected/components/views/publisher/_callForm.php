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

<div id='log-a-call' class='publisher-form' 
 <?php echo ($startVisible ? '' : "style='display: none;'"); ?>>


    <div class="row">
        <div>
            <?php 
            echo CHtml::label(
                Yii::t('app','Quick Note'), 'quickNote',
                array('style' => 'display:inline-block;')); 
            echo CHtml::dropDownList(
                'quickNote', '', array_merge(array('' => '-'), Dropdowns::getItems(117)), 
                array(
                    'ajax' => array(
                        'type' => 'GET', //request type
                        'url' => Yii::app()->controller->createUrl('/site/dynamicDropdown'),
                        'data' => 'js:{"val":$(this).val(),"dropdownId":"117"}',
                        'update' => '#quickNote2',
                        'complete' => 'function() {
                            auxlib.getElement("#call-action-description").val(""); 
                        }',
                    ),
                    'class' => 'x2-select',
                )
            );
            echo CHtml::dropDownList(
                'quickNote2',
                '',
                array('' => '-'),
                array ('class' => 'x2-select')
            ); 
            ?>
        </div>
    </div>
    <div class="row">
        <div class="text-area-wrapper">
            <?php 
            echo $model->renderInput ('actionDescription',
                array(
                    'rows' => 3,
                    'cols' => 40,
                    'class'=>'action-description x2-textarea',
                    'id'=>'call-action-description',
                ));
            ?>
        </div>
    </div>

    <div class='row action-event-panel'>
        
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
            $model->type = 'call';
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
                    'id' => 'call-form-action-due-date'
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
                    'class' => 'action-complete-date',
                    'id' => 'call-form-action-complete-date'
                ),
            ));
            ?>
        </div>
    </div><!-- #action-event-panel -->
</div>
