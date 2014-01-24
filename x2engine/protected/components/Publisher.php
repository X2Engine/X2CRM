<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

/**
 * Widget class for displaying all available inline actions.
 *
 * Displays tabs for "log a call","new action" and the like.
 *
 * @package X2CRM.components
 */
class Publisher extends X2Widget {

    protected $allTabs = array(
        'log-a-call',
        'log-time-spent',
        'new-action',
        'new-comment'
    );
    private $_hiddenTabs;

    public $associationType;        // type of record to associate actions with
    public $associationId = '';        // record to associate actions with
    public $assignedTo = null;    // user actions will be assigned to by default
    public $selectedTab = 'log-a-call';

    public $calendar = false;
    public $hideTabs = array();
    public $model;

    public $viewParams = array(
        'model',
        'associationId',
        'associationType',
        'calendar',
        'associationType',
        'hiddenTabs'
    );

    public function getHiddenTabs() {
        if(!isset($this->_hiddenTabs)) {
            $this->_hiddenTabs = array();
            $hiddenTabs = array_flip($this->hideTabs);
            foreach($this->allTabs as $tab) {
                $this->_hiddenTabs[$tab] = isset($hiddenTabs[$tab]);
            }
        }
        return $this->_hiddenTabs;
    }


    public function run() {
        $model = new Actions;
        $model->associationType = $this->associationType;
        $model->associationId = $this->associationId;
        if($this->assignedTo)
            $model->assignedTo = $this->assignedTo;
        else
            $model->assignedTo = Yii::app()->user->getName();

        Yii::app()->clientScript->registerScript('loadEmails', "
            /**
             * Ad-hoc quasi-validation for the publisher
             */
            x2.publisher.beforeSubmit = function() {
                if(x2.publisher.getElement('#action-description').val() == '') {
                    alert('".addslashes(Yii::t('actions', 'Please enter a description.'))."');
                    return false;
                }
                return true; // form is sane: submit!
            }

            //
            x2.publisher.loadFrame = function (id,type){
                if(type!='Action' && type!='QuotePrint'){
                    var frame='<iframe style=\"width:99%;height:99%\" src=\"".(Yii::app()->controller->createUrl('/actions/actions/viewEmail'))."?id='+id+'\"></iframe>';
                }else if(type=='Action'){
                    var frame='<iframe style=\"width:99%;height:99%\" src=\"".(Yii::app()->controller->createUrl('/actions/actions/viewAction'))."?id='+id+'&publisher=true\"></iframe>';
                } else if(type=='QuotePrint'){
                    var frame='<iframe style=\"width:99%;height:99%\" src=\"".(Yii::app()->controller->createUrl('/quotes/quotes/print'))."?id='+id+'\"></iframe>';
                }
                if(typeof x2.actionFrames.viewEmailDialog != 'undefined') {
                    if($(x2.actionFrames.viewEmailDialog).is(':hidden')){
                        $(x2.actionFrames.viewEmailDialog).remove();

                    }else{
                        return;
                    }
                }

                x2.actionFrames.viewEmailDialog = $('<div></div>', {id: 'x2-view-email-dialog'});

                x2.actionFrames.viewEmailDialog.dialog({
                    title: '".Yii::t('app', 'View History Item')."',
                    autoOpen: false,
                    resizable: true,
                    width: '650px',
                    show: 'fade'
                });
                jQuery('body')
                    .bind('click', function(e) {
                        if(jQuery('#x2-view-email-dialog').dialog('isOpen')
                            && !jQuery(e.target).is('.ui-dialog, a')
                            && !jQuery(e.target).closest('.ui-dialog').length
                        ) {
                            jQuery('#x2-view-email-dialog').dialog('close');
                        }
                    });

                x2.actionFrames.viewEmailDialog.data('inactive', true);
                if(x2.actionFrames.viewEmailDialog.data('inactive')) {
                    x2.actionFrames.viewEmailDialog.append(frame);
                    x2.actionFrames.viewEmailDialog.dialog('open').height('400px');
                    x2.actionFrames.viewEmailDialog.data('inactive', false);
                } else {
                    x2.actionFrames.viewEmailDialog.dialog('open');
                }
            };
            
            $(document).on('ready',function(){
                var t;
                $(document).on('mouseenter','.email-frame',function(){
                    var id=$(this).attr('id');
                    t=setTimeout(function(){x2.publisher.loadFrame(id,'Email')},500);
                });
                $(document).on('mouseleave','.email-frame',function(){
                    clearTimeout(t);
                });
                $('.quote-frame').mouseenter(function(){
                    var id=$(this).attr('id');
                    t=setTimeout(function(){x2.publisher.loadFrame(id,'Quote')},500);
                }).mouseleave(function(){
                    clearTimeout(t);
                }); // Legacy quote pop-out view

                $('.quote-print-frame').mouseenter(function(){
                    var id=$(this).attr('id');
                    t=setTimeout(function(){x2.publisher.loadFrame(id,'QuotePrint')},500);
                }).mouseleave(function(){
                    clearTimeout(t);
                }); // New quote pop-out view
            });
        ", CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerCss('recordViewPublisherCss', '
            #action-event-panel {
                margin-top: 5px;
            }
            #action-duration {
                margin-right: 10px;
            }
            #action-duration .action-duration-display {
                font-size: 30px;
                font-family: Consolas, monaco, monospace;
            }
            #action-duration input {
                width: 50px;
            }
            #action-duration .action-duration-input {
                display:inline-block;
            }
            #action-duration label {
                font-size: 10px;
            }
        ');

        if($this->calendar){
            Yii::app()->clientScript->registerCss('calendarSpecificWidgetStyle', "
        .publisher-widget-title {
            color: #222;
            font-weight: bold;
        }
        .publisher-first-row {
            margin-top: 8px;
        }
        #publisher-form .form {
            background: #eee;
        }
        #publisher-form textarea {
            min-width: 100%;
            max-width: 100%;
            width: 100%;
        }
    ");
        } else {
            Yii::app()->clientScript->registerCss('genericPublisherWidgetStyle', "
        #publisher .text-area-wrapper {
            margin-right: 75px;
        }
    ");
        }

        if($this->calendar){
            // set date, time, and region format for when javascript replaces datetimepicker
            // datetimepicker is replaced in the calendar module when the user clicks on a day
            $dateformat = Formatter::formatDatePicker('medium');
            $timeformat = Formatter::formatTimePicker();
            $ampmformat = Formatter::formatAMPM();
            $region = Yii::app()->locale->getLanguageId(Yii::app()->locale->getId());
            if($region == 'en')
                $region = '';
        }

        // save default values of fields for when the publisher is submitted and then reset
        Yii::app()->clientScript->registerScript('defaultValues', '
    x2.publisher.isCalendar = '.($this->calendar ? 'true' : 'false').';
    '.($this->calendar ?'
    // Enable fields for the calendar event publisher:
    x2.publisher.switchToTab("new-event");
    ':'
    // Turn on jquery tabs for the publisher:
    $("#publisher").tabs({
        activate: function(event, ui) { x2.publisher.tabSelected(event, ui); },
    });
    // "Quick note" menu event handler:
    $(document).on("change","#quickNote2",function(){
        $("#action-description").val($(this).val());
    });
    x2.publisher.switchToTab("'.$this->selectedTab.'");
    ').'
    if($("#publisher .ui-state-active").length !== 0) { // if publisher is present (prevents a javascript error if publisher is not present)
        var selected = $("#publisher .ui-state-active").attr("aria-controls");
        x2.publisher.switchToTab(selected);
    }

    $(x2.publisher.resetFieldsSelector).each(function(i) {
        $(this).data("defaultValue", $(this).val());
    });

    $("#publisher-form input[type=checkbox]").each(function(i) {
        $(this).data("defaultValue", $(this).is(":checked"));
    });

    // Highlight save button when something is edited in the publisher
    $("#publisher-form input, #publisher-form select, #publisher-form textarea, #publisher").
        bind("focus.compose", function(){

        $("#save-publisher").addClass("highlight");

        // Expand text area; expecting user input.
        if(this.nodeName == "TEXTAREA" || this.nodeName == "DIV") 
            $("#publisher-form textarea").height(80);

        $(document).unbind("click.publisher").bind("click.publisher",function(e) {
            if(!$(e.target).closest ("#publisher-form, .ui-datepicker, .fc-day").length && 
               $("#publisher-form textarea").val() === "") {

                $("#save-publisher").removeClass("highlight");
                $("#publisher-form textarea").animate({"height":22},300);
            }
        });

        return false;
    });

    '.($this->calendar?"
    // position the saving icon for the publisher (which starts invisible)
    var publisherLabelCenter = parseInt($('.publisher-label').css('width'), 10)/2;
    var halfIconWidth = parseInt($('#publisher-saving-icon').css('width'), 10)/2;
    var iconLeft = publisherLabelCenter - halfIconWidth;
    $('#publisher-saving-icon').css('left', iconLeft + 'px');

    // set date and time format for when datetimepicker is recreated
    $('#publisher-form').data('dateformat', '$dateformat');
    $('#publisher-form').data('timeformat', '$timeformat');
    $('#publisher-form').data('ampmformat', '$ampmformat');
    $('#publisher-form').data('region', '$region');
    ":"")."
", CClientScript::POS_READY);

        $that = $this;
        $this->model = $model;
        $this->render('publisher',array_combine($this->viewParams,array_map(function($p)use($that){return $that->$p;},$this->viewParams)));
    }

    //////////////////////////////////////////////////////////////
    // BACKWARDS COMPATIBILITY FUNCTIONS FOR OLD CUSTOM MODULES //
    //////////////////////////////////////////////////////////////

    /**
     * Old Publisher had "halfWidth" property
     */
    public function setHalfWidth($value) {
        $this->calendar = !$value;
    }

}
