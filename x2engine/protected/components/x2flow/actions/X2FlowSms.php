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
 * X2FlowAction that sends an SMS via Twilio
 *
 * @package application.components.x2flow.actions
 */
class X2FlowSms extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Send SMS';
    public $info = 'Sends a new SMS to specific phone number (Twilio Account Required)';

    /**
     * Formats a provided phone number to just integers
     * 
     * @param type $number
     * @return type
     */
    private function formatPhoneNumber($number) {
        return str_replace(array(' ', ')', '(', '-'), '', $number);
    }

    /**
     * Sets parameter rules for action
     * 
     * @return type
     */
    public function paramRules() {
        $credentials = Credentials::getCredentialOptions(null, 'twoFactorCredentialsId', 'sms');

        return array_merge(
                parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'from',
                    'label' => Yii::t('studio', 'Send As'),
                    'type' => 'dropdown',
                    'options' => $credentials['credentials']
                ),
                array(
                    'name' => 'to',
                    'label' => 'Send To (Phone number)',
                ),
                array(
                    'name' => 'message',
                    'label' => 'Message',
                    'type' => 'text',
                ),
            )
                )
        );
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $to = $this->formatPhoneNumber($this->parseOption('to', $params, false));
        $message = $this->parseOption('message', $params);
        $from = Credentials::model()->findByPk($this->parseOption('from', $params));
        $twilio = Yii::app()->controller->attachBehavior('TwilioBehavior', new TwilioBehavior);
        $twilio->initialize(array(
            'sid' => $from->auth->sid,
            'token' => $from->auth->token,
            'from' => $from->auth->from,
        ));
        $twilio->sendSMSMessage($to, $message);
        return array(true, YII_UNIT_TESTING ? $message : "");
    }

}
