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




Yii::app()->clientScript->registerCss('activeDateRangeInput',"

.action-duration {
    margin-right: 10px;
}
.action-duration .action-duration-display {
    font-size: 30px;
    font-family: Consolas, monaco, monospace;
}
.action-duration input {
    width: 50px;
}
.action-duration .action-duration-input {
    display:inline-block;
}
.action-duration label {
    font-size: 10px;
}

");
?>

<div class='active-date-range-input' 
 id='<?php echo $this->resolveId ($this->id); ?>'>
    <?php if(!isset($this->options['timeTracker']) || $this->options['timeTracker'] === true){ ?>
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
    <?php } ?>

    <div class="cell">
        <?php 
        echo CHtml::activeLabel(
            $this->model, $this->startDateAttribute,
            array('class' => 'action-start-time-label')); 
        echo X2Html::activeDatePicker ($this->model, $this->startDateAttribute, array(
                // fix datepicker so it's always on top
                'onClick' => "$('#ui-datepicker-div').css('z-index', '100');", 
                'class' => 'action-due-date',
                'id' => $this->resolveId ('action-due-date'),
            ), 'datetime', array_merge ($this->datePickerOptions, $this->options));

        echo CHtml::activeLabel(
            $this->model, $this->endDateAttribute, 
            array('class' => 'action-end-time-label'));
        echo X2Html::activeDatePicker ($this->model, $this->endDateAttribute, array(
                // fix datepicker so it's always on top
                'onClick' => "$('#ui-datepicker-div').css('z-index', '100');", 
                'class' => 'action-complete-date',
                'id' => $this->resolveId ('action-complete-date'),
            ), 'datetime', array_merge ($this->datePickerOptions, $this->options));
        ?>
    </div>
</div>
