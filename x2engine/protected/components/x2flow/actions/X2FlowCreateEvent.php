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
 * X2FlowAction that creates an event
 *
 * @package application.components.x2flow.actions
 */
class X2FlowCreateEvent extends X2FlowAction {

    /**
     * Fields
     */
    public $title = 'Post to Activity Feed';
    public $info = 'Creates a new activity feed post.';

    /**
     * Parameter rules
     * 
     * @return array
     */
    public function paramRules() {
        $eventTypes = Dropdowns::getItems(113, 'studio');

        return array_merge(parent::paramRules(), array(
            'title' => Yii::t('studio', $this->title),
            'info' => Yii::t('studio', $this->info),
            'options' => array(
                array(
                    'name' => 'type',
                    'label' => Yii::t('studio', 'Post Type'),
                    'type' => 'dropdown',
                    'options' => $eventTypes
                ),
                array(
                    'name' => 'text',
                    'label' => Yii::t('studio', 'Text'),
                    'type' => 'text'
                ),
                array(
                    'name' => 'visibility',
                    'label' => Yii::t('studio', 'Visibility'),
                    'type' => 'dropdown',
                    'options' => array(
                        1 => Yii::t('admin', 'Public'),
                        0 => Yii::t('admin', 'Private'),
                    ),
                    'defaultVal' => 1
                ),
                array(
                    'name' => 'feed',
                    'optional' => 1,
                    'label' => 'User (optional)',
                    'type' => 'dropdown',
                    'options' => array(
                '' => '----------',
                'auto' => 'Auto'
                    ) + X2Model::getAssignmentOptions(false, false)
                ),
                array(
                    'name' => 'user',
                    'optional' => 1,
                    'label' => 'Author',
                    'type' => 'dropdown',
                    'options' => array(
                'admin' => 'admin',
                'auto' => Yii::t('studio', 'Auto'),
                    ) + array_diff_key(
                            X2Model::getAssignmentOptions(false, false), array('admin' => '')
                    ),
                    'defaultVal' => 'admin',
                ),
                array(
                    'name' => 'createNotif',
                    'label' => Yii::t('studio', 'Create Notification?'),
                    'type' => 'boolean',
                    'defaultVal' => true
                ),
            )
        ));
    }

    /**
     * Executes action
     * 
     * @param array $params
     * @return array
     */
    public function execute(&$params) {
        $options = &$this->config['options'];

        $event = new Events;
        $notif = new Notification;

        $user = $this->parseOption('feed', $params);
        $author = $this->parseOption('user', $params);

        $type = $this->parseOption('type', $params);
        $visibility = $this->parseOption('visibility', $params);

        $text = $this->parseOption('text', $params);

        $notif->type = 'custom';
        $notif->text = $text;

        $event->type = 'feed';
        $event->subtype = $type;
        $event->text = $text;
        $event->visibility = $visibility;

        if ($author == 'auto' && isset($params['model']) &&
                $params['model']->hasAttribute('assignedTo') &&
                !empty($params['model']->assignedTo)) {

            $event->user = $params['model']->assignedTo;
        } else {
            $event->user = $author;
        }

        if (!empty($user)) {
            if ($user == 'auto' && isset($params['model']) &&
                    $params['model']->hasAttribute('assignedTo') &&
                    !empty($params['model']->assignedTo)) {

                $associatedUser = $params['model']->assignedTo;
            } else {
                $associatedUser = $user;
            }
            $associatedUser = User::model()->findByAttributes(array(
                'username' => $associatedUser
            ));
            if ($associatedUser) {
                $event->associationType = 'User';
                $event->associationId = $associatedUser->id;
                $notif->modelType = 'Profile';
                $notif->modelId = $event->associationId;
                $notif->type = 'social_post';
                $notif->createdBy = $event->user;
                $notif->user = $associatedUser->username;
            }
        }

        if (!$this->parseOption('createNotif', $params)) {
            if (!$notif->save()) {
                $errors = $notif->getErrors();
                return array(false, array_shift($errors));
            }
        }

        if ($event->save()) {
            return array(true, "");
        } else {
            $errors = $event->getErrors();
            return array(false, array_shift($errors));
        }
    }

}
