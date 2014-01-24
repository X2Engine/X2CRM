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
 *
 * @property integer $campaignSize The total number of items in the campaign.
 * @property array $listItems The list item IDs for this campaign (that haven't
 *  already been sent)
 * @property integer $sentCount Number of emails sent already (or undeliverable)
 * @package X2CRM.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailProgressControl extends MarketingModuleWidget {

    private $_listItems;
    private $_sentCount;
    public $campaign;

    public function getCampaignSize() {
        return count($this->listItems) + $this->sentCount;
    }

    public function getListItems() {
        if(!isset($this->_listItems,$this->_sentCount)) {
            $allListItems = CampaignMailingBehavior::deliverableItems($this->campaign->listId);
            $this->_listItems = array();
            $this->_sentCount = 0;
            foreach($allListItems as $listItem) {
                if($listItem['sent'] == 0) 
                    $this->_listItems[] = $listItem['id'];
                else
                    $this->_sentCount++;
            }
        }
        return $this->_listItems;
    }

    public function getSentCount() {
        if(!isset($this->_sentCount)) {
            $this->getListItems(); // This will set _sentCount
        }
        return $this->_sentCount;
    }

    public function init() {
        parent::init();
        $admin = Yii::app()->params->admin;
        $totalEmails = count($this->listItems) + $this->sentCount;

        Yii::app()->clientScript->registerCssFile($this->module->assetsUrl.'/css/emailProgressControl.css');
        Yii::app()->clientScript->registerScriptFile($this->module->assetsUrl.'/js/emailProgressControl.js');
        Yii::app()->clientScript->registerScript('emailProgressControl-vars','
            x2.emailProgressControl.sentCount = '.$this->sentCount.';
            x2.emailProgressControl.totalEmails = '.$totalEmails.';
            x2.emailProgressControl.listItems = '.json_encode($this->listItems).';
            x2.emailProgressControl.sendUrl = '.json_encode(Yii::app()->controller->createUrl('/marketing/marketing/mailIndividual')).';
            x2.emailProgressControl.campaignId = '.$this->campaign->id.';
            // Translation messages for the controls that will be dynamically updated
            x2.emailProgressControl.text = '.json_encode(array(
                'Resume' => Yii::t('campaign','Resume'),
                'Pause' => Yii::t('campaign','Pause'),
                'Could not send email due to an error in the request to the server.' => Yii::t('campaign','Could not send email due to an error in the request to the server.'),
            )).';
            x2.emailProgressControl.init();

            // Make the "stop" button wait for the currently-sending email to complete.
            $("#campaign-toggle-button").bind("click",function(event){
                event.preventDefault();
                var that = this;
                if(x2.emailProgressControl.paused) {
                    $(that).parents("form").submit();
                } else {
                    x2.emailProgressControl.afterSend = function() {
                        $(that).parents("form").submit();
                    }
                }
            });

            // Ask the user if they would really like to cancel the current campaign
            $("#campaign-complete-button").bind("click.confirm",function(event){
                event.preventDefault();
                var that = this;
                var proceed = x2.emailProgressControl.listItems.length == 0;
                if(!proceed)
                    proceed = confirm('.json_encode(Yii::t('marketing','You have unsent mail in this campaign. Are you sure you want to forcibly mark this campaign as complete?')).');
                if(proceed) {
                    if(x2.emailProgressControl.paused) {
                        $(that).parents("form").submit();
                    } else {
                        x2.emailProgressControl.afterSend = function() {
                            $(that).parents("form").submit();
                        }
                    }
                } else {
                    x2.emailProgressControl.afterSend = function(){};
                }
            });

            // And now finally:
            if(x2.emailProgressControl.listItems.length > 0)
                x2.emailProgressControl.start();
            else
                x2.emailProgressControl.pause();
        ',CClientScript::POS_READY);
    }

    public function run() {

        $this->render('emailProgressControl');
    }
}

?>
