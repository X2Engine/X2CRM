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

/**
 * Widget class for displaying all available inline actions.
 *
 * Displays tabs for "log a call","new action" and the like.
 *
 * @package application.components
 */
class Publisher extends X2Widget {

    public $model;
    public $associationType; // type of record to associate actions with
    public $associationId = ''; // record to associate actions with
    public $assignedTo = null; // user actions will be assigned to by default

    public $viewParams = array(
        'model',
        'associationId',
        'associationType',
    );

    protected $_packages;
    private $_tabs; // available tabs with tab titles
    private $_hiddenTabs;

    public function getTabs () {
        if (!isset ($this->_tabs)) {
            $visibleTabs = array_filter (Yii::app()->settings->actionPublisherTabs,
                function ($shown) {
                    return $shown; 
                });
            $this->_tabs = array ();
            foreach ($visibleTabs as $tabName => $shown) {
                $this->_tabs[] = new $tabName ();
            }
        }
        return $this->_tabs;
    }

    public function setTabs ($tabs) {
        $this->_tabs = $tabs;
    }

    /**
     * Magic getter. Returns this widget's packages. 
     */
    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array (
                'auxlib' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/auxlib.js',
                    ),
                ),
                'PublisherJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/publisher/Publisher.js',
                    ),
                    'depends' => array ('auxlib')
                ),
                'MultiRowTabsJS' => array(
                    'baseUrl' => Yii::app()->request->baseUrl,
                    'js' => array(
                        'js/MultiRowTabs.js',
                    ),
                ),
            );
        }
        return $this->_packages;
    }

    public function run() {
        $model = new Actions;
        $model->associationType = $this->associationType;
        $model->associationId = $this->associationId;
        if($this->assignedTo) {
            $model->assignedTo = $this->assignedTo;
        } else {
            $model->assignedTo = Yii::app()->user->getName();
        }
        
        $this->model = $model;
        $tabs = $this->tabs;
        $selectedTab = $this->tabs[0]->tabId;
        $selectedTabObj = $this->tabs[0];
        $selectedTabObj->startVisible = true;

        Yii::app()->clientScript
            ->registerCoreScript('jquery')
            ->registerCoreScript('jquery.ui');
        Yii::app()->clientScript->registerPackages($this->packages);

        Yii::app()->clientScript->registerScript('publisherScript',"
        ;(function () {
            // construct publisher object, passing tab objects to it
            x2.publisher = new x2.Publisher ({
                translations: {},
                initTabId: '".$selectedTab."',
                publisherCreateUrl: '".
                    Yii::app()->controller->createUrl ('/actions/actions/publisherCreate')."'
            });
            x2.publisher.isCalendar = ".json_encode($this->calendar).";

            x2.publisher.loadFrame = function (id,type){
                if(type!='Action' && type!='QuotePrint'){
                    var frame=
                        '<iframe style=\"width:99%;height:99%\" ' +
                          'src=\"".(Yii::app()->controller->createUrl('/actions/actions/viewEmail')).
                            "?id='+id+'\"></iframe>';
                }else if(type=='Action'){
                    var frame=
                        '<iframe style=\"width:99%;height:99%\" ' +
                          'src=\"".(Yii::app()->controller->createUrl('/actions/actions/viewAction')).
                            "?id='+id+'&publisher=true\"></iframe>';
                } else if(type=='QuotePrint'){
                    var frame=
                        '<iframe style=\"width:99%;height:99%\" ' +
                          'src=\"".(Yii::app()->controller->createUrl('/quotes/quotes/print')).
                            "?id='+id+'\"></iframe>';
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

        }) ();
        ", CClientScript::POS_END);

        Yii::app()->clientScript->registerScript('loadEmails', "

        $(document).on('ready',function(){
            var timeout;
            $(document).on('mouseenter','.email-frame',function(){
                var id=$(this).attr('id');
                timeout = setTimeout(function(){x2.publisher.loadFrame(id,'Email')},500);
            });
            $(document).on('mouseleave','.email-frame',function(){
                clearTimeout(timeout);
            });
            $(document).on ('mouseenter', '.quote-frame', function(){
                var id=$(this).attr('id');
                timeout = setTimeout(function(){x2.publisher.loadFrame(id,'Quote')},500);
            }).mouseleave(function(){
                clearTimeout(timeout);
            }); // Legacy quote pop-out view

            $('.quote-print-frame').mouseenter(function(){
                var id=$(this).attr('id');
                timeout = setTimeout(function(){x2.publisher.loadFrame(id,'QuotePrint')},500);
            }).mouseleave(function(){
                clearTimeout(timeout);
            }); // New quote pop-out view
        });
        ", CClientScript::POS_HEAD);
        Yii::app()->clientScript->registerCss('recordViewPublisherCss', '
            .action-event-panel {
                margin-top: 5px;
            }
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
            #publisher .text-area-wrapper {
                /*margin-right: 75px;*/
            }
        ');

        $that = $this;
        $this->render(
            'application.components.views.publisher.publisher',
            array_merge (
                array_combine(
                    $this->viewParams,
                    array_map(function($p)use($that){return $that->$p;}, $this->viewParams)
                ),
                array (
                    'tabs' => $tabs,
                )
            )
        );
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
    public $calendar = false; 
    public $hideTabs = array ();
    public $selectedTab = '';


}
