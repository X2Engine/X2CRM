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




/**
 * Class providing an abstraction layer for cron table editing.
 *
 * Abstracted data comes in the form (for each line of the X2Engine-managed section
 * of the cron table): an array with keys
 *
 * <dl>
 * <dt>schedule</dt><dd>Preset schedule. If this key is set, min-dayOfWeek keys will not be set.</dd>
 * <dt>min</dt><dd>Minutes of the hour at which to run</dd>
 * <dt>hour</dt><dd>Hours of the day at which to run</dd>
 * <dt>dayOfMonth</dt><dd>Days of the month at which to run</dd>
 * <dt>month</dt><dd>Months of the year at which to run</dd>
 * <dt>dayOfWeek</dt><dd>Days of the week at which to run</dd>
 * <dt>cmd</dt><dd>The cmd to run</dd>
 * <dt>tag</dt><dd>Unique string identifying the cron job</dd>
 * <dt>desc</dt><dd>Brief one-line (no carraige returns) description</dd>
 * </dl>
 * For each of the above that are defined as plural, the values could be either
 * an array of possible values or "*" for "all" (see {@link https://en.wikipedia.org/wiki/Cron}
 * for more information on cron table formatting and specification).
 *
 * NOTE: the crontab field and manged section delimeters are set the way they are
 * for backwards compatibility (with old crontabs that use the old markers)
 * despite the change in product naming (from X2Engine back to X2Engine).
 * 
 * @package application.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CrontabUtil {

    const CRONTAB_FIELD_DELIM = '#@X2CRM@';

    const CRONTAB_MANAGED_BEGIN = '#<X2CRM>';

    const CRONTAB_MANAGED_END = '#</X2CRM>';

    /**
     * If set to true, the cron tab form will include the hint.
     * @var type
     */
    public static $printHint = true;

    /**
     * @var array The parts of the cron entry that define the task itself, including metadata
     *     that is ignored by the cron daemon
     */
    public static $taskFields = array('cmd', 'tag', 'desc');

    public static $schedules = array('hourly','daily','weekly','monthly','yearly');

    /**
     * @var array The fields of the cron entry that define its schedule
     */
    public static $schedFields = array('min', 'hour', 'dayOfMonth', 'month', 'dayOfWeek');

    /**
     * Adds delimiters for identifying X2Engine-managed cron tasks to the crontab, if they
     * don't already exist.
     *
     * @param string $crontab reference to the current cron table in string form.
     * @param bool $check if enabled, an exception will be thrown in the event that no management delimeters were found.
     */
    public static function addCronMarker(&$crontab, $check = false){
        if(strpos($crontab, self::CRONTAB_MANAGED_BEGIN) === false){
            if($check)
                throw new Exception('X2Engine management delimiters not found in cron table.');
            $crontab .= implode("\n", array('', self::CRONTAB_MANAGED_BEGIN, self::CRONTAB_MANAGED_END, ''));
        }
    }

    /**
     * Modifies an existing cron table according to a specified array.
     * @param string $crontab The existing cron table
     * @param array $crontabArray The array (each index a tag and each value an
     *     array as specified in this class's documentation)
     */
    public static function arrayToCrontab(&$crontab, $crontabArray){
        self::addCronMarker($crontab);
        $crontabLines = explode("\n", $crontab);
        $newCrontabLines = array();
        $managed = false;
        $finished = false;
        foreach($crontabLines as $line){
            if(strpos($line, self::CRONTAB_MANAGED_BEGIN) !== false)
                $managed = true;
            if(strpos($line, self::CRONTAB_MANAGED_END) !== false && $finished && $managed)
                $managed = false;
            if($managed){
                if($finished) // Done writing managed cron tasks
                    continue;
                // The line is being replaced, technically, so it is necessary to
                // re-insert the opening delimeter:
                $newCrontabLines[] = self::CRONTAB_MANAGED_BEGIN;
                // Add cron jobs from the array:
                foreach($crontabArray as $lineCfg)
                    $newCrontabLines[] = self::crontabLine($lineCfg);
                $finished = true;
            } else
                $newCrontabLines[] = $line;
        }
        $crontab = implode("\n", $newCrontabLines);
    }
    
    /**
     * Takes a crontab line array and converts it to an array appropriate for
     * pre-populating the cron job schedule/configuration form (i.e. with
     * a preexisting cron job).
     */
    public static function cronJobToForm($crontabLine) {
        $data = $crontabLine;
        $data['use_schedule'] = 0;
        foreach(self::$schedFields as $name) {
            // Add "all" options for each schedule field
            $data["all_$name"] = 1;
            if(isset($data[$name])) {
                if($data[$name] == '*') {
                    $data[$name] = array();
                } else {
                    $data["all_$name"] = 0;
                }
            } else {
                $data[$name] = array();
            }
        }
        if(isset($data['schedule'])) {
            $data['use_schedule'] = 1;
        }
        return $data;
    }

    /**
     * Formats a crontab line according to the specification defined in {@link crontabArray}
     * @param type $l Line configuration array. Should have the keys specified in
     *     the main documentation of this class.
     * @return string
     */
    public static function crontabLine($l){
        if(isset($l['schedule'])) {
            return "@{$l['schedule']} {$l['cmd']} ".self::CRONTAB_FIELD_DELIM.$l['tag'].self::CRONTAB_FIELD_DELIM.$l['desc'];
        }else{
            foreach(self::$schedFields as $f){
                ${$f} = is_array($l[$f]) ? implode(',', $l[$f]) : $l[$f];
            }
            return "$min $hour $dayOfMonth $month $dayOfWeek {$l['cmd']} ".self::CRONTAB_FIELD_DELIM.$l['tag'].self::CRONTAB_FIELD_DELIM.$l['desc'];
        }
    }

    /**
     * Parse the cron table into an array.
     *
     * Each line is followed by a management delimeter, a tag (uniquely
     * identifying string for the cron job) another management delimeter and a
     * description.
     *
     * @param string $crontab The cron table.
     * @return array
     */
    public static function crontabToArray(&$crontab){
        try{
            self::addCronMarker($crontab,true);
        }catch(Exception $e){
            return array();
        }
        $cron = explode(self::CRONTAB_MANAGED_BEGIN."\n", $crontab);
        $cron = explode("\n".self::CRONTAB_MANAGED_END, $cron[1]);
        $cron = explode("\n", $cron[0]);
        $cronArray = array();
        foreach($cron as $line){
            if(!($lineCfg = self::parseCrontabLine($line)))
                continue;
            $cronArray[$lineCfg['tag']] = $lineCfg;
        }
        return $cronArray;
    }
    
    /**
     * Generates a form input name for a cron job configuration form section
     *
     * @param type $name Name of the input
     * @param type $formName Name of the form section to contain the cron input
     * @param type $isArray Whether the input will produce array-type data, i.e.
     *  a multi-select input
     * @return string
     */
    public static function inputName($formName,$tag,$name,$isArray=0) {
        return $formName."[$tag][$name]".($isArray?'[]':'');
    }

    public static function jsName($name,$tag) {
       return (strpos($name,'[') ? strtr($name,array('['=>'_',']'=>'_')) : $name)."_$tag";
    }
    
    /**
     * The inverse operation of {@link crontabLine}; returns false
     * if the line isn't compatible with specifications.
     *
     * Does not support the "/" notation (i.e. /2 for every second of whatever
     * time interval is being denoted)
     *
     * @param string $l The line to be parsed
     * @return bool|array False if the line isn't compatible with specifications, the parsed array otherwise
     */
    public static function parseCrontabLine($line) {
        $configTagDesc = explode(self::CRONTAB_FIELD_DELIM, $line);
        $nFields = count($configTagDesc);
        if(strpos($line,'#') === 0) // Ignore; this is a comment in the cron table.
            return false;
        if($nFields < 2) // Ignore; cron task on this line has no tag or description.
            return false;
        // Eliminate excess whitespace and normalize to single-space delineation:
        $config = mb_ereg_replace('\s+', ' ', $configTagDesc[0]);
        $configArr = explode(' ', $config);
        $tag = $configTagDesc[1];
        $cronLine = array(
            'tag' => $tag,
            'desc' => $nFields == 3 ? $configTagDesc[2] : ''
        );
        if(preg_match('/^@('.implode('|',self::$schedules).')/',$config,$matches)){
            // Simple schedule
            $cronLine['schedule'] = $matches[1];
            $cronLine['cmd'] = implode(' ', array_slice($configArr, 1, -1));
        }else if(preg_match('/^(?:[\*0-9,]+ ){5}/',$config,$matches)){
            // Complex schedule
            foreach(self::$schedFields as $i => $field){
                $cronLine[$field] = $configArr[$i] == '*' ? '*' : explode(',', $configArr[$i]);
            }
            $cronLine['cmd'] = implode(' ', array_slice($configArr, 5, -1)); // Remove trailing space between cmd and delimiter.
        } else {
            // Improperly formatted cron line
            return false;
        }
        return $cronLine;
    }

    /**
     * Takes data from an instance of the crontab form generated in {@link schedForm()} and
     * creates a well-formatted array for creating a corresponding crontab entry.
     *
     * Can also initialize a cron job form field set from an empty array.
     */
    public static function processForm($form=array()){
        // Initial crontab array, with unnecessary elements stripped out
        $ca = array_intersect_key($form,array_fill_keys(array_merge(self::$taskFields,self::$schedFields),''));

        if(isset($form['schedule'],$form['use_schedule'])?$form['use_schedule']:false){
            // Handle preset schedule options:
            $ca['schedule'] = $form['schedule'];
        }else{
            // Incorporate the "all" switches (which should override selection) and
            // assume unrestricted if any parts aren't set:
            foreach(self::$schedFields as $schedPart){
                // If "all_$schedPart" (i.e. all_minutes) is set to 1, or if the
                // schedule part of the form is empty / not set, assume we want
                // ALL (i.e. unselected or designated "all" for minutes will put
                // "*" in for minutes
                if((isset($form["all_$schedPart"]) ? (bool)(int)$form["all_$schedPart"] : false) || empty($form[$schedPart]))
                    $ca[$schedPart] = '*';
            }
        }
        return $ca;
    }

    /**
     * Generate form inputs for creating a cron task.
     *
     * Note, the JavaScript in this cron form is namespace-protected to allow
     * multiple cron forms within the same page. The "name" argument should be
     * used to control the name.
     *
     * @param array $data Previous form data submitted, if any.
     * @param string $name The name of the nested array in the form data containing
     *     information about the cron job. Must be usable as a JavaScript object
     *     name.
     * @param string $desc Optional description of cron job to save in crontab as a desc
     * @param string $cmd The command to run at the scheuled dates. If unspecified,
     * @param string $tag A "tag" in the comments uniquely identifying the cron job
     * @return string
     */
    public static function schedForm($data = array(), $name = 'cron',
            $cmd = null, $tag = 'default', $desc = null){
        $jsName = self::jsName($name,$tag);
        // JavaScript namespace for scripts pertaining to this form:
        $jsns = "cronForm.$jsName";
        $ns = function($return = 0) use($jsns) {if($return) return $jsns; else echo $jsns;};
        if(empty($cmd))
            $cmd = $desc = "";
        foreach(array('cmd', 'tag', 'desc') as $var){
            if(!empty(${$var}) && empty($data[$var])){
                $data[$var] = ${$var};
            }
        }
        
        // Sets a select input to disabled (i.e. minutes, if "all_minutes" is
        // set) based on the previous data and the defaults for the field.
        $formDisabled = function($n)use($data){
            (isset($data[$n]) ? $data[$n] : true) ? 'disabled' : '';};
        // Normalizes a list of values for a crontab time interval class into an array.
        // Argument $t is the value
        // Argument $l is the list of possible values
        
        // Use a local function named similar to the message function in the
        // installer so that the codebase parser (when auto-translating) will
        // pick up on it:
        $installer_t = function_exists('installer_t')
                ? function($m){return call_user_func('installer_t',$m);}
                : (class_exists('Yii')
                        ? function($m){return Yii::t('install',$m);}
                        : function($m){return $m;}
                );
        // Inline styles:
        $inlineStyles = array(
            '#cron-form-top' => 'display: block;',
            '#top-form' => 'padding-bottom: 10px;',
            '#cron-text-value' => 'margin-left: 125px;',
            '#cron-form-textarea' => 'max-width: 420px;',
            '#cron-form-value' => 'padding-left: 125px;',
            '#cron-cmd-value' => 'display: table-cell; padding-left: 26px;',
            '#cron-form-pair' => 'display: table;',
            '#cron-form-label' => 'float: left; display: table-cell;',
            '#cron-form-value' => 'display: table-cell; position: relative; padding-left: 20px;',
            '#schedFormTitle' => 'width: 100%;',
            '#simpleTitle' => 'padding-bottom: 15px;',
            '#mainTitle' => 'display: table; margin-top: -10px;',
            '#schedInputs' => 'width: 120px;display: table-cell;margin-top: -50px;padding-bottom: 15px;',
            '#cron-ui-submit' => 'position: relative; top: 7px; left: 450px; color: buttontext;',
            '#ui-radio' => 'margin-left: -50px;',
            '#cron-bot' => 'padding-bottom: 10px;',
            '#startCron' => 'margin-top: 20px;'
        );
        $checked = function($n,$tf) use($data) {
            if((bool)(int)$data[$n] == (bool)(int)$tf)
                echo 'checked';
        };

        ob_start();
        ?>
        <div style="#cron-form-top">
            <?php if(empty($cmd)): ?>
                <div style="#cron-form-pair">
                    <div style="#cron-form-label"><?php echo $installer_t('Command'); ?></div>
                    <div style="#cron-cmd-value">
                        <input style="#cron-textbox" name="<?php echo self::inputName($name,$tag,'cmd'); ?>" size="60" value="<?php echo $cmd; ?>" />
                        <input type="hidden" value="<?php echo $tag; ?>" name="<?php echo self::inputName($name,$tag,'tag'); ?>" />
                    </div>
                </div>
                <div style="#cron-form-pair">
                    <div style="#cron-form-label"><?php echo $installer_t('Description'); ?></div>
                    <div style="#cron-form-value">
                        <input style="#cron-textbox" name="<?php echo self::inputName($name,$tag,'desc'); ?>" size="60" value="<?php echo $desc; ?>" />
                        <span style="#cron-error"><?php echo $desc; ?></span>
                        <input type="hidden" name="<?php echo self::inputName($name,$tag,'desc'); ?>" value="" />
                    </div>
                </div>
                <input type="hidden" name="<?php echo self::inputName($name,$tag,'tag'); ?>" value="<?php echo htmlentities($data['tag'],ENT_QUOTES); ?>" />
            <?php else: ?>
                <input type="hidden" name="<?php echo self::inputName($name,$tag,'cmd'); ?>" value="<?php echo htmlentities($cmd, ENT_QUOTES); ?>" />
                <input type="hidden" name="<?php echo self::inputName($name,$tag,'desc'); ?>" value="<?php echo htmlentities($desc, ENT_QUOTES); ?>" />
                <input type="hidden" name="<?php echo self::inputName($name,$tag,'tag'); ?>" value="<?php echo $tag; ?>" />
            <?php endif; ?>
        </div>

        <div>
            <input type='radio' value='1' name="<?php echo self::inputName($name,$tag,'use_schedule'); ?>" <?php $checked('use_schedule',1); ?> onclick="<?php $ns(); ?>.scheduleMode(form,1);" />
            <?php echo $installer_t('Simple Schedule');
            $scheduleList = array(
                'hourly' => $installer_t('Hourly'),
                'daily' => $installer_t('Daily (at Midnight)'),
                'weekly' => $installer_t('Weekly (on Sunday)'),
                'monthly' => $installer_t('Monthly (on the 1st)'),
                'yearly' => $installer_t('Yearly (on Jan 1st)'),
            );
            $schedule = isset($data['schedule']) ? $data['schedule'] : 'hourly';
        ?>
            <select name="<?php echo self::inputName($name,$tag,'schedule'); ?>">
                <?php
                foreach($scheduleList as $scheduleName => $scheduleLabel){
                    $sel = $scheduleName == $schedule ? ' selected' : '';
                    echo "<option value=\"$scheduleName\"$sel>$scheduleLabel</option>";
                }
                ?>
            </select>
            <input type="radio" name="<?php echo self::inputName($name,$tag,'use_schedule'); ?>"  value="0" <?php $checked('use_schedule',0); ?> onchange="<?php $ns(); ?>.scheduleMode(form,0);" />
            <?php echo $installer_t('Times and Dates Selected'); ?>
            <?php echo '<p>'.(self::$printHint ? $installer_t('Note: hold down the control key (or command key, on Macintosh) to select or deselect multiple values.'):'').'</p>'; ?>
            <div style="#schedFormTitle">
                <div style="#mainTitle">
                    <div style="#schedInputs">
                        <strong><?php echo $installer_t('Minutes'); ?></strong><br />
                        <div>
                            <input type="radio" name="<?php echo self::inputName($name,$tag,'all_min'); ?>" value="1" <?php $checked('all_min',1); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'min',1); ?>',0);" />
                            <?php echo $installer_t('All'); ?>
                            <br>
                            <input type="radio" name="<?php echo self::inputName($name,$tag,'all_min'); ?>" value="0" <?php $checked('all_min',0); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'min',1); ?>',1);" />
                            <?php echo $installer_t('Selected'); ?>
                        </div>
                        <select multiple size="10" name="<?php echo self::inputName($name,$tag,'min',1); ?>" <?php echo $formDisabled(self::inputName($name,$tag,'min',1)); ?>>
                            <?php
                            $minList = range(0,59);
                            $minSel = self::timeList(isset($data['min']) ? $data['min'] : array());
                            foreach($minList as $min){
                                $sel = in_array($min,$minSel) ? ' selected' : '';
                                echo "<option value=\"$min\"$sel>$min</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="#schedInputs">
                        <strong><?php echo $installer_t('Hours'); ?></strong><br />
                        <div>
                            <input type="radio" name="<?php echo self::inputName($name,$tag,'all_hour'); ?>" value="1" <?php $checked('all_hour',1); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'hour',1); ?>',0);" />
                            <?php echo $installer_t('All'); ?>
                            <br>
                            <input type="radio" name="<?php echo self::inputName($name,$tag,'all_hour'); ?>" value="0" <?php $checked('all_hour',0); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'hour',1); ?>',1);" />
                            <?php echo $installer_t('Selected'); ?>
                        </div>
                        <select multiple size="10" name="<?php echo self::inputName($name,$tag,'hour',1); ?>" <?php echo $formDisabled(self::inputName($name,$tag,'hour',1)); ?>>
                            <?php
                            $hourList = range(0,23);
                            $hourSel = self::timeList(isset($data['hour']) ? $data['hour'] : array(),$hourList);
                            foreach($hourList as $hour){
                                $sel = in_array($hour,$hourSel) ? ' selected' : '';
                                echo "<option value=\"$hour\"$sel>$hour</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="#schedInputs">
                        <strong><?php echo $installer_t('Days'); ?></strong><br />
                        <div>
                            <input type="radio" name="<?php echo self::inputName($name,$tag,'all_dayOfMonth'); ?>" value="1" <?php $checked('all_dayOfMonth',1); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'dayOfMonth',1); ?>',0);" />
                            <?php echo $installer_t('All'); ?>
                            <br>
                            <input type="radio" name="<?php echo self::inputName($name,$tag,'all_dayOfMonth'); ?>" value="0" <?php $checked('all_dayOfMonth',0); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'dayOfMonth',1); ?>',1);" />
                            <?php echo $installer_t('Selected'); ?>
                        </div>
                        <select multiple size="10" name="<?php echo self::inputName($name,$tag,'dayOfMonth',1); ?>" <?php echo $formDisabled(self::inputName($name,$tag,'days')); ?>>
                            <?php
                            $daysList = range(1,31);
                            $daysSel = self::timeList(isset($data['dayOfMonth']) ? $data['dayOfMonth'] : array(), $daysList);
                            foreach($daysList as $day){
                                $sel = in_array($day,$daysSel) ? ' selected' : '';
                                echo "<option value=\"$day\"$sel>$day</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="#schedInputs">
                        <strong><?php echo $installer_t('Months'); ?></strong><br />
                        <input type="radio" name="<?php echo self::inputName($name,$tag,'all_month'); ?>" value="1" <?php $checked('all_month',1); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'month',1); ?>',0);" />
                        <?php echo $installer_t('All'); ?>
                        <br>
                        <input type="radio" name="<?php echo self::inputName($name,$tag,'all_month'); ?>" value="0" <?php $checked('all_month',0); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'month',1); ?>',1);" />
                        <?php echo $installer_t('Selected'); ?>
                        <select multiple size="10" name="<?php echo self::inputName($name,$tag,'month',1); ?>" <?php echo $formDisabled(self::inputName($name,$tag,'all_month')); ?>>
                            <?php
                            $monthList = array(
                                $installer_t('January'),
                                $installer_t('Febraury'),
                                $installer_t('March'),
                                $installer_t('April'),
                                $installer_t('May'),
                                $installer_t('June'),
                                $installer_t('July'),
                                $installer_t('August'),
                                $installer_t('September'),
                                $installer_t('October'),
                                $installer_t('November'),
                                $installer_t('December'),
                            );
                            $month = self::timeList(isset($data['month']) ? $data['month'] : array());
                            foreach($monthList as $i => $m){
                                $ii = $i+1;
                                $sel = in_array($ii, $month) ? ' selected' : '';
                                echo "<option value=\"$ii\"$sel>$m</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div style="#schedInputs">
                        <strong><?php echo $installer_t('Weekdays'); ?></strong><br />
                        <input type="radio" name="<?php echo self::inputName($name,$tag,'all_dayOfWeek'); ?>" value="1" <?php $checked('all_dayOfWeek',1); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'dayOfWeek',1); ?>',0);" />
                        <?php echo $installer_t('All'); ?>
                        <br>
                        <input type="radio" name="<?php echo self::inputName($name,$tag,'all_dayOfWeek'); ?>" value="0" <?php $checked('all_dayOfWeek',0); ?> onclick="<?php $ns(); ?>.enableField(form,'<?php echo self::inputName($name,$tag,'dayOfWeek',1); ?>',1);" />
                        <?php echo $installer_t('Selected'); ?>
                        <select multiple size="7" name="<?php echo self::inputName($name,$tag,'dayOfWeek',1); ?>" <?php echo $formDisabled(self::inputName($name,$tag,'dayOfWeek')); ?>>
                            <?php
                            $dayOfWeekList = array(
                                $installer_t('Sunday'),
                                $installer_t('Monday'),
                                $installer_t('Tuesday'),
                                $installer_t('Wednesday'),
                                $installer_t('Thursday'),
                                $installer_t('Friday'),
                                $installer_t('Sautrday')
                            );
                            $dayOfWeek = self::timeList(isset($data['dayOfWeek']) ? $data['dayOfWeek'] : array());

                            foreach($dayOfWeekList as $i => $w){
                                $sel = in_array($i, $dayOfWeek) ? ' selected' : '';
                                echo "<option value=\"$i\"$sel>$w</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <script>
                if(typeof cronForm == 'undefined')
                    cronForm = {};
                if(typeof cronForm._namespaces == 'undefined')
                    cronForm._nameSpaces = {};
                if(typeof(cronForm._getSubForm) == 'undefined') {
                    cronForm._subForm = function(tag) {
                        var ns = cronForm._nameSpaces[tag];
                        if(typeof ns == 'undefined')
                            return undefined;
                        if(typeof cronForm[ns] == 'undefined')
                            return {};
                        return cronForm[ns];
                    }
                }
                if(typeof <?php $ns(); ?> == 'undefined') {
                    <?php $ns(); ?> = {};
                    cronForm._nameSpaces["<?php echo $tag; ?>"] = "<?php echo $jsName; ?>";
                }
                <?php
                $schedParts =  self::$schedFields; // indexes for all "parts" of the schedule
                $schedAllParts = array_map(function($n){return "all_$n";},$schedParts); // indexes for the "all" option for each part of the schedule
                $schedNames = array(); // Full names for each input part of the scheule
                $schedAllNames = array(); // Full names for the "all" option input
                foreach($schedParts as $part) {
                    $schedNames[$part] = self::inputName($name,$tag,$part);
                    $schedAllNames["all_$part"] = self::inputName($name,$tag,"all_$part");
                }
                foreach(array('Parts','Names') as $array) {
                    foreach(array('','All') as $type) {
                        $jsVarName = "sched$type$array";
                        echo $ns(1).".$jsVarName = ".json_encode(${"$jsVarName"}).";\n".str_repeat(' ',16);
                    }
                }
                ?>
                <?php $ns(); ?>.scheduleField = <?php echo json_encode(self::inputName($name,$tag,'schedule')); ?>;
                <?php $ns(); ?>.scheduleModeField = <?php echo json_encode(self::inputName($name,$tag,'use_schedule')); ?>;
                /**
                 * Enables a field in the form
                 */
                <?php $ns(); ?>.enableField = function(form, name, enable) {
                    var elts = form.elements[name];
                    elts.disabled = !enable;
                    for(var i=0; i<elts.length; i++) {
                        elts[i].disabled = !enable;
                    }
                }
                /**
                 * Gets the "value" of a radio button group
                 */
                <?php $ns(); ?>.radioButtonValue = function(form,elt) {
                    for(var i=0;i<form[elt].length;i++)
                        if(form[elt][i].checked)
                            return form[elt][i].value
                }

                /**
                 * Sets the form into one of two modes: one where a simple/preset
                 * schedule is selected, and one in which a more complex schedule
                 * can be selected.
                 */
                <?php $ns(); ?>.scheduleMode = function(form,mode) {
                    var allElt,i;
                    for(var i=0;i<<?php $ns(); ?>.schedParts.length;i++) {
                        allElt = <?php $ns(); ?>.schedAllNames[<?php $ns(); ?>.schedAllParts[i]];
                        <?php $ns(); ?>.enableField(form,allElt,!mode);
                        <?php $ns(); ?>.enableField(form,<?php $ns(); ?>.schedNames[<?php $ns(); ?>.schedParts[i]]+'[]',(<?php $ns(); ?>.radioButtonValue(form,allElt) == '0' && !mode));
                    }
                    form.elements[<?php $ns(); ?>.scheduleField].disabled = !mode;
                }


                /**
                 * Initializes the form, enabling/disabling inputs as appropriate
                 */
                <?php $ns(); ?>.setup = function () {
                    for(var i=0;i<document.forms.length;i++) {
                        if(typeof document.forms[i].elements[<?php $ns(); ?>.scheduleField] != 'undefined') {
                            <?php $ns(); ?>.scheduleMode(document.forms[i],<?php $ns(); ?>.radioButtonValue(document.forms[i],<?php $ns(); ?>.scheduleModeField)=='1');
                            var schedModeButton = document.forms[i].elements[<?php $ns(); ?>.scheduleModeField];
                        }
                    }
                }

                // Final set-up, attempting to set forms properly:
                <?php $ns(); ?>.setup();
            </script>
            </div>
            <br />
            <?php
            $inputs = strtr(ob_get_clean(),$inlineStyles);
            
            return $inputs;
        }

    /**
     *
     * @param type $timeField The field to be parsed
     * @param type $values Possible values
     * @return type
     */
    public static function timeList($timeField){
        if(is_string($timeField)) {
            if($timeField == '*') {
                // Select none; "all" option in use
                return array();
            } else {
                return explode(',',$timeField);
            }
        } else {
            return $timeField;
        }
    }

}
    ?>
