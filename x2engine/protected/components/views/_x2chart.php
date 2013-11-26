<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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


if ($chartType === 'usersChart' || $chartType === 'eventsChart') {
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/X2Chart.js', CClientScript::POS_END);
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/X2EventsChart.js', CClientScript::POS_END);
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->getBaseUrl().'/js/X2UsersChart.js', CClientScript::POS_END);

    Yii::app()->clientScript->registerCoreScript('jquery.ui');
    Yii::app()->clientScript->packages = X2WidgetList::packages ();
    Yii::app()->clientScript->registerPackage('ChartWidgetExtJS');
    Yii::app()->clientScript->registerPackage('ChartWidgetExtCss');
    Yii::app()->clientScript->registerPackage('ChartWidgetCss');
}


$passVarsToClientScript = "
    x2.".$chartType." = {};
    x2.".$chartType.".params = {};
    x2.".$chartType.".params.chartType = '".$chartType."';
    x2.".$chartType.".params.suppressChartSettings = ".
        ($suppressChartSettings ? 'true' : 'false').";
    x2.".$chartType.".params.suppressDateRangeSelector = ".
        ($suppressDateRangeSelector ? 'true' : 'false').";
    x2.".$chartType.".params.getChartDataActionName = '".$getChartDataActionName."';
    x2.".$chartType.".params.translations = {};
    /*x2.".$chartType.".params.DEBUG = ".
        ((YII_DEBUG && $chartType === 'eventsChart') ? 'true' : 'false').";*/
";

if (isset ($subtype)) {
    $passVarsToClientScript .= "
        x2.".$chartType.".params.chartSubtype = '".$subtype."';
    ";
}

if ($chartType === 'eventsChart') {
    $passVarsToClientScript .= "
        x2.".$chartType.".params.userNames = auxlib.keys (".CJSON::encode ($userNames).");
        x2.".$chartType.".params.socialSubtypes = auxlib.keys (".
            CJSON::encode ($socialSubtypes).");
        x2.".$chartType.".params.visibilityTypes = auxlib.keys ("
            .CJSON::encode ($visibilityFilters).");
    ";
} else if ($chartType === 'usersChart') {
    $passVarsToClientScript .= "
        x2.".$chartType.".params.socialSubtypes = auxlib.keys (".
            CJSON::encode ($socialSubtypes).");
        x2.".$chartType.".params.visibilityTypes = auxlib.keys ("
            .CJSON::encode ($visibilityFilters).");
        x2.".$chartType.".params.eventTypes = auxlib.keys ("
            .CJSON::encode ($eventTypes).");
    ";
}

if (isset ($dataStartDate)) {
    $passVarsToClientScript .= "
        x2.".$chartType.".params.dataStartDate = ".$dataStartDate." * 1000;";
}

if (isset ($chartData)) {
    $passVarsToClientScript .= "
        x2.".$chartType.".params.chartData = ".CJSON::encode ($chartData).";";
}

$longMonthNames = Yii::app()->getLocale ()->getMonthNames ();
$shortMonthNames = Yii::app()->getLocale ()->getMonthNames ('abbreviated');

$translations = array (
    'Create' => Yii::t('app','Create'),
    'Cancel' => Yii::t('app','Cancel'),
    'Create Chart Setting' => Yii::t('app','Create Chart Setting'),
    'Check all' => Yii::t('app','Check all'),
    'Uncheck all' => Yii::t('app','Uncheck all'),
    'metric(s) selected' => Yii::t('app','metric(s) selected')
);

if ($chartType === 'eventsChart') {
    $translations['metric1Label'] = Yii::t('app', 'metric(s) selected');
    $translations['user(s) selected'] = Yii::t('app', 'user(s) selected');
    $translations['event subtype(s) selected'] = Yii::t('app', 'event subtype(s) selected');
    $translations['visibility setting(s) selected'] = Yii::t('app', 'visibility setting(s) selected');
} else if ($chartType === 'usersChart') {
    $translations['metric1Label'] = Yii::t('app', 'user(s) selected');
    $translations['event type(s) selected'] = Yii::t('app', 'event type(s) selected');
    $translations['event subtype(s) selected'] = Yii::t('app', 'event subtype(s) selected');
    $translations['visibility setting(s) selected'] = Yii::t('app', 'visibility setting(s) selected');
} else if ($chartType === 'actionHistoryChart') {
    $translations['metric1Label'] = Yii::t('app', 'metric(s) selected');
} 

$englishMonthNames =
    array ('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
    'September', 'October', 'November', 'December');
$englishMonthAbbrs =
    array ('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
    'Dec');

foreach ($longMonthNames as $key=>$val) {
    $translations[$englishMonthNames[$key - 1]] = $val;
}
foreach ($shortMonthNames as $key=>$val) {
    $translations[$englishMonthAbbrs[$key - 1]] = $val;
}

// pass array of predefined theme uploadedBy attributes to client
foreach ($translations as $key=>$val) {
    $passVarsToClientScript .= "x2.".$chartType.".params.translations['".
        $key. "'] = '" . addslashes ($val) . "';\n";
}

if (!$suppressChartSettings) {
    $passVarsToClientScript .= "x2.".$chartType.".params.chartSettings = {};\n";

    foreach ($chartSettingsDataProvider->data as $chartSetting) {
        $passVarsToClientScript .= 
            "x2.".$chartType.".params.".
            "chartSettings['" . addslashes ($chartSetting->name) . "'] = " .
            CJSON::encode ($chartSetting) . ";\n";
    }
}

$passVarsToClientScript .= "x2.".$chartType.".params.actionParams = {};\n";

foreach ($actionParams as $paramName => $paramVal) {
    $passVarsToClientScript .= 
        "x2.".$chartType.".params.actionParams['" . $paramName . "'] = " .
        CJSON::encode ($paramVal) . ";\n";
}

Yii::app()->clientScript->registerScript(
    $chartType.'passVarsToX2chartScript', $passVarsToClientScript,
    CClientScript::POS_HEAD);

?>

<div id="<?php echo $chartType; ?>-chart-container" class="chart-container form" 
 <?php echo ($hideByDefault ? 'style="display: none;"' : ''); ?>>

    <?php
    if ($chartType === 'eventsChart') {
    ?>
    <div class="chart-filters-container" style="display: none;">
        <select id="<?php echo $chartType; ?>-users-chart-filter" 
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
        <select id="<?php echo $chartType; ?>-social-subtypes-chart-filter" 
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
        <select id="<?php echo $chartType; ?>-visibility-chart-filter" 
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
        <select id="<?php echo $chartType; ?>-events-chart-filter" 
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
        <select id="<?php echo $chartType; ?>-social-subtypes-chart-filter" 
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
        <select id="<?php echo $chartType; ?>-visibility-chart-filter" 
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

    <div id="<?php echo $chartType; ?>-top-button-row" class="row top-button-row">

        <div id="<?php echo $chartType; ?>-first-metric-container" 
         class="first-metric-container">
            <select id="<?php echo $chartType; ?>-first-metric" class="first-metric left"
             multiple="multiple">
    
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
        <div id="<?php echo $chartType; ?>-filter-toggle-container"
         class="filter-toggle-container">
            <button id="<?php echo $chartType; ?>-show-chart-filters-button" 
             class="show-chart-filters-button x2-button x2-small-button left">
                <?php echo Yii::t('app', 'Show Filters'); ?>
            </button>
            <button id="<?php echo $chartType; ?>-hide-chart-filters-button" 
             class="show-chart-filters-button x2-button x2-small-button left"
             style='display: none;'>
                <?php echo Yii::t('app', 'Hide Filters'); ?>
            </button>
        </div>
        <?php 
        }
        ?>

        <div id="<?php echo $chartType; ?>-bin-size-button-set" 
         class="bin-size-button-set x2-button-group right">
            <a href="#" id="<?php echo $chartType; ?>-hour-bin-size" class="hour-bin-size x2-button">
                <?php echo Yii::t('app', 'Per Hour'); ?>
            </a>
            <a href="#" id="<?php echo $chartType; ?>-day-bin-size" class="day-bin-size disabled-link x2-button">
                <?php echo Yii::t('app', 'Per Day'); ?>
            </a>
            <a href="#" id="<?php echo $chartType; ?>-week-bin-size" class="week-bin-size x2-button">
                <?php echo Yii::t('app', 'Per Week'); ?>
            </a>
            <a href="#" id="<?php echo $chartType; ?>-month-bin-size" class="month-bin-size x2-button x2-last-child">
                <?php echo Yii::t('app', 'Per Month'); ?>
            </a>
        </div>
    </div>

    <div id="<?php echo $chartType; ?>-datepicker-row" class="row datepicker-row">
        <div class="left">
            <input id="<?php echo $chartType; ?>-chart-datepicker-from" class="chart-datepicker-from">
            </input>
            -
            <input id="<?php echo $chartType; ?>-chart-datepicker-to" class="chart-datepicker-to">
            </input>
            <?php
            if (!$suppressDateRangeSelector) {
            ?>
            <select id="<?php echo $chartType; ?>-date-range-selector"
             class="date-range-selector">
                 <option value="Custom"><?php echo Yii::t('app', 'Custom'); ?></option>
                 <option value="Today"><?php echo Yii::t('app', 'Today'); ?></option>
                 <option value="Yesterday"><?php echo Yii::t('app', 'Yesterday'); ?></option>
                 <option value="This Week"><?php echo Yii::t('app', 'This Week'); ?></option>
                 <option value="Last Week"><?php echo Yii::t('app', 'Last Week'); ?></option>
                 <option value="This Month"><?php echo Yii::t('app', 'This Month'); ?></option>
                 <option value="Last Month"><?php echo Yii::t('app', 'Last Month'); ?></option>
                 <!--<option value="Data Domain"><?php //echo Yii::t('app', 'Data Domain'); ?></option>-->
            </select>
            <?php
            }
            ?>
        </div>

        <?php
        if (!$suppressChartSettings) {
        ?>

        <button id="<?php echo $chartType; ?>-create-setting-button" 
         class="create-setting-button right x2-button x2-small-button">
            <?php echo Yii::t ('app', 'Create Chart Setting'); ?>
        </button>
        <a href="#" id="<?php echo $chartType; ?>-delete-setting-button" 
         class="delete-setting-button right x2-hint" style='display: none;'
         title='<?php echo Yii::t('app', 'Delete predefined chart setting'); ?>'>
            [x]
        </a>
        <select id="<?php echo $chartType; ?>-predefined-settings" class="predefined-settings right">
            <option value="" id="<?php echo $chartType; ?>-custom-settings-option" 
             class="custom-settings-option">
                <?php echo Yii::t('app', 'Custom'); ?>
            </option>
            <?php foreach ($chartSettingsDataProvider->data as $chartSetting) { ?>
            <option value="<?php echo $chartSetting->name; ?>">
                <?php echo CHTML::encode ($chartSetting->name); ?>
            </option>
            <?php } ?>
        </select>

        <?php
        }
        ?>

        <?php
        if ($chartType === 'actionHistoryChart') {
        ?>
        <div id="<?php echo $chartType; ?>-rel-chart-data-checkbox-container" 
         class="rel-chart-data-checkbox-container right">
            <input id="<?php echo $chartType; ?>-rel-chart-data-checkbox" 
             class="rel-chart-data-checkbox right" type='checkbox'>
            <label for='<?php echo $chartType; ?>-rel-chart-data-checkbox' class='right'> 
                <?php echo Yii::t('app', 'Chart related records\' actions'); ?>
            </label>
        </div>
        <?php
        }
        ?>
    </div>


    <div id="<?php echo $chartType; ?>-chart" class="chart jqplot-target">
    </div>

    <div id="<?php echo $chartType; ?>-pie-chart-count-container" 
     class="pie-chart-count-container" style="display: none;">
         <?php echo Yii::t('app', 'Total Event Count: '); ?>
        <span class="pie-chart-count"></span>
    </div>

    <table id="<?php echo $chartType; ?>-chart-legend" class="chart-legend">
        <tbody>
        </tbody>
    </table>

    <div id="<?php echo $chartType; ?>-chart-tooltip" class="chart-tooltip" style='display: none;'>
    </div>


</div>

<?php
if (!$suppressChartSettings) {
?>

<div id="<?php echo $chartType; ?>-create-chart-setting-dialog" class="create-chart-setting-dialog">
    <div class='chart-setting-name-input-container'>
        <span class='left'> <?php echo Yii::t('app', 'Setting Name'); ?>: </span>
        <input id="<?php echo $chartType; ?>-chart-setting-name" class="chart-setting-name"> </input>
    </div>
    <br/>
</div>

<?php
}
?>

<script>

    function instantiate<?php echo $chartType; ?>Chart () {
        // instantiate chart object
        <?php if ($chartType === 'eventsChart') { ?>
        x2.<?php echo $chartType; ?>.chart = new X2EventsChart (
            x2.<?php echo $chartType; ?>.params);
        <?php } else if ($chartType === 'actionHistoryChart') { ?>
        x2.<?php echo $chartType; ?>.chart = new X2ActionHistoryChart (
            x2.<?php echo $chartType; ?>.params);
        <?php } else if ($chartType === 'usersChart') { ?>
        x2.<?php echo $chartType; ?>.chart = new X2UsersChart (
            x2.<?php echo $chartType; ?>.params);
        <?php }  ?>
        $(document).trigger ('<?php echo $chartType; ?>Ready');
    }
    if (document.readyState === 'complete') {
        instantiate<?php echo $chartType; ?>Chart ();
    } else {
        $(document).on ('ready', function () {
            instantiate<?php echo $chartType; ?>Chart ();
        });
    }

</script>
