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
 * Widget-ized wrapper methods for rendering cron forms and processing input.
 *
 * @property array $jobTags Array of cron line tags for which to generate the form.
 * @property array $displayCmds Array of commands for display purposes only.
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CronForm extends X2Widget {

    /**
     * Array of commands for display purposes only
     * @var array
     */
    private $_displayCmds;

    /**
     * Stores the value of {@link jobTags}
     * @param array
     */
    private $_jobTags;

    /**
     * Command utility object
     * @var CommandUtil
     */
    protected $cmdU;

    /**
     * Current cron table
     * @var string
     */
    protected $crontab;
    
    /**
     * Array storing cron jobs configuration
     */
    protected $j = array();

    /**
     * Flag for executing or skipping due to inadequate permissions or
     * unavailability of cron on the system.
     * @var bool
     */
    protected $execute = false;

    /**
     * If set to true, this will enable receiving input from the "cmd" input
     * field on the form, thus allowing the user free reign to schedule any
     * cron job they want.
     * @var bool
     */
    public $allowUserCmdInput = false;

    /**
     * Array containing form data, i.e. $_POST
     * @var type
     */
    public $formData = array();

    /**
     * An array specifying cron jobs for which to generate inputs. 
     * 
     * The array is key-value pairs where keys are job tags and values are each
     * an array with:
     *
     * cmd: the command to run (absolutely required, unless
     *  {@link allowUserCmdInput} input is set to true)
     * title: title of the cron job
     * desc: the short description, to be saved in the cron table as a comment
     * longdesc: long user-friendly description
     * instructions: additional info about the cron job, i.e. what exactly it
     *  does and any disclaimers they should know about
     * 
     * @var type
     */
    public $jobs;

    /**
     * In the case that form inputs for multiple cron jobs are being rendered,
     * this is the string that will be included in output, separating them.
     * @var type
     */
    public $jobSeparator = '<hr />';

    /**
     * CSS class used by the label/title of each cron job section.
     * @var type
     */
    public $labelCssClass ='cron-checkitem';

    /**
     * Name (and thus index in form data) of the cron jobs.
     * @var type
     */
    public $name = 'cron';

    public $titles = array();

    /**
     * Override that skips anything and everything if it's not possible to
     * control the cron table
     * @param type $name
     * @param type $parameters
     */
    public function __call($name, $parameters){
        if($this->execute)
            parent::__call($name, $parameters);
    }

    public function __construct($owner = null){
        $this->execute = true;
        parent::__construct($owner);
        try{
            // Initialize command utility and load the cron table:
            $this->cmdU = new CommandUtil();
            $this->crontab = $this->cmdU->loadCrontab();
            $this->j = CrontabUtil::crontabToArray($this->crontab);
        }catch(Exception $e){
            $this->execute = false;
        }
    }

    public function getDisplayCmds() {
        if(!isset($this->_displayCmds)) {
            $this->_displayCmds = array();
            foreach($this->jobs as $tag => $attributes) {
                $this->_displayCmds[$tag] = isset($attributes['cmd']) ? $attributes['cmd'] : '';
            }
        }
        return $this->_displayCmds;
    }

    /**
     * Getter for {@link jobTags}
     * @return type
     */
    public function getJobTags() {
        if(!isset($this->_jobTags)) {
            $this->_jobTags = array_keys($this->jobs);
        }
        return $this->_jobTags;
    }

    /**
     * Function to retrieve default initial values for job attributes.
     * @param string $tag Tag of the job
     * @param string $index Index in the job configuration
     * @param mixed $ini Initial value if none specified
     * @return type
     */
    public function jobAttr($tag,$index,$ini=null){
        return isset($this->jobs[$tag][$index]) ? $this->jobs[$tag][$index] : $ini;
    }

    /**
     * Process form data and save in the cron table
     */
    public function save($formData){
        // Nothing to save
        if(!isset($formData[$this->name]))
            $jobs = array();
        else
            $jobs = $formData[$this->name];
        // Add/update all cron jobs for which there is form data present:
        foreach($jobs as $tag => $job) {
            if(is_array($job)) {
                if(in_array($tag, $this->jobTags)) {
                    $this->j[$tag] = CrontabUtil::processForm($job);
                    // Overwrite cmd/desc with the default as defined in the widget declaration/job config:
                    if(!$this->allowUserCmdInput){
                        $this->j[$tag]['cmd'] = $this->jobAttr($tag, 'cmd');
                        $this->j[$tag]['desc'] = $this->jobAttr($tag, 'desc');
                    }
                }
            }
        }
        
        // Delete any cron jobs not accounted for in form data, but expected:
        foreach($this->jobTags as $tag) {
            if(!isset($jobs[$tag]) && isset($this->j[$tag])) {
                unset($this->j[$tag]);
            }
        }

        // Save the cron table:
        CrontabUtil::arrayToCrontab($this->crontab, $this->j);
        $ctFile = implode(DIRECTORY_SEPARATOR, array(Yii::app()->basePath, '.crontab.tmp'));
        file_put_contents($ctFile, $this->crontab);
        $this->cmdU->run("crontab $ctFile")->complete();
        unlink($ctFile);
    }

    /**
     * Run/render a set of inputs for each cron job that this form will manage.
     * 
     * "enable_$tag" is the value stored in the cron job enablement checkbox.
     * "$tag" will be the form data loaded from crontab.
     */
    public function run() {
        // Script for toggle checkboxes:
        Yii::app()->clientScript->registerScript('cronForm', '
var toggleCronJobInputs = function(checkbox,initial) {
    var tag = checkbox.prop("id").replace("cron-job-","");
    if (typeof initial == "undefined")
        initial = false;
    if(checkbox.is(":checked")) {
        $("#"+checkbox.attr("id")+"-form").each(function(){
            if(initial) {
                $("#"+checkbox.attr("id")+"-form").show();
            } else {
                $("#"+checkbox.attr("id")+"-form").slideDown().find("input,select").prop("disabled",false);
                cronForm[cronForm._nameSpaces[tag]].setup();
            }
        });
    } else {
        $("#"+checkbox.attr("id")+"-form").each(function(){
            if(initial) {
                $(this).hide();
            } else {
                $(this).slideUp();
            }
            $(this).find("input,select").prop("disabled",true);
        });
    }
}
$(".cron-enabled").each(function() {
    toggleCronJobInputs($(this),1);
}).change(function() {
    toggleCronJobInputs($(this));
});
',CClientScript::POS_READY);

        // Render form fields for each cron job managed by this widget:
        CrontabUtil::$printHint = false;
        $jobSections = array();
        foreach($this->jobTags as $tag){
            // Populate initial form data.
            // 
            // The job is initially disabled if not found in the table
            $enabled = isset($this->j[$tag]);
            $this->formData[$tag] = CrontabUtil::cronJobToForm($enabled ? $this->j[$tag] : array());
            
            // Overwrite cmd/desc with default as defined in the widget declaration/job config:
            if(!$this->allowUserCmdInput) {
                $this->formData[$tag]['cmd'] = $this->jobAttr($tag,'cmd');
                $this->formData[$tag]['desc'] = $this->jobAttr($tag,'desc');
            }

            // Render the job form inputs for this particular job:
            $viewData = array();
            foreach(array('title', 'longdesc', 'instructions') as $attr){
                $viewData[$attr] = $this->jobAttr($tag, $attr);
            }
            $jobSections[] = $this->render(
                'application.components.views.cronJobForm', array_merge($viewData, array(

                'userCmd' => $this->allowUserCmdInput,
                'cmd' => $this->formData[$tag]['cmd'],
                'displayCmd' => isset($this->displayCmds[$tag])?$this->displayCmds[$tag]:'',
                'enabled' => $enabled,
                'labelClass' => $this->labelCssClass,
                'name' => $this->name,
                'tag' => $tag,
                'initialCron' => $this->formData[$tag],
            )),true);
        }
        echo implode($this->jobSeparator,$jobSections);
    }

    public function setDisplayCmds(array $value) {
        $this->_displayCmds = $value;
    }

    /**
     * Setter for {@link jobTags}
     * @param array $value
     */
    public function setJobTags(array $value) {
        $this->_jobTags = $value;
    }
}

?>
