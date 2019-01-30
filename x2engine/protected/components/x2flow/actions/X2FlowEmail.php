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
 * Create Record action
 *
 * @package application.components.x2flow.actions
 */
class X2FlowEmail extends BaseX2FlowEmail {

    /**
     * Fields
     */
    public $title = 'Email';
    public $info = 'Creates and sends a new custom email or template.';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $parentRules = parent::paramRules();
        $parentRules['options'] = array_merge(
                $parentRules['options'], array(
            array(
                'name' => 'to',
                'label' => Yii::t('studio', 'To:') . '&nbsp;' .
                X2Html::hint2(
                        Yii::t('studio', 'Attribute replacement can also be used in this field (e.g., {assignedTo.emailAddress})')),
                'type' => 'email',
            ),            
            array(
                'name' => 'template',
                'label' => Yii::t('studio', 'Template'),
                'type' => 'dropdown',
                'defaultVal' => '',
                'options' => array('' => Yii::t('studio', 'Custom')) +
                Docs::getEmailTemplates('email', 'Contacts')
            ),
            array(
                'name' => 'subject',
                'label' => Yii::t('studio', 'Subject'),
                'optional' => 1
            ),
            array(
                'name' => 'cc',
                'label' => Yii::t('studio', 'CC:'),
                'optional' => 1,
                'type' => 'email'
            ),
            array(
                'name' => 'bcc',
                'label' => Yii::t('studio', 'BCC:'),
                'optional' => 1,
                'type' => 'email'
            ),
            array(
                'name' => 'logEmail',
                'label' =>
                Yii::t('studio', 'Log email?') . '&nbsp;' .
                X2Html::hint2(
                        Yii::t('studio', 'Checking this box will cause the email to be attached ' .
                                'to the record associated with this flow, if it exists.')),
                'optional' => 1,
                'defaultVal' => 1,
                'type' => 'boolean',
            ),
            array(
                'name' => 'body',
                'label' => Yii::t('studio', 'Message'),
                'optional' => 1,
                'type' => 'richtext'
            ),
                )
        );
        return $parentRules;
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $eml = new InlineEmail;

        // make subject optional in order to support legacy flows  
        $eml->requireSubjectOnCustom = false;
        $id = $this->config['id'];
        $options = &$this->config['options'];
        $eml->to = $this->parseOption('to', $params);

        $historyFlag = false;
        if (isset($params['model'])) {
            $historyFlag = $options['logEmail']['value'];
            $eml->targetModel = $params['model'];
        }
        if (isset($options['cc']['value']))
            $eml->cc = $this->parseOption('cc', $params);
        if (isset($options['bcc']['value'])) {
            $eml->bcc = $this->parseOption('bcc', $params);
        }

        $eml->credId = $this->parseOption('from', $params);
        if ($eml->credentials && $eml->credentials->user)
            $eml->setUserProfile($eml->credentials->user->profile);

        $eml->subject = $this->parseOption('subject', $params);

        // "body" option (deliberately-entered content) takes precedence over template
        if (isset($options['body']['value']) && !empty($options['body']['value'])) {
            $eml->scenario = 'custom';
            $eml->message = InlineEmail::emptyBody($this->parseOption('body', $params));
            $prepared = $eml->prepareBody();

//            $prepared = Docs::replaceVariables(
//                $prepared, $eml->targetModel, 
//                array('{signature}' => self::insertedPattern('signature', $eml->signature)));
            // $eml->insertSignature(array('<br /><br /><span style="font-family:Arial,Helvetica,sans-serif; font-size:0.8em">','</span>'));
        } elseif (!empty($options['template']['value'])) {
            $eml->scenario = 'template';
            $eml->template = $this->parseOption('template', $params);
            $prepared = $eml->prepareBody();
        } else {
            $prepared = true; // no email body
        }

        if (!$prepared) { // InlineEmail failed validation
            $errors = $eml->getErrors();
            return array(false, array_shift($errors));
        }

        list ($success, $message) = $this->checkDoNotEmailFields($eml);
        if (!$success) {
            return array($success, $message);
        }

        $result = $eml->send($historyFlag);
        if (isset($result['code']) && $result['code'] == 200) {
            if (!isset($params['sentEmails'])) {
                $params['sentEmails'] = array();
            }
            $params['sentEmails'][$id] = $eml->uniqueId;
            if (YII_UNIT_TESTING) {
                return array(true, $eml->message);
            } else {
                return array(true, "");
            }
        } else {
            return array(false, Yii::t('app', "Email could not be sent"));
        }
    }

}
