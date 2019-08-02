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
 *
 * @property integer $campaignSize The total number of items in the campaign.
 * @property array $listItems The list item IDs for this campaign (that haven't
 *  already been sent)
 * @property integer $sentCount Number of emails sent already (or undeliverable)
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class EmailProgressControl extends X2Widget {

    private $_listItems;
    private $_sentCount;
    public $campaign;
    public $JSClass = 'EmailProgressControl'; 


    public function getCampaignSize() {
        return count($this->listItems) + $this->sentCount;
    }

    public function getListItems() {
        if(!isset($this->_listItems,$this->_sentCount)) {
            $allListItems = CampaignMailingBehavior::deliverableItems($this->campaign->list->id);
            $this->_listItems = array();
            $this->_sentCount = 0;
            foreach($allListItems as $listItem) {
                if($listItem['sent'] == 0 && $listItem['suppressed'] == 0)
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

    public function getPackages () {
        if (!isset ($this->_packages)) {
            $this->_packages = array_merge (parent::getPackages(), array (
                'emailProgressControl' => array(
                    'baseUrl' => $this->module->assetsUrl, 
                    'css' => array (
                        '/css/emailProgressControl.css'
                    ),
                    'js' => array(
                       '/js/emailProgressControl.js'
                    ),
                ))
            );
        }
        return $this->_packages;
    }

    public function getJSClassParams () {
        $totalEmails = count($this->listItems) + $this->sentCount;

        if (!isset ($this->_JSClassParams)) {
            $this->_JSClassParams = array_merge ( parent::getJSClassParams(),
                array(
                    'sentCount' => $this->sentCount, 
                    'totalEmails' => $totalEmails,
                    'listItems' => $this->listItems,
                    'sendUrl' => Yii::app()->controller->createUrl ('/marketing/marketing/mailIndividual'),
                    'campaignId' => $this->campaign->id,
                    'paused' => !(isset($_GET['launch']) && $_GET['launch'])
                )
            );
        }
        return $this->_JSClassParams;
    }

    public function run() {
        $this->render('emailProgressControl');
        $this->registerPackages ();
        $this->instantiateJSClass(true);
    }

    public function getTranslations() {
        return array(
            'confirm' => Yii::t('marketing', 'You have unsent mail in this campaign. Are you sure you want to forcibly mark this campaign as complete?'),
            'pause' => Yii::t('marketing', 'Pause'),
            'complete' => Yii::t('marketing', 'Email Delivery Complete'),
            'resume' => Yii::t('marketing', 'Resume'),
            'error' => Yii::t('marketing', 'Could not send email due to an error in the request to the server.')
        );
    }
}

?>
