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
class X2FlowLocationEmail extends BaseX2FlowLocation {

    public $title = 'Create Location-Based Email';
    public $info = 'Create an email based on specific location criteria. (Email Account Required)';
    public $flag = 'e';

    public function paramRules() {
        $parentRules = parent::paramRules();
        $parentRules['options'] = array_merge(array(
            array(
                'name' => 'from',
                'label' => Yii::t('studio', 'Send As'),
                'type' => 'dropdown',
                'options' => $this->getCreditOps()
            ),
                ), $parentRules['options']
        );
        return $parentRules;
    }

    protected function getCreditOps() {
        if (Yii::app()->isInSession) {
            $credOptsDict = Credentials::getCredentialOptions(null, true);
            $credOpts = $credOptsDict['credentials'];
            $selectedOpt = $credOptsDict['selectedOption'];
            foreach ($credOpts as $key => $val) {
                if ($key == $selectedOpt) {
                    $credOpts = array($key => $val) + $credOpts;
                    break;
                }
            }
        } else {
            $credOpts = array();
        }
        return $credOpts;
    }

    public function execute(&$params) {
        $locations = $this->getNearbyUserRecords($params, $this->flag);
        
        if (count($locations) > 0) {
            $user = User::model()->findByAttributes(array(
                        'username' => $this->parseOption('to', $params)
                    ))->emailAddress;
            $message = $this->createLongMessage(
                    $params, $locations, '<br/>', true
            );
            foreach ($locations as $location) {
                $this->updateSeen($location, $this->flag);
            }
            return $this->sendEmail(
                            $params, $user, 'X2CRM Location Notification', $message
            );
        }
        
        return array(true, Yii::t('app', "No email to be sent"));
    }

    protected function sendEmail(&$params, $to, $subject, $text) {
        $options = &$this->config['options'];
        $historyFlag = false;

        $email = $this->prepareEmail($params, $to, $subject, $text);
        list ($success, $message) = $this->checkDoNotEmailFields($email);

        if (!$email->prepareBody()) {
            return array(false, array_shift($email->getErrors()));
        } else if (!$success) {
            return array($success, $message);
        } else if (isset($params['model'])) {
            $historyFlag = $options['logEmail']['value'];
            $email->targetModel = $params['model'];
        }

        $result = $email->send($historyFlag);
        
        if (isset($result['code']) && $result['code'] == 200) {
            if (!isset($params['sentEmails'])) {
                $params['sentEmails'] = array();
            }
            $params['sentEmails'][$this->config['id']] = $email->uniqueId;
            return array(true, YII_UNIT_TESTING ? $email->message : "");
        }
        
        return array(false, Yii::t('app', "Email could not be sent"));
    }

    protected function prepareEmail(&$params, $to, $subject, $text) {
        $email = new InlineEmail;

        // make subject optional in order to support legacy flows
        $email->requireSubjectOnCustom = false;
        $email->to = $to;
        $email->subject = $subject;
        $email->scenario = 'custom';
        $email->message = InlineEmail::emptyBody($text);

        $email->credId = $this->parseOption('from', $params);
        if ($email->credentials && $email->credentials->user) {
            $email->setUserProfile($email->credentials->user->profile);
        }
        return $email;
    }

    protected function checkDoNotEmailFields(InlineEmail $eml) {
        if (Yii::app()->settings->x2FlowRespectsDoNotEmail &&
                !$eml->checkDoNotEmailFields()) {
            return array(
                false, Yii::t('studio', 'Email could not be sent because at least one of the ' .
                        'addressees has their "Do not email" attribute checked'));
        }
        return array(true, '');
    }

}
