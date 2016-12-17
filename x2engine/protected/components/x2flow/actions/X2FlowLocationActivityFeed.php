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
class X2FlowLocationActivityFeed extends BaseX2FlowLocation {

    public $title = 'Create Location-Based Activity Feed Post';
    public $info = 'Create an Activity Feed post based on specific location criteria.';
    public $flag = 'a';

    public function paramRules() {
        return parent::paramRules();
    }

    public function execute(&$params) {
        $locations = $this->getNearbyUserRecords($params, $this->flag);

        if (count($locations) > 0) {
            $user = $this->parseOption('to', $params);
            $message = $this->createLongMessage(
                    $params, $locations, '<br/>', true
            );
            foreach ($locations as $location) {
                $this->updateSeen($location, $this->flag);
            }
            return $this->createActivityFeedEvent($params, $user, $message);
        }

        return array(true, "No post to be sent");
    }

    private function createActivityFeedEvent(&$params, $user, $text) {
        $event = new Events;

        $event->user = $user;
        $event->type = 'feed';
        $event->subtype = $this->parseOption('type', $params);
        $event->text = $text;
        $event->visibility = 0;

        if ($event->save()) {
            return array(true, "");
        }
        
        $errors = $event->getErrors();
        return array(false, array_shift($errors));
    }

}
