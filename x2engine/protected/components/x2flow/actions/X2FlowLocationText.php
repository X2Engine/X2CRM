<?php

/* * *********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 * ******************************************************************************** */

/**
 * X2FlowAction that creates a notification
 *
 * @package application.components.x2flow.actions
 */
class X2FlowLocationText extends BaseX2FlowLocation {

    public $title = 'Create Location-Based SMS';
    public $info = 'Create a text message based on specific location criteria. (Twilio Account Required)';
    public $flag = 't';

    public function paramRules() {
        $credentials = Credentials::getCredentialOptions(null, 'twoFactorCredentialsId', 'sms');
        
        $parentRules = parent::paramRules();
        $parentRules['options'] = array_merge(array(
            array(
                'name' => 'from',
                'label' => Yii::t('studio', 'Send As'),
                'type' => 'dropdown',
                'options' => $credentials['credentials']
            ),
                ), $parentRules['options']
        );
        return $parentRules;
    }

    public function execute(&$params) {
        $locations = $this->getNearbyUserRecords($params, $flag);
        
        if (count($locations) > 0) {
            $number = $this->formatPhoneNumber(
                    User::model()->findByAttributes(array(
                        'username' => $this->parseOption('to', $params)
                    ))->cellPhone
            );
            $message = $this->createLongMessage(
                    $params, $locations, PHP_EOL, false
            );
            foreach ($locations as $location) {
                $this->updateSeen($location, $this->flag);
            }
            return $this->sendSms($params, $number, $message);
        }
        
        return array(true, Yii::t('app', "No SMS to be sent"));
    }

    private function sendSms($params, $number, $text) {
        $from = Credentials::model()->findByPk($this->parseOption('from', $params));
        $twilio = Yii::app()->controller->attachBehavior('TwilioBehavior', new TwilioBehavior);
        $twilio->initialize(array(
            'sid' => $from->auth->sid,
            'token' => $from->auth->token,
            'from' => $from->auth->from,
        ));
        $twilio->sendSMSMessage($number, $text);
        return array(true, YII_UNIT_TESTING ? $text : "");
    }

    private function formatPhoneNumber($number) {
        return str_replace(' ', '', str_replace(')', '', str_replace('(', '', str_replace('-', '', $number))));
    }

}
