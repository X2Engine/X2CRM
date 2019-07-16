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
 * X2FlowAction that creates a notification
 *
 * @package application.components.x2flow.actions
 */
class X2FlowCreateNotif extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Create Notification';
    public $info = 'Creates a new notification.';

    /**
     * Parameter rules
     * 
     * @return type
     */
    public function paramRules() {
        $assignmentOptions = array(
            '{assignedTo}' => '{' . Yii::t('studio', 'Owner of Record') . '}',
            '{user.username}' => '{' . Yii::t('studio', 'Current User') . '}'
                ) + X2Model::getAssignmentOptions(false, false);

        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'user',
                    'label' => Yii::t('studio', 'User'),
                    'type' => 'assignment',
                    'options' => $assignmentOptions
                ),
                array(
                    'name' => 'text',
                    'label' => Yii::t('studio', 'Message'),
                    'type' => 'text',
                    'optional' => 1
                ),
        )));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        // Creates notificaton
        $notif = new Notification;
        $notif->user = $this->parseOption('user', $params);
        $notif->createdBy = 'API';
        $notif->createDate = time();
        $notif->type = 'custom';
        $notif->text = $this->parseOption('text', $params);

        // Saves and checks for errors
        if ($notif->save()) {
            return array(true, "");
        } else {
            $errors = $notif->getErrors();
            return array(false, array_shift($errors));
        }
    }

}
