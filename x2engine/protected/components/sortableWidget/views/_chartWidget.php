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




?>


<div id="<?php echo $chartType; ?>-chart-container-<?php echo $widgetUID ?>" 
 class="chart-container form">

    <div class='chart-controls-container widget-settings-menu-content' style='display: none;'>

    <?php
    if ($chartType === 'eventsChart') {
    ?>
    <div class="chart-filters-container" style="display: none;">
        <select id="<?php echo $chartType; ?>-users-chart-filter-<?php echo $widgetUID; ?>" 
         class="users-chart-filter left" multiple="multiple">
            <?php
            foreach ($userNames as $userName=>$fullName) { 
            ?>
                <option value='<?php echo $userName; ?>'>
                <?php echo $fullName; ?>
                </option>
            <?php 
            } 
            ?>
        </select>
        <select 
         id="<?php echo $chartType; ?>-social-subtypes-chart-filter-<?php echo $widgetUID; ?>" 
         class="social-subtypes-chart-filter left" multiple="multiple">
            <?php
            foreach ($socialSubtypes as $subtypes) { 
            ?>
                <option value='<?php echo $subtypes; ?>'>
                <?php echo $subtypes; ?>
                </option>
            <?php 
            } 
            ?>
        </select>
        <select id="<?php echo $chartType; ?>-visibility-chart-filter-<?php echo $widgetUID; ?>" 
         class="visibility-chart-filter left" multiple="multiple">
            <?php
            foreach ($visibilityFilters as $visibilityVal=>$visibilityName) { 
            ?>
                <option value='<?php echo $visibilityVal; ?>'>
                <?php echo $visibilityName; ?>
                </option>
            <?php 
            } 
            ?>
        </select>
    </div>
    <?php 
    } else if ($chartType === 'usersChart') {
    ?>
    <div class="chart-filters-container" style="display: none;">
        <select id="<?php echo $chartType; ?>-events-chart-filter-<?php echo $widgetUID; ?>" 
         class="events-chart-filter left" multiple="multiple">
            <?php
            foreach ($eventTypes as $type=>$label) { 
            ?>
                <option value='<?php echo $type; ?>'>
                <?php echo $label; ?>
                </option>
            <?php 
            } 
            ?>
        </select>
        <select 
         id="<?php echo $chartType; ?>-social-subtypes-chart-filter-<?php echo $widgetUID; ?>" 
         class="social-subtypes-chart-filter left" multiple="multiple">
            <?php
            foreach ($socialSubtypes as $subtypes) { 
            ?>
                <option value='<?php echo $subtypes; ?>'>
                <?php echo $subtypes; ?>
                </option>
            <?php 
            } 
            ?>
        </select>
        <select id="<?php echo $chartType; ?>-visibility-chart-filter-<?php echo $widgetUID; ?>" 
         class="visibility-chart-filter left" multiple="multiple">
            <?php
            foreach ($visibilityFilters as $visibilityVal=>$visibilityName) { 
            ?>
                <option value='<?php echo $visibilityVal; ?>'>
                <?php echo $visibilityName; ?>
                </option>
            <?php 
            } 
            ?>
        </select>
    </div>
    <?php 
    }
    ?>

    <div id="<?php echo $chartType; ?>-top-button-row-<?php echo $widgetUID; ?>" 
     class="row top-button-row">

        <div id="<?php echo $chartType; ?>-first-metric-container-<?php echo $widgetUID; ?>" 
         class="first-metric-container">
            <select id="<?php echo $chartType; ?>-first-metric-<?php echo $widgetUID; ?>" 
             class="first-metric left" multiple="multiple">
    
            <?php
            foreach ($metricTypes as $key=>$type) {
            ?>
                <option value='<?php echo $key; ?>'>
                <?php echo $type; ?>
                </option>
            <?php
            }
            ?>
            </select>
        </div>

        <?php
        if ($chartType === 'eventsChart' || $chartType === 'usersChart') {
        ?>
        <div id="<?php echo $chartType; ?>-filter-toggle-container-<?php echo $widgetUID; ?>"
         class="filter-toggle-container">
            <button 
             id="<?php echo $chartType; ?>-show-chart-filters-button-<?php echo $widgetUID; ?>" 
             class="show-chart-filters-button x2-button x2-small-button left">
                <?php echo Yii::t('app', 'Show Filters'); ?>
            </button>
            <button 
             id="<?php echo $chartType; ?>-hide-chart-filters-button-<?php echo $widgetUID; ?>" 
             class="show-chart-filters-button x2-button x2-small-button left"
             style='display: none;'>
                <?php echo Yii::t('app', 'Hide Filters'); ?>
            </button>
        </div>
        <?php 
        }
        ?>

        <div id="<?php echo $chartType; ?>-bin-size-button-set-<?php echo $widgetUID; ?>" 
         class="bin-size-button-set x2-button-group right">
            <a href="#" id="<?php echo $chartType; ?>-hour-bin-size-<?php echo $widgetUID; ?>" 
             class="hour-bin-size x2-button">
                <?php echo Yii::t('app', 'Per Hour'); ?>
            </a>
            <a href="#" id="<?php echo $chartType; ?>-day-bin-size-<?php echo $widgetUID; ?>"
             class="day-bin-size disabled-link x2-button">
                <?php echo Yii::t('app', 'Per Day'); ?>
            </a>
            <a href="#" id="<?php echo $chartType; ?>-week-bin-size-<?php echo $widgetUID; ?>" 
             class="week-bin-size x2-button">
                <?php echo Yii::t('app', 'Per Week'); ?>
            </a>
            <a href="#" id="<?php echo $chartType; ?>-month-bin-size-<?php echo $widgetUID; ?>" 
             class="month-bin-size x2-button x2-last-child">
                <?php echo Yii::t('app', 'Per Month'); ?>
            </a>
        </div>
    </div>

    <div id="<?php echo $chartType; ?>-datepicker-row-<?php echo $widgetUID; ?>" 
     class="row datepicker-row">
        <div class="left">
            <input id="<?php echo $chartType; ?>-chart-datepicker-from-<?php echo $widgetUID; ?>" class="chart-datepicker-from">
            </input>
            -
            <input id="<?php echo $chartType; ?>-chart-datepicker-to-<?php echo $widgetUID; ?>" class="chart-datepicker-to">
            </input>
            <?php
            if (!$suppressDateRangeSelector) {
            ?>
            <select id="<?php echo $chartType; ?>-date-range-type-selector-<?php echo $widgetUID; ?>"
             class="date-range-type-selector x2-select">
                <option value="this"> <?php echo Yii::t('app', 'This') ?></option>
                <option value="trailing"> <?php echo Yii::t('app', 'Trailing') ?></option>
                <option value="last"> <?php echo Yii::t('app', 'Last') ?></option>
                <option value="custom"> <?php echo Yii::t('app', 'Custom') ?></option>
            </select>

            <select id="<?php echo $chartType; ?>-date-range-selector-<?php echo $widgetUID; ?>"
             class="date-range-selector x2-select">
                <option value="day"> <?php echo Yii::t('app', 'Day') ?></option>
                <option value="week"> <?php echo Yii::t('app', 'Week') ?></option>
                <option value="month"> <?php echo Yii::t('app', 'Month') ?></option>
                <option value="quarter"> <?php echo Yii::t('app', 'Quarter') ?></option>
                <option value="year"> <?php echo Yii::t('app', 'Year') ?></option>

            </select>
            <?php
            }
            ?>
        </div>

        <?php
        if (!$suppressChartSettings) {
        ?>

        <button id="<?php echo $chartType; ?>-create-setting-button-<?php echo $widgetUID; ?>" 
         class="create-setting-button right x2-button x2-small-button">
            <?php echo Yii::t ('app', 'Create Chart Setting'); ?>
        </button>
        <a href="#" id="<?php echo $chartType; ?>-delete-setting-button-<?php echo $widgetUID; ?>" 
         class="delete-setting-button right x2-hint" style='display: none;'
         title='<?php echo Yii::t('app', 'Delete predefined chart setting'); ?>'>
            [x]
        </a>
        <select id="<?php echo $chartType; ?>-predefined-settings-<?php echo $widgetUID; ?>" class="x2-select predefined-settings right">
            <option value="" 
             id="<?php echo $chartType; ?>-custom-settings-option-<?php echo $widgetUID; ?>" 
             class="custom-settings-option">
                <?php echo Yii::t('app', 'Custom'); ?>
            </option>
            <?php foreach ($chartSettingsDataProvider->data as $chartSetting) { ?>
            <option value="<?php echo $chartSetting->name; ?>">
                <?php echo CHtml::encode ($chartSetting->name); ?>
            </option>
            <?php } ?>
        </select>

        <?php
        }
        ?>

        <?php
        if ($chartType === 'actionHistoryChart') {
        ?>
        <div id="<?php echo $chartType; ?>-rel-chart-data-checkbox-container-<?php echo $widgetUID; ?>" 
         class="rel-chart-data-checkbox-container right">
            <input id="<?php echo $chartType; ?>-rel-chart-data-checkbox-<?php echo $widgetUID; ?>" 
             class="rel-chart-data-checkbox right" type='checkbox'>
            <label for='<?php echo $chartType; ?>-rel-chart-data-checkbox' class='right'> 
                <?php echo Yii::t('app', 'Chart related records\' actions'); ?>
            </label>
        </div>
        <?php
        }

        ?>
        <div class='chart-widget-button-container right'>
        <?php
        if ($this->relabelingEnabled) {
        ?>
            <button class='relabel-widget-button x2-button x2-small-button left'><?php 
                echo Yii::t('app', 'Rename Widget'); 
            ?></button>
        <?php
        }
        if ($this->canBeDeleted) {
        ?>
            <button class='delete-widget-button x2-button x2-small-button'><?php 
                echo Yii::t('app', 'Delete Widget'); 
            ?></button>
        <?php
        }
        ?>
        </div>
    </div>

    </div>

    <div id="<?php echo $chartType; ?>-chart-<?php echo $widgetUID; ?>" class="chart jqplot-target">
    </div>

    <div id="<?php echo $chartType; ?>-pie-chart-count-container-<?php echo $widgetUID; ?>" 
     class="pie-chart-count-container" style="display: none;">
         <?php echo Yii::t('app', 'Total Event Count: '); ?>
        <span class="pie-chart-count"></span>
    </div>

    <table id="<?php echo $chartType; ?>-chart-legend-<?php echo $widgetUID; ?>" class="chart-legend">
        <tbody>
        </tbody>
    </table>

    <div id="<?php echo $chartType; ?>-chart-tooltip-<?php echo $widgetUID; ?>" class="chart-tooltip" style='display: none;'>
    </div>


</div>

<?php
if (!$suppressChartSettings) {
?>

<div id="<?php echo $chartType; ?>-create-chart-setting-dialog-<?php echo $widgetUID; ?>" 
 class="create-chart-setting-dialog" style='display: none;'>
    <div class='chart-setting-name-input-container'>
        <span class='left'> <?php echo Yii::t('app', 'Setting Name'); ?>: </span>
        <input id="<?php echo $chartType; ?>-chart-setting-name-<?php echo $widgetUID; ?>" class="chart-setting-name"> </input>
    </div>
    <br/>
</div>

<?php
}
?>
