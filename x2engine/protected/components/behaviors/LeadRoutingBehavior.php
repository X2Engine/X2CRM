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
 * Logic attributes/methods for lead distribution.
 * 
 * LeadRouting is a CBehavior that provides logic for simple or complex 
 * distribution of leads to users
 * @package application.components
 */
class LeadRoutingBehavior extends CBehavior {

    /**
     * Picks the next asignee based on the routing type
     * 
     * @return string Username that should be assigned the next lead
     */
    public function getNextAssignee($contact = null) {
        $admin = &Yii::app()->settings;
        $type = $admin->leadDistribution;
        if ($type == "") {
            return "Anyone";
        } elseif ($type == "evenDistro") { // legacy lead routing option
            return $this->evenDistro();
        } elseif ($type == "trueRoundRobin") {
            return $this->roundRobin();
        } elseif ($type == "customRoundRobin") {
            return $this->customRoundRobin($contact);
        } elseif ($type == 'singleUser') {
            return $this->singleUser();
        }
    }

    public function singleUser() {
        $admin = &Yii::app()->settings;
        $user = User::model()->findByPk($admin->rrId);
        if (isset($user)) {
            $username = $user->username;

            if (($admin->onlineOnly && !in_array($username,
                            Session::getOnlineUsers())) ||
                    !in_array($username,
                            Profile::model()->getUsernamesOfAvailableUsers())) {

                return 'Anyone';
            } else {
                return $username;
            }
        } else {
            return "Anyone";
        }
    }

    /**
     * Picks the next asignee for custom round robin lead routing rule.
     * @param mixed $contact null or Contacts model. If this is set, it will be used in place of 
     *  POST data for the purposes of testing routing rules.
     * @return mixed
     */
    public function customRoundRobin($contact = null) {
        if ($contact) {
            $arr = $contact->getAttributes();
        } else {
            $arr = $_POST;
            /* for new lead capture form:
              "Contacts" maps to an array of fields, check if this array exists and has fields,
              if so, set arr */
            if (isset($arr['Contacts']) && is_array($arr['Contacts']) &&
                    count($arr['Contacts']) > 0) {
                $arr = $arr['Contacts'];
            }
        }
        $users = $this->getRoutingRules($arr);
        if (!empty($users) && is_array($users) && count($users) > 1) {
            $rrId = $users[count($users) - 1];
            unset($users[count($users) - 1]);
            $i = $rrId % count($users);
            return $users[$i];
        } else {
            return "Anyone";
        }
    }

    /**
     * Legacy lead routing option. This can no longer be selected option from the lead routing
     * admin page.
     *
     * Picks the next asignee such that the resulting routing distribution 
     * would be even.
     * 
     * @return mixed
     */
    public function evenDistro() {
        $admin = &Yii::app()->settings;
        $online = $admin->onlineOnly;
        Session::cleanUpSessions();
        $usernames = array();
        $sessions = Session::getOnlineUsers();
        $users = X2Model::model('User')->findAll();
        foreach ($users as $user) {
            $usernames[] = $user->username;
        }

        if ($online == 1) {
            foreach ($usernames as $user) {
                if (in_array($user, $sessions)) $users[] = $user;
            }
        }else {
            $users = $usernames;
        }

        $users = array_values(array_intersect(Profile::model()->getUsernamesOfAvailableUsers(),
                        $users));

        $numbers = array();
        foreach ($users as $user) {
            if ($user != 'admin' && $user != 'api') {
                $actions = X2Model::model('Actions')->findAllByAttributes(array(
                    'assignedTo' => $user, 'complete' => 'No'));
                if (isset($actions)) $numbers[$user] = count($actions);
                else $numbers[$user] = 0;
            }
        }
        asort($numbers);
        reset($numbers);
        return key($numbers);
    }

    /**
     * Picks the next assignee in a round-robin manner.
     * 
     * Users get a chance to be picked in this manner only if online. In the
     * round-robin distribution of leads, the last person who was picked for
     * a lead assignment is stored using {@link updateRoundRobin()}. If no 
     * one is online, the lead will be assigned to "Anyone".
     * @return mixed 
     */
    public function roundRobin() {
        $admin = &Yii::app()->settings;
        $online = $admin->onlineOnly;
        Session::cleanUpSessions();
        $usernames = array();
        $sessions = Session::getOnlineUsers();
        $users = X2Model::model('User')->findAll();
        foreach ($users as $userRecord) {
            //exclude admin from candidates
            if ($userRecord->username != 'admin' && $userRecord->username != 'api')
                    $usernames[] = $userRecord->username;
        }
        if ($online == 1) {
            $userList = array();
            foreach ($usernames as $user) {
                if (in_array($user, $sessions)) $userList[] = $user;
            }
        }else {
            $userList = $usernames;
        }

        $userList = array_values(
                array_intersect(Profile::model()->getUsernamesOfAvailableUsers(),
                        $userList));

        $rrId = $this->getRoundRobin();
        if (count($userList) > 0) {
            $i = $rrId % count($userList);
            $this->updateRoundRobin();
            return $userList[$i];
        } else {
            return "Anyone";
        }
    }

    /**
     * Returns the round-robin state
     * @return integer
     */
    public function getRoundRobin() {
        $admin = &Yii::app()->settings;
        $rrId = $admin->rrId;
        return $rrId;
    }

    /**
     * Stores the round-robin state. 
     */
    public function updateRoundRobin() {
        $admin = &Yii::app()->settings;
        $admin->rrId = $admin->rrId + 1;
        $admin->save();
    }

    const WITHIN_GROUPS = 0;
    const BETWEEN_GROUPS = 1;

    /**
     * Obtains lead routing rules.
     * @param type $data
     * @return type 
     */
    public function getRoutingRules($data) {
        $admin = &Yii::app()->settings;
        $online = $admin->onlineOnly;
        Session::cleanUpSessions();
        $sessions = Session::getOnlineUsers();
        $criteria = new CDbCriteria;
        $criteria->order = "priority ASC";
        $rules = X2Model::model('LeadRouting')->findAll($criteria);
        foreach ($rules as $rule) {
            $arr = LeadRouting::parseCriteria($rule->criteria);
            $flagArr = array();
            foreach ($arr as $criteria) {
                if (isset($data[$criteria['field']])) {
                    $val = $data[$criteria['field']];
                    $operator = $criteria['comparison'];
                    $target = $criteria['value'];
                    if ($operator != 'contains') {
                        switch ($operator) {
                            case '>':
                                $flag = ($val >= $target);
                                break;
                            case '<':
                                $flag = ($val <= $target);
                                break;
                            case '=':
                                $flag = ($val == $target);
                                break;
                            case '!=':
                                $flag = ($val != $target);
                                break;
                            default:
                                $flag = false;
                        }
                    } else {
                        $flag = preg_match("/$target/i", $val) != 0;
                    }
                    $flagArr[] = $flag;
                }
            }
            if (!in_array(false, $flagArr) && count($flagArr) > 0) {
                $users = $rule->users;
                $users = explode(", ", $users);
                if (is_null($rule->groupType)) {
                    if ($online == 1)
                            $users = array_intersect($users, $sessions);
                }else {
                    $groups = $rule->users;
                    $groups = explode(", ", $groups);
                    $users = array();
                    foreach ($groups as $group) {
                        if ($rule->groupType == self::WITHIN_GROUPS) {
                            $links = GroupToUser::model()->findAllByAttributes(
                                    array('groupId' => $group));
                            foreach ($links as $link) {
                                $usernames[] = User::model()->findByPk($link->userId)->username;
                            }
                        } else { // $rule->groupType == self::BETWEEN_GROUPS
                            $users[] = $group;
                        }
                    }
                    if ($online == 1 && $rule->groupType == self::WITHIN_GROUPS) {
                        foreach ($usernames as $user) {
                            if (in_array($user, $sessions)) $users[] = $user;
                        }
                    }elseif ($rule->groupType == self::WITHIN_GROUPS) {
                        $users = $usernames;
                    }
                }

                if ($rule->groupType == self::WITHIN_GROUPS) {
                    $users = array_values(
                            array_intersect(
                                    Profile::model()->getUsernamesOfAvailableUsers(),
                                    $users));
                }

                $users[] = $rule->rrId;
                $rule->rrId++;
                $rule->save();
                return $users;
            }
        }
    }

}
